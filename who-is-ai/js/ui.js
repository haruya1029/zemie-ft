
document.addEventListener("DOMContentLoaded", () => {

    // HTMLの要素を取得
    const loginTab = document.getElementById("login-tab");
    const registerTab = document.getElementById("register-tab");

    const form = document.getElementById("auth-form");
    const formTitle = document.getElementById("form-title");

    const username = document.getElementById("username");
    const password = document.getElementById("password");

    const submitText = document.getElementById("submit-text");
    const submitIcon = document.getElementById("submit-icon");

    const togglePassword = document.getElementById("toggle-password");

    // タブ切り替え
    function switchTab(mode) {

        // 入力内容をリセット
        form.reset();

        // パスワードを非表示に戻す
        password.type = "password";
        togglePassword.textContent = "◉";
        togglePassword.setAttribute(
            "aria-label",
            "パスワードを表示"
        );

        if (mode === "login") {

            // ログイン処理へ送信
            form.action = "../back/login_process.php";

            formTitle.textContent = "Welcome back.";
            submitText.textContent = "ENTER THE GRID";
            submitIcon.textContent = "↗";

            password.placeholder = "••••••••";
            password.minLength = 0;
            password.autocomplete = "current-password";

            username.autocomplete = "username";

            loginTab.classList.add("active");
            registerTab.classList.remove("active");

        } else {

            // 新規登録処理へ送信
            form.action = "../back/register_process.php";

            formTitle.textContent = "Join the network.";
            submitText.textContent = "CREATE ACCESS";
            submitIcon.textContent = "+";

            password.placeholder = "4文字以上";
            password.minLength = 4;
            password.autocomplete = "new-password";

            username.autocomplete = "username";

            registerTab.classList.add("active");
            loginTab.classList.remove("active");
        }
    }

    // LOGINボタン
    loginTab.addEventListener("click", () => {
        switchTab("login");
    });

    // NEW USERボタン
    registerTab.addEventListener("click", () => {
        switchTab("register");
    });

    // パスワード表示・非表示
    togglePassword.addEventListener("click", () => {

        if (password.type === "password") {
            password.type = "text";

            togglePassword.setAttribute(
                "aria-label",
                "パスワードを隠す"
            );

        } else {
            password.type = "password";

            togglePassword.setAttribute(
                "aria-label",
                "パスワードを表示"
            );
        }
    });

    // 初期表示はログイン
    switchTab("login");
});
