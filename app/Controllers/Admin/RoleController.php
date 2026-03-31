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

    /**
     * @return array<int, Role>
     */
    public function getAllRoles(): array
    {
        $sql = 'SELECT * FROM Role ORDER BY role ASC, Id_Role ASC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $roles = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_Role'])) {
                $roles[] = Role::fromArray($row);
            }
        }

        Database::closeConnection();

        return $roles;
    }

    public function getRoleById(int $idRole): ?Role
    {
        $sql = 'SELECT * FROM Role WHERE Id_Role = :idRole';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idRole' => $idRole,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_Role'])) {
            Database::closeConnection();
            return null;
        }

        $role = Role::fromArray($row);

        Database::closeConnection();

        return $role;
    }

    public function createRole(Role $role): ?int
    {
        try {
            $sql = 'INSERT INTO Role (role) VALUES (:role)';

            $statement = Database::getConnection()->prepare($sql);
            $result = $statement->execute([
                ':role' => $role->getRole(),
            ]);

            if (!$result) {
                return null;
            }

            $lastId = Database::getConnection()->lastInsertId();
            return $lastId ? (int) $lastId : null;
        } finally {
            Database::closeConnection();
        }
    }

    public function updateRole(int $idRole, Role $role): bool
    {
        try {
            $sql = 'UPDATE Role SET role = :role WHERE Id_Role = :idRole';

            $statement = Database::getConnection()->prepare($sql);
            return $statement->execute([
                ':role' => $role->getRole(),
                ':idRole' => $idRole,
            ]);
        } finally {
            Database::closeConnection();
        }
    }

    public function deleteRole(int $idRole): bool
    {
        try {
            $sql = 'DELETE FROM Role WHERE Id_Role = :idRole';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idRole' => $idRole,
            ]);

            return $statement->rowCount() > 0;
        } finally {
            Database::closeConnection();
        }
    }

    public function getRoleByIdUser(int $idUser): ?Role
    {
        $sql = 'SELECT r.* FROM Role r JOIN User_ u ON r.Id_Role = u.Id_Role WHERE u.Id_User = :idUser';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idUser' => $idUser,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_Role'])) {
            Database::closeConnection();
            return null;
        }

        $role = Role::fromArray($row);

        Database::closeConnection();

        return $role;
    }
}
