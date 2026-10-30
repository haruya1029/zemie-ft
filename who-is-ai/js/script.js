document.addEventListener('DOMContentLoaded', () => {

    /* ===================================================
       1. ホーム画面（home.php）用の処理
       =================================================== */
    const primaryBtn = document.querySelector('.home-menu .btn-primary');
    const secondaryBtn = document.querySelector('.home-menu .btn-secondary');

    // ボタンの無効化（disabled・CSSカーソル・ pointer-events）を完全に強制作業で解除する関数
    function enableButton(btn) {
        if (!btn) return;
        btn.removeAttribute('disabled');             // HTML属性を削除
        btn.disabled = false;                        // JSプロパティを解除
        btn.style.cursor = 'pointer';                // カーソルをポインター（指マーク）に変更
        btn.style.pointerEvents = 'auto';            // クリックイベントを強制許可
        btn.style.opacity = '1';                     // 半透明化を解除
    }

    // CREATE ROOM ボタンを有効化
    if (primaryBtn) {
        enableButton(primaryBtn);

        primaryBtn.addEventListener('click', async () => {
            primaryBtn.disabled = true;

            try {
                const response = await fetch('../api/room_create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await response.json();

                if (data.success) {
                    window.location.href = 'room.php';
                } else {
                    alert(data.message || 'ルーム作成に失敗しました。');
                    enableButton(primaryBtn);
                }
            } catch (err) {
                alert('通信エラーが発生しました。');
                enableButton(primaryBtn);
            }
        });
    }

    // JOIN ROOM ボタンを有効化
    if (secondaryBtn) {
        enableButton(secondaryBtn);

        secondaryBtn.addEventListener('click', async () => {
            const roomCode = prompt('6桁のルームコードを入力してください:');
            
            if (!roomCode) return; // キャンセル時
            
            if (roomCode.trim().length !== 6) {
                alert('6桁のコードを正確に入力してください。');
                return;
            }

            try {
                const response = await fetch('../api/room_join.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_code: roomCode.trim() })
                });
                const data = await response.json();

                if (data.success) {
                    window.location.href = 'room.php';
                } else {
                    alert(data.message || 'ルームに参加できませんでした。');
                }
            } catch (err) {
                alert('通信エラーが発生しました。');
            }
        });
    }

    /* ===================================================
       2. 待機部屋画面（room.php）用の処理
       =================================================== */
    const isRoomPage = window.location.pathname.includes('room.php') || document.getElementById('player-list');

    if (isRoomPage) {
        function checkRoomStatus() {
            fetch('api/players.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const listEl = document.getElementById('player-list');
                        if (listEl) {
                            listEl.innerHTML = data.players.map(p => `
                                <li>
                                    <span class="avatar">${p.avatar_id || '🤖'}</span>
                                    <span class="name">@${p.username}</span>
                                    ${p.is_host ? '<span class="badge">HOST</span>' : ''}
                                </li>
                            `).join('');
                        }
                    }
                })
                .catch(err => console.error("プレイヤー取得エラー:", err));

            fetch('api/room_status.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.room.status === 'playing') {
                        window.location.href = 'game.php';
                    }
                })
                .catch(err => console.error("ステータス確認エラー:", err));
        }

        checkRoomStatus();
        setInterval(checkRoomStatus, 3000);
    }

});