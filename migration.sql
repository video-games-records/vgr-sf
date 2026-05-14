ALTER TABLE vgr_team ADD forum_id INT DEFAULT NULL;
ALTER TABLE vgr_team ADD CONSTRAINT FK_5801B8C629CCBAD0 FOREIGN KEY (forum_id) REFERENCES pnf_forum (id) ON DELETE SET NULL;
CREATE UNIQUE INDEX UNIQ_5801B8C629CCBAD0 ON vgr_team (forum_id);

-- Migration: Remplacer download_url par table game_download_url
-- Date: 2026-04-18



CREATE TABLE pnu_user_parameter (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    param_key  VARCHAR(50)  NOT NULL,
    value      VARCHAR(50)  NOT NULL,
    UNIQUE KEY unique_user_param (user_id, param_key),
    INDEX idx_user_parameter_user_id (user_id),
    CONSTRAINT fk_user_parameter_user FOREIGN KEY (user_id) REFERENCES pnu_user (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- V1 : score_form_per_page (default 20 — stored only when user overrides)
-- V2 candidates : forum_topics_per_page, forum_messages_per_page, leaderboard_limit

-- Feature: 2fa-totp
ALTER TABLE pnu_user
    ADD COLUMN totp_secret VARCHAR(255) DEFAULT NULL,
    ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0;

-- Feature: context-gaming-platform
CREATE TABLE player_platform_connection (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    player_id   INT NOT NULL,
    platform    VARCHAR(50) NOT NULL,
    external_id VARCHAR(255) NOT NULL,
    username    VARCHAR(255) DEFAULT NULL,
    linked_at   DATETIME NOT NULL,
    UNIQUE KEY uq_player_platform (player_id, platform),
    CONSTRAINT fk_platform_connection_player FOREIGN KEY (player_id) REFERENCES vgr_player(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
