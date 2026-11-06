
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../db/connect_db.php';

function sendJson(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

if (!isset($_SESSION['user_id'])) {
    sendJson(401, [
        'success' => false,
        'message' => 'ログインしてください。'
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJson(405, [
        'success' => false,
        'message' => 'GET通信のみ許可されています。'
    ]);
}

$userId = (int)$_SESSION['user_id'];

$roundId = filter_input(
    INPUT_GET,
    'round_id',
    FILTER_VALIDATE_INT
);

if (!$roundId || $roundId <= 0) {
    sendJson(400, [
        'success' => false,
        'message' => 'ラウンドIDが不正です。'
    ]);
}

try {
    $sql = "
        SELECT ep.emojis
        FROM who_is_ai.emoji_pools ep

        JOIN who_is_ai.answers a
            ON ep.answer_id = a.id

        JOIN who_is_ai.rounds r
            ON a.round_id = r.id

        JOIN who_is_ai.rooms rm
            ON r.room_id = rm.id

        JOIN who_is_ai.room_players rp
            ON rp.room_id = r.room_id
            AND rp.user_id = :player_id

        WHERE a.round_id = :round_id
          AND a.user_id = :answer_user_id
          AND a.is_ai = FALSE
          AND r.status = 'hinting'
          AND rm.status = 'playing'
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':player_id' => $userId,
        ':round_id' => $roundId,
        ':answer_user_id' => $userId
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        sendJson(404, [
            'success' => false,
            'message' => '絵文字候補が見つかりません。'
        ]);
    }

    $emojis = json_decode(
        $row['emojis'],
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (
        !is_array($emojis) ||
        count($emojis) !== 12 ||
        count(array_unique($emojis)) !== 12
    ) {
        throw new RuntimeException(
            '絵文字候補のデータが不正です。'
        );
    }

    sendJson(200, [
        'success' => true,
        'emojis' => array_values($emojis)
    ]);

} catch (Throwable $e) {
    error_log(
        'get_emoji_pool.php: ' . $e->getMessage()
    );

    sendJson(500, [
        'success' => false,
        'message' => '絵文字候補の取得に失敗しました。'
    ]);
}
