<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db/connect_db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未ログインです。']);
    exit;
}

$userId = $_SESSION['user_id'];
$roomId = $_SESSION['room_id'] ?? null;

if ($roomId) {
    try {
        // room_players から現在のユーザーを削除
        $stmt = $pdo->prepare("
            DELETE FROM who_is_ai.room_players 
            WHERE room_id = :room_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':room_id' => $roomId,
            ':user_id' => $userId
        ]);

        // ホストだった場合、他の参加者が残っていれば新しいホストを自動割り当て
        $stmtHost = $pdo->prepare("SELECT host_user_id FROM who_is_ai.rooms WHERE id = :room_id");
        $stmtHost->execute([':room_id' => $roomId]);
        $room = $stmtHost->fetch(PDO::FETCH_ASSOC);

        if ($room && $room['host_user_id'] == $userId) {
            // 次に古い参加者をホストにする
            $stmtNext = $pdo->prepare("
                SELECT user_id FROM who_is_ai.room_players 
                WHERE room_id = :room_id 
                ORDER BY joined_at ASC LIMIT 1
            ");
            $stmtNext->execute([':room_id' => $roomId]);
            $nextHostId = $stmtNext->fetchColumn();

            if ($nextHostId) {
                $stmtUpdateHost = $pdo->prepare("
                    UPDATE who_is_ai.rooms 
                    SET host_user_id = :next_host 
                    WHERE id = :room_id
                ");
                $stmtUpdateHost->execute([
                    ':next_host' => $nextHostId,
                    ':room_id' => $roomId
                ]);
            }
        }
    } catch (Exception $e) {
        // エラーログ等
    }
}

// セッションからルーム情報を削除
unset($_SESSION['room_id']);
unset($_SESSION['room_code']);

echo json_encode(['success' => true]);