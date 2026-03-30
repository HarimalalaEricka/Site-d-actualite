<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class HitoPublicationController
{
    public function __construct()
    {
    }

    public function createHitoPublication(string $action, int $idArticle, int $idUser): ?int
    {
        $connection = Database::getConnection();

        $sql = 'INSERT INTO hito_publication (date_, action, Id_Article, Id_User)
                VALUES (NOW(), :action, :idArticle, :idUser)';

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':action' => $action,
            ':idArticle' => $idArticle,
            ':idUser' => $idUser,
        ]);

        $insertedId = (int) $connection->lastInsertId();

        Database::closeConnection();

        return $insertedId > 0 ? $insertedId : null;
    }
}
