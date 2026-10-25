
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

// POST以外は禁止
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../front/home.php");
    exit();
}

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/index.php");
    exit();
}

// CSRF対策
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit("不正なリクエストです。");
}

// 送信されたアバター
$avatar_id = $_POST['avatar_id'] ?? '';

// 登録可能なアバター
$allowed_avatars = [
    'robot',
    'human',
    'ghost',
    'cat',
    'brain',
    'smile'
];

// 不正な値を防ぐ
if (
    !is_string($avatar_id) ||
    !in_array($avatar_id, $allowed_avatars, true)
) {
    http_response_code(400);
    exit("アバターの選択が正しくありません。");
}

try {

    // DBのアバター情報を更新
    $sql = "
        UPDATE who_is_ai.users
        SET avatar_id = :avatar_id
        WHERE id = :user_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':avatar_id' => $avatar_id,
        ':user_id' => $_SESSION['user_id']
    ]);

    // ホームへ戻る
    header("Location: ../front/home.php");
    exit();

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("アバターの保存に失敗しました。");
}
