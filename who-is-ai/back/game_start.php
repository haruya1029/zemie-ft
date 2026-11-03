
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';
require_once __DIR__ . '/save_ai_answer.php';

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

// ユーザーID
$user_id = (int)$_SESSION['user_id'];

// ルームID取得
$room_id = filter_input(
    INPUT_POST,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$room_id || $room_id <= 0) {
    http_response_code(400);
    exit("ルームIDが正しくありません。");
}

try {

    // トランザクション開始
    $pdo->beginTransaction();

    // ルーム情報を取得してロック
    $stmt = $pdo->prepare("
        SELECT
            id,
            host_user_id,
            status
        FROM who_is_ai.rooms
        WHERE id = :room_id
        FOR UPDATE
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception("ルームが見つかりません。");
    }

    // ホストのみ開始可能
    if ((int)$room['host_user_id'] !== $user_id) {
        throw new Exception(
            "ゲームを開始できるのはホストだけです。"
        );
    }

    // 待機中のルームのみ開始可能
    if ($room['status'] !== 'waiting') {
        throw new Exception(
            "このゲームはすでに開始されています。"
        );
    }

    // お題をランダムに取得
    $stmt = $pdo->query("
        SELECT
            id,
            question_text
        FROM who_is_ai.questions
        ORDER BY RANDOM()
        LIMIT 1
    ");

    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        throw new Exception(
            "お題が登録されていません。"
        );
    }

    // 第1ラウンドを作成
    $stmt = $pdo->prepare("
        INSERT INTO who_is_ai.rounds
            (
                room_id,
                round_number,
                question_id,
                status
            )
        VALUES
            (
                :room_id,
                1,
                :question_id,
                'answering'
            )
        RETURNING id
    ");

    $stmt->execute([
        ':room_id' => $room_id,
        ':question_id' => $question['id']
    ]);

    // ラウンドIDを取得
    $round_id = (int)$stmt->fetchColumn();

    if ($round_id <= 0) {
        throw new Exception(
            "ラウンドの作成に失敗しました。"
        );
    }

    // Gemini APIで回答と絵文字を生成してDBに保存
    saveAiAnswer(
        $pdo,
        $round_id,
        $question['question_text']
    );

    // ルームをゲーム進行中に変更
    $stmt = $pdo->prepare("
        UPDATE who_is_ai.rooms
        SET status = 'playing'
        WHERE id = :room_id
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    // すべて成功したら確定
    $pdo->commit();

    // 成功メッセージ
    echo "ゲームを開始しました。";

} catch (Throwable $e) {

    // エラー時はDB変更を取り消す
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // サーバーログに記録
    error_log(
        'game_start.php エラー: ' .
        $e->getMessage() .
        ' / ファイル: ' .
        basename($e->getFile()) .
        ' / 行番号: ' .
        $e->getLine()
    );

    // 開発中のエラー表示
    http_response_code(400);

    exit(
        "エラーファイル: " .
        basename($e->getFile()) .
        " / 行番号: " .
        $e->getLine() .
        " / エラー内容: " .
        $e->getMessage()
    );
}
