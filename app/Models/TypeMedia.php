<?php

declare(strict_types=1);

namespace App\Models;

final class TypeMedia
{
    private ?int $idTypeMedia = null;
    private string $type = '';

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdTypeMedia(isset($data['Id_type_media']) ? (int) $data['Id_type_media'] : null);
        $item->setType((string) ($data['type'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_type_media' => $this->idTypeMedia,
            'type' => $this->type,
        ];
    }

    public function getIdTypeMedia(): ?int
    {
        return $this->idTypeMedia;
    }

    public function setIdTypeMedia(?int $idTypeMedia): void
    {
        $this->idTypeMedia = $idTypeMedia;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = trim($type);
    }
}