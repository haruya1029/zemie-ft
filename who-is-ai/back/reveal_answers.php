
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

header('Content-Type: application/json; charset=utf-8');

function sendResponse($success, $message, $status = 200)
{
    http_response_code($status);

    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'ログインしてください。', 401);
}

// POSTのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'POSTでアクセスしてください。', 405);
}

$user_id = (int)$_SESSION['user_id'];

$room_id = filter_input(
    INPUT_POST,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$room_id || $room_id <= 0) {
    sendResponse(false, 'ルームIDが正しくありません。', 400);
}

try {
    $pdo->beginTransaction();

    // ホスト確認
    $stmt = $pdo->prepare("
        SELECT host_user_id
        FROM who_is_ai.rooms
        WHERE id = :room_id
        FOR UPDATE
    ");

    $stmt->execute([':room_id' => $room_id]);

    $host_user_id = $stmt->fetchColumn();

    if ($host_user_id === false) {
        throw new Exception('ルームが見つかりません。');
    }

    if ((int)$host_user_id !== $user_id) {
        throw new Exception('回答を公開できるのはホストだけです。');
    }

    // 現在のラウンド
    $stmt = $pdo->prepare("
        SELECT id, status
        FROM who_is_ai.rounds
        WHERE room_id = :room_id
        ORDER BY round_number DESC
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([':room_id' => $room_id]);

    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round) {
        throw new Exception('ラウンドが見つかりません。');
    }

    if ($round['status'] !== 'discussing') {
        throw new Exception('まだ回答を公開できません。');
    }

    // 回答公開 → 投票段階へ
    $stmt = $pdo->prepare("
        UPDATE who_is_ai.rounds
        SET status = 'voting'
        WHERE id = :round_id
    ");

    $stmt->execute([
        ':round_id' => (int)$round['id']
    ]);

    $pdo->commit();

    sendResponse(true, '回答を公開しました。');

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'reveal_answers.php: ' . $e->getMessage()
    );

    sendResponse(false, $e->getMessage(), 400);
}
