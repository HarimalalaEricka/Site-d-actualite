-- V5: Ajout index FULLTEXT pour FO-006 Recherche
-- Date: 2026-03-31

USE actualite;

-- Procédure utilitaire pour création index idempotente
DELIMITER //

CREATE PROCEDURE sp_add_fulltext_if_missing(
    p_table VARCHAR(255),
    p_index_name VARCHAR(255),
    p_column_def VARCHAR(500)
)
BEGIN
    DECLARE v_index_exists INT;
    SELECT COUNT(*) INTO v_index_exists
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = p_table
      AND INDEX_NAME = p_index_name;
    
    IF v_index_exists = 0 THEN
        SET @sql = CONCAT('CREATE FULLTEXT INDEX ', p_index_name, ' ON ', p_table, ' (', p_column_def, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

DELIMITER ;

-- Créer index FULLTEXT pour recherche titre/contenu
CALL sp_add_fulltext_if_missing('Article', 'ft_article_titre_contenu', 'titre, contenu');

-- Index régulier pour recherche + filtre tag (si pas déjà présent)
DELIMITER //

CREATE PROCEDURE sp_add_regular_index_if_missing(
    p_table VARCHAR(255),
    p_index_name VARCHAR(255),
    p_column_def VARCHAR(500)
)
BEGIN
    DECLARE v_index_exists INT;
    SELECT COUNT(*) INTO v_index_exists
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = p_table
      AND INDEX_NAME = p_index_name;
    
    IF v_index_exists = 0 THEN
        SET @sql = CONCAT('CREATE INDEX ', p_index_name, ' ON ', p_table, ' (', p_column_def, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

DELIMITER ;

-- Index pour recherche par tag (si pas déjà présent de V3)
CALL sp_add_regular_index_if_missing('article_tag', 'idx_article_tag_tag_article', 'Id_tag, Id_Article');

-- Index composite pour recherche + filtre catégorie
CALL sp_add_regular_index_if_missing('Article', 'idx_article_cat_status_date', 'Id_Categorie, Id_status_article, date_publication DESC');

-- Index pour recherche + filtre date
CALL sp_add_regular_index_if_missing('Article', 'idx_article_status_date', 'Id_status_article, date_publication DESC');

-- Index pour recherche + filtre date + langue
CALL sp_add_regular_index_if_missing('Article', 'idx_article_lang_status_date', 'lang, Id_status_article, date_publication DESC');

-- Cleanup des procédures stockées
DROP PROCEDURE IF EXISTS sp_add_fulltext_if_missing;
DROP PROCEDURE IF EXISTS sp_add_regular_index_if_missing;
