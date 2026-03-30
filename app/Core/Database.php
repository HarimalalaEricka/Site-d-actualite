<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $configPath = dirname(__DIR__, 2) . '/config/config.php';

        if (!file_exists($configPath)) {
            throw new RuntimeException('Fichier de configuration introuvable: ' . $configPath);
        }

        /** @var array<string, mixed> $config */
        $config = require $configPath;

        if (!isset($config['db']) || !is_array($config['db'])) {
            throw new RuntimeException('Configuration de base de donnees invalide.');
        }

        $db = $config['db'];

        $host = $db['host'] ?? '127.0.0.1';
        $port = (int) ($db['port'] ?? 3306);
        $name = $db['name'] ?? '';
        $user = $db['user'] ?? '';
        $pass = $db['pass'] ?? '';
        $charset = $db['charset'] ?? 'utf8mb4';

        if ($name === '') {
            throw new RuntimeException('Le nom de la base de donnees est vide.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            (string) $host,
            $port,
            (string) $name,
            (string) $charset
        );

        try {
            self::$connection = new PDO($dsn, (string) $user, (string) $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Connexion a la base de donnees impossible: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return self::$connection;
    }
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}
