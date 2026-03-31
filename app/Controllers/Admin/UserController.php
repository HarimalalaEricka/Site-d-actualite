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

    /**
     * @return array<int, User>
     */
    public function getAllUsers(): array
    {
        $sql = 'SELECT * FROM User_ ORDER BY nom ASC, prenom ASC, Id_User ASC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $users = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_User'])) {
                $users[] = User::fromArray($row);
            }
        }

        Database::closeConnection();

        return $users;
    }

    public function createUser(User $user): ?int
    {
        try {
            $sql = 'INSERT INTO User_ (email, nom, prenom, mdp, numero_tel, adresse, Id_Role)
                    VALUES (:email, :nom, :prenom, :mdp, :numeroTel, :adresse, :idRole)';

            $statement = Database::getConnection()->prepare($sql);
            $result = $statement->execute([
                ':email' => $user->getEmail(),
                ':nom' => $user->getNom(),
                ':prenom' => $user->getPrenom(),
                ':mdp' => $user->getMdp(),
                ':numeroTel' => $user->getNumeroTel(),
                ':adresse' => $user->getAdresse(),
                ':idRole' => $user->getIdRole(),
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

    public function updateUser(int $idUser, User $user): bool
    {
        try {
            $sql = 'UPDATE User_ 
                    SET email = :email, nom = :nom, prenom = :prenom, mdp = :mdp, 
                        numero_tel = :numeroTel, adresse = :adresse, Id_Role = :idRole
                    WHERE Id_User = :idUser';

            $statement = Database::getConnection()->prepare($sql);
            return $statement->execute([
                ':email' => $user->getEmail(),
                ':nom' => $user->getNom(),
                ':prenom' => $user->getPrenom(),
                ':mdp' => $user->getMdp(),
                ':numeroTel' => $user->getNumeroTel(),
                ':adresse' => $user->getAdresse(),
                ':idRole' => $user->getIdRole(),
                ':idUser' => $idUser,
            ]);
        } finally {
            Database::closeConnection();
        }
    }

    public function deleteUser(int $idUser): bool
    {
        try {
            $sql = 'DELETE FROM User_ WHERE Id_User = :idUser';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idUser' => $idUser,
            ]);

            return $statement->rowCount() > 0;
        } finally {
            Database::closeConnection();
        }
    }
}
?>