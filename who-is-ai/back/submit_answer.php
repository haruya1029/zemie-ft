
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("ログインしてください。");
}

// POST通信のみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("不正なリクエストです。");
}

// 送信された情報を取得
$user_id = $_SESSION['user_id'];
$round_id = filter_input(INPUT_POST, 'round_id', FILTER_VALIDATE_INT);
$answer_text = trim($_POST['answer_text'] ?? '');

// 入力チェック
if (!$round_id || $round_id <= 0) {
    http_response_code(400);
    exit("ラウンドIDが正しくありません。");
}

if (!is_string($answer_text) || $answer_text === '' ||
    mb_strlen($answer_text) > 200) {
    http_response_code(400);
    exit("回答は1〜200文字で入力してください。");
}

try {
    $pdo->beginTransaction();

    // 対象ラウンドとルームの状態を確認
    $sql = "
        SELECT r.id, r.room_id, r.status, rm.status AS room_status
        FROM who_is_ai.rounds r
        JOIN who_is_ai.rooms rm ON r.room_id = rm.id
        WHERE r.id = :round_id
        FOR UPDATE OF r
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':round_id' => $round_id]);
    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round ||
        $round['status'] !== 'answering' ||
        $round['room_status'] !== 'playing') {
        throw new Exception("現在は回答できません。");
    }

    // ルームに参加しているか確認
    $sql = "
        SELECT id
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
          AND user_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room_id' => $round['room_id'],
        ':user_id' => $user_id
    ]);

    if (!$stmt->fetch()) {
        throw new Exception("このルームに参加していません。");
    }

    // 回答をDBに保存
    $sql = "
        INSERT INTO who_is_ai.answers
            (round_id, user_id, answer_text, is_ai)
        VALUES
            (:round_id, :user_id, :answer_text, FALSE)
        ON CONFLICT (round_id, user_id)
        DO NOTHING
        RETURNING id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id,
        ':answer_text' => $answer_text
    ]);

    $answer_id = $stmt->fetchColumn();

    if (!$answer_id) {
        throw new Exception("このラウンドではすでに回答済みです。");
    }

    $pdo->commit();

    echo "回答を保存しました。";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    exit($e->getMessage());
}
