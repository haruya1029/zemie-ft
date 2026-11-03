
<?php
require_once __DIR__ . '/lib/ai_client.php';
/**
 * AIの回答と絵文字をDBに保存する
 *
 * 呼び出し元でトランザクションを開始している前提
 */
function saveAiAnswer(PDO $pdo, int $round_id, string $question): void
{
    // Geminiで回答と絵文字を生成
    $aiData = generateAiAnswer($question);
    $answer = trim($aiData['answer'] ?? '');
    $emojis = $aiData['emojis'] ?? [];
    if (
        $answer === '' ||
        !is_array($emojis) ||
        count($emojis) !== 3
    ) {
        throw new Exception("AIの回答が正しくありません。");
    }
    foreach ($emojis as $emoji) {
        if (
            !is_string($emoji) ||
            trim($emoji) === '' ||
            mb_strlen($emoji) > 10
        ) {
            throw new Exception("AIの絵文字が正しくありません。");
        }
    }
    // AIの回答を保存
    $sql = "
        INSERT INTO who_is_ai.answers
            (round_id, user_id, answer_text, is_ai)
        VALUES
            (:round_id, NULL, :answer_text, TRUE)
        RETURNING id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':round_id' => $round_id,
        ':answer_text' => $answer
    ]);
    $answer_id = (int)$stmt->fetchColumn();
    // AIの絵文字を保存
    $sql = "
        INSERT INTO who_is_ai.hints
            (answer_id, emoji1, emoji2, emoji3)
        VALUES
            (:answer_id, :emoji1, :emoji2, :emoji3)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':answer_id' => $answer_id,
        ':emoji1' => $emojis[0],
        ':emoji2' => $emojis[1],
        ':emoji3' => $emojis[2]
    ]);
}