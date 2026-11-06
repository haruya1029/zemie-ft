
document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================
       1. ホーム画面（home.php）
       ========================================== */

    const primaryBtn =
        document.querySelector('.home-menu .btn-primary') ||
        document.querySelector('.btn-primary');

    const secondaryBtn =
        document.querySelector('.home-menu .btn-secondary') ||
        document.querySelector('.btn-secondary');

    function enableButton(btn) {
        if (!btn) return;

        btn.removeAttribute('disabled');
        btn.disabled = false;
        btn.style.cursor = 'pointer';
        btn.style.pointerEvents = 'auto';
        btn.style.opacity = '1';
    }

    // CREATE ROOM
    if (primaryBtn) {
        enableButton(primaryBtn);

        primaryBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            primaryBtn.disabled = true;

            try {
                const response = await fetch('../api/room_create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'room.php';
                } else {
                    alert(data.message || 'ルーム作成に失敗しました。');
                }

            } catch (err) {
                console.error('ルーム作成エラー:', err);
                alert('通信エラーが発生しました。');
            } finally {
                enableButton(primaryBtn);
            }
        });
    }

    // JOIN ROOM
    if (secondaryBtn) {
        enableButton(secondaryBtn);

        secondaryBtn.addEventListener('click', async (event) => {
            event.preventDefault();

            let roomCode = prompt(
                '6桁のルームコードを入力してください:'
            );

            if (!roomCode) return;

            roomCode = roomCode.trim().toUpperCase();

            if (roomCode.length !== 6) {
                alert('6桁のコードを正確に入力してください。');
                return;
            }

            try {
                const response = await fetch('../api/room_join.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        room_code: roomCode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = 'room.php';
                } else {
                    alert(data.message || 'ルームに参加できませんでした。');
                }

            } catch (err) {
                console.error('ルーム参加エラー:', err);
                alert('通信エラーが発生しました。');
            }
        });
    }

    /* ==========================================
       2. 待機部屋画面（room.php）
       ========================================== */

    const isRoomPage =
        window.location.pathname.includes('room.php') ||
        document.getElementById('player-list');

    if (isRoomPage) {

        const avatarMap = {
            robot: '🤖',
            human: '👤',
            ghost: '👻',
            cat: '🐱',
            brain: '🧠',
            smile: '😊'
        };

        function checkRoomStatus() {

            // 参加者一覧を取得
            fetch('../api/players.php')
                .then(response => response.json())
                .then(data => {

                    if (!data.success || !Array.isArray(data.players)) {
                        return;
                    }

                    const listEl =
                        document.getElementById('player-list');

                    const countEl =
                        document.getElementById('player-count');

                    if (countEl) {
                        countEl.textContent = data.players.length;
                    }

                    if (listEl) {
                        listEl.replaceChildren();

                        data.players.forEach(player => {
                            const li = document.createElement('li');
                            li.className = 'player-item';

                            li.style.cssText =
                                'display:flex;align-items:center;' +
                                'justify-content:space-between;' +
                                'padding:12px 16px;background:#181818;' +
                                'border-radius:6px;margin-bottom:8px;';

                            const info = document.createElement('div');
                            info.className = 'player-info';
                            info.style.cssText =
                                'display:flex;align-items:center;gap:12px;';

                            const avatar = document.createElement('span');
                            avatar.className = 'avatar';
                            avatar.style.fontSize = '1.5rem';
                            avatar.textContent =
                                avatarMap[player.avatar_id] ||
                                player.avatar_id ||
                                '🤖';

                            const name = document.createElement('span');
                            name.className = 'name';
                            name.style.cssText =
                                'color:#fff;font-weight:bold;';
                            name.textContent =
                                '@' + (player.username || 'UNKNOWN');

                            info.append(avatar, name);
                            li.appendChild(info);

                            if (player.is_host) {
                                const badge = document.createElement('span');
                                badge.className = 'badge-host';
                                badge.textContent = 'HOST';
                                badge.style.cssText =
                                    'background:#00ff66;color:#000;' +
                                    'font-size:.75rem;font-weight:bold;' +
                                    'padding:2px 8px;border-radius:4px;';
                                li.appendChild(badge);
                            }

                            listEl.appendChild(li);
                        });
                    }
                })
                .catch(error => {
                    console.error('プレイヤー取得エラー:', error);
                });

            // ゲーム開始状態を確認
            fetch('../api/room_status.php')
                .then(response => response.json())
                .then(data => {

                    if (
                        data.success &&
                        data.room &&
                        data.room.status === 'playing'
                    ) {
                        window.location.href = 'game.php';
                    }
                })
                .catch(error => {
                    console.error('ステータス確認エラー:', error);
                });
        }

        // 初回取得＋3秒ごとに更新
        checkRoomStatus();
        setInterval(checkRoomStatus, 3000);
    }

});
