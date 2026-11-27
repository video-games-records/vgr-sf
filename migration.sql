

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

-- Feature: remove-game-status-created
UPDATE vgr_game SET status = 'ADD_SCORE' WHERE status = 'CREATED';
ALTER TABLE vgr_game ALTER COLUMN status SET DEFAULT 'ADD_SCORE';

-- Feature: serie-nbpost
ALTER TABLE vgr_serie ADD COLUMN nb_post INT NOT NULL DEFAULT 0;

-- Feature: admin-send-group-messages
-- group_type/group_id generic pair (no FK: polymorphic target, same convention as Player.user_id)
-- so adding a new group type later (e.g. Team) does not require a new table.
CREATE TABLE vgr_group_message_broadcast (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    group_type       VARCHAR(20) NOT NULL,
    group_id         INT NOT NULL,
    sender_user_id   INT NOT NULL,
    subject          VARCHAR(255) NOT NULL,
    message          LONGTEXT NOT NULL,
    recipient_count  INT NOT NULL,
    created_at       DATETIME NOT NULL,
    updated_at       DATETIME NOT NULL,
    INDEX idx_group_message_broadcast_group (group_type, group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
