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

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

try {
    $pdo->beginTransaction();

    // 重複しない6桁のルームコードを発行
    do {
        $roomCode = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM who_is_ai.rooms WHERE room_code = :code");
        $stmt->execute([':code' => $roomCode]);
    } while ($stmt->fetchColumn() > 0);

    // ルーム作成（host_user_id を設定）
    $stmt = $pdo->prepare("
        INSERT INTO who_is_ai.rooms (room_code, host_user_id, status)
        VALUES (:code, :host_id, 'waiting')
        RETURNING id
    ");
    $stmt->execute([
        ':code' => $roomCode,
        ':host_id' => $userId
    ]);
    $roomId = $stmt->fetchColumn();

    // room_players にホストとして参加登録
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

    // セッションにルーム情報を保持
    $_SESSION['room_id'] = $roomId;
    $_SESSION['room_code'] = $roomCode;

    echo json_encode([
        'success' => true,
        'room_code' => $roomCode,
        'user_id' => $userId,
        'is_host' => true
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'エラーが発生しました: ' . $e->getMessage()]);
}