document.addEventListener('DOMContentLoaded', () => {

    /* ===================================================
       1. ホーム画面（home.php）用の処理
          ・CREATE ROOM / JOIN ROOM ボタンの有効化と通信
       =================================================== */
    const primaryBtn = document.querySelector('.home-menu .btn-primary') || document.querySelector('.btn-primary');
    const secondaryBtn = document.querySelector('.home-menu .btn-secondary') || document.querySelector('.btn-secondary');

    // ボタンの無効化（disabled・CSSカーソル・pointer-events）を強制作業で解除する関数
    function enableButton(btn) {
        if (!btn) return;
        btn.removeAttribute('disabled');
        btn.disabled = false;
        btn.style.cursor = 'pointer';
        btn.style.pointerEvents = 'auto';
        btn.style.opacity = '1';
    }

    // CREATE ROOM ボタン処理
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

    // JOIN ROOM ボタン処理
    if (secondaryBtn) {
        enableButton(secondaryBtn);

        secondaryBtn.addEventListener('click', async () => {
            let roomCode = prompt('6桁のルームコードを入力してください:');
            
            if (!roomCode) return; // キャンセル時
            
            roomCode = roomCode.trim().toUpperCase(); // 大小文字・空白整形
            
            if (roomCode.length !== 6) {
                alert('6桁のコードを正確に入力してください。');
                return;
            }

            try {
                const response = await fetch('../api/room_join.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ room_code: roomCode })
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
          ・参加者一覧とゲーム開始ステータスの3秒周期監視
       =================================================== */
    const isRoomPage = window.location.pathname.includes('room.php') || document.getElementById('player-list');

    if (isRoomPage) {
        // アバターIDから絵文字への変換マッピング
        const avatarMap = {
            'robot': '🤖',
            'human': '👤',
            'ghost': '👻',
            'cat': '🐱',
            'brain': '🧠',
            'smile': '😊'
        };

        function checkRoomStatus() {
            // 1. 参加者一覧の取得・描画更新
            fetch('../api/players.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && Array.isArray(data.players)) {
                        const listEl = document.getElementById('player-list');
                        const countEl = document.getElementById('player-count');

                        if (countEl) {
                            countEl.textContent = data.players.length;
                        }

                        if (listEl) {
                            listEl.innerHTML = data.players.map(p => {
                                // avatar_id が文字列（'robot'等）なら絵文字に変換。未設定等の場合はフォールバック
                                const displayAvatar = avatarMap[p.avatar_id] || p.avatar_id || '🤖';

                                return `
                                    <li class="player-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #181818; border-radius: 6px; margin-bottom: 8px;">
                                        <div class="player-info" style="display: flex; align-items: center; gap: 12px;">
                                            <span class="avatar" style="font-size: 1.5rem;">${displayAvatar}</span>
                                            <span class="name" style="color: #fff; font-weight: bold;">@${p.username || 'UNKNOWN'}</span>
                                        </div>
                                        ${p.is_host ? '<span class="badge-host" style="background: #00ff66; color: #000; font-size: 0.75rem; font-weight: bold; padding: 2px 8px; border-radius: 4px;">HOST</span>' : ''}
                                    </li>
                                `;
                            }).join('');
                        }
                    }
                })
                .catch(err => console.error("プレイヤー取得エラー:", err));

            // 2. ルーム状態（ゲーム開始）の監視
            fetch('../api/room_status.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.room && data.room.status === 'playing') {
                        window.location.href = 'game.php';
                    }
                })
                .catch(err => console.error("ステータス確認エラー:", err));
        }

        // 初回実行 ＋ 3秒ごとの定期監視
        checkRoomStatus();
        setInterval(checkRoomStatus, 3000);
    }

});