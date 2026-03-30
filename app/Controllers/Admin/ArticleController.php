<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Models\Article;

final class ArticleController
{
    public function __construct()
    {

    }

    /**
     * @return array<int, Article>
     */
    public function getArticleByUser(int $idUser, string $status_article): array
    {
        $sql = 'SELECT a.*
                FROM Article a
                JOIN collaboration c ON a.Id_Article = c.Id_Article
                JOIN status_article s ON s.Id_status_article = a.Id_status_article
                WHERE c.Id_User = :idUser
                AND s.status = :status_article
                ORDER BY a.date_publication DESC, a.Id_Article DESC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idUser' => $idUser,
            ':status_article' => $status_article,
        ]);

        $rows = $statement->fetchAll();

        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $articles = [];

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['Id_Article'])) {
                $articles[] = Article::fromArray($row);
            }
        }

        Database::closeConnection();

        return $articles;
    }
    public function createArticle(Article $article): ?int
    {
        $connection = Database::getConnection();

        $sql = 'INSERT INTO Article (titre, date_publication, contenu, nbr_vues, Id_User_principal, Id_status_article, Id_Categorie, lang) 
                VALUES (:titre, :date_publication, :contenu, :nbr_vues, :Id_User_principal, :Id_status_article, :Id_Categorie, :lang)';

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':titre' => $article->getTitre(),
            ':date_publication' => $article->getDatePublication(),
            ':contenu' => $article->getContenu(),
            ':nbr_vues' => $article->getNbrVues(),
            ':Id_User_principal' => $article->getIdUserPrincipal(),
            ':Id_status_article' => $article->getIdStatusArticle(),
            ':Id_Categorie' => $article->getIdCategorie(),
            ':lang' => $article->getLang(),
        ]);

        $insertedId = (int) $connection->lastInsertId();

        Database::closeConnection();

        return $insertedId > 0 ? $insertedId : null;
    }
    public function getArticleById(int $idArticle): ?Article
    {
        $sql = 'SELECT * FROM Article WHERE Id_Article = :idArticle';
        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([
            ':idArticle' => $idArticle,
        ]);

        $row = $statement->fetch();

        if (!is_array($row) || !isset($row['Id_Article'])) {
            Database::closeConnection();
            return null;
        }

        $article = new Article(
            idArticle: isset($row['Id_Article']) ? (int) $row['Id_Article'] : null,
            titre: (string) ($row['titre'] ?? ''),
            datePublication: (string) ($row['date_publication'] ?? ''),
            contenu: (string) ($row['contenu'] ?? ''),
            nbrVues: isset($row['nbr_vues']) ? (int) $row['nbr_vues'] : null,
            idUserPrincipal: isset($row['Id_User_principal']) ? (int) $row['Id_User_principal'] : null,
            idStatusArticle: isset($row['Id_status_article']) ? (int) $row['Id_status_article'] : null,
            idCategorie: isset($row['Id_Categorie']) ? (int) $row['Id_Categorie'] : null,
            lang: (string) ($row['lang'] ?? '')
        );

        Database::closeConnection();
        return $article;
    }
    public function deleteArticle(int $idArticle, int $idUserAction): bool
    {
        $connection = Database::getConnection();

        try {
            $connection->beginTransaction();

            $findArticle = $connection->prepare('SELECT Id_Article FROM Article WHERE Id_Article = :idArticle FOR UPDATE');
            $findArticle->execute([':idArticle' => $idArticle]);
            $articleExists = $findArticle->fetch();

            if (!is_array($articleExists) || !isset($articleExists['Id_Article'])) {
                $connection->rollBack();
                return false;
            }

            $findRetraitStatus = $connection->prepare(
                "SELECT Id_status_article FROM status_article WHERE status IN ('rejete', 'retire', 'retrait') ORDER BY Id_status_article LIMIT 1"
            );
            $findRetraitStatus->execute();
            $statusRow = $findRetraitStatus->fetch();

            $retraitStatusId = 3;
            if (is_array($statusRow) && isset($statusRow['Id_status_article'])) {
                $retraitStatusId = (int) $statusRow['Id_status_article'];
            }

            $updateStatus = $connection->prepare('UPDATE Article SET Id_status_article = :statusId WHERE Id_Article = :idArticle');
            $updateStatus->execute([
                ':statusId' => $retraitStatusId,
                ':idArticle' => $idArticle,
            ]);

            $insertHitoPublication = $connection->prepare(
                "INSERT INTO hito_publication (date_, action, Id_Article, Id_User) VALUES (NOW(), 'retrait', :idArticle, :idUser)"
            );
            $insertHitoPublication->execute([
                ':idArticle' => $idArticle,
                ':idUser' => $idUserAction,
            ]);

            $connection->commit();

            return true;
        } catch (\Throwable $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $e;
        } finally {
            Database::closeConnection();
        }
    }

    public function publishArticle(int $idArticle, int $idUserAction): bool
    {
        $connection = Database::getConnection();

        try {
            $connection->beginTransaction();

            $findStatus = $connection->prepare(
                "SELECT Id_status_article, status FROM status_article WHERE status IN ('brouillon', 'publie')"
            );
            $findStatus->execute();
            $statusRows = $findStatus->fetchAll();

            $draftStatusId = 1;
            $publishedStatusId = 2;

            if (is_array($statusRows)) {
                foreach ($statusRows as $statusRow) {
                    if (!is_array($statusRow) || !isset($statusRow['Id_status_article'], $statusRow['status'])) {
                        continue;
                    }

                    if ($statusRow['status'] === 'brouillon') {
                        $draftStatusId = (int) $statusRow['Id_status_article'];
                    }

                    if ($statusRow['status'] === 'publie') {
                        $publishedStatusId = (int) $statusRow['Id_status_article'];
                    }
                }
            }

            $findArticle = $connection->prepare(
                'SELECT Id_Article, Id_status_article FROM Article WHERE Id_Article = :idArticle FOR UPDATE'
            );
            $findArticle->execute([':idArticle' => $idArticle]);
            $articleRow = $findArticle->fetch();

            if (!is_array($articleRow) || !isset($articleRow['Id_Article'], $articleRow['Id_status_article'])) {
                $connection->rollBack();
                return false;
            }

            if ((int) $articleRow['Id_status_article'] !== $draftStatusId) {
                $connection->rollBack();
                return false;
            }

            $updateStatus = $connection->prepare(
                'UPDATE Article SET Id_status_article = :publishedStatusId, date_publication = NOW() WHERE Id_Article = :idArticle'
            );
            $updateStatus->execute([
                ':publishedStatusId' => $publishedStatusId,
                ':idArticle' => $idArticle,
            ]);

            $insertHitoPublication = $connection->prepare(
                "INSERT INTO hito_publication (date_, action, Id_Article, Id_User) VALUES (NOW(), 'publication', :idArticle, :idUser)"
            );
            $insertHitoPublication->execute([
                ':idArticle' => $idArticle,
                ':idUser' => $idUserAction,
            ]);

            $connection->commit();

            return true;
        } catch (\Throwable $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $e;
        } finally {
            Database::closeConnection();
        }
    }
}