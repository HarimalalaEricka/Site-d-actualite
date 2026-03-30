-- V4: Indexation archives FO-005 (idempotent)

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_add_index_if_missing $$
CREATE PROCEDURE sp_add_index_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_index_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
          AND index_name = p_index_name
    ) THEN
        SET @stmt = p_index_sql;
        PREPARE s FROM @stmt;
        EXECUTE s;
        DEALLOCATE PREPARE s;
    END IF;
END $$

CALL sp_add_index_if_missing('Article', 'idx_article_date_publication',
    'ALTER TABLE Article ADD INDEX idx_article_date_publication (date_publication)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_status_date_archives',
    'ALTER TABLE Article ADD INDEX idx_article_status_date_archives (Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_lang_status_date_archives',
    'ALTER TABLE Article ADD INDEX idx_article_lang_status_date_archives (lang, Id_status_article, date_publication DESC)') $$

DROP PROCEDURE IF EXISTS sp_add_index_if_missing $$
DELIMITER ;
