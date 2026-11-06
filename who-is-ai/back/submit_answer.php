
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';
require_once __DIR__ . '/lib/contextual_emoji_pool.php';

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

// 送信情報を取得
$user_id = (int)$_SESSION['user_id'];

$round_id = filter_input(
    INPUT_POST,
    'round_id',
    FILTER_VALIDATE_INT
);

$answer_input = $_POST['answer_text'] ?? '';

/* ========================================
   1. 入力チェック
======================================== */

if (!$round_id || $round_id <= 0) {
    http_response_code(400);
    exit("ラウンドIDが正しくありません。");
}

// 配列などの不正な入力を防止
if (!is_string($answer_input)) {
    http_response_code(400);
    exit("回答の形式が正しくありません。");
}

$answer_text = trim($answer_input);

if (
    $answer_text === '' ||
    mb_strlen($answer_text, 'UTF-8') > 200
) {
    http_response_code(400);
    exit("回答は1〜200文字で入力してください。");
}

try {

    $pdo->beginTransaction();

    /* ========================================
       2. ラウンドとルームの状態を確認
    ======================================== */

    // questions テーブルを結合し、
    // お題の文章も取得する
    $sql = "
        SELECT
            r.id,
            r.room_id,
            r.status,
            rm.status AS room_status,
            q.question_text
        FROM who_is_ai.rounds r
        JOIN who_is_ai.rooms rm
            ON r.room_id = rm.id
        JOIN who_is_ai.questions q
            ON r.question_id = q.id
        WHERE r.id = :round_id
        FOR UPDATE OF r
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':round_id' => $round_id
    ]);

    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$round ||
        $round['status'] !== 'answering' ||
        $round['room_status'] !== 'playing'
    ) {
        throw new Exception(
            "現在は回答できません。"
        );
    }

    /* ========================================
       3. ルーム参加者か確認
    ======================================== */

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

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception(
            "このルームに参加していません。"
        );
    }

    /* ========================================
       4. 回答をDBに保存
    ======================================== */

    $sql = "
        INSERT INTO who_is_ai.answers
            (
                round_id,
                user_id,
                answer_text,
                is_ai
            )
        VALUES
            (
                :round_id,
                :user_id,
                :answer_text,
                FALSE
            )
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
        throw new Exception(
            "このラウンドではすでに回答済みです。"
        );
    }

    /* ========================================
       5. 回答に関連する絵文字12個を生成
    ======================================== */

    // お題と回答文をGeminiに渡す
    //
    // 関連する絵文字：6個
    // ランダムな絵文字：6個
    // 合計：12個

    $emojiPool = generateContextualEmojiPool(
        $round['question_text'],
        $answer_text
    );

    /* ========================================
       6. 生成結果を確認
    ======================================== */

    if (
        !is_array($emojiPool) ||
        count($emojiPool) !== 12 ||
        count(array_unique($emojiPool)) !== 12
    ) {
        throw new Exception(
            "絵文字の生成に失敗しました。"
        );
    }

    foreach ($emojiPool as $emoji) {

        if (
            !is_string($emoji) ||
            trim($emoji) === '' ||
            mb_strlen($emoji, 'UTF-8') > 10
        ) {
            throw new Exception(
                "生成された絵文字が正しくありません。"
            );
        }
    }

    /* ========================================
       7. 絵文字12個をDBに保存
    ======================================== */

    $sql = "
        INSERT INTO who_is_ai.emoji_pools
            (
                answer_id,
                emojis
            )
        VALUES
            (
                :answer_id,
                :emojis
            )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':answer_id' => $answer_id,
        ':emojis' => json_encode(
            $emojiPool,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        )
    ]);

    /* ========================================
       8. 保存を確定
    ======================================== */

    $pdo->commit();

    echo "回答を保存しました。";

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // サーバーログに詳細を記録
    error_log(
        'submit_answer.php: ' . $e->getMessage()
    );

    // DB関連のエラー
    if ($e instanceof PDOException) {
        http_response_code(500);
        exit(
            "データベースの保存に失敗しました。"
        );
    }

    // PHP内部エラー
    if ($e instanceof Error) {
        http_response_code(500);
        exit(
            "サーバー内部でエラーが発生しました。"
        );
    }

    // Gemini APIや絵文字生成のエラー
    http_response_code(400);
    exit($e->getMessage());
}
