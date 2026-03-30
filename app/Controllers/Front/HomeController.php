<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;

final class HomeController
{
    /**
     * @return array{featured: array<string, mixed>|null, latest: array<int, array<string, mixed>>}
     */
    public function getHomeData(string $lang): array
    {
        $connection = Database::getConnection();

        $sql = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.nbr_vues, a.lang,
                       c.categorie,
                       m.url AS image_url
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                LEFT JOIN Media m ON m.Id_Article = a.Id_Article AND m.priorite = 1
                WHERE s.status = 'publie' AND a.lang = :lang
                ORDER BY a.date_publication DESC, a.Id_Article DESC
                LIMIT 12";

        $statement = $connection->prepare($sql);
        $statement->execute([':lang' => $lang]);
        $rows = $statement->fetchAll();

        Database::closeConnection();

        if (!is_array($rows) || $rows === []) {
            return [
                'featured' => null,
                'latest' => [],
            ];
        }

        foreach ($rows as &$row) {
            if (is_array($row)) {
                $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
                $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
            }
        }
        unset($row);

        $featured = $rows[0];
        $latest = array_slice($rows, 1);

        return [
            'featured' => $featured,
            'latest' => $latest,
        ];
    }
}
