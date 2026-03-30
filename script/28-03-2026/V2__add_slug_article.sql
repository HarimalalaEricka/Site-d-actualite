ALTER TABLE Article
  ADD COLUMN slug VARCHAR(300) NOT NULL DEFAULT '' AFTER titre;

UPDATE Article
SET slug = LOWER(
    TRIM(BOTH '-' FROM REGEXP_REPLACE(
      REGEXP_REPLACE(CONVERT(titre USING ascii), '[^a-zA-Z0-9]+', '-'),
      '-+',
      '-'
    ))
  )
WHERE slug = '';

ALTER TABLE Article
  ADD UNIQUE KEY uq_article_lang_slug (lang, slug);
