<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;

final class ArticleController
{
    /**
     * @return array<string, mixed>|null
     */
    public function getPublishedArticleById(int $idArticle, string $lang): ?array
    {
                $connection = Database::getConnection();

                $sql = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.contenu, a.nbr_vues, a.lang,
                       a.Id_Categorie, c.categorie,
                                             u.Id_User AS principal_user_id, u.prenom, u.nom,
                                             m.url AS primary_media_url,
                                             m.url AS image_url,
                                             tm.type AS primary_media_type
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                INNER JOIN User_ u ON u.Id_User = a.Id_User_principal
                LEFT JOIN Media m ON m.Id_Article = a.Id_Article AND m.priorite = 1
                                LEFT JOIN type_media tm ON tm.Id_type_media = m.Id_type_media
                WHERE a.Id_Article = :idArticle
                  AND a.lang = :lang
                  AND s.status = 'publie'
                LIMIT 1";

                $statement = $connection->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
            ':lang' => $lang,
        ]);

        $row = $statement->fetch();
        Database::closeConnection();

        if (!is_array($row) || !isset($row['Id_Article'])) {
            return null;
        }

        $row['contenu'] = $this->sanitizeContentHtml((string) ($row['contenu'] ?? ''));
        $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
        $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
        $row['primary_media_kind'] = $this->resolveMediaKind(
            isset($row['primary_media_type']) ? (string) $row['primary_media_type'] : null,
            (string) ($row['primary_media_url'] ?? '')
        );
        $row['tags'] = $this->getTagsByArticle($connection, (int) $row['Id_Article']);
        $row['collaborations'] = $this->getCollaborationsByArticle(
            $connection,
            (int) $row['Id_Article'],
            (int) ($row['principal_user_id'] ?? 0)
        );
        $row['media_gallery'] = $this->getSecondaryMediaByArticle($connection, (int) $row['Id_Article']);
        $row['similar_articles'] = $this->getSimilarArticles(
            $connection,
            (int) $row['Id_Article'],
            (int) ($row['Id_Categorie'] ?? 0),
            (string) ($row['lang'] ?? 'fr')
        );

        Database::closeConnection();

        return $row;
    }

    /**
     * @return array<int, array{Id_tag:int, nom:string}>
     */
    private function getTagsByArticle(\PDO $connection, int $idArticle): array
    {
        $sql = "SELECT t.Id_tag, t.nom
                FROM article_tag atg
                INNER JOIN tag t ON t.Id_tag = atg.Id_tag
                WHERE atg.Id_Article = :idArticle
                ORDER BY t.nom ASC";

        $statement = $connection->prepare($sql);
        $statement->execute([':idArticle' => $idArticle]);
        $rows = $statement->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array{Id_User:int, prenom:string, nom:string}>
     */
    private function getCollaborationsByArticle(\PDO $connection, int $idArticle, int $principalUserId): array
    {
        $sql = "SELECT u.Id_User, u.prenom, u.nom
                FROM collaboration c
                INNER JOIN User_ u ON u.Id_User = c.Id_User
                WHERE c.Id_Article = :idArticle
                  AND u.Id_User <> :principalUserId
                ORDER BY u.nom ASC, u.prenom ASC";

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
            ':principalUserId' => $principalUserId,
        ]);
        $rows = $statement->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
         * @return array<int, array{Id_Media:int, url:string, description:string|null, media_type:string|null, media_kind:string}>
     */
    private function getSecondaryMediaByArticle(\PDO $connection, int $idArticle): array
    {
                $sql = "SELECT m.Id_Media, m.url, m.description, tm.type AS media_type
                FROM Media m
                                LEFT JOIN type_media tm ON tm.Id_type_media = m.Id_type_media
                WHERE m.Id_Article = :idArticle
                  AND (m.priorite = 0 OR m.priorite IS NULL)
                ORDER BY m.Id_Media ASC";

        $statement = $connection->prepare($sql);
        $statement->execute([':idArticle' => $idArticle]);
        $rows = $statement->fetchAll();

        if (!is_array($rows)) {
            return [];
        }

        foreach ($rows as &$row) {
            if (!is_array($row)) {
                continue;
            }
            $row['media_kind'] = $this->resolveMediaKind(
                isset($row['media_type']) ? (string) $row['media_type'] : null,
                (string) ($row['url'] ?? '')
            );
        }
        unset($row);

        return $rows;
    }

    private function resolveMediaKind(?string $mediaType, string $url): string
    {
        $type = strtolower(trim((string) $mediaType));
        if ($type === 'video') {
            return 'video';
        }
        if ($type === 'image') {
            return 'image';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $videoExtensions = ['mp4', 'webm', 'ogg', 'ogv', 'm4v', 'mov'];

        if (in_array($extension, $videoExtensions, true)) {
            return 'video';
        }

        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false || strpos($url, 'vimeo.com') !== false) {
            return 'video';
        }

        return 'image';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSimilarArticles(\PDO $connection, int $idArticle, int $categoryId, string $lang): array
    {
        $sql = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, c.categorie
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                WHERE a.Id_Article <> :idArticle
                  AND a.Id_Categorie = :categoryId
                  AND a.lang = :lang
                  AND s.status = 'publie'
                ORDER BY a.date_publication DESC, a.Id_Article DESC
                LIMIT 3";

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
            ':categoryId' => $categoryId,
            ':lang' => $lang,
        ]);
        $rows = $statement->fetchAll();

        if (!is_array($rows)) {
            return [];
        }

        foreach ($rows as &$row) {
            if (is_array($row)) {
                $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
                $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
            }
        }
        unset($row);

        return $rows;
    }

    private function sanitizeContentHtml(string $html): string
    {
        $withoutScripts = (string) preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html);
        $withoutStyles = (string) preg_replace('#<style\b[^>]*>(.*?)</style>#is', '', $withoutScripts);

        return strip_tags(
            $withoutStyles,
            '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><blockquote><a><img><video><source>'
        );
    }

    public function buildCanonicalPath(array $article): string
    {
        $timestamp = strtotime((string) ($article['date_publication'] ?? ''));
        $year = $timestamp !== false ? date('Y', $timestamp) : date('Y');
        $month = $timestamp !== false ? date('m', $timestamp) : date('m');
        $day = $timestamp !== false ? date('d', $timestamp) : date('d');

        $lang = (string) ($article['lang'] ?? 'fr');
        $categorySlug = (string) ($article['category_slug'] ?? 'actualite');
        $id = (int) ($article['Id_Article'] ?? 0);
        $slug = (string) ($article['slug'] ?? 'article');

        return sprintf('/%s/%s/article/%s/%s/%s/%d-%s.html', $lang, $categorySlug, $year, $month, $day, $id, $slug);
    }

    /**
     * Incrémente le compteur de vues d'un article
     *
     * @param int $idArticle L'ID de l'article
     * @return bool True si l'incrémentation a réussi, false sinon
     */
    public function incrementArticleViews(int $idArticle): bool
    {
        try {
            $connection = Database::getConnection();

            $sql = 'UPDATE Article SET nbr_vues = nbr_vues + 1 WHERE Id_Article = :idArticle';
            $statement = $connection->prepare($sql);
            $result = $statement->execute([
                ':idArticle' => $idArticle,
            ]);

            Database::closeConnection();

            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
