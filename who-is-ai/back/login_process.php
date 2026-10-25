<?php
session_start();

require_once __DIR__ . "/../db/connect_db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../front/index.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$sql = "SELECT id, username, password
        FROM who_is_ai.users
        WHERE username = :username";

$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && !empty($user['password']) &&
    password_verify($password, $user['password'])) {

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    header("Location: ../front/home.php");
    exit();

} else {
    echo "ユーザー名またはパスワードが正しくありません。";
}