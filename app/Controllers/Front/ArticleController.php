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
        $sql = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.contenu, a.nbr_vues, a.lang,
                       a.Id_Categorie, c.categorie,
                       u.prenom, u.nom,
                       m.url AS image_url
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                INNER JOIN User_ u ON u.Id_User = a.Id_User_principal
                LEFT JOIN Media m ON m.Id_Article = a.Id_Article AND m.priorite = 1
                WHERE a.Id_Article = :idArticle
                  AND a.lang = :lang
                  AND s.status = 'publie'
                LIMIT 1";

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
            ':lang' => $lang,
        ]);

        $row = $statement->fetch();
        Database::closeConnection();

        if (!is_array($row) || !isset($row['Id_Article'])) {
            return null;
        }

        $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
        $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));

        return $row;
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
}
