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
