<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;

final class CategoryController
{
    /**
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $categorySlug): ?array
    {
        $sql = 'SELECT Id_Categorie, categorie FROM Categorie';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll();

        if (!is_array($rows)) {
            Database::closeConnection();
            return null;
        }

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['Id_Categorie'], $row['categorie'])) {
                continue;
            }

            $computedSlug = Article::slugify((string) $row['categorie']);
            if ($computedSlug === $categorySlug) {
                Database::closeConnection();
                return [
                    'Id_Categorie' => (int) $row['Id_Categorie'],
                    'categorie' => (string) $row['categorie'],
                    'slug' => $computedSlug,
                ];
            }
        }

        Database::closeConnection();
        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPublishedByCategory(int $categoryId, string $lang): array
    {
        $sql = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.nbr_vues,
                       c.categorie,
                       m.url AS image_url
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                LEFT JOIN Media m ON m.Id_Article = a.Id_Article AND m.priorite = 1
                WHERE s.status = 'publie'
                  AND a.lang = :lang
                  AND a.Id_Categorie = :categoryId
                ORDER BY a.date_publication DESC, a.Id_Article DESC";

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':lang' => $lang,
            ':categoryId' => $categoryId,
        ]);

        $rows = $statement->fetchAll();
        Database::closeConnection();

        if (!is_array($rows) || $rows === []) {
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
}
