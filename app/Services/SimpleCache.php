<?php

declare(strict_types=1);

namespace App\Services;

/**
 * SimpleCache: cache applicatif en mémoire avec TTL
 * Durée de vie: session PHP ou < 120 secondes max
 * 
 * Rationale: Évite les requêtes SQL répétées pour Home lors de pics trafic.
 * Le cache est transparent: stocké en mémoire pendant la durée de vie du script PHP.
 * En production (PHP-FPM/Apache), chaque process a son propre cache isolé.
 */
final class SimpleCache
{
    /**
     * Store pour cacher les données
     * @var array<string, array{value: mixed, ttl: int, timestamp: int}>
     */
    private static array $store = [];

    /**
     * Récupère une valeur du cache
     * @return mixed null si absent ou expiré
     */
    public static function get(string $key): mixed
    {
        if (!isset(self::$store[$key])) {
            return null;
        }

        $cached = self::$store[$key];
        $now = time();

        // Vérifier l'expiration
        if ($now - $cached['timestamp'] > $cached['ttl']) {
            unset(self::$store[$key]);
            return null;
        }

        return $cached['value'];
    }

    /**
     * Stocke une valeur dans le cache
     * @param mixed $value
     * @param int $ttlSeconds durée de vie en secondes (défaut: 60s)
     */
    public static function set(string $key, mixed $value, int $ttlSeconds = 60): void
    {
        self::$store[$key] = [
            'value' => $value,
            'ttl' => $ttlSeconds,
            'timestamp' => time(),
        ];
    }

    /**
     * Supprime une entrée du cache
     */
    public static function delete(string $key): void
    {
        unset(self::$store[$key]);
    }

    /**
     * Vide le cache complètement
     */
    public static function clear(): void
    {
        self::$store = [];
    }
}
