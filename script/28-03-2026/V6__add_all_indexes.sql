-- V6: Regroupement de tous les scripts d'indexation (FO-002, FO-003, FO-005, FO-006)
-- Script idempotent et rejouable

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

DROP PROCEDURE IF EXISTS sp_add_fulltext_if_missing $$
CREATE PROCEDURE sp_add_fulltext_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_columns_sql VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
          AND index_name = p_index_name
    ) THEN
        SET @stmt = CONCAT('CREATE FULLTEXT INDEX ', p_index_name, ' ON ', p_table_name, ' (', p_columns_sql, ')');
        PREPARE s FROM @stmt;
        EXECUTE s;
        DEALLOCATE PREPARE s;
    END IF;
END $$

-- FO-002: Home (recents, categories, plus lus)
CALL sp_add_index_if_missing('Article', 'idx_article_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_status_date (Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_lang_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_lang_status_date (lang, Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_cat_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_cat_status_date (Id_Categorie, Id_status_article, date_publication DESC)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_nbr_vues',
    'ALTER TABLE Article ADD INDEX idx_article_nbr_vues (nbr_vues)') $$

-- FO-003: Detail article (media, tags, collaborations, similaires)
CALL sp_add_index_if_missing('Media', 'idx_media_article_priorite',
    'ALTER TABLE Media ADD INDEX idx_media_article_priorite (Id_Article, priorite)') $$

CALL sp_add_index_if_missing('article_tag', 'idx_article_tag_tag_article',
    'ALTER TABLE article_tag ADD INDEX idx_article_tag_tag_article (Id_tag, Id_Article)') $$

CALL sp_add_index_if_missing('collaboration', 'idx_collab_article_user',
    'ALTER TABLE collaboration ADD INDEX idx_collab_article_user (Id_Article, Id_User)') $$

CALL sp_add_index_if_missing('Article', 'idx_article_cat_lang_status_date',
    'ALTER TABLE Article ADD INDEX idx_article_cat_lang_status_date (Id_Categorie, lang, Id_status_article, date_publication DESC)') $$

-- FO-005: Archives (navigation temporelle)
CALL sp_add_index_if_missing('Article', 'idx_article_date_publication',
    'ALTER TABLE Article ADD INDEX idx_article_date_publication (date_publication)') $$

-- FO-006: Recherche
CALL sp_add_fulltext_if_missing('Article', 'ft_article_titre_contenu', 'titre, contenu') $$

DROP PROCEDURE IF EXISTS sp_add_fulltext_if_missing $$
DROP PROCEDURE IF EXISTS sp_add_index_if_missing $$

DELIMITER ;
