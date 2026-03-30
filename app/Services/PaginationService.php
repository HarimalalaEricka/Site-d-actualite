<?php

declare(strict_types=1);

namespace App\Services;

/**
 * PaginationService: Service centralisé de pagination pour toutes les pages Front
 * 
 * Fournit:
 * - Calcul du total de pages, offset LIMIT
 * - Validation et normalisation des paramètres (page, perPage)
 * - Génération sécurisée d'URLs de pagination
 * - Support de multiples filtres (query string preservation)
 */
final class PaginationService
{
    /**
     * Calcule les données de pagination
     * 
     * @param int $total Nombre total d'éléments
     * @param int $page Numéro de page (1-based)
     * @param int $perPage Éléments par page
     * @return array{page: int, perPage: int, offset: int, total: int, totalPages: int}
     */
    public static function calculate(int $total, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $safePage = max(1, min($page, $totalPages));

        return [
            'page' => $safePage,
            'perPage' => $perPage,
            'offset' => $offset,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Convertit un tableau de paramètres GET en query string sûr
     * 
     * @param array<string, mixed> $params Paramètres à encoder
     * @param array<int, string> $exclude Paramètres à exclure (ex: ['page'])
     * @return string Query string encodé (vide si aucun param)
     */
    public static function buildQueryString(array $params, array $exclude = []): string
    {
        $filtered = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $exclude, true) || $value === '' || $value === null) {
                continue;
            }
            // Sécuriser la clé et la valeur
            if (is_scalar($value)) {
                $filtered[htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8')] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }
        }

        if (empty($filtered)) {
            return '';
        }

        return http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Génère une URL de pagination avec filtres préservés
     * 
     * @param int $pageNum Numéro de page cible
     * @param array<string, mixed> $params Paramètres actuels
     * @param string $baseUrl URL de base (ex: '/fr/search' ou '?')
     * @param array<int, string> $exclude Paramètres à ne pas transmettre
     * @return string URL sécurisée avec page + filtres
     */
    public static function buildPageUrl(
        int $pageNum,
        array $params = [],
        string $baseUrl = '?',
        array $exclude = ['page']
    ): string {
        $queryString = self::buildQueryString($params, $exclude);
        $separator = $baseUrl === '?' || str_ends_with($baseUrl, '?') ? '' : '?';
        $pageParam = 'page=' . $pageNum;

        if ($queryString === '') {
            return $baseUrl . $separator . $pageParam;
        }

        return $baseUrl . $separator . $queryString . '&' . $pageParam;
    }

    /**
     * Rend l'HTML de la navbar de pagination
     * 
     * @param int $currentPage Page actuelle
     * @param int $totalPages Total de pages
     * @param array<string, mixed> $params Paramètres query string
     * @param string $baseUrl URL de base pour les liens
     * @return string HTML de navigation (vide si totalPages <= 1)
     */
    public static function renderNav(
        int $currentPage,
        int $totalPages,
        array $params = [],
        string $baseUrl = '?'
    ): string {
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav class="pagination">';

        if ($currentPage > 1) {
            $prevUrl = self::buildPageUrl($currentPage - 1, $params, $baseUrl);
            $html .= sprintf(
                '<a href="%s">← Précédent</a>',
                htmlspecialchars($prevUrl, ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= sprintf(
            '<span class="page-info">Page %d/%d</span>',
            $currentPage,
            $totalPages
        );

        if ($currentPage < $totalPages) {
            $nextUrl = self::buildPageUrl($currentPage + 1, $params, $baseUrl);
            $html .= sprintf(
                '<a href="%s">Suivant →</a>',
                htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= '</nav>';
        return $html;
    }
}
