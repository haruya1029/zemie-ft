document.addEventListener('DOMContentLoaded', () => {

    /* ===================================================
       1. ホーム画面（home.php）用の処理
          ・CREATE ROOM / JOIN ROOM ボタンの有効化と通信
       =================================================== */
    const primaryBtn = document.querySelector('.home-menu .btn-primary');
    const secondaryBtn = document.querySelector('.home-menu .btn-secondary');

    // CREATE ROOM ボタンの設定
    if (primaryBtn) {
        primaryBtn.removeAttribute('disabled'); // disabled属性を解除

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
                    primaryBtn.disabled = false;
                }
            } catch (err) {
                alert('通信エラーが発生しました。');
                primaryBtn.disabled = false;
            }
        });
    }

    // JOIN ROOM ボタンの設定
    if (secondaryBtn) {
        secondaryBtn.removeAttribute('disabled'); // disabled属性を解除

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
          ・参加者一覧とゲーム開始ステータスの3秒周期監視
       =================================================== */
    // 待機部屋要素（例: #player-list や room.php 固有の要素）が存在する場合のみポーリングを実行
    const isRoomPage = window.location.pathname.includes('room.php') || document.getElementById('player-list');

    if (isRoomPage) {
        function checkRoomStatus() {
            // 参加者一覧の取得・更新
            fetch('api/players.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        console.log("現在の参加者:", data.players);
                        // HTML要素（<ul id="player-list"> など）を更新する処理
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

            // ルーム状態の監視（ホストがスタートしたか検知）
            fetch('api/room_status.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.room.status === 'playing') {
                        // ホストがゲームを開始したら game.php に遷移
                        window.location.href = 'game.php';
                    }
                })
                .catch(err => console.error("ステータス確認エラー:", err));
        }

        // 初回実行 ＋ 3秒ごとの定期ポーリング開始
        checkRoomStatus();
        setInterval(checkRoomStatus, 3000);
    }

});