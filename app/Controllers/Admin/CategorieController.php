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
}