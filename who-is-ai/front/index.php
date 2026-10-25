<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>WHO IS AI?</title>

    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/ui.js" defer></script>
</head>

<body>
<div class="page">

    <!-- ヘッダー -->
    <header class="nav">
        <a href="index.php" class="brand">
            <span class="brand-mark"></span>
            WHO IS <span class="brand-ai">AI?</span>
        </a>

        <span class="nav-status">
            <span class="active-dot"></span>
            SYSTEM ONLINE
        </span>
    </header>

    <main class="main">

        <!-- 左側：ゲーム紹介 -->
        <section class="hero">

            <!-- 光るAI球体 -->
            <div class="ai-orb" aria-hidden="true">
                <div class="ai-orb-glow"></div>
                <div class="ai-orb-ring"></div>
                <div class="ai-orb-ring ai-orb-ring-inner"></div>
                <div class="ai-orb-sphere"></div>
            </div>

            <p class="hero-label">
                INTELLIGENCE, REIMAGINED
            </p>

            <h1>
                THINK BEYOND
                <span>THE POSSIBLE.</span>
            </h1>

            <p class="hero-description">
                その回答は、人間か。それともAIか。<br>
                友達と推理し、隠されたAIの正体を見破ろう。
            </p>

            <div class="hero-bottom">
                <span>
                    <span class="active-dot"></span>
                    GAME SYSTEM READY
                </span>

                <span>NETWORK / WHO IS AI?</span>
            </div>

        </section>

        <!-- 右側：ログインカード -->
        <section class="auth-card">

            <div class="card-heading">
                <div>
                    <p class="card-label">SECURE ACCESS / 01</p>
                    <h2 id="form-title">Welcome back.</h2>
                </div>

                <div class="signal-bars" aria-hidden="true">
                    <i></i><i></i><i></i><i></i>
                </div>
            </div>

            <!-- ログイン・新規登録切り替え -->
            <div class="tabs">
                <button type="button"
                        id="login-tab"
                        class="tab active">
                    LOGIN
                </button>

                <button type="button"
                        id="register-tab"
                        class="tab">
                    NEW USER
                </button>
            </div>

            <!-- 共通フォーム -->
            <form id="auth-form"
                  action="../back/login_process.php"
                  method="post">

                <div class="form-group">
                    <label for="username">USER NAME</label>

                    <div class="input-shell">
                        <span class="input-prefix">@</span>

                        <input type="text"
                               id="username"
                               name="username"
                               placeholder="your_name"
                               maxlength="50"
                               autocomplete="username"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>

                    <div class="input-shell">
                        <span class="input-prefix">⌑</span>

                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required>

                        <button type="button"
                                class="visibility-button"
                                id="toggle-password"
                                aria-label="パスワードを表示">
                            ◉
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="btn-primary"
                        id="submit-btn">

                    <span id="submit-text">
                        ENTER THE GRID
                    </span>

                    <span id="submit-icon">↗</span>
                </button>

            </form>

            <div class="card-footer">
                <span>ENCRYPTION</span>

                <span>
                    <span class="active-dot"></span>
                    ACTIVE
                </span>

                <span>v2.4.1</span>
            </div>

        </section>

    </main>

    <!-- フッター -->
    <footer class="footer">
        <span>NODE / WHO-IS-AI</span>
        <span>© 2026 WHO IS AI?</span>
    </footer>

</div>
</body>
</html>