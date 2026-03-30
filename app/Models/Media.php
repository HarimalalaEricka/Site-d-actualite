<?php

declare(strict_types=1);

namespace App\Models;

final class Media
{
    private ?int $idMedia = null;
    private string $url = '';
    private string $description = '';
    private bool $priorite = false;
    private ?int $idTypeMedia = null;
    private ?int $idArticle = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdMedia(isset($data['Id_Media']) ? (int) $data['Id_Media'] : null);
        $item->setUrl((string) ($data['url'] ?? ''));
        $item->setDescription((string) ($data['description'] ?? ''));
        $item->setPriorite(isset($data['priorite']) ? (bool) $data['priorite'] : false);
        $item->setIdTypeMedia(isset($data['Id_type_media']) ? (int) $data['Id_type_media'] : null);
        $item->setIdArticle(isset($data['Id_Article']) ? (int) $data['Id_Article'] : null);

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_Media' => $this->idMedia,
            'url' => $this->url,
            'description' => $this->description,
            'priorite' => $this->priorite,
            'Id_type_media' => $this->idTypeMedia,
            'Id_Article' => $this->idArticle,
        ];
    }

    public function getIdMedia(): ?int
    {
        return $this->idMedia;
    }

    public function setIdMedia(?int $idMedia): void
    {
        $this->idMedia = $idMedia;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = trim($url);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    public function isPriorite(): bool
    {
        return $this->priorite;
    }

    public function setPriorite(bool $priorite): void
    {
        $this->priorite = $priorite;
    }

    public function getIdTypeMedia(): ?int
    {
        return $this->idTypeMedia;
    }

    public function setIdTypeMedia(?int $idTypeMedia): void
    {
        $this->idTypeMedia = $idTypeMedia;
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