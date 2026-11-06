
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';

header('Content-Type: application/json; charset=utf-8');
<<<<<<< HEAD
header('Cache-Control: no-store');

// エラー応答
=======

>>>>>>> origin/main
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

<<<<<<< HEAD
    /* ========================================
       1. ルーム参加確認
    ======================================== */

=======
    // ルーム参加確認
>>>>>>> origin/main
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
<<<<<<< HEAD
        sendError(
            'このルームに参加していません。',
            403
        );
    }

    /* ========================================
       2. 現在のラウンド取得
    ======================================== */

=======
        sendError('このルームに参加していません。', 403);
    }

    // 現在のラウンド取得
>>>>>>> origin/main
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

<<<<<<< HEAD
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
=======
    $stmt->execute([':room_id' => $room_id]);
    $round = $stmt->fetch(PDO::FETCH_ASSOC);

>>>>>>> origin/main
    if (!$round) {
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'status' => 'waiting',
<<<<<<< HEAD
            'is_host' => $is_host,
=======
>>>>>>> origin/main
            'message' => 'ゲーム開始待ちです。'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    $round_id = (int)$round['id'];
    $status = $round['status'];

<<<<<<< HEAD
    /* ========================================
       4. 参加人数
    ======================================== */

=======
    // ホスト確認
    $stmt = $pdo->prepare("
        SELECT host_user_id
        FROM who_is_ai.rooms
        WHERE id = :room_id
    ");

    $stmt->execute([':room_id' => $room_id]);

    $is_host = (int)$stmt->fetchColumn() === $user_id;

    // 参加人数
>>>>>>> origin/main
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.room_players
        WHERE room_id = :room_id
    ");

<<<<<<< HEAD
    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $player_count = (int)$stmt->fetchColumn();

    /* ========================================
       5. 人間の回答数
    ======================================== */

=======
    $stmt->execute([':room_id' => $room_id]);
    $player_count = (int)$stmt->fetchColumn();

    // 人間の回答数
>>>>>>> origin/main
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers
        WHERE round_id = :round_id
          AND is_ai = FALSE
    ");

<<<<<<< HEAD
    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $answer_count = (int)$stmt->fetchColumn();

    /* ========================================
       6. 人間の絵文字提出数
    ======================================== */

=======
    $stmt->execute([':round_id' => $round_id]);
    $answer_count = (int)$stmt->fetchColumn();

    // 人間の絵文字提出数
>>>>>>> origin/main
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.hints h
        JOIN who_is_ai.answers a
          ON h.answer_id = a.id
        WHERE a.round_id = :round_id
          AND a.is_ai = FALSE
    ");

<<<<<<< HEAD
    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $hint_count = (int)$stmt->fetchColumn();

    /* ========================================
       7. 自分の回答状況
    ======================================== */

=======
    $stmt->execute([':round_id' => $round_id]);
    $hint_count = (int)$stmt->fetchColumn();

    // 自分の回答状況
>>>>>>> origin/main
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

<<<<<<< HEAD
    $has_answered =
        (int)$stmt->fetchColumn() > 0;

    /* ========================================
       8. 自分のヒント提出状況
    ======================================== */

=======
    $has_answered = (int)$stmt->fetchColumn() > 0;

    // 自分の絵文字提出状況
>>>>>>> origin/main
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

<<<<<<< HEAD
    $has_submitted_hint =
        (int)$stmt->fetchColumn() > 0;

    /* ========================================
       9. AIの準備状況
    ======================================== */

=======
    $has_submitted_hint = (int)$stmt->fetchColumn() > 0;

    // AIの回答と絵文字がそろっているか
>>>>>>> origin/main
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.answers a
        JOIN who_is_ai.hints h
          ON h.answer_id = a.id
<<<<<<< HEAD
        JOIN who_is_ai.emoji_pools ep
          ON ep.answer_id = a.id
=======
>>>>>>> origin/main
        WHERE a.round_id = :round_id
          AND a.is_ai = TRUE
    ");

<<<<<<< HEAD
    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $ai_ready =
        (int)$stmt->fetchColumn() === 1;

    /* ========================================
       10. ゲーム状態を更新
    ======================================== */

    // 全員が回答したら絵文字選択へ
=======
    $stmt->execute([':round_id' => $round_id]);
    $ai_ready = (int)$stmt->fetchColumn() === 1;

    // 全員が回答したら絵文字入力へ
>>>>>>> origin/main
    if (
        $status === 'answering' &&
        $player_count > 0 &&
        $answer_count === $player_count
    ) {
        $status = 'hinting';
    }
<<<<<<< HEAD

    // 全員の絵文字が揃ったら投票へ
=======
    // 全員のヒントとAIがそろったら直接投票へ
>>>>>>> origin/main
    if (
        $status === 'hinting' &&
        $player_count > 0 &&
        $hint_count === $player_count &&
        $ai_ready
<<<<<<< HEAD
    ) {
        $status = 'voting';
    }

    if ($status !== $round['status']) {

=======
        ) {
            $status = 'voting';
            }

    // 状態変更を保存
    if ($status !== $round['status']) {
>>>>>>> origin/main
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

<<<<<<< HEAD
    /* ========================================
       11. 投票状況
    ======================================== */

=======
    // 投票数
>>>>>>> origin/main
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM who_is_ai.votes
        WHERE round_id = :round_id
    ");

<<<<<<< HEAD
    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $vote_count = (int)$stmt->fetchColumn();

=======
    $stmt->execute([':round_id' => $round_id]);
    $vote_count = (int)$stmt->fetchColumn();

    // 自分の投票状況
>>>>>>> origin/main
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

<<<<<<< HEAD
    $has_voted =
        (int)$stmt->fetchColumn() > 0;

    /* ========================================
       12. 基本レスポンス
    ======================================== */

=======
    $has_voted = (int)$stmt->fetchColumn() > 0;

    // フロントに返すデータ
>>>>>>> origin/main
    $response = [
        'success' => true,
        'room_id' => $room_id,
        'round_id' => $round_id,
<<<<<<< HEAD
        'round_number' =>
            (int)$round['round_number'],
=======
        'round_number' => (int)$round['round_number'],
>>>>>>> origin/main
        'question' => $round['question_text'],
        'status' => $status,
        'is_host' => $is_host,
        'player_count' => $player_count,
        'answer_count' => $answer_count,
        'hint_count' => $hint_count,
        'vote_count' => $vote_count,
        'has_answered' => $has_answered,
<<<<<<< HEAD
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

=======
        'has_submitted_hint' => $has_submitted_hint,
        'has_voted' => $has_voted
    ];

    // 議論中は絵文字だけを公開
    if ($status === 'discussing') {
>>>>>>> origin/main
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

<<<<<<< HEAD
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

=======
        $stmt->execute([':round_id' => $round_id]);

        $response['hints'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 投票・結果では回答文も公開
    if ($status === 'voting' || $status === 'result') {
>>>>>>> origin/main
        $stmt = $pdo->prepare("
            SELECT
                a.id AS answer_id,
                a.answer_text,
<<<<<<< HEAD
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
=======
                h.emoji1,
                h.emoji2,
                h.emoji3
            FROM who_is_ai.answers a
            JOIN who_is_ai.hints h
              ON h.answer_id = a.id
>>>>>>> origin/main
            WHERE a.round_id = :round_id
            ORDER BY a.id
        ");

<<<<<<< HEAD
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

=======
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
>>>>>>> origin/main
        $stmt = $pdo->prepare("
            SELECT
                u.username,
                s.points
            FROM who_is_ai.scores s
            JOIN who_is_ai.users u
              ON s.user_id = u.id
            WHERE s.room_id = :room_id
<<<<<<< HEAD
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

=======
            ORDER BY s.points DESC, u.username ASC
        ");

        $stmt->execute([':room_id' => $room_id]);

        $response['scores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

>>>>>>> origin/main
    $pdo->commit();

    echo json_encode(
        $response,
<<<<<<< HEAD
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
=======
        JSON_UNESCAPED_UNICODE
>>>>>>> origin/main
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
