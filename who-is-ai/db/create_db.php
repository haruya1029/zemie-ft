<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once __DIR__ . "/connect_db.php";

try{
    $pdo->exec("
        CREATE SCHEMA IF NOT EXISTS  WHO_IS_AI;
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS WHO_IS_AI.users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    //roomsテーブルの作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS WHO_IS_AI.rooms (
            id SERIAL PRIMARY KEY,
            room_code VARCHAR(6) UNIQUE NOT NULL,
            host_user_id INT NOT NULL REFERENCES WHO_IS_AI.users(id),
            status VARCHAR(20) NOT NULL DEFAULT 'waiting',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    //room_playersテーブルの作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS WHO_IS_AI.room_players (
            id SERIAL PRIMARY KEY,
            room_id INT NOT NULL REFERENCES WHO_IS_AI.rooms(id),
            user_id INT NOT NULL REFERENCES WHO_IS_AI.users(id),
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (room_id, user_id)
        )
    ");



    //questionsテーブル（お題作成用）の作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS WHO_IS_AI.questions (
            id SERIAL PRIMARY KEY,
            question_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    //roundsラウンド管理のテーブル作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS WHO_IS_AI.rounds (
            id SERIAL PRIMARY KEY,
            room_id INT NOT NULL REFERENCES WHO_IS_AI.rooms(id),
            round_number INT NOT NULL,
            question_id INT NOT NULL REFERENCES WHO_IS_AI.questions(id),
            status VARCHAR(20) DEFAULT 'answering',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (room_id, round_number)
        )
    ");

    //answersテーブルの作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS who_is_ai.answers (
            id SERIAL PRIMARY KEY,
            round_id INTEGER NOT NULL REFERENCES who_is_ai.rounds(id),
            user_id INTEGER REFERENCES who_is_ai.users(id),
            answer_text TEXT NOT NULL,
            is_ai BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(round_id, user_id),
            CHECK (
                (is_ai = TRUE AND user_id IS NULL)
                OR
                (is_ai = FALSE AND user_id IS NOT NULL)
            )
        )

    ");
    
    //hintsテーブルの作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS who_is_ai.hints (
            id SERIAL PRIMARY KEY,
            answer_id INTEGER NOT NULL UNIQUE
                REFERENCES who_is_ai.answers(id),
            emoji1 VARCHAR(20) NOT NULL,
            emoji2 VARCHAR(20) NOT NULL,
            emoji3 VARCHAR(20) NOT NULL
        )
    ");

    //votes投票のテーブル作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS who_is_ai.votes (
            id SERIAL PRIMARY KEY,
            round_id INTEGER NOT NULL REFERENCES who_is_ai.rounds(id),
            user_id INTEGER NOT NULL REFERENCES who_is_ai.users(id),
            answer_id INTEGER NOT NULL REFERENCES who_is_ai.answers(id),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(round_id, user_id)
        )
    ");

    //scoresスコア管理のテーブル作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS who_is_ai.scores (
            id SERIAL PRIMARY KEY,
            room_id INTEGER NOT NULL REFERENCES who_is_ai.rooms(id),
            user_id INTEGER NOT NULL REFERENCES who_is_ai.users(id),
            points INTEGER NOT NULL DEFAULT 0,
            UNIQUE(room_id, user_id)
        )
    ");

    // usersテーブルにパスワードカラムを追加
    $pdo->exec("
        ALTER TABLE who_is_ai.users
        ADD COLUMN IF NOT EXISTS password VARCHAR(255)
    ");

    // アバター情報を追加
    $pdo->exec("
        ALTER TABLE who_is_ai.users
        ADD COLUMN IF NOT EXISTS avatar_id
        VARCHAR(20) NOT NULL DEFAULT 'robot'
    ");

    echo "テーブル作成が完了しました。";

}catch (PDOException $e) {
    exit('テーブル作成エラー: ' . $e->getMessage());
}
        
