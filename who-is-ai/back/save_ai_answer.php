
<?php
<<<<<<< HEAD

require_once __DIR__ . '/lib/ai_client.php';
require_once __DIR__ . '/lib/emoji_pool.php';

/**
 * AIの回答・絵文字候補・ヒントをDBに保存
 *
 * 呼び出し元でトランザクションを開始している前提
 */
function saveAiAnswer(
    PDO $pdo,
    int $round_id,
    string $question
): void {

    /* ========================================
       1. AI専用の12個を生成
       ======================================== */

    $emojiPool = generateEmojiPool();

    if (
        count($emojiPool) !== 12 ||
        count(array_unique($emojiPool)) !== 12
    ) {
        throw new Exception(
            'AI用の絵文字候補の生成に失敗しました。'
        );
    }

    /* ========================================
       2. Geminiで回答と絵文字を生成
       ======================================== */

    $aiData = generateAiAnswer(
        $question,
        $emojiPool
    );

    $answer = trim(
        $aiData['answer'] ?? ''
    );

    $emojis = $aiData['emojis'] ?? [];

    if (
        $answer === '' ||
        !is_array($emojis) ||
        count($emojis) !== 3 ||
        count(array_unique($emojis)) !== 3
    ) {
        throw new Exception(
            'AIの回答が正しくありません。'
        );
    }

    // 12個から選んでいるか最終チェック
    foreach ($emojis as $emoji) {

        if (
            !is_string($emoji) ||
            !in_array($emoji, $emojiPool, true)
        ) {
            throw new Exception(
                'AIが配布されていない絵文字を選択しました。'
            );
        }
    }

    /* ========================================
       3. AIの回答を保存
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
                NULL,
                :answer_text,
                TRUE
            )
        RETURNING id
    ";

    $stmt = $pdo->prepare($sql);

=======
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
>>>>>>> origin/main
    $stmt->execute([
        ':round_id' => $round_id,
        ':answer_text' => $answer
    ]);
<<<<<<< HEAD

    $answer_id = (int)$stmt->fetchColumn();

    if ($answer_id <= 0) {
        throw new Exception(
            'AIの回答を保存できませんでした。'
        );
    }

    /* ========================================
       4. AIに配った12個をDBに保存
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
       5. AIが選んだ3個を保存
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
    ";

    $stmt = $pdo->prepare($sql);

=======
    $answer_id = (int)$stmt->fetchColumn();
    // AIの絵文字を保存
    $sql = "
        INSERT INTO who_is_ai.hints
            (answer_id, emoji1, emoji2, emoji3)
        VALUES
            (:answer_id, :emoji1, :emoji2, :emoji3)
    ";
    $stmt = $pdo->prepare($sql);
>>>>>>> origin/main
    $stmt->execute([
        ':answer_id' => $answer_id,
        ':emoji1' => $emojis[0],
        ':emoji2' => $emojis[1],
        ':emoji3' => $emojis[2]
    ]);
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
