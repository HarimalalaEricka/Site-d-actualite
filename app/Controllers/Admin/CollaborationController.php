<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Collaboration;

final class CollaborationController
{
    public function __construct()
    {

    }
    public function createCollaboration(Collaboration $Collaboration): ?int
    {
        $connection = Database::getConnection();

        $sql = 'INSERT INTO collaboration (Id_User, Id_Article) 
                VALUES (:Id_User, :Id_Article)';

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':Id_User' => $Collaboration->getIdUser(),
            ':Id_Article' => $Collaboration->getIdArticle(),
        ]);

        $insertedId = (int) $connection->lastInsertId();

        Database::closeConnection();

        return $insertedId > 0 ? $insertedId : null;
    }

    /**
     * Supprime une collaboration entre un utilisateur et un article
     *
     * @param int $idUser L'ID de l'utilisateur
     * @param int $idArticle L'ID de l'article
     * @return bool True si la suppression a réussi, false sinon
     */
    public function deleteCollaboration(int $idUser, int $idArticle): bool
    {
        $connection = Database::getConnection();

        try {
            $sql = 'DELETE FROM collaboration WHERE Id_User = :idUser AND Id_Article = :idArticle';
            $statement = $connection->prepare($sql);
            $result = $statement->execute([
                ':idUser' => $idUser,
                ':idArticle' => $idArticle,
            ]);

            return $result;
        } finally {
            Database::closeConnection();
        }
    }

    /**
     * Récupère toutes les collaborations pour un article
     *
     * @param int $idArticle L'ID de l'article
     * @return array<int, Collaboration> Liste des collaborations
     */
    public function getCollaborationsByArticle(int $idArticle): array
    {
        $sql = 'SELECT Id_User, Id_Article FROM collaboration WHERE Id_Article = :idArticle';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
        ]);

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $collaborations = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_User'], $row['Id_Article'])) {
                $collab = new Collaboration(
                    (int) $row['Id_User'],
                    (int) $row['Id_Article']
                );
                $collaborations[] = $collab;
            }
        }

        Database::closeConnection();

        return $collaborations;
    }
}