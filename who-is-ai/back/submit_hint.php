
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

<<<<<<< HEAD
// 送信情報を取得
$user_id = (int)$_SESSION['user_id'];

$round_id = filter_input(
    INPUT_POST,
    'round_id',
    FILTER_VALIDATE_INT
);

=======
$user_id = $_SESSION['user_id'];
$round_id = filter_input(INPUT_POST, 'round_id', FILTER_VALIDATE_INT);

// 送信された絵文字を取得
>>>>>>> origin/main
$emoji1 = $_POST['emoji1'] ?? '';
$emoji2 = $_POST['emoji2'] ?? '';
$emoji3 = $_POST['emoji3'] ?? '';

<<<<<<< HEAD
$selectedEmojis = [
    $emoji1,
    $emoji2,
    $emoji3
];

/* ========================================
   1. 入力チェック
======================================== */

=======
// ラウンドIDを確認
>>>>>>> origin/main
if (!$round_id || $round_id <= 0) {
    http_response_code(400);
    exit("ラウンドIDが正しくありません。");
}

<<<<<<< HEAD
// 3個の絵文字が正しい文字列か確認
foreach ($selectedEmojis as $emoji) {

    if (
        !is_string($emoji) ||
        trim($emoji) === '' ||
        mb_strlen($emoji, 'UTF-8') > 10
    ) {
        http_response_code(400);
        exit(
            "絵文字を3つ正しく選択してください。"
        );
    }
}

// 重複していないか確認
if (count(array_unique($selectedEmojis)) !== 3) {
    http_response_code(400);
    exit(
        "同じ絵文字を複数選ぶことはできません。"
    );
}

try {

    $pdo->beginTransaction();

    /* ========================================
       2. ラウンドの状態を確認
    ======================================== */

    $sql = "
        SELECT
            r.id,
            r.room_id,
            r.status,
            rm.status AS room_status
        FROM who_is_ai.rounds r
        JOIN who_is_ai.rooms rm
            ON r.room_id = rm.id
=======
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
>>>>>>> origin/main
        WHERE r.id = :round_id
        FOR UPDATE OF r
    ";

    $stmt = $pdo->prepare($sql);
<<<<<<< HEAD

    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$round ||
        $round['status'] !== 'hinting' ||
        $round['room_status'] !== 'playing'
    ) {
        throw new Exception(
            "現在はヒントを提出できません。"
        );
    }

    /* ========================================
       3. ルーム参加者か確認
    ======================================== */

=======
    $stmt->execute([':round_id' => $round_id]);
    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round ||
        $round['status'] !== 'hinting' ||
        $round['room_status'] !== 'playing') {
        throw new Exception("現在はヒントを提出できません。");
    }

    // ルームの参加者か確認
>>>>>>> origin/main
    $sql = "
        SELECT id
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
          AND user_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
<<<<<<< HEAD

=======
>>>>>>> origin/main
    $stmt->execute([
        ':room_id' => $round['room_id'],
        ':user_id' => $user_id
    ]);

<<<<<<< HEAD
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception(
            "このルームに参加していません。"
        );
    }

    /* ========================================
       4. 自分の回答IDを取得
    ======================================== */

=======
    if (!$stmt->fetch()) {
        throw new Exception("このルームに参加していません。");
    }

    // 自分が提出した回答を取得
>>>>>>> origin/main
    $sql = "
        SELECT id
        FROM who_is_ai.answers
        WHERE round_id = :round_id
          AND user_id = :user_id
          AND is_ai = FALSE
    ";

    $stmt = $pdo->prepare($sql);
<<<<<<< HEAD

=======
>>>>>>> origin/main
    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id
    ]);

    $answer_id = $stmt->fetchColumn();

    if (!$answer_id) {
<<<<<<< HEAD
        throw new Exception(
            "先に回答を提出してください。"
        );
    }

    /* ========================================
       5. 自分に配布された12個を取得
    ======================================== */

    $sql = "
        SELECT emojis
        FROM who_is_ai.emoji_pools
        WHERE answer_id = :answer_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':answer_id' => $answer_id
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception(
            "絵文字候補が見つかりません。"
        );
    }

    $emojiPool = json_decode(
        $row['emojis'],
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    // 候補が12個で重複していないか
    if (
        !is_array($emojiPool) ||
        count($emojiPool) !== 12 ||
        count(array_unique($emojiPool)) !== 12
    ) {
        throw new Exception(
            "絵文字候補のデータが不正です。"
        );
    }

    /* ========================================
       6. 配布された12個から選んだか確認
    ======================================== */

    foreach ($selectedEmojis as $emoji) {

        if (!in_array($emoji, $emojiPool, true)) {
            throw new Exception(
                "配布されていない絵文字は使用できません。"
            );
        }
    }

    /* ========================================
       7. ヒントの重複提出を確認
    ======================================== */

    $sql = "
        SELECT id
        FROM who_is_ai.hints
        WHERE answer_id = :answer_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':answer_id' => $answer_id
    ]);

    if ($stmt->fetchColumn()) {
        throw new Exception(
            "ヒントはすでに提出済みです。"
        );
    }

    /* ========================================
       8. 選択した3個をDBに保存
    ======================================== */

    $sql = "
        INSERT INTO who_is_ai.hints
            (
                answer_id,
                emoji1,
                emoji2,
                emoji3
            )
        VALUES
            (
                :answer_id,
                :emoji1,
                :emoji2,
                :emoji3
            )
=======
        throw new Exception("先に回答を提出してください。");
    }

    // 絵文字ヒントを保存
    $sql = "
        INSERT INTO who_is_ai.hints
            (answer_id, emoji1, emoji2, emoji3)
        VALUES
            (:answer_id, :emoji1, :emoji2, :emoji3)
>>>>>>> origin/main
        ON CONFLICT (answer_id)
        DO NOTHING
        RETURNING id
    ";

    $stmt = $pdo->prepare($sql);
<<<<<<< HEAD

=======
>>>>>>> origin/main
    $stmt->execute([
        ':answer_id' => $answer_id,
        ':emoji1' => $emoji1,
        ':emoji2' => $emoji2,
        ':emoji3' => $emoji3
    ]);

    $hint_id = $stmt->fetchColumn();

    if (!$hint_id) {
<<<<<<< HEAD
        throw new Exception(
            "ヒントはすでに提出済みです。"
        );
    }

    /* ========================================
       9. 保存確定
    ======================================== */

=======
        throw new Exception("ヒントはすでに提出済みです。");
    }

>>>>>>> origin/main
    $pdo->commit();

    echo "絵文字ヒントを保存しました。";

<<<<<<< HEAD
} catch (Throwable $e) {

=======
} catch (Exception $e) {
>>>>>>> origin/main
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

<<<<<<< HEAD
    // 詳細はサーバーログに記録
    error_log(
        'submit_hint.php: ' . $e->getMessage()
    );

    if ($e instanceof PDOException ||
        $e instanceof Error) {

        http_response_code(500);
        exit(
            "サーバー内部でエラーが発生しました。"
        );
    }

    http_response_code(400);

=======
    http_response_code(400);
>>>>>>> origin/main
    exit($e->getMessage());
}
