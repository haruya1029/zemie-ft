
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

$user_id = $_SESSION['user_id'];
$round_id = filter_input(INPUT_POST, 'round_id', FILTER_VALIDATE_INT);

// 送信された絵文字を取得
$emoji1 = $_POST['emoji1'] ?? '';
$emoji2 = $_POST['emoji2'] ?? '';
$emoji3 = $_POST['emoji3'] ?? '';

// ラウンドIDを確認
if (!$round_id || $round_id <= 0) {
    http_response_code(400);
    exit("ラウンドIDが正しくありません。");
}

// 絵文字の入力チェック
foreach ([$emoji1, $emoji2, $emoji3] as $emoji) {
    if (!is_string($emoji) ||
        trim($emoji) === '' ||
        mb_strlen($emoji, 'UTF-8') > 10) {

        http_response_code(400);
        exit("絵文字を3つ正しく選択してください。");
    }
}

try {
    $pdo->beginTransaction();

    // ラウンドの状態を確認
    $sql = "
        SELECT r.id, r.room_id, r.status,
               rm.status AS room_status
        FROM who_is_ai.rounds r
        JOIN who_is_ai.rooms rm ON r.room_id = rm.id
        WHERE r.id = :round_id
        FOR UPDATE OF r
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':round_id' => $round_id]);
    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round ||
        $round['status'] !== 'hinting' ||
        $round['room_status'] !== 'playing') {
        throw new Exception("現在はヒントを提出できません。");
    }

    // ルームの参加者か確認
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

    // 自分が提出した回答を取得
    $sql = "
        SELECT id
        FROM who_is_ai.answers
        WHERE round_id = :round_id
          AND user_id = :user_id
          AND is_ai = FALSE
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id
    ]);

    $answer_id = $stmt->fetchColumn();

    if (!$answer_id) {
        throw new Exception("先に回答を提出してください。");
    }

    // 絵文字ヒントを保存
    $sql = "
        INSERT INTO who_is_ai.hints
            (answer_id, emoji1, emoji2, emoji3)
        VALUES
            (:answer_id, :emoji1, :emoji2, :emoji3)
        ON CONFLICT (answer_id)
        DO NOTHING
        RETURNING id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':answer_id' => $answer_id,
        ':emoji1' => $emoji1,
        ':emoji2' => $emoji2,
        ':emoji3' => $emoji3
    ]);

    $hint_id = $stmt->fetchColumn();

    if (!$hint_id) {
        throw new Exception("ヒントはすでに提出済みです。");
    }

    $pdo->commit();

    echo "絵文字ヒントを保存しました。";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    exit($e->getMessage());
}
