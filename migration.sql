

-- V1 : score_form_per_page (default 20 — stored only when user overrides)
-- V2 candidates : forum_topics_per_page, forum_messages_per_page, leaderboard_limit

-- Feature: 2fa-totp
ALTER TABLE pnu_user
    ADD COLUMN totp_secret VARCHAR(255) DEFAULT NULL,
    ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0;

-- Feature: context-gaming-platform
CREATE TABLE vgr_player_platform_connection (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    player_id   INT NOT NULL,
    platform    VARCHAR(50) NOT NULL,
    external_id VARCHAR(255) NOT NULL,
    username    VARCHAR(255) DEFAULT NULL,
    linked_at   DATETIME NOT NULL,
    token_data  TEXT NULL,
    UNIQUE KEY uq_player_platform (player_id, platform),
    CONSTRAINT fk_platform_connection_player FOREIGN KEY (player_id) REFERENCES vgr_player(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feature: map-igdb-game
ALTER TABLE vgr_platform
    ADD COLUMN igdb_platform_id INT NULL,
    ADD CONSTRAINT fk_vgr_platform_igdb FOREIGN KEY (igdb_platform_id) REFERENCES igdb_platform(id) ON DELETE SET NULL;
