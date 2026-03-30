<?php

declare(strict_types=1);

namespace App\Models;

final class Collaboration
{
    private ?int $idUser = null;
    private ?int $idArticle = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdUser(isset($data['Id_User']) ? (int) $data['Id_User'] : null);
        $item->setIdArticle(isset($data['Id_Article']) ? (int) $data['Id_Article'] : null);

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_User' => $this->idUser,
            'Id_Article' => $this->idArticle,
        ];
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(?int $idUser): void
    {
        $this->idUser = $idUser;
    }

    public function getIdArticle(): ?int
    {
        return $this->idArticle;
    }

    public function setIdArticle(?int $idArticle): void
    {
        $this->idArticle = $idArticle;
    }
}