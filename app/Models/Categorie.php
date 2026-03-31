<?php

declare(strict_types=1);

namespace App\Models;

final class Categorie
{
    private ?int $idCategorie = null;
    private string $categorie = '';
    private string $description = '';

    public function __construct(
        string $categorie = '',
        string $description = '',
    ) {
        $this->setCategorie($categorie);
        $this->setDescription($description);
    }

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdCategorie(isset($data['Id_Categorie']) ? (int) $data['Id_Categorie'] : null);
        $item->setCategorie((string) ($data['categorie'] ?? ''));
        $item->setDescription((string) ($data['description'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_Categorie' => $this->idCategorie,
            'categorie' => $this->categorie,
            'description' => $this->description,
        ];
    }

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function setIdCategorie(?int $idCategorie): void
    {
        $this->idCategorie = $idCategorie;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): void
    {
        $this->categorie = trim($categorie);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }
}