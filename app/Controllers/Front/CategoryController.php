<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;
use App\Services\PaginationService;

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
     * Récupère les articles publiés d'une catégorie avec pagination
     * 
     * @return array<string, mixed> ['articles' => [...], 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function getPublishedByCategory(
        int $categoryId,
        string $lang,
        int $page = 1,
        int $perPage = 10
    ): array {
        $connection = Database::getConnection();

        // Compter le total
        $countSql = "SELECT COUNT(*) as total
                     FROM Article a
                     INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                     WHERE s.status = 'publie'
                       AND a.lang = :lang
                       AND a.Id_Categorie = :categoryId";
        $countStmt = $connection->prepare($countSql);
        $countStmt->execute([
            ':lang' => $lang,
            ':categoryId' => $categoryId,
        ]);
        $countResult = $countStmt->fetch();
        $total = (int) ($countResult['total'] ?? 0);

        // Calculer pagination
        $pagination = PaginationService::calculate($total, $page, $perPage);
        $offset = $pagination['offset'];

        // Récupérer les articles paginés
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
                ORDER BY a.date_publication DESC, a.Id_Article DESC
                LIMIT :limit OFFSET :offset";

        $statement = $connection->prepare($sql);
        $statement->bindValue(':lang', $lang, \PDO::PARAM_STR);
        $statement->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
        $statement->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll();
        Database::closeConnection();

        if (!is_array($rows)) {
            $rows = [];
        }

        foreach ($rows as &$row) {
            if (is_array($row)) {
                $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
                $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
            }
        }
        unset($row);

        return [
            'articles' => $rows,
            'total' => $pagination['total'],
            'page' => $pagination['page'],
            'perPage' => $pagination['perPage'],
            'totalPages' => $pagination['totalPages'],
        ];
    }
}
