
<?php

/**
- Gemini APIと通信するファイル
- お題を受け取り、AIの回答と絵文字3つを生成する
 */

function generateAiAnswer($question)
{
    // PHPの実行時間制限を解除（タイムアウト防止）
    set_time_limit(0);

    // APIキーを環境変数から取得（GEMINI_API_KEY）
    $apiKey = getenv('GEMINI_API_KEY');

    if (!$apiKey) {
        throw new Exception('Gemini APIキーが設定されていません。');
    }

    if (empty($question)) {
        throw new Exception('お題が入力されていません。');
    }

    // AIへの指示
    $prompt = "
あなたは「WHO IS AI?」という正体当てゲームに参加するAIです。

次のお題に対して、自然な人間らしい回答を1つ作ってください。

【お題】
{$question}

【回答の条件】
・大学生が考えそうな回答にする（あるあるネタ、少し個性的な視点、ズボラな回答など、毎回異なるバリエーションで考える）
・長すぎない文章にする（1〜2文程度）
・不自然にAIらしい丁寧すぎる回答にしない
・回答を表す絵文字を必ず3つ選ぶ
";

    // Gemini APIに送信するデータ構造
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
            'temperature' => 1.0,
            'topP' => 0.95,
            'responseSchema' => [
                'type' => 'OBJECT',
                'properties' => [
                    'answer' => ['type' => 'STRING'],
                    'emojis' => [
                        'type' => 'ARRAY',
                        'items' => ['type' => 'STRING']
                    ]
                ],
                'required' => ['answer', 'emojis']
            ]
        ]
    ];

    // Gemini API エンドポイント
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=' . $apiKey;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 60 // タイムアウトを60秒に増やす
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        @curl_close($ch);
        // API通信タイムアウト・エラー時のフォールバック処理（ゲームを停止させないための代替回答）
        return [
            'answer' => 'とりあえず二度寝するか迷いつつスマホを触る',
            'emojis' => ['🛌', '📱', '⏰']
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    @curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        // HTTPエラー時もフォールバック値を返す
        return [
            'answer' => 'とりあえず二度寝するか迷いつつスマホを触る',
            'emojis' => ['🛌', '📱', '⏰']
        ];
    }

    // レスポンスからテキスト抽出
    $outputText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

    if ($outputText === '') {
        return [
            'answer' => 'とりあえず二度寝するか迷いつつスマホを触る',
            'emojis' => ['🛌', '📱', '⏰']
        ];
    }

    $aiData = json_decode($outputText, true);

    if (
        !is_array($aiData) ||
        empty($aiData['answer']) ||
        !isset($aiData['emojis']) ||
        !is_array($aiData['emojis']) ||
        count($aiData['emojis']) !== 3
    ) {
        return [
            'answer' => 'とりあえず二度寝するか迷いつつスマホを触る',
            'emojis' => ['🛌', '📱', '⏰']
        ];
    }

    return [
        'answer' => $aiData['answer'],
        'emojis' => $aiData['emojis']
    ];
}