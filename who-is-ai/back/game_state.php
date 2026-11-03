
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

header('Content-Type: application/json; charset=utf-8');

function sendError($message, $status = 400)
{
    global $pdo;

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code($status);

    echo json_encode([
        'success' => false,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    sendError('ログインしてください。', 401);
}

// GETのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('GETでアクセスしてください。', 405);
}

$user_id = (int)$_SESSION['user_id'];

$room_id = filter_input(
    INPUT_GET,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$room_id || $room_id <= 0) {
    sendError('ルームIDが正しくありません。');
}

try {
    $pdo->beginTransaction();

    // ルーム参加確認
    $stmt = $pdo->prepare("
        SELECT id
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
          AND user_id = :user_id
    ");

    $stmt->execute([
        ':room_id' => $room_id,
        ':user_id' => $user_id
    ]);

    if (!$stmt->fetch()) {
        sendError('このルームに参加していません。', 403);
    }

    // 現在のラウンド取得
    $stmt = $pdo->prepare("
        SELECT
            r.id,
            r.round_number,
            r.status,
            q.question_text
        FROM who_is_ai.rounds r
        JOIN who_is_ai.questions q
          ON r.question_id = q.id
        WHERE r.room_id = :room_id
        ORDER BY r.round_number DESC
        LIMIT 1
        FOR UPDATE OF r
    ");

    $stmt->execute([':room_id' => $room_id]);
    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round) {
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'status' => 'waiting',
            'message' => 'ゲーム開始待ちです。'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $round_id = (int)$round['id'];
    $status = $round['status'];

    // ホスト確認
    $stmt = $pdo->prepare("
        SELECT host_user_id
        FROM who_is_ai.rooms
        WHERE id = :room_id
    ");

    $stmt->execute([':room_id' => $room_id]);

    $is_host = (int)$stmt->fetchColumn() === $user_id;

    // 参加人数
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
    ");

    $stmt->execute([':room_id' => $room_id]);
    $player_count = (int)$stmt->fetchColumn();

    // 人間の回答数
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers
        WHERE round_id = :round_id
          AND is_ai = FALSE
    ");

    $stmt->execute([':round_id' => $round_id]);
    $answer_count = (int)$stmt->fetchColumn();

    // 人間の絵文字提出数
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.hints h
        JOIN who_is_ai.answers a
          ON h.answer_id = a.id
        WHERE a.round_id = :round_id
          AND a.is_ai = FALSE
    ");

    $stmt->execute([':round_id' => $round_id]);
    $hint_count = (int)$stmt->fetchColumn();

    // 自分の回答状況
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers
        WHERE round_id = :round_id
          AND user_id = :user_id
          AND is_ai = FALSE
    ");

    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id
    ]);

    $has_answered = (int)$stmt->fetchColumn() > 0;

    // 自分の絵文字提出状況
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.hints h
        JOIN who_is_ai.answers a
          ON h.answer_id = a.id
        WHERE a.round_id = :round_id
          AND a.user_id = :user_id
          AND a.is_ai = FALSE
    ");

    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id
    ]);

    $has_submitted_hint = (int)$stmt->fetchColumn() > 0;

    // AIの回答と絵文字がそろっているか
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers a
        JOIN who_is_ai.hints h
          ON h.answer_id = a.id
        WHERE a.round_id = :round_id
          AND a.is_ai = TRUE
    ");

    $stmt->execute([':round_id' => $round_id]);
    $ai_ready = (int)$stmt->fetchColumn() === 1;

    // 全員が回答したら絵文字入力へ
    if (
        $status === 'answering' &&
        $player_count > 0 &&
        $answer_count === $player_count
    ) {
        $status = 'hinting';
    }
    // 全員のヒントとAIがそろったら直接投票へ
    if (
        $status === 'hinting' &&
        $player_count > 0 &&
        $hint_count === $player_count &&
        $ai_ready
        ) {
            $status = 'voting';
            }

    // 状態変更を保存
    if ($status !== $round['status']) {
        $stmt = $pdo->prepare("
            UPDATE who_is_ai.rounds
            SET status = :status
            WHERE id = :round_id
        ");

        $stmt->execute([
            ':status' => $status,
            ':round_id' => $round_id
        ]);
    }

    // 投票数
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.votes
        WHERE round_id = :round_id
    ");

    $stmt->execute([':round_id' => $round_id]);
    $vote_count = (int)$stmt->fetchColumn();

    // 自分の投票状況
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.votes
        WHERE round_id = :round_id
          AND user_id = :user_id
    ");

    $stmt->execute([
        ':round_id' => $round_id,
        ':user_id' => $user_id
    ]);

    $has_voted = (int)$stmt->fetchColumn() > 0;

    // フロントに返すデータ
    $response = [
        'success' => true,
        'room_id' => $room_id,
        'round_id' => $round_id,
        'round_number' => (int)$round['round_number'],
        'question' => $round['question_text'],
        'status' => $status,
        'is_host' => $is_host,
        'player_count' => $player_count,
        'answer_count' => $answer_count,
        'hint_count' => $hint_count,
        'vote_count' => $vote_count,
        'has_answered' => $has_answered,
        'has_submitted_hint' => $has_submitted_hint,
        'has_voted' => $has_voted
    ];

    // 議論中は絵文字だけを公開
    if ($status === 'discussing') {
        $stmt = $pdo->prepare("
            SELECT
                a.id AS answer_id,
                h.emoji1,
                h.emoji2,
                h.emoji3
            FROM who_is_ai.answers a
            JOIN who_is_ai.hints h
              ON h.answer_id = a.id
            WHERE a.round_id = :round_id
            ORDER BY a.id
        ");

        $stmt->execute([':round_id' => $round_id]);

        $response['hints'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 投票・結果では回答文も公開
    if ($status === 'voting' || $status === 'result') {
        $stmt = $pdo->prepare("
            SELECT
                a.id AS answer_id,
                a.answer_text,
                h.emoji1,
                h.emoji2,
                h.emoji3
            FROM who_is_ai.answers a
            JOIN who_is_ai.hints h
              ON h.answer_id = a.id
            WHERE a.round_id = :round_id
            ORDER BY a.id
        ");

        $stmt->execute([':round_id' => $round_id]);

        $response['answers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 結果発表
    if ($status === 'result') {

        // AIの回答ID
        $stmt = $pdo->prepare("
            SELECT id
            FROM who_is_ai.answers
            WHERE round_id = :round_id
              AND is_ai = TRUE
            LIMIT 1
        ");

        $stmt->execute([':round_id' => $round_id]);

        $ai_answer_id = $stmt->fetchColumn();

        $response['ai_answer_id'] = $ai_answer_id
            ? (int)$ai_answer_id
            : null;

        // ランキング
        $stmt = $pdo->prepare("
            SELECT
                u.username,
                s.points
            FROM who_is_ai.scores s
            JOIN who_is_ai.users u
              ON s.user_id = u.id
            WHERE s.room_id = :room_id
            ORDER BY s.points DESC, u.username ASC
        ");

        $stmt->execute([':room_id' => $room_id]);

        $response['scores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $pdo->commit();

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE
    );

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        'game_state.php: ' . $e->getMessage()
    );

    sendError(
        'ゲーム状態の取得に失敗しました。',
        500
    );
}
