<?php

declare(strict_types=1);

namespace App\Models;

final class Tag
{
    private ?int $idTag = null;
    private string $nom = '';

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdTag(isset($data['Id_tag']) ? (int) $data['Id_tag'] : null);
        $item->setNom((string) ($data['nom'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_tag' => $this->idTag,
            'nom' => $this->nom,
        ];
    }

    public function getIdTag(): ?int
    {
        return $this->idTag;
    }

    public function setIdTag(?int $idTag): void
    {
        $this->idTag = $idTag;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = trim($nom);
    }
}