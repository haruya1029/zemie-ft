<?php
require_once __DIR__ . "/../db/connect_db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../front/register.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || mb_strlen($username) > 50 || strlen($password) < 4) {
    exit("入力内容を確認してください。");
}

try {
    // 同じユーザー名が存在するか確認
    $sql = "SELECT id FROM who_is_ai.users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);

    if ($stmt->fetch()) {
        exit("このユーザー名はすでに使われています。");
    }

    // パスワードをハッシュ化
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // DBに登録
    $sql = "INSERT INTO who_is_ai.users (username, password)
            VALUES (:username, :password)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    header("Location: ../front/index.php");
    exit();

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("登録処理でエラーが発生しました。");
}