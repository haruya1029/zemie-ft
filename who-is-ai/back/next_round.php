
<?php
session_start();

require_once __DIR__ . '/../db/connect_db.php';
require_once __DIR__ . '/save_ai_answer.php';

header('Content-Type: text/plain; charset=utf-8');

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('ログインしてください。');
}

// POSTのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('不正なリクエストです。');
}

$user_id = (int)$_SESSION['user_id'];

$room_id = filter_input(
    INPUT_POST,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$room_id || $room_id <= 0) {
    http_response_code(400);
    exit('ルームIDが正しくありません。');
}

try {

    $pdo->beginTransaction();

    // ルームをロック
    $stmt = $pdo->prepare("
        SELECT id, host_user_id, status
        FROM who_is_ai.rooms
        WHERE id = :room_id
        FOR UPDATE
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception(
            'ルームが見つかりません。'
        );
    }

    // ホスト確認
    if ((int)$room['host_user_id'] !== $user_id) {
        throw new Exception(
            'ホストのみ操作できます。'
        );
    }

    // ゲーム進行中か確認
    if ($room['status'] !== 'playing') {
        throw new Exception(
            'ゲームが進行中ではありません。'
        );
    }

    // 最新ラウンド取得
    $stmt = $pdo->prepare("
        SELECT id, round_number, status
        FROM who_is_ai.rounds
        WHERE room_id = :room_id
        ORDER BY round_number DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $round = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$round) {
        throw new Exception(
            'ラウンドが存在しません。'
        );
    }

    // 結果発表後のみ進める
    if ($round['status'] !== 'result') {
        throw new Exception(
            'まだ次のラウンドへ進めません。'
        );
    }

    $next_round_number =
        (int)$round['round_number'] + 1;
                // 最大3ラウンド
                if ($next_round_number > 3) {

        $stmt = $pdo->prepare("
            UPDATE who_is_ai.rooms
            SET status = 'finished'
            WHERE id = :room_id
        ");

        $stmt->execute([
            ':room_id' => $room_id
        ]);

        $pdo->commit();

        exit('全5ラウンドが終了しました！');
    }

    // 未使用のお題を取得
    $stmt = $pdo->prepare("
        SELECT q.id, q.question_text
        FROM who_is_ai.questions q
        WHERE q.id NOT IN (
            SELECT r.question_id
            FROM who_is_ai.rounds r
            WHERE r.room_id = :room_id
        )
        ORDER BY RANDOM()
        LIMIT 1
    ");

    $stmt->execute([
        ':room_id' => $room_id
    ]);

    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {

        $stmt = $pdo->prepare("
            UPDATE who_is_ai.rooms
            SET status = 'finished'
            WHERE id = :room_id
        ");

        $stmt->execute([
            ':room_id' => $room_id
        ]);

        $pdo->commit();

        exit('すべてのお題が終了しました。');
    }

    // 次のラウンドを登録
    $stmt = $pdo->prepare("
        INSERT INTO who_is_ai.rounds
            (room_id, round_number, question_id, status)
        VALUES
            (:room_id, :round_number, :question_id, 'answering')
        RETURNING id
    ");

    $stmt->execute([
        ':room_id' => $room_id,
        ':round_number' => $next_round_number,
        ':question_id' => $question['id']
    ]);

    $round_id = (int)$stmt->fetchColumn();

    // AI回答と絵文字を生成・保存
    saveAiAnswer(
        $pdo,
        $round_id,
        $question['question_text']
    );

    // AI生成成功後に確定
    $pdo->commit();

    echo "第{$next_round_number}ラウンドを開始しました。";

} catch (Throwable $e) {

    // 失敗したラウンドを取り消す
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // サーバーログにも記録
    error_log(
        'next_round.php エラー: ' .
        $e->getMessage() .
        ' / ファイル: ' .
        $e->getFile() .
        ' / 行: ' .
        $e->getLine()
    );

    http_response_code(400);

    // 開発中のため詳細表示
    exit(
        'エラー内容: ' . $e->getMessage() .
        "\nエラーファイル: " . basename($e->getFile()) .
        "\n行番号: " . $e->getLine()
    );
}
