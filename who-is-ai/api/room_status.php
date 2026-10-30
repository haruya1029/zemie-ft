<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/connect_db.php';

$roomId = $_SESSION['room_id'] ?? $_GET['room_id'] ?? null;

if (!$roomId) {
    echo json_encode(['success' => false, 'message' => 'ルームIDが指定されていません。']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, room_code, status, host_user_id 
        FROM who_is_ai.rooms 
        WHERE id = :id
    ");
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'ルームが見つかりません。']);
        exit;
    }

    $currentUserId = $_SESSION['user_id'] ?? null;
    $isHost = ($currentUserId && $room['host_user_id'] == $currentUserId);

    echo json_encode([
        'success' => true,
        'room' => [
            'id' => $room['id'],
            'room_code' => $room['room_code'],
            'status' => $room['status'],
            'is_host' => $isHost
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'エラーが発生しました: ' . $e->getMessage()]);
}