<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class HistoStatusController
{
    public function __construct()
    {
    }

    /**
     * Enregistre un changement de statut dans histo_status
     *
     * @param int $idArticle L'ID de l'article
     * @param int $idStatusArticle L'ID du nouveau statut
     * @return ?int L'ID de l'enregistrement inséré, ou null en cas d'erreur
     */
    public function createHistoStatus(int $idArticle, int $idStatusArticle): ?int
    {
        try {
            $sql = 'INSERT INTO histo_status (Id_Article, Id_status_article, date_) 
                    VALUES (:idArticle, :idStatusArticle, NOW())';

            $statement = Database::getConnection()->prepare($sql);
            $result = $statement->execute([
                ':idArticle' => $idArticle,
                ':idStatusArticle' => $idStatusArticle,
            ]);

            if ($result) {
                $lastId = Database::getConnection()->lastInsertId();
                return $lastId ? (int) $lastId : null;
            }

            return null;
        } finally {
            Database::closeConnection();
        }
    }
}
