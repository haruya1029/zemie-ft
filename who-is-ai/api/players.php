<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

// ★修正箇所: db/ フォルダを指定
require_once __DIR__ . '/../db/connect_db.php';

// ログイン & ルーム参加チェック
if (!isset($_SESSION['user_id']) || !isset($_SESSION['room_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'セッションが切れているか、ルームに参加していません。',
        'players' => []
    ]);
    exit;
}

$roomId = $_SESSION['room_id'];

try {
    // 参加者一覧を取得（who_is_ai スキーマを指定）
    $stmt = $pdo->prepare("
        SELECT 
            u.id AS user_id,
            u.username,
            u.avatar_id,
            (r.host_user_id = u.id) AS is_host
        FROM who_is_ai.room_players rp
        JOIN who_is_ai.users u ON rp.user_id = u.id
        JOIN who_is_ai.rooms r ON rp.room_id = r.id
        WHERE rp.room_id = :room_id
        ORDER BY rp.joined_at ASC
    ");
    
    $stmt->execute([':room_id' => $roomId]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // is_host を boolean 型に変換
    foreach ($players as &$p) {
        $p['is_host'] = (bool)$p['is_host'];
    }

    echo json_encode([
        'success' => true,
        'players' => $players
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'DBエラーが発生しました: ' . $e->getMessage(),
        'players' => []
    ]);
}