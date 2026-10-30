<?php
// エラー詳細を画面に表示（デバッグ用）
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../connect_db.php";

$roomId = $_SESSION['room_id'] ?? $_GET['room_id'] ?? null;

if (!$roomId) {
    echo json_encode(['success' => false, 'message' => 'ルームIDが指定されていません。']);
    exit;
}

try {
    // rooms テーブルと結合してホスト判定およびアバター情報を取得
    $stmt = $pdo->prepare("
        SELECT 
            u.id AS user_id, 
            u.username, 
            u.avatar_id,
            (r.host_user_id = u.id) AS is_host
        FROM WHO_IS_AI.room_players rp
        JOIN WHO_IS_AI.users u ON rp.user_id = u.id
        JOIN WHO_IS_AI.rooms r ON rp.room_id = r.id
        WHERE rp.room_id = ?
        ORDER BY is_host DESC, rp.joined_at ASC
    ");
    $stmt->execute([$roomId]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($players),
        'players' => $players
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'エラーが発生しました: ' . $e->getMessage()]);
}