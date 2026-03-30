<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;
use App\Services\PaginationService;

class SearchController
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Recherche articles avec filtres optionnels
     *
     * @param string $lang Langue (fr, en)
     * @param string|null $query Terme de recherche
     * @param int|null $categoryId Filtre catégorie
     * @param string|null $tagSlug Filtre tag
     * @param string|null $dateFrom Filtre date minimale (YYYY-MM-DD)
     * @param string|null $dateTo Filtre date maximale (YYYY-MM-DD)
     * @param int $page Numéro de page (1-based)
     * @param int $perPage Articles par page
     * @return array ['articles' => [...], 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function search(
        string $lang,
        ?string $query = null,
        ?int $categoryId = null,
        ?string $tagSlug = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $page = 1,
        int $perPage = 10
    ): array {
        $normalizedQuery = trim((string) $query);
        $queryTagSlugs = $this->extractHashtagSlugs($normalizedQuery);
        $textQuery = $this->removeHashtagsFromQuery($normalizedQuery);

        $conditions = [
            'sa.status = :status',
            'a.lang = :lang',
        ];
        $params = [
            ':status' => 'publie',
            ':lang' => $lang,
        ];

        if ($textQuery !== '') {
                        $conditions[] = '(
                                MATCH(a.titre, a.contenu) AGAINST(:qfilter IN BOOLEAN MODE)
                                OR a.titre LIKE :qlikeTitle
                                OR a.contenu LIKE :qlikeContent
                                OR EXISTS (
                                        SELECT 1
                                        FROM User_ up
                                        WHERE up.Id_User = a.Id_User_principal
                                            AND (
                                                up.nom LIKE :qAuthorNomPrincipal
                                                OR up.prenom LIKE :qAuthorPrenomPrincipal
                                                OR CONCAT(COALESCE(up.prenom, ""), " ", COALESCE(up.nom, "")) LIKE :qAuthorFullPrincipal
                                                OR CONCAT(COALESCE(up.nom, ""), " ", COALESCE(up.prenom, "")) LIKE :qAuthorFullPrincipalAlt
                                            )
                                )
                                OR EXISTS (
                                        SELECT 1
                                        FROM collaboration col
                                        INNER JOIN User_ uc ON uc.Id_User = col.Id_User
                                        WHERE col.Id_Article = a.Id_Article
                                            AND (
                                                uc.nom LIKE :qAuthorNomCollab
                                                OR uc.prenom LIKE :qAuthorPrenomCollab
                                                OR CONCAT(COALESCE(uc.prenom, ""), " ", COALESCE(uc.nom, "")) LIKE :qAuthorFullCollab
                                                OR CONCAT(COALESCE(uc.nom, ""), " ", COALESCE(uc.prenom, "")) LIKE :qAuthorFullCollabAlt
                                            )
                                )
                        )';
            $params[':qfilter'] = $this->escapeBooleanMode($textQuery);
            $params[':qlikeTitle'] = '%' . $textQuery . '%';
            $params[':qlikeContent'] = '%' . $textQuery . '%';
                        $params[':qAuthorNomPrincipal'] = '%' . $textQuery . '%';
                        $params[':qAuthorPrenomPrincipal'] = '%' . $textQuery . '%';
                        $params[':qAuthorFullPrincipal'] = '%' . $textQuery . '%';
                        $params[':qAuthorFullPrincipalAlt'] = '%' . $textQuery . '%';
                        $params[':qAuthorNomCollab'] = '%' . $textQuery . '%';
                        $params[':qAuthorPrenomCollab'] = '%' . $textQuery . '%';
                        $params[':qAuthorFullCollab'] = '%' . $textQuery . '%';
                        $params[':qAuthorFullCollabAlt'] = '%' . $textQuery . '%';
        }

        if ($categoryId !== null) {
            $conditions[] = 'a.Id_Categorie = :categoryId';
            $params[':categoryId'] = $categoryId;
        }

        $tagSlugs = [];
        if ($tagSlug !== null && $tagSlug !== '') {
            $tagSlugs[] = $tagSlug;
        }
        if ($queryTagSlugs !== []) {
            $tagSlugs = array_merge($tagSlugs, $queryTagSlugs);
        }
        $tagSlugs = array_values(array_unique(array_filter($tagSlugs, static fn ($value): bool => $value !== '')));

        if ($tagSlugs !== []) {
            $tagIds = $this->resolveTagIdsBySlugs($tagSlugs);
            if ($tagIds === []) {
                return [
                    'articles' => [],
                    'total' => 0,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalPages' => 1,
                ];
            }

            $tagPlaceholders = [];
            foreach ($tagIds as $index => $idTag) {
                $placeholder = ':tagId' . $index;
                $tagPlaceholders[] = $placeholder;
                $params[$placeholder] = $idTag;
            }

            $conditions[] = 'EXISTS (
                SELECT 1
                FROM article_tag at
                WHERE at.Id_Article = a.Id_Article
                  AND at.Id_tag IN (' . implode(', ', $tagPlaceholders) . ')
            )';
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $conditions[] = 'a.date_publication >= :dateFrom';
            $params[':dateFrom'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null && $dateTo !== '') {
            $conditions[] = 'a.date_publication <= :dateTo';
            $params[':dateTo'] = $dateTo . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $conditions);

        $countSql = "
            SELECT COUNT(*)
            FROM Article a
            INNER JOIN status_article sa ON sa.Id_status_article = a.Id_status_article
            WHERE {$whereClause}
        ";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Calculer pagination
        $pagination = PaginationService::calculate($total, $page, $perPage);
        $offset = $pagination['offset'];

        $relevanceSelect = '0 AS relevance';
        if ($textQuery !== '') {
            $relevanceSelect = 'MATCH(a.titre, a.contenu) AGAINST(:qscore IN BOOLEAN MODE) AS relevance';
            $params[':qscore'] = $this->escapeBooleanMode($textQuery);
        }

        $sql = "
            SELECT
                a.Id_Article,
                a.titre,
                a.slug,
                a.date_publication,
                a.contenu,
                a.Id_User_principal,
                a.nbr_vues,
                a.Id_Categorie,
                c.categorie,
                u.prenom,
                u.nom,
                {$relevanceSelect}
            FROM Article a
            INNER JOIN status_article sa ON sa.Id_status_article = a.Id_status_article
            INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
            INNER JOIN User_ u ON u.Id_User = a.Id_User_principal
            WHERE {$whereClause}
            ORDER BY " . ($textQuery !== '' ? 'relevance DESC, ' : '') . "a.date_publication DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($articles as &$article) {
            $article['author_name'] = trim(((string) ($article['prenom'] ?? '')) . ' ' . ((string) ($article['nom'] ?? '')));
            if ($article['author_name'] === '') {
                $article['author_name'] = 'Inconnu';
            }
            $article['category_name'] = (string) ($article['categorie'] ?? 'Inconnu');
            $article['category_slug'] = Article::slugify($article['category_name']);
            $article['slug'] = (string) ($article['slug'] ?? Article::slugify((string) ($article['titre'] ?? '')));
            $article['resume'] = $this->buildExcerpt((string) ($article['contenu'] ?? ''), 220);
        }
        unset($article);

        return [
            'articles' => $articles,
            'total' => $pagination['total'],
            'page' => $pagination['page'],
            'perPage' => $pagination['perPage'],
            'totalPages' => $pagination['totalPages'],
        ];
    }

    /**
     * Récupère catégories pour filtre
     *
     * @return array Liste catégories
     */
    public function getCategories(): array
    {
        $sql = 'SELECT Id_Categorie, categorie FROM Categorie ORDER BY categorie ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    /**
     * Récupère tags populaires
     *
     * @param int $limit Nombre de tags à retourner
     * @return array Liste tags avec count
     */
    public function getPopularTags(string $lang, int $limit = 20): array
    {
        $sql = "
            SELECT
                t.Id_tag,
                t.nom,
                COUNT(at.Id_Article) AS usage_count
            FROM tag t
            INNER JOIN article_tag at ON t.Id_tag = at.Id_tag
            INNER JOIN Article a ON at.Id_Article = a.Id_Article
            INNER JOIN status_article sa ON sa.Id_status_article = a.Id_status_article
            WHERE sa.status = :status AND a.lang = :lang
            GROUP BY t.Id_tag
            ORDER BY usage_count DESC
            LIMIT :limit
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', 'publie');
        $stmt->bindValue(':lang', $lang);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['tag_slug'] = Article::slugify((string) ($row['nom'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    /**
     * Échappe query pour BOOLEAN MODE (ajoute guillemets pour phrases exactes)
     *
     * @param string $query Terme brut
     * @return string Escaped pour MATCH BOOLEAN
     */
    private function escapeBooleanMode(string $query): string
    {
        // Trim et split par espaces
        $terms = array_filter(explode(' ', trim($query)));

        if (count($terms) === 1) {
            // Mot simple: chercher avec +term (required)
            return '+' . addslashes(current($terms));
        }

        // Multi-mots: chercher comme phrase exacte
        return '"' . addslashes($query) . '"';
    }

    /**
     * Résout slug catégorie vers ID
     *
     * @param string $slug Slug catégorie
     * @return int|null ID catégorie ou null
     */
    public function resolveCategoryIdBySlug(string $slug): ?int
    {
        $sql = 'SELECT Id_Categorie, categorie FROM Categorie';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as $row) {
            if (Article::slugify((string) ($row['categorie'] ?? '')) === $slug) {
                return (int) ($row['Id_Categorie'] ?? 0);
            }
        }

        return null;
    }

    private function resolveTagIdBySlug(string $slug): ?int
    {
        $sql = 'SELECT Id_tag, nom FROM tag';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as $row) {
            if (Article::slugify((string) ($row['nom'] ?? '')) === $slug) {
                return (int) ($row['Id_tag'] ?? 0);
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $slugs
     * @return array<int, int>
     */
    private function resolveTagIdsBySlugs(array $slugs): array
    {
        $ids = [];
        foreach ($slugs as $slug) {
            $id = $this->resolveTagIdBySlug($slug);
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<int, string>
     */
    private function extractHashtagSlugs(string $query): array
    {
        if ($query === '') {
            return [];
        }

        $matches = [];
        preg_match_all('/#([\p{L}\p{N}_-]+)/u', $query, $matches);
        $tokens = $matches[1] ?? [];

        $slugs = [];
        foreach ($tokens as $token) {
            $slug = Article::slugify((string) $token);
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }

        return array_values(array_unique($slugs));
    }

    private function removeHashtagsFromQuery(string $query): string
    {
        if ($query === '') {
            return '';
        }

        $withoutTags = preg_replace('/#[\p{L}\p{N}_-]+/u', ' ', $query);
        return trim((string) preg_replace('/\s+/', ' ', (string) $withoutTags));
    }

    private function buildExcerpt(string $html, int $maxLength): string
    {
        $text = trim(strip_tags($html));
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text) <= $maxLength) {
                return $text;
            }

            return mb_substr($text, 0, $maxLength - 1) . '…';
        }

        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - 1) . '...';
    }
}
