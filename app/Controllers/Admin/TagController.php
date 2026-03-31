<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Tag;

final class TagController
{
    public function __construct()
    {
    }

    /**
     * @return array<int, Tag>
     */
    public function getAllTags(): array
    {
        $sql = 'SELECT * FROM tag ORDER BY nom ASC, Id_tag ASC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $tags = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_tag'])) {
                $tags[] = Tag::fromArray($row);
            }
        }

        Database::closeConnection();

        return $tags;
    }

    public function getTagById(int $idTag): ?Tag
    {
        $sql = 'SELECT * FROM tag WHERE Id_tag = :idTag';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idTag' => $idTag,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_tag'])) {
            Database::closeConnection();
            return null;
        }

        $tag = Tag::fromArray($row);

        Database::closeConnection();

        return $tag;
    }

    public function createTag(Tag $tag): ?int
    {
        try {
            $sql = 'INSERT INTO tag (nom) VALUES (:nom)';

            $statement = Database::getConnection()->prepare($sql);
            $result = $statement->execute([
                ':nom' => $tag->getNom(),
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

    public function updateTag(int $idTag, Tag $tag): bool
    {
        try {
            $sql = 'UPDATE tag SET nom = :nom WHERE Id_tag = :idTag';

            $statement = Database::getConnection()->prepare($sql);
            return $statement->execute([
                ':nom' => $tag->getNom(),
                ':idTag' => $idTag,
            ]);
        } finally {
            Database::closeConnection();
        }
    }

    public function deleteTag(int $idTag): bool
    {
        try {
            $sql = 'DELETE FROM tag WHERE Id_tag = :idTag';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idTag' => $idTag,
            ]);

            return $statement->rowCount() > 0;
        } finally {
            Database::closeConnection();
        }
    }

    /**
     * @return array<int, Tag>
     */
    public function getTagsByArticle(int $idArticle): array
    {
        $sql = 'SELECT t.* FROM tag t 
                JOIN article_tag at ON t.Id_tag = at.Id_tag 
                WHERE at.Id_Article = :idArticle 
                ORDER BY t.nom ASC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
        ]);

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $tags = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_tag'])) {
                $tags[] = Tag::fromArray($row);
            }
        }

        Database::closeConnection();

        return $tags;
    }

    public function assignTagToArticle(int $idArticle, int $idTag): bool
    {
        try {
            $sql = 'INSERT IGNORE INTO article_tag (Id_Article, Id_tag) VALUES (:idArticle, :idTag)';

            $statement = Database::getConnection()->prepare($sql);
            return $statement->execute([
                ':idArticle' => $idArticle,
                ':idTag' => $idTag,
            ]);
        } finally {
            Database::closeConnection();
        }
    }

    public function removeTagFromArticle(int $idArticle, int $idTag): bool
    {
        try {
            $sql = 'DELETE FROM article_tag WHERE Id_Article = :idArticle AND Id_tag = :idTag';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idArticle' => $idArticle,
                ':idTag' => $idTag,
            ]);

            return $statement->rowCount() > 0;
        } finally {
            Database::closeConnection();
        }
    }

    public function removeAllTagsFromArticle(int $idArticle): bool
    {
        try {
            $sql = 'DELETE FROM article_tag WHERE Id_Article = :idArticle';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idArticle' => $idArticle,
            ]);

            return true;
        } finally {
            Database::closeConnection();
        }
    }
}
