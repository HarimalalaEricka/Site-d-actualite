<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Media;
use App\Models\TypeMedia;

final class MediaController
{
    /**
     * @return array<int, string>
     */
    private function extractMediaUrlsFromHtml(string $html): array
    {
        $result = [];

        if (!class_exists('DOMDocument')) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $imageMatches);
            foreach (($imageMatches[1] ?? []) as $url) {
                $cleanUrl = trim((string) $url);
                if ($cleanUrl !== '') {
                    $result[] = $cleanUrl;
                }
            }

            return array_values(array_unique($result));
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('img') as $imgElement) {
            $src = trim((string) $imgElement->getAttribute('src'));
            if ($src !== '') {
                $result[] = $src;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @return array<int, Media>
     */
    public function getAllMedia(): array
    {
        $sql = 'SELECT * FROM Media ORDER BY Id_Media DESC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $medias = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_Media'])) {
                $medias[] = Media::fromArray($row);
            }
        }

        Database::closeConnection();

        return $medias;
    }

    public function getMediaById(int $idMedia): ?Media
    {
        $sql = 'SELECT * FROM Media WHERE Id_Media = :idMedia';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idMedia' => $idMedia,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_Media'])) {
            Database::closeConnection();
            return null;
        }

        $media = Media::fromArray($row);

        Database::closeConnection();

        return $media;
    }

    public function createMedia(Media $media): ?int
    {
        try {
            $sql = 'INSERT INTO Media (url, description, priorite, Id_type_media, Id_Article)
                    VALUES (:url, :description, :priorite, :idTypeMedia, :idArticle)';

            $statement = Database::getConnection()->prepare($sql);
            $result = $statement->execute([
                ':url' => $media->getUrl(),
                ':description' => $media->getDescription(),
                ':priorite' => $media->isPriorite() ? 1 : 0,
                ':idTypeMedia' => $media->getIdTypeMedia(),
                ':idArticle' => $media->getIdArticle(),
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

    public function updateMedia(int $idMedia, Media $media): bool
    {
        try {
            $sql = 'UPDATE Media
                    SET url = :url,
                        description = :description,
                        priorite = :priorite,
                        Id_type_media = :idTypeMedia,
                        Id_Article = :idArticle
                    WHERE Id_Media = :idMedia';

            $statement = Database::getConnection()->prepare($sql);
            return $statement->execute([
                ':url' => $media->getUrl(),
                ':description' => $media->getDescription(),
                ':priorite' => $media->isPriorite() ? 1 : 0,
                ':idTypeMedia' => $media->getIdTypeMedia(),
                ':idArticle' => $media->getIdArticle(),
                ':idMedia' => $idMedia,
            ]);
        } finally {
            Database::closeConnection();
        }
    }

    public function deleteMedia(int $idMedia): bool
    {
        try {
            $sql = 'DELETE FROM Media WHERE Id_Media = :idMedia';

            $statement = Database::getConnection()->prepare($sql);
            $statement->execute([
                ':idMedia' => $idMedia,
            ]);

            return $statement->rowCount() > 0;
        } finally {
            Database::closeConnection();
        }
    }

    /**
     * @return array<int, TypeMedia>
     */
    public function getAllTypeMedia(): array
    {
        $sql = 'SELECT * FROM type_media ORDER BY type ASC, Id_type_media ASC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $types = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_type_media'])) {
                $types[] = TypeMedia::fromArray($row);
            }
        }

        Database::closeConnection();

        return $types;
    }

    /**
     * @return array<int, array{id:int, titre:string}>
     */
    public function getArticleChoices(): array
    {
        $sql = 'SELECT Id_Article, titre FROM Article ORDER BY date_publication DESC, Id_Article DESC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $choices = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_Article'])) {
                $choices[] = [
                    'id' => (int) $row['Id_Article'],
                    'titre' => (string) ($row['titre'] ?? ''),
                ];
            }
        }

        Database::closeConnection();

        return $choices;
    }

    /**
     * @return array<int, array{id:int, url:string, description:string, priorite:bool, idTypeMedia:int, typeMedia:string, idArticle:int, articleTitre:string}>
     */
    public function getAllMediaWithDetails(): array
    {
        $sql = 'SELECT m.Id_Media,
                       m.url,
                       m.description,
                       m.priorite,
                       m.Id_type_media,
                       tm.type AS type_media,
                       m.Id_Article,
                       a.titre AS article_titre
                FROM Media m
                JOIN type_media tm ON tm.Id_type_media = m.Id_type_media
                JOIN Article a ON a.Id_Article = m.Id_Article
                ORDER BY m.Id_Media DESC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['Id_Media'])) {
                continue;
            }

            $result[] = [
                'id' => (int) $row['Id_Media'],
                'url' => (string) ($row['url'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'priorite' => (bool) ($row['priorite'] ?? false),
                'idTypeMedia' => (int) ($row['Id_type_media'] ?? 0),
                'typeMedia' => (string) ($row['type_media'] ?? ''),
                'idArticle' => (int) ($row['Id_Article'] ?? 0),
                'articleTitre' => (string) ($row['article_titre'] ?? ''),
            ];
        }

        Database::closeConnection();

        return $result;
    }

    /**
     * @return array<int, array{id:int, url:string, description:string, priorite:bool, idTypeMedia:int, typeMedia:string}>
     */
    public function getMediaByArticle(int $idArticle): array
    {
        $sql = 'SELECT m.Id_Media,
                       m.url,
                       m.description,
                       m.priorite,
                       m.Id_type_media,
                       tm.type AS type_media
                FROM Media m
                JOIN type_media tm ON tm.Id_type_media = m.Id_type_media
                WHERE m.Id_Article = :idArticle
                ORDER BY m.priorite DESC, m.Id_Media ASC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
        ]);

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            Database::closeConnection();
            return [];
        }

        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row['Id_Media'])) {
                continue;
            }

            $result[] = [
                'id' => (int) $row['Id_Media'],
                'url' => (string) ($row['url'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'priorite' => (bool) ($row['priorite'] ?? false),
                'idTypeMedia' => (int) ($row['Id_type_media'] ?? 0),
                'typeMedia' => (string) ($row['type_media'] ?? ''),
            ];
        }

        Database::closeConnection();

        return $result;
    }

    public function syncArticleMediaFromContent(int $idArticle, string $content): void
    {
        $connection = Database::getConnection();

        try {
            $findTypeSql = 'SELECT Id_type_media FROM type_media WHERE LOWER(type) = :type LIMIT 1';
            $findTypeStmt = $connection->prepare($findTypeSql);
            $findTypeStmt->execute([
                ':type' => 'image',
            ]);

            $typeRow = $findTypeStmt->fetch();
            if (!is_array($typeRow) || !isset($typeRow['Id_type_media'])) {
                return;
            }

            $imageTypeId = (int) $typeRow['Id_type_media'];

            $mediaUrls = $this->extractMediaUrlsFromHtml($content);

            $connection->beginTransaction();

            $deleteSql = 'DELETE FROM Media WHERE Id_Article = :idArticle AND Id_type_media = :idTypeMedia';
            $deleteStmt = $connection->prepare($deleteSql);

            $insertSql = 'INSERT INTO Media (url, description, priorite, Id_type_media, Id_Article)
                          VALUES (:url, :description, :priorite, :idTypeMedia, :idArticle)';
            $insertStmt = $connection->prepare($insertSql);

            $deleteStmt->execute([
                ':idArticle' => $idArticle,
                ':idTypeMedia' => $imageTypeId,
            ]);

            foreach ($mediaUrls as $index => $url) {
                $insertStmt->execute([
                    ':url' => $url,
                    ':description' => '',
                    ':priorite' => $index === 0 ? 1 : 0,
                    ':idTypeMedia' => $imageTypeId,
                    ':idArticle' => $idArticle,
                ]);
            }

            $connection->commit();
        } catch (\Throwable $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $e;
        } finally {
            Database::closeConnection();
        }
    }

    public function syncArticleImagesFromContent(int $idArticle, string $content): void
    {
        $this->syncArticleMediaFromContent($idArticle, $content);
    }
}
