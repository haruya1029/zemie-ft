
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

// ログインしていない場合
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/index.php");
    exit();
}

// ユーザー情報を取得
$sql = "
    SELECT id, username, avatar_id
    FROM who_is_ai.users
    WHERE id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id' => $_SESSION['user_id']
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ユーザーが存在しない場合
if (!$user) {
    header("Location: ../back/logout.php");
    exit();
}

$username = $user['username'];
$avatar_id = $user['avatar_id'];

// 選択可能なアバター
$avatars = [
    'robot' => '🤖',
    'human' => '👤',
    'ghost' => '👻',
    'cat'   => '🐱',
    'brain' => '🧠',
    'smile' => '😊'
];

// 想定外の値なら初期アバター
if (!array_key_exists($avatar_id, $avatars)) {
    $avatar_id = 'robot';
}

// アバター変更用CSRFトークン
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
