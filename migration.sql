ALTER TABLE vgr_platform CHANGE picture picture VARCHAR(255) NULL, CHANGE slug slug VARCHAR(255) NOT NULL;
UPDATE vgr_platform SET picture=null;
