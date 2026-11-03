<?php
session_start();
require_once __DIR__ . '/../db/connect_db.php';

// 一時的な登録ページ。必ず登録後に削除すること。
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('ログインが必要です。');
}

$questions = [
    '無人島に1つだけ持っていくなら何？',
    '100万円もらえたら何に使う？',
    '1日だけ透明人間になれたら何をする？',
    '人生で一度は行ってみたい場所は？',
    '自分を動物に例えるなら何？'
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="POST"><button type="submit">お題を登録する</button></form>';
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO who_is_ai.questions (question_text)
        SELECT :question
        WHERE NOT EXISTS (
            SELECT 1 FROM who_is_ai.questions
            WHERE question_text = :existing_question
        )
    ");

    foreach ($questions as $question) {
        $stmt->execute([
            ':question' => $question,
            ':existing_question' => $question
        ]);
    }

    $pdo->commit();

    echo 'お題の登録が完了しました！';

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo '登録に失敗しました。サーバーのエラーログを確認してください。';
}