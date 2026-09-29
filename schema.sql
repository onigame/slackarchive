CREATE DATABASE IF NOT EXISTS slackarchive;
USE slackarchive;

CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(100),
    real_name VARCHAR(255),
    avatar_url TEXT
);

CREATE TABLE IF NOT EXISTS channels (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100),
    description TEXT
);

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    channel_id VARCHAR(50),
    user_id VARCHAR(50),
    text LONGTEXT,
    ts VARCHAR(50),
    thread_ts VARCHAR(50) DEFAULT NULL,
    UNIQUE KEY unique_msg (channel_id, ts),
    INDEX idx_channel (channel_id),
    INDEX idx_thread (thread_ts)
);

CREATE TABLE IF NOT EXISTS allowed_exceptions (
    discord_user_id VARCHAR(100) PRIMARY KEY
);
