<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Database;

final class ViewCounterController
{
    /**
     * Incrémente le compteur de vues dans la base de données
     *
     * @param int $idArticle L'ID de l'article
     * @return bool True si succès, false sinon
     */
    public function incrementViewCount(int $idArticle): bool
    {
        try {
            $connection = Database::getConnection();
            $sql = 'UPDATE Article SET nbr_vues = nbr_vues + 1 WHERE Id_Article = :idArticle';
            $statement = $connection->prepare($sql);
            $result = $statement->execute([':idArticle' => $idArticle]);
            Database::closeConnection();
            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Récupère le nombre de vues depuis la base de données
     *
     * @param int $idArticle L'ID de l'article
     * @return int Le nombre de vues actuelles
     */
    public function getViewCount(int $idArticle): int
    {
        try {
            $connection = Database::getConnection();
            $sql = 'SELECT nbr_vues FROM Article WHERE Id_Article = :idArticle';
            $statement = $connection->prepare($sql);
            $statement->execute([':idArticle' => $idArticle]);
            $result = $statement->fetch();
            Database::closeConnection();

            return $result ? (int)$result['nbr_vues'] : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
