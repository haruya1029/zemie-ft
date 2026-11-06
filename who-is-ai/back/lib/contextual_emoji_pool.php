
<?php

require_once __DIR__ . '/emoji_pool.php';

/**
 * WHO IS AI?
 *
 * 回答に関連する絵文字6個
 * ＋
 * ランダムな絵文字6個
 *
 * 合計12個を生成する
 *
 * Gemini APIが429の場合は、
 * カテゴリー別ランダム12個に切り替える
 */
function generateContextualEmojiPool(
    string $question,
    string $answer
): array {

    /* ========================================
       1. 入力チェック
    ======================================== */

    $question = trim($question);
    $answer = trim($answer);

    if ($question === '' || $answer === '') {
        throw new Exception(
            'お題または回答が空です。'
        );
    }

    /* ========================================
       2. 登録済みの絵文字を取得
    ======================================== */

    $categories = getEmojiCategories();

    $allEmojis = [];

    foreach ($categories as $emojis) {

        $allEmojis = array_merge(
            $allEmojis,
            $emojis
        );
    }

    // 重複削除
    $allEmojis = array_values(
        array_unique($allEmojis)
    );

    if (count($allEmojis) < 12) {
        throw new Exception(
            '登録されている絵文字が不足しています。'
        );
    }

    /* ========================================
       3. Gemini APIキー
    ======================================== */

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

    /* ========================================
       4. 絵文字にIDを付ける
    ======================================== */

    $candidates = [];

    foreach ($allEmojis as $index => $emoji) {

        $candidates[] = [
            'id' => $index,
            'emoji' => $emoji
        ];
    }

    $candidateJson = json_encode(
        $candidates,
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );

    /* ========================================
       5. Geminiへの指示
    ======================================== */

    $prompt = <<<PROMPT
あなたは「WHO IS AI?」というゲームの
絵文字候補を選定するAIです。

人間のプレイヤーはお題に回答し、
配られた12個の絵文字から3個を選んで
自分の回答を表現します。

【今回のお題】
{$question}

【プレイヤーの回答】
{$answer}

【使用できる絵文字一覧】
{$candidateJson}

【あなたの仕事】
プレイヤーの回答を表現しやすくするため、
一覧から関連性の高い絵文字を6個選んでください。

【ルール】
・回答内容との関連性を重視する
・直接的な絵文字と間接的な絵文字を混ぜる
・一般的な連想で意味が伝わるものを優先する
・同じIDを重複して選ばない
・必ず一覧にあるIDだけを選ぶ
・ちょうど6個選ぶ
・回答文は変更しない
・絵文字を新しく作らない
・説明や前置きは書かない

【出力形式】
{"ids":[0,1,2,3,4,5]}

上記は形式の例です。
実際には回答に合うIDを選んでください。

必ずJSON形式だけで返してください。
PROMPT;

    /* ========================================
       6. Gemini APIに送るデータ
    ======================================== */

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

            'temperature' => 0.4,

            'maxOutputTokens' => 512,

            'thinkingConfig' => [
                'thinkingBudget' => 0
            ],

            'responseSchema' => [
                'type' => 'OBJECT',

                'properties' => [
                    'ids' => [
                        'type' => 'ARRAY',

                        'items' => [
                            'type' => 'INTEGER'
                        ],

                        'minItems' => 6,
                        'maxItems' => 6
                    ]
                ],

                'required' => [
                    'ids'
                ]
            ]
        ]
    ];

    $requestBody = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );

    /* ========================================
       7. Gemini APIのURL
    ======================================== */

    $url =
        'https://generativelanguage.googleapis.com/v1beta/models/' .
        'gemini-2.5-flash:generateContent';

    /* ========================================
       8. Gemini APIを呼び出す
    ======================================== */

    // 最大3回試す
    for ($attempt = 1; $attempt <= 3; $attempt++) {

        $ch = curl_init($url);

        if ($ch === false) {
            throw new Exception(
                'Gemini API通信を開始できません。'
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

        /* ====================================
           通信エラー
        ==================================== */

        if ($response === false) {

            $error = curl_error($ch);

            curl_close($ch);

            error_log(
                'Contextual emoji communication error: ' .
                $error
            );

            throw new Exception(
                '絵文字候補の通信に失敗しました。'
            );
        }

        $httpCode = (int)curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        /* ====================================
           HTTP 429への対応
        ==================================== */

        if ($httpCode === 429) {

            error_log(
                'Contextual emoji: Gemini API HTTP 429. ' .
                'Switching to random emoji pool.'
            );

            // Geminiが使えないため、
            // 以前のカテゴリー別ランダム方式にする
            return generateFallbackEmojiPool(
                $question,
                $answer
            );
        }

        /* ====================================
           その他のHTTPエラー
        ==================================== */

        if ($httpCode !== 200) {

            error_log(
                'Contextual emoji Gemini HTTP ' .
                $httpCode . ': ' . $response
            );

            throw new Exception(
                '絵文字候補の生成に失敗しました。HTTP ' .
                $httpCode
            );
        }

        /* ====================================
           APIレスポンスを解析
        ==================================== */

        $result = json_decode(
            $response,
            true
        );

        if (!is_array($result)) {

            error_log(
                'Contextual emoji: invalid API JSON'
            );

            continue;
        }

        $parts =
            $result['candidates'][0]['content']['parts']
            ?? [];

        $output = '';

        foreach ($parts as $part) {

            if (
                isset($part['text']) &&
                is_string($part['text'])
            ) {

                $output .= $part['text'];
            }
        }

        /* ====================================
           Geminiの出力を解析
        ==================================== */

        $generated = json_decode(
            $output,
            true
        );

        $ids = $generated['ids'] ?? null;

        if (
            !is_array($ids) ||
            count($ids) !== 6
        ) {

            error_log(
                'Contextual emoji: invalid IDs ' .
                'attempt=' . $attempt
            );

            continue;
        }

        /* ====================================
           関連する絵文字6個を取得
        ==================================== */

        $related = [];

        $valid = true;

        foreach ($ids as $id) {

            if (
                !is_int($id) ||
                !array_key_exists(
                    $id,
                    $allEmojis
                )
            ) {

                $valid = false;
                break;
            }

            $related[] = $allEmojis[$id];
        }

        /* ====================================
           重複チェック
        ==================================== */

        if (
            !$valid ||
            count($related) !== 6 ||
            count(array_unique($related)) !== 6
        ) {

            error_log(
                'Contextual emoji: invalid selection ' .
                'attempt=' . $attempt
            );

            continue;
        }

        /* ====================================
           ランダムな絵文字6個を生成
        ==================================== */

        // 関連6個を候補から除外
        $remaining = array_values(
            array_diff(
                $allEmojis,
                $related
            )
        );

        // シャッフル
        shuffle($remaining);

        // ランダム6個
        $random = array_slice(
            $remaining,
            0,
            6
        );

        /* ====================================
           合計12個を作る
        ==================================== */

        $emojiPool = array_merge(
            $related,
            $random
        );

        // 関連・ランダムを区別できないようにする
        shuffle($emojiPool);

        /* ====================================
           最終チェック
        ==================================== */

        if (
            count($emojiPool) !== 12 ||
            count(array_unique($emojiPool)) !== 12
        ) {

            throw new Exception(
                '絵文字12個の生成に失敗しました。'
            );
        }

        /* ====================================
           正常に生成できた場合
        ==================================== */

        return $emojiPool;
    }

    /* ========================================
       9. JSON形式が3回不正だった場合
    ======================================== */

    error_log(
        'Contextual emoji: Gemini output invalid ' .
        'after 3 attempts. Using random pool.'
    );

    // APIの応答が不正でもゲームを継続
    return generateFallbackEmojiPool(
        $question,
        $answer
    );
}
