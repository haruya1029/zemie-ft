<?php

// JSON形式で返す
header('Content-Type: application/json; charset=UTF-8');

// POST以外のアクセスを拒否
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'POSTメソッドで送信してください。'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// AI通信ファイルを読み込む
require_once __DIR__ . '/../lib/ai_client.php';

try {

    // 送信されたJSONを取得
    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    // お題を取得
    $question = trim($input['question'] ?? '');

    if ($question === '') {
        throw new Exception('お題が指定されていません。');
    }

    // AIの回答を生成
    $aiData = generateAiAnswer($question);

    // 結果を返す
    echo json_encode([
        'success' => true,
        'answer' => $aiData['answer'],
        'emojis' => $aiData['emojis']
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

}