<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Categorie;

final class CategorieController
{
    public function __construct()
    {

    }

    /**
     * @return array<int, Categorie>
     */
    public function getAllCategories(): array
    {
        $sql = 'SELECT * FROM Categorie';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $Categories = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_Categorie'])) {
                $Categories[] = Categorie::fromArray($row);
            }
        }

        Database::closeConnection();

        return $Categories;
    }
    public function createCategorie(Categorie $categorie): ?int
    {
        try {
            $sql = 'INSERT INTO Categorie (categorie, description) 
                    VALUES (:nom, :description)';

            $statement = Database::getConnection()->prepare($sql);
            $result = $statement->execute([
                ':nom' => $categorie->getCategorie(),
                ':description' => $categorie->getDescription(),
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

    public function getCategorieById(int $idCategorie): ?Categorie
    {
        $sql = 'SELECT * FROM Categorie WHERE Id_Categorie = :idCategorie';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idCategorie' => $idCategorie,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_Categorie'])) {
            Database::closeConnection();
            return null;
        }

        $categorie = Categorie::fromArray($row);

        Database::closeConnection();

        return $categorie;
    }

    public function updateCategorie(int $idCategorie, Categorie $categorie): bool
    {
        try {
            $sql = 'UPDATE Categorie
                    SET categorie = :nom, description = :description
                    WHERE Id_Categorie = :idCategorie';

            $statement = Database::getConnection()->prepare($sql);
            return $statement->execute([
                ':nom' => $categorie->getCategorie(),
                ':description' => $categorie->getDescription(),
                ':idCategorie' => $idCategorie,
            ]);
        } finally {
            Database::closeConnection();
        }
    }

    public function deleteCategorie(int $idCategorie): bool
    {
        try {
            $sql = 'DELETE FROM Categorie WHERE Id_Categorie = :idCategorie';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idCategorie' => $idCategorie,
            ]);

            return $statement->rowCount() > 0;
        } finally {
            Database::closeConnection();
        }
    }
}