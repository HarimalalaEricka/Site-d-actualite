<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;
use App\Models\Article;
use App\Services\SimpleCache;

/**
 * HomeController: agrégation performante des blocs Home
 * 
 * Stratégie:
 * 1. Cache applicatif court TTL (90s) pour éviter requêtes répétées
 * 2. Requête optimisée avec indexes: (lang, Id_status_article, date_publication DESC)
 * 3. Récupère: A la une (1) + Dernières (6) + Plus lus (5)
 * 4. N requêtes SQL fixe (pas de N+1)
 * 
 * Performance cible: < 300ms SQL (+ cache hit ~ 0ms)
 */
final class HomeController
{
    private const CACHE_KEY = 'home_data_';
    private const CACHE_TTL = 90; // secondes
    private const FEATURED_COUNT = 1;
    private const LATEST_COUNT = 6;
    private const TOP_VIEWED_COUNT = 5;

    /**
     * Récupère les données complètes de Home
     * @return array{featured: array<string, mixed>|null, latest: array<int, array<string, mixed>>, popular: array<int, array<string, mixed>>, categories: array<int, array<string, mixed>>}
     */
    public function getHomeData(string $lang): array
    {
        // Vérifier le cache d'abord
        $cacheKey = self::CACHE_KEY . $lang;
        $cachedData = SimpleCache::get($cacheKey);
        
        if (is_array($cachedData)) {
            return $cachedData;
        }

        // Cache miss: construire les données
        $homeData = $this->buildHomeData($lang);
        
        // Stocker en cache
        SimpleCache::set($cacheKey, $homeData, self::CACHE_TTL);
        
        return $homeData;
    }

    /**
     * Construit les données de Home (requête SQL optimisée)
     * @return array{featured: array<string, mixed>|null, latest: array<int, array<string, mixed>>, popular: array<int, array<string, mixed>>, categories: array<int, array<string, mixed>>}
     */
    private function buildHomeData(string $lang): array
    {
        $connection = Database::getConnection();

        // === REQUÊTE 1: Dernières + A la une (utilise index: lang, status, date DESC) ===
        $sql = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.nbr_vues, a.lang,
                       c.Id_Categorie, c.categorie,
                       m.url AS image_url
                FROM Article a
                INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                LEFT JOIN Media m ON m.Id_Article = a.Id_Article AND m.priorite = 1
                WHERE s.status = 'publie' AND a.lang = :lang
                ORDER BY a.date_publication DESC, a.Id_Article DESC
                LIMIT " . (self::FEATURED_COUNT + self::LATEST_COUNT);

        $stmt = $connection->prepare($sql);
        $stmt->execute([':lang' => $lang]);
        $recentRows = $stmt->fetchAll();

        // === REQUÊTE 2: Plus lus (utilise compteur total Article.nbr_vues) ===
        $sqlPopular = "SELECT a.Id_Article, a.titre, a.slug, a.date_publication, a.nbr_vues,
                              c.Id_Categorie, c.categorie,
                              m.url AS image_url
                       FROM Article a
                       INNER JOIN status_article s ON s.Id_status_article = a.Id_status_article
                       INNER JOIN Categorie c ON c.Id_Categorie = a.Id_Categorie
                       LEFT JOIN Media m ON m.Id_Article = a.Id_Article AND m.priorite = 1
                       WHERE s.status = 'publie' AND a.lang = :lang
                   ORDER BY COALESCE(a.nbr_vues, 0) DESC, a.date_publication DESC
                       LIMIT " . self::TOP_VIEWED_COUNT;

        $stmtPopular = $connection->prepare($sqlPopular);
        $stmtPopular->execute([':lang' => $lang]);
        $popularRows = $stmtPopular->fetchAll();

        // === REQUÊTE 3: Catégories disponibles (petit jeu de données) ===
        $sqlCategories = "SELECT DISTINCT c.Id_Categorie, c.categorie, COUNT(a.Id_Article) AS article_count
                          FROM Categorie c
                          LEFT JOIN Article a ON a.Id_Categorie = c.Id_Categorie 
                                             AND a.lang = :lang 
                                             AND a.Id_status_article = (SELECT Id_status_article FROM status_article WHERE status = 'publie')
                          GROUP BY c.Id_Categorie, c.categorie
                          ORDER BY article_count DESC
                          LIMIT 10";

        $stmtCategories = $connection->prepare($sqlCategories);
        $stmtCategories->execute([':lang' => $lang]);
        $categories = $stmtCategories->fetchAll();

        Database::closeConnection();

        // === TRAITEMENT: normaliser les données ===
        if (!is_array($recentRows) || $recentRows === []) {
            return [
                'featured' => null,
                'latest' => [],
                'popular' => [],
                'categories' => $this->normalizeCategories($categories),
            ];
        }

        // Normaliser récents
        foreach ($recentRows as &$row) {
            if (is_array($row)) {
                $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
                $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
            }
        }
        unset($row);

        // Normaliser populaires
        foreach ($popularRows as &$row) {
            if (is_array($row)) {
                $row['slug'] = (string) ($row['slug'] ?? Article::slugify((string) ($row['titre'] ?? '')));
                $row['category_slug'] = Article::slugify((string) ($row['categorie'] ?? ''));
            }
        }
        unset($row);

        $featured = $recentRows[0] ?? null;
        $latest = array_slice($recentRows, 1, self::LATEST_COUNT);

        return [
            'featured' => $featured,
            'latest' => $latest,
            'popular' => $popularRows,
            'categories' => $this->normalizeCategories($categories),
        ];
    }

    /**
     * Normalise les catégories pour la vue
     * @param array<int, array<string, mixed>> $categories
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCategories(array $categories): array
    {
        $normalized = [];
        
        foreach ($categories as $cat) {
            if (is_array($cat)) {
                $normalized[] = [
                    'Id_Categorie' => $cat['Id_Categorie'],
                    'categorie' => $cat['categorie'],
                    'category_slug' => Article::slugify((string) ($cat['categorie'] ?? '')),
                    'article_count' => (int) ($cat['article_count'] ?? 0),
                ];
            }
        }
        
        return $normalized;
    }
}
