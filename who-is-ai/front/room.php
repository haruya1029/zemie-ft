<?php
session_start();
require_once __DIR__ . '/../db/connect_db.php';

// ログインしていない、またはルームに参加していない場合はホームへリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$userId = $_SESSION['user_id'];
$roomId = $_SESSION['room_id'] ?? null;
$roomCode = $_SESSION['room_code'] ?? '------';

if (!$roomId) {
    header("Location: home.php");
    exit();
}

// 自分がホストかどうか確認
$isHost = false;
try {
    $stmt = $pdo->prepare("SELECT host_user_id FROM who_is_ai.rooms WHERE id = :id");
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($room && $room['host_user_id'] == $userId) {
        $isHost = true;
    }
} catch (Exception $e) {
    // DBエラー時のフォールバック
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WHO IS AI? - WAITING ROOM</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .room-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 24px;
            background: #0d0d0d;
            border: 1px solid #222;
            border-radius: 8px;
            color: #fff;
            font-family: sans-serif;
        }
        .room-header {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .room-code-box {
            font-size: 2.5rem;
            letter-spacing: 6px;
            color: #00ff66;
            font-weight: bold;
            margin: 12px 0;
            background: #111;
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
        }
        .player-list {
            list-style: none;
            padding: 0;
            margin: 0 0 24px 0;
        }
        .player-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #181818;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .player-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .avatar {
            font-size: 1.5rem;
        }
        .badge-host {
            background: #00ff66;
            color: #000;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .btn-start {
            width: 100%;
            padding: 14px;
            background: #00ff66;
            color: #000;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-start:disabled {
            background: #444;
            color: #888;
            cursor: not-allowed;
        }
        .waiting-text {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="room-container">
    <div class="room-header">
        <p style="color: #aaa; margin: 0; font-size: 0.85rem;">ROOM CODE</p>
        <div class="room-code-box"><?php echo htmlspecialchars($roomCode, ENT_QUOTES, 'UTF-8'); ?></div>
        <p style="font-size: 0.85rem; color: #888; margin-top: 4px;">友達にこのコードを共有してください</p>
    </div>

    <h3>MEMBER LIST (<span id="player-count">1</span>)</h3>
    
    <!-- 参加者一覧が動的に更新される領域 -->
    <ul id="player-list" class="player-list">
        <li class="player-item">
            <div class="player-info">
                <span class="avatar">🤖</span>
                <span class="name">読み込み中...</span>
            </div>
        </li>
    </ul>

    <!-- ホストのみ「ゲーム開始」ボタンを表示 -->
    <?php if ($isHost): ?>
        <button id="btn-start-game" class="btn-start">GAME START</button>
    <?php else: ?>
        <p class="waiting-text">ホストがゲームを開始するのを待っています...</p>
    <?php endif; ?>
</div>

<!-- 統合JSファイルを読み込み -->
<script src="../js/script.js"></script>

<?php if ($isHost): ?>
<script>
// ホスト用のゲーム開始処理
document.addEventListener('DOMContentLoaded', () => {
    const startBtn = document.getElementById('btn-start-game');
    if (startBtn) {
        startBtn.addEventListener('click', async () => {
            startBtn.disabled = true;
            try {
                // ※③ゲーム進行担当の api/game_start.php 等を呼ぶ想定
                const response = await fetch('../api/game_start.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'game.php';
                } else {
                    alert(data.message || 'ゲーム開始に失敗しました。');
                    startBtn.disabled = false;
                }
            } catch (err) {
                // まだ game_start.php がない場合のテスト遷移用
                window.location.href = 'game.php';
            }
        });
    }
});
</script>
<?php endif; ?>

</body>
</html>