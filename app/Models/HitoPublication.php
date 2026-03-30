<?php

declare(strict_types=1);

namespace App\Models;

final class HitoPublication
{
    private ?int $idHitoPublication = null;
    private string $date = '';
    private string $action = '';
    private ?int $idArticle = null;
    private ?int $idUser = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdHitoPublication(isset($data['Id_hito_publication']) ? (int) $data['Id_hito_publication'] : null);
        $item->setDate((string) ($data['date_'] ?? ''));
        $item->setAction((string) ($data['action'] ?? ''));
        $item->setIdArticle(isset($data['Id_Article']) ? (int) $data['Id_Article'] : null);
        $item->setIdUser(isset($data['Id_User']) ? (int) $data['Id_User'] : null);

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_hito_publication' => $this->idHitoPublication,
            'date_' => $this->date,
            'action' => $this->action,
            'Id_Article' => $this->idArticle,
            'Id_User' => $this->idUser,
        ];
    }

    public function getIdHitoPublication(): ?int
    {
        return $this->idHitoPublication;
    }

    public function setIdHitoPublication(?int $idHitoPublication): void
    {
        $this->idHitoPublication = $idHitoPublication;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = trim($date);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = trim($action);
    }

    public function getIdArticle(): ?int
    {
        return $this->idArticle;
    }

    public function setIdArticle(?int $idArticle): void
    {
        $this->idArticle = $idArticle;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): void
    {
        $this->idUser = $idUser;
    }
}