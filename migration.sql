ALTER TABLE vgr_platform CHANGE picture picture VARCHAR(255) NULL, CHANGE slug slug VARCHAR(255) NOT NULL;
UPDATE vgr_platform SET picture=null;

CREATE INDEX idx_player_status ON vgr_player_chart (player_id, status);

ALTER TABLE vgr_badge ADD dtype VARCHAR(50) NOT NULL DEFAULT 'Badge';
UPDATE vgr_badge SET dtype = 'MasterBadge' WHERE type = 'Master';
UPDATE vgr_badge SET dtype = 'SerieBadge' WHERE type = 'Serie';
UPDATE vgr_badge SET dtype = 'PlatformBadge' WHERE type = 'Platform';
UPDATE vgr_badge SET dtype = 'CountryBadge' WHERE type = 'VgrSpecialCountry';
