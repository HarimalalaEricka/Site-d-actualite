-- V3: Indexation simplifiee pour FO-002 et FO-003
-- Script idempotent (rejouable sans erreur)

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

-- FO-002: home (recents, categories, plus lus)
CALL sp_add_index_if_missing('Article', 'idx_article_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_status_date (Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_lang_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_lang_status_date (lang, Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_cat_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_cat_status_date (Id_Categorie, Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_nbr_vues',
    'ALTER TABLE Article ADD INDEX idx_article_nbr_vues (nbr_vues)') $$

-- FO-003: detail article (media, tags, collaborations, similaires)
CALL sp_add_index_if_missing('Media', 'idx_media_article_priorite',
    'ALTER TABLE Media ADD INDEX idx_media_article_priorite (Id_Article, priorite)') $$

CALL sp_add_index_if_missing('article_tag', 'idx_article_tag_tag_article',
    'ALTER TABLE article_tag ADD INDEX idx_article_tag_tag_article (Id_tag, Id_Article)') $$

CALL sp_add_index_if_missing('collaboration', 'idx_collab_article_user',
    'ALTER TABLE collaboration ADD INDEX idx_collab_article_user (Id_Article, Id_User)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_cat_lang_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_cat_lang_status_date (Id_Categorie, lang, Id_status_article, date_publication DESC)') $$

DROP PROCEDURE IF EXISTS sp_add_index_if_missing $$
DELIMITER ;
