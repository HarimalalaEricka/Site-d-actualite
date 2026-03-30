<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;
use App\Services\PaginationService;

final class ArchiveController
{
    /**
     * @return array<string, mixed>
     */
    public function getArchiveData(string $lang, ?int $year, ?int $month, int $page, int $perPage, ?string $categorySlug = null): array
    {
        $connection = Database::getConnection();

        $categoryId = $this->resolveCategoryIdBySlug($connection, $categorySlug);
        $categories = $this->getCategories($connection);

        $availableMonths = $this->getAvailableMonths($connection, $lang, $categoryId);
        $total = $this->countArchiveArticles($connection, $lang, $year, $month, $categoryId);
        $articles = $this->getArchiveArticles($connection, $lang, $year, $month, $page, $perPage, $categoryId);

        Database::closeConnection();

        $pagination = PaginationService::calculate($total, $page, $perPage);

        return [
            'availableMonths' => $availableMonths,
            'categories' => $categories,
            'articles' => $articles,
            'year' => $year,
            'month' => $month,
            'selectedCategorySlug' => $categorySlug,
            'page' => $pagination['page'],
            'perPage' => $pagination['perPage'],
            'total' => $pagination['total'],
            'totalPages' => $pagination['totalPages'],
        ];
    }

    /**
     * @return array<int, array{year:int, month:int, total:int}>
     */
    private function getAvailableMonths(\PDO $connection, string $lang, ?int $categoryId): array
    {
        $sql = "SELECT YEAR(a.date_publication) AS y, MONTH(a.date_publication) AS m, COUNT(*) AS total
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                WHERE a.lang = :lang
                  AND s.status = 'publie'";

        if ($categoryId !== null) {
            $sql .= ' AND a.Id_Categorie = :categoryId';
        }

        $sql .= "
                GROUP BY YEAR(a.date_publication), MONTH(a.date_publication)
                ORDER BY YEAR(a.date_publication) DESC, MONTH(a.date_publication) DESC";

        $statement = $connection->prepare($sql);
        $statement->bindValue(':lang', $lang, \PDO::PARAM_STR);
        if ($categoryId !== null) {
            $statement->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
        }
        $statement->execute();
        $rows = $statement->fetchAll();

        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $result[] = [
                'year' => (int) ($row['y'] ?? 0),
                'month' => (int) ($row['m'] ?? 0),
                'total' => (int) ($row['total'] ?? 0),
            ];
        }

        return $result;
    }

    private function countArchiveArticles(\PDO $connection, string $lang, ?int $year, ?int $month, ?int $categoryId): int
    {
        $conditions = ["a.lang = :lang", "s.status = 'publie'"];

        if ($year !== null) {
            $conditions[] = 'YEAR(a.date_publication) = :year';
        }

        if ($month !== null) {
            $conditions[] = 'MONTH(a.date_publication) = :month';
        }

        if ($categoryId !== null) {
            $conditions[] = 'a.Id_Categorie = :categoryId';
        }

        $sql = 'SELECT COUNT(*) AS total
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                WHERE ' . implode(' AND ', $conditions);

        $statement = $connection->prepare($sql);
        $statement->bindValue(':lang', $lang, \PDO::PARAM_STR);

        if ($year !== null) {
            $statement->bindValue(':year', $year, \PDO::PARAM_INT);
        }
        if ($month !== null) {
            $statement->bindValue(':month', $month, \PDO::PARAM_INT);
        }
        if ($categoryId !== null) {
            $statement->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
        }

        $statement->execute();
        $row = $statement->fetch();

        if (!is_array($row)) {
            return 0;
        }

        return (int) ($row['total'] ?? 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getArchiveArticles(\PDO $connection, string $lang, ?int $year, ?int $month, int $page, int $perPage, ?int $categoryId): array
    {
        $conditions = ["a.lang = :lang", "s.status = 'publie'"];

        if ($year !== null) {
            $conditions[] = 'YEAR(a.date_publication) = :year';
        }

        if ($month !== null) {
            $conditions[] = 'MONTH(a.date_publication) = :month';
        }

        if ($categoryId !== null) {
            $conditions[] = 'a.Id_Categorie = :categoryId';
        }

        $offset = max(0, ($page - 1) * $perPage);

        $sql = 'SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.lang,
                       c.categorie,
                       u.prenom, u.nom
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                INNER JOIN User_ u ON u.Id_User = a.Id_User_principal
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY a.date_publication DESC, a.Id_Article DESC
                LIMIT :limit OFFSET :offset';

        $statement = $connection->prepare($sql);
        $statement->bindValue(':lang', $lang, \PDO::PARAM_STR);

        if ($year !== null) {
            $statement->bindValue(':year', $year, \PDO::PARAM_INT);
        }
        if ($month !== null) {
            $statement->bindValue(':month', $month, \PDO::PARAM_INT);
        }
        if ($categoryId !== null) {
            $statement->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
        }

        $statement->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $statement->execute();

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

    /**
     * @return array<int, array{Id_Categorie:int, categorie:string, category_slug:string}>
     */
    private function getCategories(\PDO $connection): array
    {
        $sql = 'SELECT Id_Categorie, categorie FROM Categorie ORDER BY categorie ASC';
        $statement = $connection->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll();

        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $name = (string) ($row['categorie'] ?? '');
            $result[] = [
                'Id_Categorie' => (int) ($row['Id_Categorie'] ?? 0),
                'categorie' => $name,
                'category_slug' => Article::slugify($name),
            ];
        }

        return $result;
    }

    private function resolveCategoryIdBySlug(\PDO $connection, ?string $slug): ?int
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        $categories = $this->getCategories($connection);
        foreach ($categories as $category) {
            if (($category['category_slug'] ?? '') === $slug) {
                return (int) ($category['Id_Categorie'] ?? 0);
            }
        }

        return null;
    }
}
