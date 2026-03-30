<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\User;

final class UserController
{

    public function __construct()
    {
    }

    public function checkLogin(string $email, string $password): ?int
    {
        $sql = 'SELECT Id_User FROM User_ WHERE email = :email AND mdp = :password';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':email' => $email,
            ':password' => $password,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_User'])) {
            return null;
        }

        return (int) $row['Id_User'];
    }
    public function getUserById(int $idUser): ?User
    {
        $sql = 'SELECT * FROM User_ WHERE Id_User = :idUser';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idUser' => $idUser,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_User'])) {
            return null;
        }

        $user = new User( 
            idUser: isset($row['Id_User']) ? (int) $row['Id_User'] : null,
            email: (string) ($row['email'] ?? ''),
            nom: (string) ($row['nom'] ?? ''),
            prenom: (string) ($row['prenom'] ?? ''),
            mdp: (string) ($row['mdp'] ?? ''),
            numeroTel: (string) ($row['numero_tel'] ?? ''),
            adresse: (string) ($row['adresse'] ?? ''),
            idRole: isset($row['Id_Role']) ? (int) $row['Id_Role'] : null
        );

        Database::closeConnection();
        return $user;
    }
    public function getAllUsersExept( int $idUser): array
    {
        $sql = 'SELECT * FROM User_ WHERE Id_User != :idUser';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idUser' => $idUser
        ]);

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $Users = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_User'])) {
                $Users[] = User::fromArray($row);
            }
        }

        Database::closeConnection();

        return $Users;
    }
}
?>