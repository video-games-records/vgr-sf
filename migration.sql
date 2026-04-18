ALTER TABLE vgr_team ADD forum_id INT DEFAULT NULL;
ALTER TABLE vgr_team ADD CONSTRAINT FK_5801B8C629CCBAD0 FOREIGN KEY (forum_id) REFERENCES pnf_forum (id) ON DELETE SET NULL;
CREATE UNIQUE INDEX UNIQ_5801B8C629CCBAD0 ON vgr_team (forum_id);

-- Migration: Remplacer download_url par table game_download_url
-- Date: 2026-04-18

-- 1. Créer la nouvelle table game_download_url
CREATE TABLE vgr_game_download_url (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    platform_id INT NOT NULL,
    url VARCHAR(500) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_game_platform (game_id, platform_id),
    UNIQUE KEY unique_game_platform (game_id, platform_id),
    FOREIGN KEY (game_id) REFERENCES vgr_game(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES vgr_platform(id) ON DELETE CASCADE
);


-- 3. Supprimer la colonne download_url de la table vgr_game
ALTER TABLE vgr_game DROP COLUMN download_url;
