<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/connect_db.php';

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です。']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$roomCode = trim($input['room_code'] ?? '');
$userId = $_SESSION['user_id'];

if (empty($roomCode)) {
    echo json_encode(['success' => false, 'message' => 'ルームコードを入力してください。']);
    exit;
}

try {
    $pdo->beginTransaction();

    // ルームが存在し、待機中か確認
    $stmt = $pdo->prepare("SELECT id, status, host_user_id FROM who_is_ai.rooms WHERE room_code = :code");
    $stmt->execute([':code' => $roomCode]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        echo json_encode(['success' => false, 'message' => '指定されたルームが存在しません。']);
        exit;
    }

    if ($room['status'] !== 'waiting') {
        echo json_encode(['success' => false, 'message' => 'このゲームはすでに開始されているか、終了しています。']);
        exit;
    }

    $roomId = $room['id'];

    // room_players に参加追加
    $stmt = $pdo->prepare("
        INSERT INTO who_is_ai.room_players (room_id, user_id)
        VALUES (:room_id, :user_id)
        ON CONFLICT (room_id, user_id) DO NOTHING
    ");
    $stmt->execute([
        ':room_id' => $roomId,
        ':user_id' => $userId
    ]);

    $pdo->commit();

    // セッションにルーム情報を更新
    $_SESSION['room_id'] = $roomId;
    $_SESSION['room_code'] = $roomCode;

    $isHost = ($room['host_user_id'] == $userId);

    echo json_encode([
        'success' => true,
        'room_code' => $roomCode,
        'user_id' => $userId,
        'is_host' => $isHost
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'エラーが発生しました: ' . $e->getMessage()]);
}