<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * ViewCounterService: incrémentation simple des vues
 *
 * Stratégie simplifiée:
 * 1. Générer un hash anonyme (IP + User-Agent)
 * 2. Créer une clé de garde en cache par article + visiteur + bucket 30 min
 * 3. Si la clé existe: ne pas recompter
 * 4. Sinon: incrémenter directement Article.nbr_vues
 */
final class ViewCounterService
{
    private const VIEW_GUARD_TTL = 31 * 60;

    /**
     * Génère un hash anonyme du visiteur
     * Combine IP + User-Agent pour minimiser les collisions entre vrais visiteurs
     * 
     * @return string hash SHA-256 (64 char)
     */
    public static function generateVisitorHash(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        
        // Combiner pour créer empreinte anonyme
        $fingerprint = $ip . '|' . substr($userAgent, 0, 100);
        
        return hash('sha256', $fingerprint);
    }

    /**
     * Calcule le bucket de 30 minutes pour antiduplication
     * @return string format: "2026-03-30 14:30:00" (arrondi à la demi-heure)
     */
    public static function getCurrentTimeBucket(): string
    {
        $nowMinutes = (int) date('i');
        $bucket = ($nowMinutes < 30) ? 0 : 30;
        
        return date('Y-m-d H:') . str_pad((string) $bucket, 2, '0', STR_PAD_LEFT) . ':00';
    }

    /**
     * Enregistre une vue d'article avec anti-duplication 30 min
     * 
     * @return bool true si vue comptée, false si dupliquée
     */
    public static function recordView(int $articleId): bool
    {
        try {
            $visitorHash = self::generateVisitorHash();
            $timeBucket = self::getCurrentTimeBucket();
            $guardKey = sprintf('view_guard_%d_%s_%s', $articleId, $visitorHash, $timeBucket);

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            if (!isset($_SESSION['article_view_guard']) || !is_array($_SESSION['article_view_guard'])) {
                $_SESSION['article_view_guard'] = [];
            }

            $now = time();

            // Nettoyage opportuniste des anciennes clés
            foreach ($_SESSION['article_view_guard'] as $key => $timestamp) {
                if (!is_int($timestamp) || ($now - $timestamp) > self::VIEW_GUARD_TTL) {
                    unset($_SESSION['article_view_guard'][$key]);
                }
            }

            if (isset($_SESSION['article_view_guard'][$guardKey])) {
                return false;
            }

            $connection = Database::getConnection();
            self::incrementViewCount($connection, $articleId);
            Database::closeConnection();

            $_SESSION['article_view_guard'][$guardKey] = $now;

            return true;
        } catch (\Throwable $e) {
            error_log('ViewCounterService error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Incrémente le compteur nbr_vues de manière atomique
     */
    private static function incrementViewCount(\PDO $connection, int $articleId): void
    {
        $sql = "UPDATE Article SET nbr_vues = COALESCE(nbr_vues, 0) + 1 WHERE Id_Article = :article_id";
        $stmt = $connection->prepare($sql);
        $stmt->execute([':article_id' => $articleId]);
    }

}
