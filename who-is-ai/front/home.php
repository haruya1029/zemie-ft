
<?php
require_once __DIR__ . '/../back/home_process.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>HOME | WHO IS AI?</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<div class="page">

    <!-- ヘッダー -->
    <header class="nav">
        <a href="home.php" class="brand">
            <span class="brand-mark"></span>
            WHO IS <span class="brand-ai">AI?</span>
        </a>

        <span class="nav-status">
            <span class="active-dot"></span>
            PLAYER ONLINE
        </span>
    </header>

    <main class="main home-main">

        <!-- 左側：プロフィール -->
        <section class="hero home-hero">

            <div class="avatar-display">

                <!-- アバター -->
                <div class="avatar-orb">
                    <div class="avatar-glow"></div>
                    <div class="avatar-ring"></div>

                    <span class="avatar-image">
                        <?= htmlspecialchars(
                            $avatars[$avatar_id],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </div>

                <p class="hero-label">
                    PLAYER IDENTITY
                </p>

                <!-- ユーザー名 -->
                <h1 class="player-name">
                    @<?= htmlspecialchars(
                        $username,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h1>

                <p class="hero-description">
                    あなたのアバターを選択して、<br>
                    AIとの心理戦を楽しもう。
                </p>

                <!-- アバター選択 -->
                <details class="avatar-selector">

                    <summary class="avatar-change">
                        CHANGE AVATAR ↗
                    </summary>

                    <form action="../back/avatar_process.php"
                          method="post"
                          class="avatar-form">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars(
                                   $_SESSION['csrf_token'],
                                   ENT_QUOTES,
                                   'UTF-8'
                               ) ?>">

                        <p>SELECT YOUR AVATAR</p>

                        <div class="avatar-grid">

                            <?php foreach ($avatars as $id => $emoji): ?>

                                <label class="avatar-option">

                                    <input type="radio"
                                           name="avatar_id"
                                           value="<?= htmlspecialchars(
                                               $id,
                                               ENT_QUOTES,
                                               'UTF-8'
                                           ) ?>"
                                           <?= $avatar_id === $id
                                               ? 'checked' : '' ?>>

                                    <span>
                                        <?= htmlspecialchars(
                                            $emoji,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                </label>

                            <?php endforeach; ?>

                        </div>

                        <button type="submit"
                                class="btn-primary avatar-save">
                            <span>SAVE AVATAR</span>
                            <span>↗</span>
                        </button>

                    </form>
                </details>

            </div>

        </section>

        <!-- 右側：ゲームメニュー -->
        <section class="auth-card home-card">

            <div class="card-heading">
                <div>
                    <p class="card-label">
                        PLAYER DASHBOARD / 01
                    </p>

                    <h2>Ready to play?</h2>
                </div>

                <div class="signal-bars" aria-hidden="true">
                    <i></i><i></i><i></i><i></i>
                </div>
            </div>

            <p class="menu-label">
                SELECT YOUR ACTION
            </p>

            <!-- ルーム機能は後で接続 -->
            <div class="home-menu">

                <button type="button"
                        class="btn-primary"
                        >
                    <span>CREATE ROOM</span>
                    <span>↗</span>
                </button>

                <button type="button"
                        class="btn-secondary"
                        >
                    <span>JOIN ROOM</span>
                    <span>→</span>
                </button>

            </div>

            <!-- ゲームルール -->
            <details class="rules-accordion">

                <summary>
                    <span>HOW TO PLAY / ゲームルール</span>
                    <span class="rules-arrow">⌄</span>
                </summary>

                <div class="rules-content">

                    <div class="rule-item">
                        <span class="rule-number">01</span>
                        <div>
                            <h3>お題に回答する</h3>
                            <p>
                                全員が同じお題に回答。
                                AIも回答を1つ作成する。
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <span class="rule-number">02</span>
                        <div>
                            <h3>絵文字ヒントを作る</h3>
                            <p>
                                自分の回答を表す
                                絵文字を3つ選択する。
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <span class="rule-number">03</span>
                        <div>
                            <h3>みんなで推理する</h3>
                            <p>
                                匿名の回答とヒントを見て、
                                AIの回答を話し合って探す。
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <span class="rule-number">04</span>
                        <div>
                            <h3>AIに投票する</h3>
                            <p>
                                AIだと思う回答を
                                1つ選んで投票する。
                            </p>
                        </div>
                    </div>

                    <div class="rule-item">
                        <span class="rule-number">05</span>
                        <div>
                            <h3>結果発表</h3>
                            <p>
                                正解と得点を確認。
                                制限ラウンド終了後に
                                最終順位を決定する。
                            </p>
                        </div>
                    </div>

                    <div class="rule-warning">
                        注意：自分の回答を直接
                        教えることは禁止！
                    </div>

                </div>

            </details>

            <!-- ログアウト -->
            <form action="../back/logout.php"
                  method="post"
                  class="logout-form">

                <button type="submit"
                        class="btn-logout">

                    <span>DISCONNECT / LOGOUT</span>
                    <span>→</span>

                </button>
            </form>

            <div class="card-footer">
                <span>PLAYER SESSION</span>

                <span>
                    <span class="active-dot"></span>
                    ACTIVE
                </span>

                <span>v1.0</span>
            </div>

        </section>

    </main>

    <!-- フッター -->
    <footer class="footer">
        <span>NODE / WHO-IS-AI</span>
        <span>© 2026 WHO IS AI?</span>
    </footer>
    <script src="../js/script.js"></script>


</div>
</body>
</html>
                                