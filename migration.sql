ALTER TABLE vgr_platform CHANGE picture picture VARCHAR(255) NULL, CHANGE slug slug VARCHAR(255) NOT NULL;
UPDATE vgr_platform SET picture=null;

CREATE INDEX idx_player_status ON vgr_player_chart (player_id, status);
