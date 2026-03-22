ALTER TABLE vgr_team ADD forum_id INT DEFAULT NULL;
ALTER TABLE vgr_team ADD CONSTRAINT FK_5801B8C629CCBAD0 FOREIGN KEY (forum_id) REFERENCES pnf_forum (id) ON DELETE SET NULL;
CREATE UNIQUE INDEX UNIQ_5801B8C629CCBAD0 ON vgr_team (forum_id);

-- new-badges feature
ALTER TABLE vgr_player_game ADD is_complete TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE vgr_game ADD nb_chart_without_dlc INT NOT NULL DEFAULT 0;
ALTER TABLE vgr_game ADD completion_badge_id INT NULL;
ALTER TABLE vgr_game ADD CONSTRAINT FK_vgr_game_completion_badge FOREIGN KEY (completion_badge_id) REFERENCES vgr_badge(id);

-- Initialiser nb_chart_without_dlc sur les jeux existants
UPDATE vgr_game g
SET g.nb_chart_without_dlc = (
    SELECT COUNT(c.id)
    FROM vgr_chart c
    JOIN vgr_group gr ON c.group_id = gr.id
    WHERE gr.game_id = g.id
    AND gr.is_dlc = 0
);

-- Créer un CompletionBadge pour chaque jeu existant et le lier
DELIMITER //
CREATE PROCEDURE create_completion_badges()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE gid INT;
    DECLARE bid INT;
    DECLARE cur CURSOR FOR SELECT id FROM vgr_game WHERE completion_badge_id IS NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO gid;
        IF done THEN LEAVE read_loop; END IF;

        INSERT INTO vgr_badge (dtype, type, picture, value, nb_player)
        SELECT 'CompletionBadge', 'Completion', mb.picture, g.nb_chart_without_dlc, 0
        FROM vgr_game g
        JOIN vgr_badge mb ON mb.id = g.badge_id
        WHERE g.id = gid;

        SET bid = LAST_INSERT_ID();
        UPDATE vgr_game SET completion_badge_id = bid WHERE id = gid;
    END LOOP;
    CLOSE cur;
END//
DELIMITER ;

CALL create_completion_badges();
DROP PROCEDURE create_completion_badges();
