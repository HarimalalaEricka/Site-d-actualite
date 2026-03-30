<?php

declare(strict_types=1);

namespace App\Models;

final class ArticleTag
{
    private ?int $idArticle = null;
    private ?int $idTag = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdArticle(isset($data['Id_Article']) ? (int) $data['Id_Article'] : null);
        $item->setIdTag(isset($data['Id_tag']) ? (int) $data['Id_tag'] : null);

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_Article' => $this->idArticle,
            'Id_tag' => $this->idTag,
        ];
    }

    public function getIdArticle(): ?int
    {
        return $this->idArticle;
    }

    public function setIdArticle(?int $idArticle): void
    {
        $this->idArticle = $idArticle;
    }

    public function getIdTag(): ?int
    {
        return $this->idTag;
    }

    public function setIdTag(?int $idTag): void
    {
        $this->idTag = $idTag;
    }
}