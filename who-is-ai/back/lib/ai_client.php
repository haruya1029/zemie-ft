
<?php
/**
 * WHO IS AI?
<<<<<<< HEAD
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

=======
 * Gemini APIで回答と絵文字ヒント3つを生成
 *
 * ・自然な大学生らしい回答
 * ・絵文字ヒントは絵文字のみ
 * ・固定回答なし
 * ・生成内容がおかしい場合は最大3回試す
 */

function generateAiAnswer($question)
{
    $question = trim((string)$question);

    if ($question === '') {
        throw new Exception('お題が入力されていません。');
    }

    // APIキーを読み込む
    $configPath = __DIR__ . '/../config/gemini_config.php';

    if (!is_file($configPath)) {
        throw new Exception('Geminiの設定ファイルがありません。');
    }

    $config = require $configPath;
    $apiKey = trim((string)($config['api_key'] ?? ''));

    if ($apiKey === '') {
        throw new Exception('Gemini APIキーが設定されていません。');
    }

>>>>>>> origin/main
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

<<<<<<< HEAD
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
=======
【絵文字ヒントのルール】
・回答に関連する絵文字を3つ作る
・3つとも違う絵文字にする
・1つの項目には絵文字を1個だけ入れる
・文章、単語、数字、顔文字は入れない
・一般的な絵文字を使用する
・国旗や複雑な組み合わせは避ける
・回答そのものを直接表す絵文字は避ける
・少し遠回しなヒントにする

【出力例】
{"answer":"友達の本音を聞く","emojis":["👂","🤫","👥"]}

必ずJSON形式でanswerとemojisを返してください。
>>>>>>> origin/main
PROMPT;

    // Gemini APIに送るデータ
    $data = [
        'contents' => [
            [
                'parts' => [
<<<<<<< HEAD
                    [
                        'text' => $prompt
                    ]
=======
                    ['text' => $prompt]
>>>>>>> origin/main
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
<<<<<<< HEAD

=======
>>>>>>> origin/main
                'properties' => [
                    'answer' => [
                        'type' => 'STRING'
                    ],
<<<<<<< HEAD

                    'emojis' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'STRING',
                            'enum' => array_values($emojiPool)
=======
                    'emojis' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'STRING'
>>>>>>> origin/main
                        ],
                        'minItems' => 3,
                        'maxItems' => 3
                    ]
                ],
<<<<<<< HEAD

=======
>>>>>>> origin/main
                'required' => [
                    'answer',
                    'emojis'
                ]
            ]
        ]
    ];

<<<<<<< HEAD
    // Gemini APIのURL
=======
    // Gemini API
>>>>>>> origin/main
    $url =
        'https://generativelanguage.googleapis.com/v1beta/models/' .
        'gemini-2.5-flash:generateContent';

<<<<<<< HEAD
    $requestBody = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );

    $lastReason =
        '回答の形式が正しくありません。';

    // 最大3回試す
=======
    $lastReason = '回答の形式が正しくありません。';

    // 最大3回まで生成を試す
>>>>>>> origin/main
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

<<<<<<< HEAD
            CURLOPT_POSTFIELDS => $requestBody,

            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 45
=======
            CURLOPT_POSTFIELDS => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ),

            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 25
>>>>>>> origin/main
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

<<<<<<< HEAD
            $errorData = json_decode(
                $response,
                true
            );
=======
            $errorData = json_decode($response, true);
>>>>>>> origin/main

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
<<<<<<< HEAD
        $result = json_decode(
            $response,
            true
        );

        if (!is_array($result)) {
            $lastReason =
                'APIレスポンスがJSONではありません。';
            continue;
        }

=======
        $result = json_decode($response, true);

        if (!is_array($result)) {
            $lastReason = 'APIレスポンスがJSONではありません。';
            continue;
        }

        // Geminiの生成内容を取得
>>>>>>> origin/main
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
<<<<<<< HEAD
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
=======
        $aiData = json_decode($outputText, true);

        if (
            !is_array($aiData) ||
            !isset($aiData['answer'], $aiData['emojis']) ||
>>>>>>> origin/main
            !is_string($aiData['answer']) ||
            !is_array($aiData['emojis']) ||
            count($aiData['emojis']) !== 3
        ) {
            $lastReason =
<<<<<<< HEAD
                '回答と絵文字3個のJSON形式が不正です。';

            error_log(
                'Gemini JSON形式エラー attempt=' .
                $attempt
=======
                '回答と絵文字3つのJSON形式が不正です。';

            error_log(
                'Gemini JSON形式エラー attempt=' . $attempt
>>>>>>> origin/main
            );

            continue;
        }

<<<<<<< HEAD
        // 回答文の検証
=======
        // 回答を取得
>>>>>>> origin/main
        $answer = trim($aiData['answer']);

        if (
            $answer === '' ||
            mb_strlen($answer, 'UTF-8') > 30
        ) {
            $lastReason =
                '回答が空、または30文字を超えています。';
<<<<<<< HEAD
            continue;
        }

        // 絵文字が12個の候補に含まれるか検証
        $selectedEmojis = [];
=======

            continue;
        }

        // 絵文字を取得
        $cleanEmojis = [];
>>>>>>> origin/main
        $valid = true;

        foreach ($aiData['emojis'] as $emoji) {

            if (!is_string($emoji)) {
                $valid = false;
                break;
            }

<<<<<<< HEAD
            // 完全一致で確認
            if (!in_array(
                $emoji,
                $emojiPool,
                true
            )) {
=======
            // 絵文字を1つ抽出
            $pattern =
                '/\p{Extended_Pictographic}' .
                '(?:\x{FE0F}|\p{Emoji_Modifier}' .
                '|\x{200D}\p{Extended_Pictographic})*/u';

            $matched = @preg_match(
                $pattern,
                $emoji,
                $match
            );

            // 正規表現が対応していない場合の代替処理
            if ($matched !== 1) {
                $matched = preg_match(
                    '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u',
                    $emoji,
                    $match
                );
            }

            if (
                $matched !== 1 ||
                empty($match[0])
            ) {
>>>>>>> origin/main
                $valid = false;
                break;
            }

<<<<<<< HEAD
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
=======
            $cleanEmojis[] = $match[0];
        }

        // 絵文字3つと重複を確認
        if (
            !$valid ||
            count($cleanEmojis) !== 3 ||
            count(array_unique($cleanEmojis)) !== 3
        ) {
            $lastReason =
                '絵文字を3つ抽出できないか、重複しています。';

            error_log(
                'Gemini絵文字エラー attempt=' . $attempt
>>>>>>> origin/main
            );

            continue;
        }

        // 正常に生成できた場合
        return [
            'answer' => $answer,
<<<<<<< HEAD
            'emojis' => $selectedEmojis,
            'emoji_pool' => $emojiPool
        ];
    }

=======
            'emojis' => $cleanEmojis
        ];
    }

    // 3回失敗した場合
>>>>>>> origin/main
    throw new Exception(
        'Geminiの回答を3回生成しましたが、' .
        $lastReason
    );
}
