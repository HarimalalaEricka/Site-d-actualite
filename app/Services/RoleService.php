<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Role;
use PDO;

final class RoleService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * @return array<int, Role>
     */
    public function getAllRole(): array
    {
        $sql = 'SELECT Id_Role, role FROM Role ORDER BY Id_Role DESC';
        $statement = $this->pdo->query($sql);
        $rows = $statement->fetchAll();

        $roles = [];

        foreach ($rows as $row) {
            $roles[] = Role::fromArray($row);
        }

        return $roles;
    }
}
