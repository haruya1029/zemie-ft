<?php
session_start();

// セッション情報を削除
$_SESSION = [];

// セッションCookieも削除
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// セッションを破棄
session_destroy();

// ログイン画面へ移動
header("Location: ../front/index.php");
exit();