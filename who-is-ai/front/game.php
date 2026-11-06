
<?php
session_start();

// ログイン確認
if (!isset($_SESSION['user_id'])) {
<<<<<<< HEAD
    header('Location: index.php');
=======
    header('Location: home.php');
>>>>>>> origin/main
    exit;
}

// ルームID取得
$room_id = filter_input(
    INPUT_GET,
    'room_id',
    FILTER_VALIDATE_INT
);

if (!$room_id) {
    $room_id = (int)($_SESSION['room_id'] ?? 0);
}

if ($room_id <= 0) {
    http_response_code(400);
    exit('ルームIDが指定されていません。');
}
<<<<<<< HEAD
?>

=======

if (
    isset($_SESSION['room_id']) &&
    (int)$_SESSION['room_id'] !== $room_id
) {
    http_response_code(403);
    exit('参加しているルームと一致しません。');
}
?>
>>>>>>> origin/main
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD

<title>WHO IS AI? - GAME</title>

<style>

/* ========================================
   基本デザイン
======================================== */

=======
<title>WHO IS AI? - GAME</title>

<style>
>>>>>>> origin/main
* {
    box-sizing: border-box;
}

body {
    margin: 0;
<<<<<<< HEAD
    min-height: 100vh;
=======
>>>>>>> origin/main
    background: #101820;
    color: #ffffff;
    font-family: Arial, sans-serif;
}

.container {
    max-width: 760px;
    margin: 40px auto;
    padding: 20px;
}

h1 {
    color: #b9ff66;
    text-align: center;
    letter-spacing: 2px;
}

<<<<<<< HEAD
h2 {
    margin-top: 0;
    font-size: 22px;
}

h3 {
    font-size: 18px;
}

=======
>>>>>>> origin/main
.card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 16px;
    padding: 24px;
    margin-top: 20px;
}

.small {
    color: #94a3b8;
    font-size: 14px;
}

.question {
    font-size: 23px;
    font-weight: bold;
    margin-top: 12px;
<<<<<<< HEAD
    overflow-wrap: anywhere;
=======
>>>>>>> origin/main
}

input,
textarea {
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    border: 1px solid #475569;
    border-radius: 10px;
    background: #0f172a;
    color: white;
    font-size: 16px;
}

<<<<<<< HEAD
textarea {
    resize: vertical;
    font-family: inherit;
}

=======
>>>>>>> origin/main
button {
    background: #b9ff66;
    color: #101820;
    border: none;
    border-radius: 10px;
    padding: 13px 20px;
    margin-top: 14px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
<<<<<<< HEAD
    transition: 0.2s;
=======
>>>>>>> origin/main
}

button:hover {
    background: #a3e653;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hidden {
    display: none !important;
}

<<<<<<< HEAD
/* ========================================
   絵文字選択
======================================== */

.emoji-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-top: 20px;
}

.emoji-choice {
    width: 100%;
    aspect-ratio: 1;
    padding: 8px;
    margin: 0;

    display: flex;
    justify-content: center;
    align-items: center;

    background: #0f172a;
    border: 2px solid #475569;
    border-radius: 12px;

    font-size: 28px;
    cursor: pointer;
    transition: 0.2s;
}

.emoji-choice:hover {
    background: #334155;
    border-color: #b9ff66;
}

.emoji-choice.selected {
    background: #344a25;
    border-color: #b9ff66;
    box-shadow: 0 0 12px rgba(185, 255, 102, 0.25);
}

.emoji-preview {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
}

.emoji-slot {
    width: 65px;
    height: 65px;

    display: flex;
    justify-content: center;
    align-items: center;

    background: #0f172a;
    border: 1px solid #475569;
    border-radius: 12px;

    font-size: 30px;
}

.emoji-counter {
    margin-top: 15px;
    color: #b9ff66;
    font-weight: bold;
=======
.emoji-row {
    display: flex;
    gap: 10px;
}

.emoji-row input {
    text-align: center;
    font-size: 26px;
    min-width: 0;
>>>>>>> origin/main
}

.emoji-display {
    font-size: 36px;
    letter-spacing: 5px;
    margin-top: 10px;
    overflow-wrap: anywhere;
}

<<<<<<< HEAD
/* ========================================
   投票画面
======================================== */

=======
>>>>>>> origin/main
.answer-option {
    display: block;
    background: #334155;
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 18px;
    margin-top: 12px;
    cursor: pointer;
}

.answer-option:hover {
    background: #475569;
}

.answer-option.selected {
    border-color: #b9ff66;
    background: #3b4d5d;
}

.answer-option input {
    width: auto;
    margin: 0 10px 0 0;
}

.option-title {
    color: #94a3b8;
    font-size: 14px;
}

<<<<<<< HEAD
/* ========================================
   メッセージ
======================================== */

=======
>>>>>>> origin/main
.message {
    margin-top: 15px;
    color: #b9ff66;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.error {
    color: #ff8b8b;
}

<<<<<<< HEAD
/* ========================================
   結果発表
======================================== */

.ai-reveal {
    background: #334155;
    border: 2px solid #b9ff66;
    border-radius: 12px;
    padding: 20px;
    margin-top: 16px;
    text-align: center;
}

.ai-reveal h3 {
    color: #b9ff66;
}

/* 全員の回答公開 */

.reveal-heading {
    margin-top: 28px;
    color: #ffffff;
}

.reveal-card {
    background: #0f172a;
    border: 1px solid #475569;
    border-radius: 14px;
    padding: 20px;
    margin-top: 15px;
}

.reveal-card.is-ai {
    border: 2px solid #b9ff66;
}

.reveal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.reveal-role {
    color: #94a3b8;
    font-size: 14px;
    font-weight: bold;
}

.reveal-role.ai {
    color: #b9ff66;
}

.reveal-answer {
    font-size: 17px;
    margin-top: 16px;
    overflow-wrap: anywhere;
}

.reveal-pool-title {
    margin-top: 20px;
}

.reveal-pool .emoji-choice {
    cursor: default;
}

.reveal-pool .emoji-choice:hover {
    transform: none;
}

.reveal-pool .emoji-choice.selected {
    background: #344a25;
    border-color: #b9ff66;
}

/* ========================================
   スコア
======================================== */

=======
>>>>>>> origin/main
.score-row {
    padding: 12px;
    border-bottom: 1px solid #334155;
}

.final-title {
    text-align: center;
    color: #b9ff66;
    font-size: 28px;
}

.final-subtitle {
    text-align: center;
    color: #94a3b8;
}

.winner-card {
    background: #334155;
    border: 2px solid #b9ff66;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    margin: 20px 0;
}

.winner-name {
    font-size: 25px;
    font-weight: bold;
    margin: 12px 0;
<<<<<<< HEAD
    overflow-wrap: anywhere;
=======
>>>>>>> origin/main
}

.winner-points {
    font-size: 36px;
    font-weight: bold;
    color: #b9ff66;
}

.final-message {
    text-align: center;
    margin-top: 24px;
    font-weight: bold;
}

<<<<<<< HEAD
/* ========================================
   スマホ対応
======================================== */

@media (max-width: 480px) {

=======
.ai-reveal {
    background: #334155;
    border: 2px solid #b9ff66;
    border-radius: 12px;
    padding: 20px;
    margin-top: 16px;
    text-align: center;
}

@media (max-width: 480px) {
>>>>>>> origin/main
    .container {
        margin: 15px auto;
        padding: 12px;
    }

    .card {
        padding: 18px;
    }

<<<<<<< HEAD
    .emoji-grid {
        grid-template-columns: repeat(4, 1fr);
    }

    .emoji-choice {
        font-size: 25px;
    }

    .emoji-display {
        font-size: 30px;
    }

    .emoji-slot {
        width: 55px;
        height: 55px;
    }

    .reveal-card {
        padding: 15px;
    }
}

=======
    .emoji-display {
        font-size: 30px;
    }
}
>>>>>>> origin/main
</style>
</head>

<body>

<div class="container">

    <h1>WHO IS AI?</h1>

    <p class="small" style="text-align:center;">
        GAME / ROOM
        <?php
        echo htmlspecialchars(
            (string)$room_id,
            ENT_QUOTES,
            'UTF-8'
        );
        ?>
    </p>

<<<<<<< HEAD
    <!-- =====================================
         お題
    ====================================== -->

    <div class="card">

=======
    <!-- お題 -->
    <div class="card">
>>>>>>> origin/main
        <p id="roundLabel" class="small">
            ゲーム情報を取得中...
        </p>

        <div id="question" class="question">
            読み込み中...
        </div>

        <p id="progress" class="small"></p>
<<<<<<< HEAD

    </div>

    <!-- =====================================
         ゲーム開始待ち
    ====================================== -->

    <div id="waitingSection" class="card hidden">

        <h2>ゲーム開始待ち</h2>

        <p>
            ホストがゲームを開始してください。
        </p>
=======
    </div>

    <!-- ゲーム開始待ち -->
    <div id="waitingSection" class="card hidden">
        <h2>ゲーム開始待ち</h2>
        <p>ホストがゲームを開始してください。</p>
>>>>>>> origin/main

        <button
            id="startButton"
            onclick="startGame()"
        >
            ゲーム開始
        </button>
<<<<<<< HEAD

    </div>

    <!-- =====================================
         回答入力
    ====================================== -->

    <div id="answerSection" class="card hidden">

        <h2>あなたの回答</h2>

        <p class="small">
            回答文は投票終了まで公開されません。
=======
    </div>

    <!-- 回答入力 -->
    <div id="answerSection" class="card hidden">
        <h2>あなたの回答</h2>

        <p class="small">
            回答文はほかのプレイヤーには公開されません。
>>>>>>> origin/main
        </p>

        <textarea
            id="answerText"
            maxlength="200"
            rows="3"
            placeholder="お題に対する回答を入力"
        ></textarea>

        <button onclick="submitAnswer()">
            回答を送信
        </button>
<<<<<<< HEAD

    </div>

    <!-- =====================================
         絵文字選択
    ====================================== -->

    <div id="hintSection" class="card hidden">

        <h2>絵文字ヒントを3つ選ぼう</h2>

        <p class="small">
            あなたに配られた12個の絵文字から、
            自分の回答を表現する3個を選んでください。
        </p>

        <div
            id="emojiGrid"
            class="emoji-grid"
        ></div>

        <p
            id="emojiCounter"
            class="emoji-counter"
        >
            選択中：0 / 3
        </p>

        <div class="emoji-preview">

            <div
                class="emoji-slot"
                id="selectedEmoji1"
            >？</div>

            <div
                class="emoji-slot"
                id="selectedEmoji2"
            >？</div>

            <div
                class="emoji-slot"
                id="selectedEmoji3"
            >？</div>

        </div>

        <button
            id="submitHintButton"
            onclick="submitHint()"
            disabled
        >
            ヒントを送信
        </button>

    </div>

    <!-- =====================================
         投票画面
    ====================================== -->

=======
    </div>

    <!-- 絵文字入力 -->
    <div id="hintSection" class="card hidden">
        <h2>絵文字ヒントを3つ選ぼう</h2>

        <p class="small">
            自分の回答を連想させる絵文字を
            3つ入力してください。
        </p>

        <div class="emoji-row">
            <input id="emoji1" maxlength="10" placeholder="😀">
            <input id="emoji2" maxlength="10" placeholder="🎮">
            <input id="emoji3" maxlength="10" placeholder="✨">
        </div>

        <button onclick="submitHint()">
            ヒントを送信
        </button>
    </div>

    <!-- 絵文字だけの投票画面 -->
>>>>>>> origin/main
    <div id="voteSection" class="card hidden">

        <h2>どの絵文字がAI？</h2>

        <p class="small">
            全員の絵文字が公開されました！
<<<<<<< HEAD
            AIが選んだと思うものを選んでください。
=======
            AIが考えた絵文字だと思うものを選んでください。
>>>>>>> origin/main
        </p>

        <div id="answerList"></div>

        <button onclick="submitVote()">
            この絵文字に投票
        </button>
<<<<<<< HEAD

    </div>

    <!-- =====================================
         通常結果
    ====================================== -->

=======
    </div>

    <!-- 通常結果 -->
>>>>>>> origin/main
    <div id="resultSection" class="card hidden">

        <h2>結果発表！</h2>

        <div id="resultText"></div>

        <h3>現在のランキング</h3>

        <div id="scoreList"></div>

        <button
            id="nextRoundButton"
            onclick="nextRound()"
        >
            次のラウンドへ
        </button>
<<<<<<< HEAD

    </div>

    <!-- =====================================
         最終結果
    ====================================== -->

=======
    </div>

    <!-- 最終結果 -->
>>>>>>> origin/main
    <div id="finalSection" class="card hidden">

        <h2 class="final-title">
            🏆 FINAL RESULT
        </h2>

        <p class="final-subtitle">
            全3ラウンド終了！
        </p>

        <div id="winnerArea"></div>

        <h3>総合ランキング</h3>

        <div id="finalScoreList"></div>

        <p class="final-message">
            ゲーム終了！お疲れさまでした！
        </p>
<<<<<<< HEAD

    </div>

    <!-- =====================================
         待機画面
    ====================================== -->

    <div id="waitSection" class="card hidden">

        <h2>
            ほかのプレイヤーを待っています...
        </h2>

        <p
            id="waitMessage"
            class="small"
        ></p>

    </div>

    <!-- メッセージ -->

=======
    </div>

    <!-- 待機 -->
    <div id="waitSection" class="card hidden">

        <h2>ほかのプレイヤーを待っています...</h2>

        <p id="waitMessage" class="small"></p>
    </div>

>>>>>>> origin/main
    <p id="message" class="message"></p>

    <button onclick="loadGameState()">
        状態を更新
    </button>

</div>

<script>

<<<<<<< HEAD
/* ========================================
   基本設定
======================================== */

=======
>>>>>>> origin/main
const roomId = <?php
    echo json_encode((int)$room_id);
?>;

const MAX_ROUNDS = 3;

let currentRoundId = null;
let currentStatus = null;
let latestState = null;

let loading = false;
let operating = false;
let lastRoundId = null;

<<<<<<< HEAD
// 絵文字選択
let emojiPool = [];
let selectedEmojis = [];
let emojiPoolRoundId = null;
let emojiPoolLoading = false;

/* ========================================
   共通処理
======================================== */

=======
>>>>>>> origin/main
// 画面をすべて隠す
function hideSections() {

    const ids = [
        'waitingSection',
        'answerSection',
        'hintSection',
        'voteSection',
        'resultSection',
        'finalSection',
        'waitSection'
    ];

    ids.forEach(id => {
<<<<<<< HEAD

        document.getElementById(id)
            .classList.add('hidden');

=======
        document.getElementById(id)
            .classList.add('hidden');
>>>>>>> origin/main
    });
}

// メッセージ表示
function showMessage(text, isError = false) {

<<<<<<< HEAD
    const el =
        document.getElementById('message');

    el.textContent = text;

    el.classList.toggle(
        'error',
        isError
    );
}

// POST通信
=======
    const el = document.getElementById('message');

    el.textContent = text;
    el.classList.toggle('error', isError);
}

// APIにPOST
>>>>>>> origin/main
async function postData(file, data) {

    const body = new URLSearchParams(data);

    const response = await fetch(
        '../back/' + file,
        {
            method: 'POST',
            body: body,
            credentials: 'same-origin'
        }
    );

    const text = await response.text();

    let json = null;

    try {
        json = JSON.parse(text);
    } catch (e) {
<<<<<<< HEAD
        // テキスト応答にも対応
    }

    if (!response.ok) {

=======
        // JSON以外にも対応
    }

    if (!response.ok) {
>>>>>>> origin/main
        throw new Error(
            json?.message ||
            text ||
            '処理に失敗しました。'
        );
    }

    if (json && json.success === false) {
<<<<<<< HEAD

        throw new Error(
            json.message ||
            '処理に失敗しました。'
=======
        throw new Error(
            json.message || '処理に失敗しました。'
>>>>>>> origin/main
        );
    }

    return json?.message || text;
}

<<<<<<< HEAD
/* ========================================
   絵文字12個の取得
======================================== */

async function loadEmojiPool() {

    if (
        !currentRoundId ||
        emojiPoolLoading ||
        emojiPoolRoundId === currentRoundId
    ) {
        return;
    }

    const requestedRoundId = currentRoundId;

    emojiPoolLoading = true;

    try {

        const response = await fetch(
            '../back/get_emoji_pool.php?round_id=' +
            encodeURIComponent(requestedRoundId),
            {
                credentials: 'same-origin',
                cache: 'no-store'
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {

            throw new Error(
                data.message ||
                '絵文字を取得できません。'
            );
        }

        if (
            !Array.isArray(data.emojis) ||
            data.emojis.length !== 12 ||
            new Set(data.emojis).size !== 12
        ) {

            throw new Error(
                '絵文字候補のデータが不正です。'
            );
        }

        // ラウンド変更時は古い結果を破棄
        if (currentRoundId !== requestedRoundId) {
            return;
        }

        emojiPool = data.emojis;

        emojiPoolRoundId = requestedRoundId;

        renderEmojiPool();

    } catch (error) {

        showMessage(
            error.message,
            true
        );

    } finally {

        emojiPoolLoading = false;
    }
}

/* ========================================
   絵文字候補表示
======================================== */

function renderEmojiPool() {

    const grid =
        document.getElementById('emojiGrid');

    grid.replaceChildren();

    emojiPool.forEach(emoji => {

        const button =
            document.createElement('button');

        button.type = 'button';

        button.className = 'emoji-choice';

        button.textContent = emoji;

        const isSelected =
            selectedEmojis.includes(emoji);

        if (isSelected) {
            button.classList.add('selected');
        }

        button.setAttribute(
            'aria-pressed',
            String(isSelected)
        );

        button.addEventListener(
            'click',
            () => toggleEmoji(emoji)
        );

        grid.appendChild(button);
    });

    updateEmojiPreview();
}

// 絵文字選択・解除
function toggleEmoji(emoji) {

    if (!emojiPool.includes(emoji)) {
        return;
    }

    if (selectedEmojis.includes(emoji)) {

        selectedEmojis =
            selectedEmojis.filter(
                item => item !== emoji
            );

    } else {

        if (selectedEmojis.length >= 3) {
            return;
        }

        selectedEmojis.push(emoji);
    }

    renderEmojiPool();
}

// 選択中の3個を表示
function updateEmojiPreview() {

    for (let i = 0; i < 3; i++) {

        document.getElementById(
            'selectedEmoji' + (i + 1)
        ).textContent =
            selectedEmojis[i] || '？';
    }

    document.getElementById(
        'emojiCounter'
    ).textContent =
        '選択中：' +
        selectedEmojis.length +
        ' / 3';

    document.getElementById(
        'submitHintButton'
    ).disabled =
        selectedEmojis.length !== 3 ||
        operating;
}

/* ========================================
   ゲーム状態の取得
======================================== */

=======
// ゲーム状態取得
>>>>>>> origin/main
async function loadGameState() {

    if (loading || operating) {
        return;
    }

    loading = true;

    try {

        const response = await fetch(
            '../back/game_state.php?room_id=' +
            encodeURIComponent(roomId),
            {
                credentials: 'same-origin',
                cache: 'no-store'
            }
        );

        const state = await response.json();

        if (!response.ok || !state.success) {
<<<<<<< HEAD

=======
>>>>>>> origin/main
            throw new Error(
                state.message ||
                'ゲーム情報を取得できません。'
            );
        }

        latestState = state;
<<<<<<< HEAD

        currentRoundId =
            state.round_id || null;

        currentStatus = state.status;

        // 新しいラウンドなら初期化
=======
        currentRoundId = state.round_id || null;
        currentStatus = state.status;

        // 新ラウンドなら入力欄をリセット
>>>>>>> origin/main
        if (
            currentRoundId &&
            currentRoundId !== lastRoundId
        ) {
<<<<<<< HEAD

            document.getElementById(
                'answerText'
            ).value = '';

            emojiPool = [];
            selectedEmojis = [];
            emojiPoolRoundId = null;

            renderEmojiPool();
=======
            document.getElementById('answerText').value = '';
            document.getElementById('emoji1').value = '';
            document.getElementById('emoji2').value = '';
            document.getElementById('emoji3').value = '';
>>>>>>> origin/main

            lastRoundId = currentRoundId;
        }

        renderGame(state);

    } catch (error) {

<<<<<<< HEAD
        showMessage(
            error.message,
            true
        );
=======
        showMessage(error.message, true);
>>>>>>> origin/main

    } finally {

        loading = false;
    }
}

<<<<<<< HEAD
/* ========================================
   ゲーム画面の切り替え
======================================== */

=======
// 画面切り替え
>>>>>>> origin/main
function renderGame(state) {

    hideSections();

    const questionEl =
        document.getElementById('question');

    const roundEl =
        document.getElementById('roundLabel');

    const progressEl =
        document.getElementById('progress');

<<<<<<< HEAD
    /* ゲーム開始待ち */

    if (state.status === 'waiting') {

        roundEl.textContent = 'WAITING';

        questionEl.textContent =
            'ゲーム開始待ち';

=======
    // 開始待ち
    if (state.status === 'waiting') {

        roundEl.textContent = 'WAITING';
        questionEl.textContent = 'ゲーム開始待ち';
>>>>>>> origin/main
        progressEl.textContent = '';

        document.getElementById(
            'waitingSection'
        ).classList.remove('hidden');

        document.getElementById(
            'startButton'
        ).classList.toggle(
            'hidden',
<<<<<<< HEAD
            !state.is_host
=======
            state.is_host === false
>>>>>>> origin/main
        );

        return;
    }

<<<<<<< HEAD
    /* お題・進行状況 */

    roundEl.textContent =
        'ROUND ' +
        state.round_number +
        ' / ' +
        MAX_ROUNDS;
=======
    roundEl.textContent =
        'ROUND ' + state.round_number +
        ' / ' + MAX_ROUNDS;
>>>>>>> origin/main

    questionEl.textContent =
        state.question || '';

    progressEl.textContent =
<<<<<<< HEAD
        '回答 ' +
        state.answer_count +
        '/' +
        state.player_count +
        '人 ・ ヒント ' +
        state.hint_count +
        '/' +
        state.player_count +
        '人 ・ 投票 ' +
        state.vote_count +
        '/' +
        state.player_count +
        '人';

    /* 回答中 */

=======
        '回答 ' + state.answer_count + '/' +
        state.player_count + '人 ・ ヒント ' +
        state.hint_count + '/' +
        state.player_count + '人 ・ 投票 ' +
        state.vote_count + '/' +
        state.player_count + '人';

    // 回答入力
>>>>>>> origin/main
    if (state.status === 'answering') {

        if (state.has_answered) {

            showWaiting(
                '全員の回答を待っています。'
            );

        } else {

            document.getElementById(
                'answerSection'
            ).classList.remove('hidden');
        }
    }

<<<<<<< HEAD
    /* 絵文字選択中 */

=======
    // 絵文字入力
>>>>>>> origin/main
    else if (state.status === 'hinting') {

        if (state.has_submitted_hint) {

            showWaiting(
                '全員の絵文字ヒントを待っています。'
            );

        } else {

            document.getElementById(
                'hintSection'
            ).classList.remove('hidden');
<<<<<<< HEAD

            loadEmojiPool();
        }
    }

    /* 投票中 */

=======
        }
    }

    // 絵文字だけで投票
>>>>>>> origin/main
    else if (state.status === 'voting') {

        if (state.has_voted) {

            showWaiting(
                '全員の投票を待っています。'
            );

        } else {

            document.getElementById(
                'voteSection'
            ).classList.remove('hidden');

<<<<<<< HEAD
            renderAnswers(
                state.answers || []
            );
        }
    }

    /* 結果発表 */

    else if (state.status === 'result') {

        if (
            Number(state.round_number) >=
            MAX_ROUNDS
=======
            renderAnswers(state.answers || []);
        }
    }

    // 結果発表
    else if (state.status === 'result') {

        if (
            Number(state.round_number) >= MAX_ROUNDS
>>>>>>> origin/main
        ) {

            document.getElementById(
                'finalSection'
            ).classList.remove('hidden');

            renderFinalResult(state);

        } else {

            document.getElementById(
                'resultSection'
            ).classList.remove('hidden');

            document.getElementById(
                'nextRoundButton'
            ).classList.toggle(
                'hidden',
<<<<<<< HEAD
                !state.is_host
=======
                state.is_host !== true
>>>>>>> origin/main
            );

            renderResult(state);
        }
    }

<<<<<<< HEAD
    /* その他 */

=======
>>>>>>> origin/main
    else {

        showWaiting(
            'ゲーム情報を確認しています。'
        );
    }
}

<<<<<<< HEAD
/* ========================================
   待機画面
======================================== */

=======
// 待機画面
>>>>>>> origin/main
function showWaiting(message) {

    document.getElementById(
        'waitSection'
    ).classList.remove('hidden');

    document.getElementById(
        'waitMessage'
    ).textContent = message;
}

<<<<<<< HEAD
/* ========================================
   ゲーム開始
======================================== */

async function startGame() {

    if (operating) return;

=======
// ゲーム開始
async function startGame() {

    if (operating) return;
>>>>>>> origin/main
    operating = true;

    try {

<<<<<<< HEAD
        showMessage(
            'ゲームを開始しています...'
        );

        const result = await postData(
            'game_start.php',
            {
                room_id: roomId
            }
=======
        showMessage('ゲームを開始しています...');

        const result = await postData(
            'game_start.php',
            { room_id: roomId }
>>>>>>> origin/main
        );

        showMessage(result);

    } catch (error) {

<<<<<<< HEAD
        showMessage(
            error.message,
            true
        );
=======
        showMessage(error.message, true);
>>>>>>> origin/main

    } finally {

        operating = false;
<<<<<<< HEAD

=======
>>>>>>> origin/main
        await loadGameState();
    }
}

<<<<<<< HEAD
/* ========================================
   回答送信
======================================== */

=======
// 回答送信
>>>>>>> origin/main
async function submitAnswer() {

    if (operating) return;

    const answer =
<<<<<<< HEAD
        document.getElementById(
            'answerText'
        ).value.trim();

    if (!answer) {

=======
        document.getElementById('answerText')
            .value.trim();

    if (!answer) {
>>>>>>> origin/main
        showMessage(
            '回答を入力してください。',
            true
        );
<<<<<<< HEAD

=======
>>>>>>> origin/main
        return;
    }

    operating = true;

    try {

        const result = await postData(
            'submit_answer.php',
            {
                round_id: currentRoundId,
                answer_text: answer
            }
        );

        showMessage(result);

    } catch (error) {

<<<<<<< HEAD
        showMessage(
            error.message,
            true
        );
=======
        showMessage(error.message, true);
>>>>>>> origin/main

    } finally {

        operating = false;
<<<<<<< HEAD

=======
>>>>>>> origin/main
        await loadGameState();
    }
}

<<<<<<< HEAD
/* ========================================
   絵文字ヒント送信
======================================== */

=======
// 絵文字送信
>>>>>>> origin/main
async function submitHint() {

    if (operating) return;

<<<<<<< HEAD
    if (selectedEmojis.length !== 3) {

        showMessage(
            '絵文字を3個選択してください。',
            true
        );

        return;
    }

    if (
        new Set(selectedEmojis).size !== 3 ||
        selectedEmojis.some(
            emoji => !emojiPool.includes(emoji)
        )
    ) {

        showMessage(
            '配られた12個から3個選択してください。',
=======
    const emoji1 =
        document.getElementById('emoji1')
            .value.trim();

    const emoji2 =
        document.getElementById('emoji2')
            .value.trim();

    const emoji3 =
        document.getElementById('emoji3')
            .value.trim();

    if (!emoji1 || !emoji2 || !emoji3) {

        showMessage(
            '絵文字を3つ入力してください。',
>>>>>>> origin/main
            true
        );

        return;
    }

    operating = true;

<<<<<<< HEAD
    updateEmojiPreview();

=======
>>>>>>> origin/main
    try {

        const result = await postData(
            'submit_hint.php',
            {
                round_id: currentRoundId,
<<<<<<< HEAD
                emoji1: selectedEmojis[0],
                emoji2: selectedEmojis[1],
                emoji3: selectedEmojis[2]
=======
                emoji1: emoji1,
                emoji2: emoji2,
                emoji3: emoji3
>>>>>>> origin/main
            }
        );

        showMessage(result);

    } catch (error) {

<<<<<<< HEAD
        showMessage(
            error.message,
            true
        );
=======
        showMessage(error.message, true);
>>>>>>> origin/main

    } finally {

        operating = false;
<<<<<<< HEAD

        await loadGameState();

        updateEmojiPreview();
    }
}

/* ========================================
   投票候補を表示
======================================== */

=======
        await loadGameState();
    }
}

// 絵文字だけの投票候補
>>>>>>> origin/main
function renderAnswers(answers) {

    const list =
        document.getElementById('answerList');

<<<<<<< HEAD
    // 自動更新しても選択を維持
=======
    // 自動更新時も選択を維持
>>>>>>> origin/main
    const selected =
        document.querySelector(
            'input[name="voteAnswer"]:checked'
        )?.value || null;

<<<<<<< HEAD
    list.replaceChildren();
=======
    list.innerHTML = '';
>>>>>>> origin/main

    answers.forEach((answer, index) => {

        const label =
            document.createElement('label');

        label.className = 'answer-option';

        const radio =
            document.createElement('input');

        radio.type = 'radio';
<<<<<<< HEAD

        radio.name = 'voteAnswer';

=======
        radio.name = 'voteAnswer';
>>>>>>> origin/main
        radio.value = answer.answer_id;

        if (
            selected !== null &&
            String(selected) ===
            String(answer.answer_id)
        ) {
            radio.checked = true;
        }

        const title =
            document.createElement('div');

        title.className = 'option-title';

        title.textContent =
            '回答 ' + (index + 1);

        const emojis =
            document.createElement('div');

        emojis.className = 'emoji-display';

        emojis.textContent = [
            answer.emoji1,
            answer.emoji2,
            answer.emoji3
        ].join(' ');

        label.appendChild(radio);
        label.appendChild(title);
        label.appendChild(emojis);

<<<<<<< HEAD
        radio.addEventListener(
            'change',
            () => {

                document.querySelectorAll(
                    '.answer-option'
                ).forEach(el => {

                    el.classList.remove(
                        'selected'
                    );
                });

                label.classList.add(
                    'selected'
                );
            }
        );
=======
        radio.addEventListener('change', () => {
            document.querySelectorAll(
                '.answer-option'
            ).forEach(el => {
                el.classList.remove('selected');
            });

            label.classList.add('selected');
        });
>>>>>>> origin/main

        if (radio.checked) {
            label.classList.add('selected');
        }

        list.appendChild(label);
    });
}

<<<<<<< HEAD
/* ========================================
   投票送信
======================================== */

=======
// 投票送信
>>>>>>> origin/main
async function submitVote() {

    if (operating) return;

    const selected =
        document.querySelector(
            'input[name="voteAnswer"]:checked'
        );

    if (!selected) {

        showMessage(
            '投票する絵文字を選んでください。',
            true
        );

        return;
    }

    operating = true;

    try {

        const result = await postData(
            'vote_process.php',
            {
                round_id: currentRoundId,
                answer_id: selected.value
            }
        );

        showMessage(result);

    } catch (error) {

<<<<<<< HEAD
        showMessage(
            error.message,
            true
        );
=======
        showMessage(error.message, true);
>>>>>>> origin/main

    } finally {

        operating = false;
<<<<<<< HEAD

=======
>>>>>>> origin/main
        await loadGameState();
    }
}

<<<<<<< HEAD
/* ========================================
   結果発表：全員の回答を公開
======================================== */

function renderRevealAnswers(
    answers,
    container
) {

    const section =
        document.createElement('div');

    const heading =
        document.createElement('h3');

    heading.className = 'reveal-heading';

    heading.textContent =
        '全員の回答を公開！';

    section.appendChild(heading);

    answers.forEach((answer, index) => {

        /* ====================================
           回答カード
        ==================================== */

        const card =
            document.createElement('div');

        card.className = 'reveal-card';

        if (answer.is_ai) {
            card.classList.add('is-ai');
        }

        /* ====================================
           回答番号と役職
        ==================================== */

        const header =
            document.createElement('div');

        header.className = 'reveal-header';

        const answerNumber =
            document.createElement('strong');

        answerNumber.textContent =
            '回答 ' + (index + 1);

        const role =
            document.createElement('span');

        role.className = 'reveal-role';

        if (answer.is_ai) {

            role.textContent = 'AI';

            role.classList.add('ai');

        } else {

            role.textContent = '人間';
        }

        header.appendChild(answerNumber);
        header.appendChild(role);

        /* ====================================
           選択された絵文字3個
        ==================================== */

        const selected =
            document.createElement('div');

        selected.className =
            'emoji-display';

        selected.textContent = [
            answer.emoji1,
            answer.emoji2,
            answer.emoji3
        ].join(' ');

        /* ====================================
           実際の回答文
        ==================================== */

        const answerText =
            document.createElement('p');

        answerText.className =
            'reveal-answer';

        answerText.textContent =
            '実際の回答：' +
            (answer.answer_text || '不明');

        /* ====================================
           回答者
        ==================================== */

        const username =
            document.createElement('p');

        username.className = 'small';

        username.textContent =
            '回答者：' +
            (
                answer.is_ai
                    ? 'AI'
                    : (answer.username || '不明')
            );

        /* ====================================
           配られた12個の絵文字
        ==================================== */

        const poolTitle =
            document.createElement('p');

        poolTitle.className =
            'small reveal-pool-title';

        poolTitle.textContent =
            '配られた12個の絵文字';

        const poolGrid =
            document.createElement('div');

        poolGrid.className =
            'emoji-grid reveal-pool';

        const pool =
            Array.isArray(answer.emoji_pool)
                ? answer.emoji_pool
                : [];

        pool.forEach(emoji => {

            const emojiBox =
                document.createElement('div');

            emojiBox.className =
                'emoji-choice';

            emojiBox.textContent = emoji;

            // 選択した3個を緑色にする
            if ([
                answer.emoji1,
                answer.emoji2,
                answer.emoji3
            ].includes(emoji)) {

                emojiBox.classList.add(
                    'selected'
                );
            }

            poolGrid.appendChild(
                emojiBox
            );
        });

        if (pool.length === 0) {

            const empty =
                document.createElement('p');

            empty.className = 'small';

            empty.textContent =
                '候補データがありません。';

            poolGrid.appendChild(empty);
        }

        /* ====================================
           カードの組み立て
        ==================================== */

        card.appendChild(header);

        card.appendChild(selected);

        card.appendChild(answerText);

        card.appendChild(username);

        card.appendChild(poolTitle);

        card.appendChild(poolGrid);

        section.appendChild(card);
    });

    container.appendChild(section);
}

/* ========================================
   通常結果表示
======================================== */

function renderResult(state) {

    const resultEl =
        document.getElementById(
            'resultText'
        );

    const scoreEl =
        document.getElementById(
            'scoreList'
        );

    resultEl.replaceChildren();
    scoreEl.replaceChildren();

    const answers =
        state.answers || [];

    /* ====================================
       AIの回答番号を探す
    ==================================== */

    const aiIndex = answers.findIndex(
        answer =>
            Number(answer.answer_id) ===
            Number(state.ai_answer_id)
    );

    const aiAnswer =
        aiIndex >= 0
            ? answers[aiIndex]
            : null;

    /* ====================================
       AIの正体発表
    ==================================== */

    const reveal =
        document.createElement('div');

=======
// 通常の結果表示
function renderResult(state) {

    const resultEl =
        document.getElementById('resultText');

    const scoreEl =
        document.getElementById('scoreList');

    resultEl.innerHTML = '';
    scoreEl.innerHTML = '';

    const answers = state.answers || [];

    const aiIndex = answers.findIndex(
        answer =>
            String(answer.answer_id) ===
            String(state.ai_answer_id)
    );

    const aiAnswer =
        aiIndex >= 0 ? answers[aiIndex] : null;

    const reveal = document.createElement('div');
>>>>>>> origin/main
    reveal.className = 'ai-reveal';

    if (aiAnswer) {

<<<<<<< HEAD
        const title =
            document.createElement('h3');

        title.textContent =
            'AIは回答 ' +
            (aiIndex + 1) +
            '！';

        const emojis =
            document.createElement('div');

        emojis.className =
            'emoji-display';
=======
        const title = document.createElement('h3');
        title.textContent =
            'AIは回答 ' + (aiIndex + 1) + '！';

        const emojis = document.createElement('div');
        emojis.className = 'emoji-display';
>>>>>>> origin/main

        emojis.textContent = [
            aiAnswer.emoji1,
            aiAnswer.emoji2,
            aiAnswer.emoji3
        ].join(' ');

<<<<<<< HEAD
        const answerText =
            document.createElement('p');

        answerText.textContent =
            'AIの回答：' +
            aiAnswer.answer_text;

        reveal.appendChild(title);
        reveal.appendChild(emojis);
        reveal.appendChild(answerText);
=======
        reveal.appendChild(title);
        reveal.appendChild(emojis);
>>>>>>> origin/main

    } else {

        reveal.textContent =
<<<<<<< HEAD
            'AIの回答を確認できませんでした。';
=======
            'AIの絵文字を確認できませんでした。';
>>>>>>> origin/main
    }

    resultEl.appendChild(reveal);

<<<<<<< HEAD
    /* ====================================
       全員の回答と12個の絵文字を公開
    ==================================== */

    renderRevealAnswers(
        answers,
        resultEl
    );

    /* ====================================
       現在のランキング
    ==================================== */

=======
>>>>>>> origin/main
    (state.scores || []).forEach(
        (score, index) => {

            const row =
                document.createElement('div');

            row.className = 'score-row';

            row.textContent =
<<<<<<< HEAD
                (index + 1) +
                '位 ' +
                score.username +
                '：' +
                score.points +
                '点';
=======
                (index + 1) + '位 ' +
                score.username + '：' +
                score.points + '点';
>>>>>>> origin/main

            scoreEl.appendChild(row);
        }
    );
}

<<<<<<< HEAD
/* ========================================
   最終結果表示
======================================== */

function renderFinalResult(state) {

    const winnerArea =
        document.getElementById(
            'winnerArea'
        );

    const scoreList =
        document.getElementById(
            'finalScoreList'
        );

    winnerArea.replaceChildren();
    scoreList.replaceChildren();

    const scores =
        state.scores || [];

    /* ====================================
       優勝者発表
    ==================================== */

    if (scores.length > 0) {

        const topScore =
            Number(scores[0].points);

        // 同点優勝にも対応
        const winners = scores.filter(
            score =>
                Number(score.points) ===
                topScore
        );

        const winnerCard =
            document.createElement('div');

        winnerCard.className =
            'winner-card';

        const trophy =
            document.createElement('div');

        trophy.textContent =
            winners.length === 1
                ? '🏆 優勝 🏆'
                : '🏆 同点優勝 🏆';

        const name =
            document.createElement('div');

        name.className =
            'winner-name';

        name.textContent =
            winners.map(
                winner => winner.username
            ).join('・');

        const points =
            document.createElement('div');

        points.className =
            'winner-points';

        points.textContent =
            topScore + '点';

        winnerCard.appendChild(trophy);
        winnerCard.appendChild(name);
        winnerCard.appendChild(points);

        winnerArea.appendChild(
            winnerCard
        );

        /* ====================================
           総合ランキング
        ==================================== */

        scores.forEach(
            (score, index) => {

                const row =
                    document.createElement('div');

                row.className =
                    'score-row';

                row.textContent =
                    (index + 1) +
                    '位 ' +
                    score.username +
                    '：' +
                    score.points +
                    '点';

                scoreList.appendChild(
                    row
                );
            }
        );

    } else {

        const p =
            document.createElement('p');
=======
// 最終結果を表示
function renderFinalResult(state) {

    const winnerArea =
        document.getElementById('winnerArea');

    const scoreList =
        document.getElementById('finalScoreList');

    winnerArea.innerHTML = '';
    scoreList.innerHTML = '';

    const scores = state.scores || [];

    if (scores.length === 0) {

        const p = document.createElement('p');
>>>>>>> origin/main

        p.textContent =
            'ランキングを取得できませんでした。';

        scoreList.appendChild(p);
<<<<<<< HEAD
    }

    /* ====================================
       最終ラウンドのAIの正体発表
    ==================================== */

    const answers =
        state.answers || [];

    const aiIndex = answers.findIndex(
        answer =>
            Number(answer.answer_id) ===
            Number(state.ai_answer_id)
    );

    if (aiIndex >= 0) {

        const aiAnswer = answers[aiIndex];

        const reveal =
            document.createElement('div');

        reveal.className = 'ai-reveal';

        const title =
            document.createElement('h3');

        title.textContent =
            '最終ラウンドのAIは回答 ' +
            (aiIndex + 1) +
            '！';

        const emojis =
            document.createElement('div');

        emojis.className =
            'emoji-display';

        emojis.textContent = [
            aiAnswer.emoji1,
            aiAnswer.emoji2,
            aiAnswer.emoji3
        ].join(' ');

        const answerText =
            document.createElement('p');

        answerText.textContent =
            'AIの回答：' +
            aiAnswer.answer_text;

        reveal.appendChild(title);
        reveal.appendChild(emojis);
        reveal.appendChild(answerText);

        winnerArea.appendChild(reveal);
    }

    /* ====================================
       最終ラウンドの全員の回答公開
    ==================================== */

    if (answers.length > 0) {

        renderRevealAnswers(
            answers,
            winnerArea
        );
    }
}

/* ========================================
   次のラウンド
======================================== */

=======
        return;
    }

    // 1位を表示
    const winner = scores[0];

    const winnerCard =
        document.createElement('div');

    winnerCard.className = 'winner-card';

    const trophy = document.createElement('div');
    trophy.textContent = '🏆 優勝 🏆';

    const name = document.createElement('div');
    name.className = 'winner-name';
    name.textContent = winner.username;

    const points = document.createElement('div');
    points.className = 'winner-points';
    points.textContent = winner.points + '点';

    winnerCard.appendChild(trophy);
    winnerCard.appendChild(name);
    winnerCard.appendChild(points);

    winnerArea.appendChild(winnerCard);

    // 総合ランキング
    scores.forEach((score, index) => {

        const row = document.createElement('div');
        row.className = 'score-row';

        row.textContent =
            (index + 1) + '位 ' +
            score.username + '：' +
            score.points + '点';

        scoreList.appendChild(row);
    });
}

// 次のラウンド
>>>>>>> origin/main
async function nextRound() {

    if (operating) return;

    if (
        latestState &&
<<<<<<< HEAD
        Number(
            latestState.round_number
        ) >= MAX_ROUNDS
    ) {

=======
        Number(latestState.round_number) >= MAX_ROUNDS
    ) {
>>>>>>> origin/main
        showMessage(
            '全3ラウンドが終了しました。',
            true
        );
<<<<<<< HEAD

=======
>>>>>>> origin/main
        return;
    }

    operating = true;

    try {

        showMessage(
            '次のラウンドを準備しています...'
        );

        const result = await postData(
            'next_round.php',
            {
                room_id: roomId
            }
        );

        showMessage(result);

    } catch (error) {

<<<<<<< HEAD
        showMessage(
            error.message,
            true
        );
=======
        showMessage(error.message, true);
>>>>>>> origin/main

    } finally {

        operating = false;
<<<<<<< HEAD

=======
>>>>>>> origin/main
        await loadGameState();
    }
}

<<<<<<< HEAD
/* ========================================
   初期読み込み
======================================== */

loadGameState();

/* ========================================
   3秒ごとに自動更新
======================================== */

setInterval(
    loadGameState,
    3000
);
=======
// 最初に読み込む
loadGameState();

// 3秒ごとに自動更新
setInterval(loadGameState, 3000);
>>>>>>> origin/main

</script>

</body>
</html>
