
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
$user_id = (int)$_SESSION['user_id'];

$round_id = filter_input(
    INPUT_POST,
    'round_id',
    FILTER_VALIDATE_INT
);

$answer_id = filter_input(
    INPUT_POST,
    'answer_id',
    FILTER_VALIDATE_INT
);

// 入力チェック
if (!$round_id || $round_id <= 0 ||
    !$answer_id || $answer_id <= 0) {

    http_response_code(400);
    exit("投票情報が正しくありません。");
}

try {
    $pdo->beginTransaction();

    // ラウンド情報を取得してロック
    $sql = "
        SELECT r.id, r.room_id, r.status,
               rm.status AS room_status
        FROM who_is_ai.rounds r
        JOIN who_is_ai.rooms rm
          ON r.room_id = rm.id
        WHERE r.id = :round_id
        FOR UPDATE OF r
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round ||
        $round['status'] !== 'voting' ||
        $round['room_status'] !== 'playing') {

        throw new Exception("現在は投票できません。");
    }

    $room_id = (int)$round['room_id'];

    // ルーム参加者か確認
    $sql = "
        SELECT id
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
          AND user_id = :user_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room_id' => $room_id,
        ':user_id' => $user_id
    ]);

    if (!$stmt->fetch()) {
        throw new Exception("このルームに参加していません。");
    }

    // 投票先が今回のラウンドの回答か確認
    $sql = "
        SELECT id, is_ai
        FROM who_is_ai.answers
        WHERE id = :answer_id
          AND round_id = :round_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':answer_id' => $answer_id,
        ':round_id' => $round_id
    ]);

    $answer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$answer) {
        throw new Exception("投票先の回答が見つかりません。");
    }

    // 投票を保存（1人1票）
    $sql = "
        INSERT INTO who_is_ai.votes
            (round_id, user_id, answer_id)
        VALUES
            (:round_id, :user_id, :answer_id)
        ON CONFLICT (round_id, user_id)
        DO NOTHING
        RETURNING id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id,
        ':answer_id' => $answer_id
    ]);

    $vote_id = $stmt->fetchColumn();

    if (!$vote_id) {
        throw new Exception("すでに投票済みです。");
    }

    // 現在の投票数を取得
    $sql = "
        SELECT COUNT(*)
        FROM who_is_ai.votes
        WHERE round_id = :round_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $vote_count = (int)$stmt->fetchColumn();

    // 参加人数を取得
    $sql = "
        SELECT COUNT(*)
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $player_count = (int)$stmt->fetchColumn();

    // 全員の投票がそろったら得点を計算
    if ($player_count > 0 && $vote_count === $player_count) {

        // AIの回答に投票したプレイヤーを取得
        $sql = "
            SELECT v.user_id
            FROM who_is_ai.votes v
            JOIN who_is_ai.answers a
              ON v.answer_id = a.id
            WHERE v.round_id = :round_id
              AND a.round_id = :round_id
              AND a.is_ai = TRUE
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':round_id' => $round_id
        ]);

        $correct_users = $stmt->fetchAll(
            PDO::FETCH_COLUMN
        );

        // 正解者に10点を加算
        $sql = "
            INSERT INTO who_is_ai.scores
                (room_id, user_id, points)
            VALUES
                (:room_id, :user_id, 10)
            ON CONFLICT (room_id, user_id)
            DO UPDATE SET
                points = who_is_ai.scores.points + 10
        ";

        $score_stmt = $pdo->prepare($sql);

        foreach ($correct_users as $correct_user_id) {
            $score_stmt->execute([
                ':room_id' => $room_id,
                ':user_id' => $correct_user_id
            ]);
        }

        // 結果発表へ進める
        $sql = "
            UPDATE who_is_ai.rounds
            SET status = 'result'
            WHERE id = :round_id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':round_id' => $round_id
        ]);
    }

    $pdo->commit();

    echo "投票を保存しました。";

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    http_response_code(400);
    exit("投票処理に失敗しました。入力内容や投票状況を確認してください。");
}
