
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// エラー応答
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

    /* ========================================
       1. ルーム参加確認
    ======================================== */

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
        sendError(
            'このルームに参加していません。',
            403
        );
    }

    /* ========================================
       2. 現在のラウンド取得
    ======================================== */

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

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    /* ========================================
       3. ホスト確認
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT host_user_id
        FROM who_is_ai.rooms
        WHERE id = :room_id
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $is_host =
        (int)$stmt->fetchColumn() === $user_id;

    // まだラウンドが存在しない場合
    if (!$round) {
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'status' => 'waiting',
            'is_host' => $is_host,
            'message' => 'ゲーム開始待ちです。'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $round_id = (int)$round['id'];
    $status = $round['status'];

    /* ========================================
       4. 参加人数
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $player_count = (int)$stmt->fetchColumn();

    /* ========================================
       5. 人間の回答数
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers
        WHERE round_id = :round_id
          AND is_ai = FALSE
    ");

    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $answer_count = (int)$stmt->fetchColumn();

    /* ========================================
       6. 人間の絵文字提出数
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.hints h
        JOIN who_is_ai.answers a
          ON h.answer_id = a.id
        WHERE a.round_id = :round_id
          AND a.is_ai = FALSE
    ");

    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $hint_count = (int)$stmt->fetchColumn();

    /* ========================================
       7. 自分の回答状況
    ======================================== */

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

    $has_answered =
        (int)$stmt->fetchColumn() > 0;

    /* ========================================
       8. 自分のヒント提出状況
    ======================================== */

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

    $has_submitted_hint =
        (int)$stmt->fetchColumn() > 0;

    /* ========================================
       9. AIの準備状況
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers a
        JOIN who_is_ai.hints h
          ON h.answer_id = a.id
        JOIN who_is_ai.emoji_pools ep
          ON ep.answer_id = a.id
        WHERE a.round_id = :round_id
          AND a.is_ai = TRUE
    ");

    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $ai_ready =
        (int)$stmt->fetchColumn() === 1;

    /* ========================================
       10. ゲーム状態を更新
    ======================================== */

    // 全員が回答したら絵文字選択へ
    if (
        $status === 'answering' &&
        $player_count > 0 &&
        $answer_count === $player_count
    ) {
        $status = 'hinting';
    }

    // 全員の絵文字が揃ったら投票へ
    if (
        $status === 'hinting' &&
        $player_count > 0 &&
        $hint_count === $player_count &&
        $ai_ready
    ) {
        $status = 'voting';
    }

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

    /* ========================================
       11. 投票状況
    ======================================== */

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.votes
        WHERE round_id = :round_id
    ");

    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $vote_count = (int)$stmt->fetchColumn();

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

    $has_voted =
        (int)$stmt->fetchColumn() > 0;

    /* ========================================
       12. 基本レスポンス
    ======================================== */

    $response = [
        'success' => true,
        'room_id' => $room_id,
        'round_id' => $round_id,
        'round_number' =>
            (int)$round['round_number'],
        'question' => $round['question_text'],
        'status' => $status,
        'is_host' => $is_host,
        'player_count' => $player_count,
        'answer_count' => $answer_count,
        'hint_count' => $hint_count,
        'vote_count' => $vote_count,
        'has_answered' => $has_answered,
        'has_submitted_hint' =>
            $has_submitted_hint,
        'has_voted' => $has_voted
    ];

    /* ========================================
       13. 投票中：絵文字だけ公開
    ======================================== */

    if (
        $status === 'discussing' ||
        $status === 'voting'
    ) {

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

        $stmt->execute([
            ':round_id' => $round_id
        ]);

        $response['answers'] =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 回答文・AIの正体・候補12個は返さない
    }

    /* ========================================
       14. 結果発表：すべて公開
    ======================================== */

    if ($status === 'result') {

        $stmt = $pdo->prepare("
            SELECT
                a.id AS answer_id,
                a.answer_text,
                a.is_ai,
                u.username,
                h.emoji1,
                h.emoji2,
                h.emoji3,
                ep.emojis AS emoji_pool
            FROM who_is_ai.answers a
            JOIN who_is_ai.hints h
              ON h.answer_id = a.id
            LEFT JOIN who_is_ai.users u
              ON a.user_id = u.id
            LEFT JOIN who_is_ai.emoji_pools ep
              ON ep.answer_id = a.id
            WHERE a.round_id = :round_id
            ORDER BY a.id
        ");

        $stmt->execute([
            ':round_id' => $round_id
        ]);

        $answers =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        // JSONBをPHP配列に変換
        foreach ($answers as &$answer) {

            $answer['answer_id'] =
                (int)$answer['answer_id'];

            $answer['is_ai'] =
                ($answer['is_ai'] === true ||
                 $answer['is_ai'] === 't' ||
                 $answer['is_ai'] === '1');

            $answer['username'] =
                $answer['is_ai']
                ? 'AI'
                : ($answer['username'] ?? 'UNKNOWN');

            $answer['emoji_pool'] =
                $answer['emoji_pool'] !== null
                ? json_decode(
                    $answer['emoji_pool'],
                    true
                )
                : [];
        }

        unset($answer);

        $response['answers'] = $answers;

        /* AIの回答ID */

        $ai_answer_id = null;

        foreach ($answers as $answer) {
            if ($answer['is_ai']) {
                $ai_answer_id =
                    $answer['answer_id'];
                break;
            }
        }

        $response['ai_answer_id'] =
            $ai_answer_id;

        /* ランキング */

        $stmt = $pdo->prepare("
            SELECT
                u.username,
                s.points
            FROM who_is_ai.scores s
            JOIN who_is_ai.users u
              ON s.user_id = u.id
            WHERE s.room_id = :room_id
            ORDER BY
                s.points DESC,
                u.username ASC
        ");

        $stmt->execute([
            ':room_id' => $room_id
        ]);

        $response['scores'] =
            $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ========================================
       15. DB処理を確定
    ======================================== */

    $pdo->commit();

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
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
