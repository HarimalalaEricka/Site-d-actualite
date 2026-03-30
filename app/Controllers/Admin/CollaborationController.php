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

}