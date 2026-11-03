
<?php
session_start();

// ログイン確認
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');
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

if (
    isset($_SESSION['room_id']) &&
    (int)$_SESSION['room_id'] !== $room_id
) {
    http_response_code(403);
    exit('参加しているルームと一致しません。');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WHO IS AI? - GAME</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
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

.emoji-row {
    display: flex;
    gap: 10px;
}

.emoji-row input {
    text-align: center;
    font-size: 26px;
    min-width: 0;
}

.emoji-display {
    font-size: 36px;
    letter-spacing: 5px;
    margin-top: 10px;
    overflow-wrap: anywhere;
}

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

.message {
    margin-top: 15px;
    color: #b9ff66;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.error {
    color: #ff8b8b;
}

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

.ai-reveal {
    background: #334155;
    border: 2px solid #b9ff66;
    border-radius: 12px;
    padding: 20px;
    margin-top: 16px;
    text-align: center;
}

@media (max-width: 480px) {
    .container {
        margin: 15px auto;
        padding: 12px;
    }

    .card {
        padding: 18px;
    }

    .emoji-display {
        font-size: 30px;
    }
}
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

    <!-- お題 -->
    <div class="card">
        <p id="roundLabel" class="small">
            ゲーム情報を取得中...
        </p>

        <div id="question" class="question">
            読み込み中...
        </div>

        <p id="progress" class="small"></p>
    </div>

    <!-- ゲーム開始待ち -->
    <div id="waitingSection" class="card hidden">
        <h2>ゲーム開始待ち</h2>
        <p>ホストがゲームを開始してください。</p>

        <button
            id="startButton"
            onclick="startGame()"
        >
            ゲーム開始
        </button>
    </div>

    <!-- 回答入力 -->
    <div id="answerSection" class="card hidden">
        <h2>あなたの回答</h2>

        <p class="small">
            回答文はほかのプレイヤーには公開されません。
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
    <div id="voteSection" class="card hidden">

        <h2>どの絵文字がAI？</h2>

        <p class="small">
            全員の絵文字が公開されました！
            AIが考えた絵文字だと思うものを選んでください。
        </p>

        <div id="answerList"></div>

        <button onclick="submitVote()">
            この絵文字に投票
        </button>
    </div>

    <!-- 通常結果 -->
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
    </div>

    <!-- 最終結果 -->
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
    </div>

    <!-- 待機 -->
    <div id="waitSection" class="card hidden">

        <h2>ほかのプレイヤーを待っています...</h2>

        <p id="waitMessage" class="small"></p>
    </div>

    <p id="message" class="message"></p>

    <button onclick="loadGameState()">
        状態を更新
    </button>

</div>

<script>

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
        document.getElementById(id)
            .classList.add('hidden');
    });
}

// メッセージ表示
function showMessage(text, isError = false) {

    const el = document.getElementById('message');

    el.textContent = text;
    el.classList.toggle('error', isError);
}

// APIにPOST
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
        // JSON以外にも対応
    }

    if (!response.ok) {
        throw new Error(
            json?.message ||
            text ||
            '処理に失敗しました。'
        );
    }

    if (json && json.success === false) {
        throw new Error(
            json.message || '処理に失敗しました。'
        );
    }

    return json?.message || text;
}

// ゲーム状態取得
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
            throw new Error(
                state.message ||
                'ゲーム情報を取得できません。'
            );
        }

        latestState = state;
        currentRoundId = state.round_id || null;
        currentStatus = state.status;

        // 新ラウンドなら入力欄をリセット
        if (
            currentRoundId &&
            currentRoundId !== lastRoundId
        ) {
            document.getElementById('answerText').value = '';
            document.getElementById('emoji1').value = '';
            document.getElementById('emoji2').value = '';
            document.getElementById('emoji3').value = '';

            lastRoundId = currentRoundId;
        }

        renderGame(state);

    } catch (error) {

        showMessage(error.message, true);

    } finally {

        loading = false;
    }
}

// 画面切り替え
function renderGame(state) {

    hideSections();

    const questionEl =
        document.getElementById('question');

    const roundEl =
        document.getElementById('roundLabel');

    const progressEl =
        document.getElementById('progress');

    // 開始待ち
    if (state.status === 'waiting') {

        roundEl.textContent = 'WAITING';
        questionEl.textContent = 'ゲーム開始待ち';
        progressEl.textContent = '';

        document.getElementById(
            'waitingSection'
        ).classList.remove('hidden');

        document.getElementById(
            'startButton'
        ).classList.toggle(
            'hidden',
            state.is_host === false
        );

        return;
    }

    roundEl.textContent =
        'ROUND ' + state.round_number +
        ' / ' + MAX_ROUNDS;

    questionEl.textContent =
        state.question || '';

    progressEl.textContent =
        '回答 ' + state.answer_count + '/' +
        state.player_count + '人 ・ ヒント ' +
        state.hint_count + '/' +
        state.player_count + '人 ・ 投票 ' +
        state.vote_count + '/' +
        state.player_count + '人';

    // 回答入力
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

    // 絵文字入力
    else if (state.status === 'hinting') {

        if (state.has_submitted_hint) {

            showWaiting(
                '全員の絵文字ヒントを待っています。'
            );

        } else {

            document.getElementById(
                'hintSection'
            ).classList.remove('hidden');
        }
    }

    // 絵文字だけで投票
    else if (state.status === 'voting') {

        if (state.has_voted) {

            showWaiting(
                '全員の投票を待っています。'
            );

        } else {

            document.getElementById(
                'voteSection'
            ).classList.remove('hidden');

            renderAnswers(state.answers || []);
        }
    }

    // 結果発表
    else if (state.status === 'result') {

        if (
            Number(state.round_number) >= MAX_ROUNDS
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
                state.is_host !== true
            );

            renderResult(state);
        }
    }

    else {

        showWaiting(
            'ゲーム情報を確認しています。'
        );
    }
}

// 待機画面
function showWaiting(message) {

    document.getElementById(
        'waitSection'
    ).classList.remove('hidden');

    document.getElementById(
        'waitMessage'
    ).textContent = message;
}

// ゲーム開始
async function startGame() {

    if (operating) return;
    operating = true;

    try {

        showMessage('ゲームを開始しています...');

        const result = await postData(
            'game_start.php',
            { room_id: roomId }
        );

        showMessage(result);

    } catch (error) {

        showMessage(error.message, true);

    } finally {

        operating = false;
        await loadGameState();
    }
}

// 回答送信
async function submitAnswer() {

    if (operating) return;

    const answer =
        document.getElementById('answerText')
            .value.trim();

    if (!answer) {
        showMessage(
            '回答を入力してください。',
            true
        );
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

        showMessage(error.message, true);

    } finally {

        operating = false;
        await loadGameState();
    }
}

// 絵文字送信
async function submitHint() {

    if (operating) return;

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
            true
        );

        return;
    }

    operating = true;

    try {

        const result = await postData(
            'submit_hint.php',
            {
                round_id: currentRoundId,
                emoji1: emoji1,
                emoji2: emoji2,
                emoji3: emoji3
            }
        );

        showMessage(result);

    } catch (error) {

        showMessage(error.message, true);

    } finally {

        operating = false;
        await loadGameState();
    }
}

// 絵文字だけの投票候補
function renderAnswers(answers) {

    const list =
        document.getElementById('answerList');

    // 自動更新時も選択を維持
    const selected =
        document.querySelector(
            'input[name="voteAnswer"]:checked'
        )?.value || null;

    list.innerHTML = '';

    answers.forEach((answer, index) => {

        const label =
            document.createElement('label');

        label.className = 'answer-option';

        const radio =
            document.createElement('input');

        radio.type = 'radio';
        radio.name = 'voteAnswer';
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

        radio.addEventListener('change', () => {
            document.querySelectorAll(
                '.answer-option'
            ).forEach(el => {
                el.classList.remove('selected');
            });

            label.classList.add('selected');
        });

        if (radio.checked) {
            label.classList.add('selected');
        }

        list.appendChild(label);
    });
}

// 投票送信
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

        showMessage(error.message, true);

    } finally {

        operating = false;
        await loadGameState();
    }
}

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
    reveal.className = 'ai-reveal';

    if (aiAnswer) {

        const title = document.createElement('h3');
        title.textContent =
            'AIは回答 ' + (aiIndex + 1) + '！';

        const emojis = document.createElement('div');
        emojis.className = 'emoji-display';

        emojis.textContent = [
            aiAnswer.emoji1,
            aiAnswer.emoji2,
            aiAnswer.emoji3
        ].join(' ');

        reveal.appendChild(title);
        reveal.appendChild(emojis);

    } else {

        reveal.textContent =
            'AIの絵文字を確認できませんでした。';
    }

    resultEl.appendChild(reveal);

    (state.scores || []).forEach(
        (score, index) => {

            const row =
                document.createElement('div');

            row.className = 'score-row';

            row.textContent =
                (index + 1) + '位 ' +
                score.username + '：' +
                score.points + '点';

            scoreEl.appendChild(row);
        }
    );
}

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

        p.textContent =
            'ランキングを取得できませんでした。';

        scoreList.appendChild(p);
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
async function nextRound() {

    if (operating) return;

    if (
        latestState &&
        Number(latestState.round_number) >= MAX_ROUNDS
    ) {
        showMessage(
            '全3ラウンドが終了しました。',
            true
        );
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

        showMessage(error.message, true);

    } finally {

        operating = false;
        await loadGameState();
    }
}

// 最初に読み込む
loadGameState();

// 3秒ごとに自動更新
setInterval(loadGameState, 3000);

</script>

</body>
</html>
