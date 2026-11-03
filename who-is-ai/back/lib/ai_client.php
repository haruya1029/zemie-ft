
<?php
/**
 * WHO IS AI?
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
PROMPT;

    // Gemini APIに送るデータ
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
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
                            'type' => 'STRING'
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

    // Gemini API
    $url =
        'https://generativelanguage.googleapis.com/v1beta/models/' .
        'gemini-2.5-flash:generateContent';

    $lastReason = '回答の形式が正しくありません。';

    // 最大3回まで生成を試す
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

            CURLOPT_POSTFIELDS => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ),

            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 25
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

            $errorData = json_decode($response, true);

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
        $result = json_decode($response, true);

        if (!is_array($result)) {
            $lastReason = 'APIレスポンスがJSONではありません。';
            continue;
        }

        // Geminiの生成内容を取得
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
        $aiData = json_decode($outputText, true);

        if (
            !is_array($aiData) ||
            !isset($aiData['answer'], $aiData['emojis']) ||
            !is_string($aiData['answer']) ||
            !is_array($aiData['emojis']) ||
            count($aiData['emojis']) !== 3
        ) {
            $lastReason =
                '回答と絵文字3つのJSON形式が不正です。';

            error_log(
                'Gemini JSON形式エラー attempt=' . $attempt
            );

            continue;
        }

        // 回答を取得
        $answer = trim($aiData['answer']);

        if (
            $answer === '' ||
            mb_strlen($answer, 'UTF-8') > 30
        ) {
            $lastReason =
                '回答が空、または30文字を超えています。';

            continue;
        }

        // 絵文字を取得
        $cleanEmojis = [];
        $valid = true;

        foreach ($aiData['emojis'] as $emoji) {

            if (!is_string($emoji)) {
                $valid = false;
                break;
            }

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
                $valid = false;
                break;
            }

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
            );

            continue;
        }

        // 正常に生成できた場合
        return [
            'answer' => $answer,
            'emojis' => $cleanEmojis
        ];
    }

    // 3回失敗した場合
    throw new Exception(
        'Geminiの回答を3回生成しましたが、' .
        $lastReason
    );
}
