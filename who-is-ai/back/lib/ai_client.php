
<?php
/**
 * WHO IS AI?
 * Gemini APIによる回答・絵文字生成
 *
 * AIも人間と同じ条件で、
 * カテゴリー別の12個から絵文字3個を選ぶ。
 */

/**
 * @param string $question お題
 * @param array|null $emojiPool AIに配布する12個
 * @return array 回答・選択絵文字・候補12個
 */
function generateAiAnswer(
    string $question,
    ?array $emojiPool = null
): array {

    $question = trim($question);

    if ($question === '') {
        throw new Exception(
            'お題が入力されていません。'
        );
    }

    // 絵文字候補生成用ファイル
    require_once __DIR__ . '/emoji_pool.php';

    // 候補を指定していない場合は新規生成
    if ($emojiPool === null) {
        $emojiPool = generateEmojiPool();
    }

    // 12個・重複なしを確認
    if (
        count($emojiPool) !== 12 ||
        count(array_unique($emojiPool)) !== 12
    ) {
        throw new Exception(
            'AI用の絵文字候補が不正です。'
        );
    }

    foreach ($emojiPool as $emoji) {
        if (!is_string($emoji) || $emoji === '') {
            throw new Exception(
                '絵文字候補に不正な値があります。'
            );
        }
    }

    // APIキー読み込み
    $configPath =
        __DIR__ . '/../config/gemini_config.php';

    if (!is_file($configPath)) {
        throw new Exception(
            'Geminiの設定ファイルがありません。'
        );
    }

    $config = require $configPath;

    $apiKey = trim(
        (string)($config['api_key'] ?? '')
    );

    if ($apiKey === '') {
        throw new Exception(
            'Gemini APIキーが設定されていません。'
        );
    }

    // Geminiに渡す候補をJSONにする
    $emojiList = json_encode(
        $emojiPool,
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );

    // Geminiへの指示
    $prompt = <<<PROMPT
あなたは「WHO IS AI?」というゲームに参加している
普通の日本の大学生です。

人間とAIが同じお題に答え、
プレイヤーがどれがAIなのかを推理するゲームです。

【今回のお題】
{$question}

【回答のルール】
・必ず今回のお題に直接答える
・普通の大学生が友達に話すような自然な日本語にする
・基本的に5〜20文字程度
・最大30文字以内
・回答は短い1文だけ
・長い理由や説明は禁止
・大げさな表現を避ける
・無理に面白いことを言わない
・少しラフな口調でもよい
・回答文には絵文字を入れない
・前置きや解説は書かない

【あなたに配られた12個の絵文字】
{$emojiList}

【絵文字選択のルール】
・回答を決めてから絵文字を選択する
・上記の12個だけを使用する
・12個以外の絵文字は絶対に使わない
・回答に関連する絵文字を3個選ぶ
・同じ絵文字を重複して選ばない
・絵文字の順番は自由
・無理に分かりやすい組み合わせにしなくてもよい
・人間が限られた候補から選ぶように考える
・回答そのものを直接表現できなくてもよい
・少し遠回しな連想でもよい
・文章や数字を絵文字欄に入れない
・絵文字は候補と完全に同じ文字列で返す

【重要】
このゲームでは回答文は投票時に公開されません。
プレイヤーは絵文字3個だけからAIを推理します。

自然な回答を考え、その回答を配布された絵文字だけで
表現してください。

必ず以下のJSON形式で返してください。

{
  "answer": "回答文",
  "emojis": ["絵文字1", "絵文字2", "絵文字3"]
}

JSON以外の文章は出力しないでください。
PROMPT;

    // Gemini APIに送るデータ
    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $prompt
                    ]
                ]
            ]
        ],

        'generationConfig' => [
            'responseMimeType' => 'application/json',
            'temperature' => 0.85,
            'maxOutputTokens' => 1024,

            'thinkingConfig' => [
                'thinkingBudget' => 0
            ],

            'responseSchema' => [
                'type' => 'OBJECT',

                'properties' => [
                    'answer' => [
                        'type' => 'STRING'
                    ],

                    'emojis' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'STRING',
                            'enum' => array_values($emojiPool)
                        ],
                        'minItems' => 3,
                        'maxItems' => 3
                    ]
                ],

                'required' => [
                    'answer',
                    'emojis'
                ]
            ]
        ]
    ];

    // Gemini APIのURL
    $url =
        'https://generativelanguage.googleapis.com/v1beta/models/' .
        'gemini-2.5-flash:generateContent';

    $requestBody = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );

    $lastReason =
        '回答の形式が正しくありません。';

    // 最大3回試す
    for ($attempt = 1; $attempt <= 3; $attempt++) {

        $ch = curl_init($url);

        if ($ch === false) {
            throw new Exception(
                'Gemini APIの通信を開始できません。'
            );
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey
            ],

            CURLOPT_POSTFIELDS => $requestBody,

            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 45
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new Exception(
                'Gemini API通信エラー: ' . $error
            );
        }

        $httpCode = (int)curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        // HTTPエラー
        if ($httpCode !== 200) {

            $errorData = json_decode(
                $response,
                true
            );

            $errorMessage =
                $errorData['error']['message']
                ?? '詳細不明';

            error_log(
                'Gemini API HTTP ' .
                $httpCode . ': ' . $errorMessage
            );

            throw new Exception(
                'Gemini APIがエラーを返しました。HTTP ' .
                $httpCode
            );
        }

        // APIレスポンスを解析
        $result = json_decode(
            $response,
            true
        );

        if (!is_array($result)) {
            $lastReason =
                'APIレスポンスがJSONではありません。';
            continue;
        }

        $parts =
            $result['candidates'][0]['content']['parts']
            ?? [];

        $outputText = '';

        foreach ($parts as $part) {
            if (
                isset($part['text']) &&
                is_string($part['text'])
            ) {
                $outputText .= $part['text'];
            }
        }

        // JSON解析
        $aiData = json_decode(
            $outputText,
            true
        );

        if (
            !is_array($aiData) ||
            !isset(
                $aiData['answer'],
                $aiData['emojis']
            ) ||
            !is_string($aiData['answer']) ||
            !is_array($aiData['emojis']) ||
            count($aiData['emojis']) !== 3
        ) {
            $lastReason =
                '回答と絵文字3個のJSON形式が不正です。';

            error_log(
                'Gemini JSON形式エラー attempt=' .
                $attempt
            );

            continue;
        }

        // 回答文の検証
        $answer = trim($aiData['answer']);

        if (
            $answer === '' ||
            mb_strlen($answer, 'UTF-8') > 30
        ) {
            $lastReason =
                '回答が空、または30文字を超えています。';
            continue;
        }

        // 絵文字が12個の候補に含まれるか検証
        $selectedEmojis = [];
        $valid = true;

        foreach ($aiData['emojis'] as $emoji) {

            if (!is_string($emoji)) {
                $valid = false;
                break;
            }

            // 完全一致で確認
            if (!in_array(
                $emoji,
                $emojiPool,
                true
            )) {
                $valid = false;
                break;
            }

            $selectedEmojis[] = $emoji;
        }

        // 3個で重複がないか確認
        if (
            !$valid ||
            count($selectedEmojis) !== 3 ||
            count(array_unique(
                $selectedEmojis
            )) !== 3
        ) {
            $lastReason =
                '配布された12個から異なる絵文字3個を選べていません。';

            error_log(
                'Gemini絵文字選択エラー attempt=' .
                $attempt
            );

            continue;
        }

        // 正常に生成できた場合
        return [
            'answer' => $answer,
            'emojis' => $selectedEmojis,
            'emoji_pool' => $emojiPool
        ];
    }

    throw new Exception(
        'Geminiの回答を3回生成しましたが、' .
        $lastReason
    );
}
