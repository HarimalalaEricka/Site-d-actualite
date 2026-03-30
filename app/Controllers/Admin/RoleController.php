<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Role;

final class RoleController
{
    public function __construct()
    {
    }

    public function getRoleByIdUser( int $idUser): ?Role
    {
        $sql = 'SELECT r.* FROM Role r JOIN User_ u ON r.Id_Role = u.Id_Role WHERE u.Id_User = :idUser';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idUser' => $idUser,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_Role'])) {
            return null;
        }

        return Role::fromArray($row);
    }
}
