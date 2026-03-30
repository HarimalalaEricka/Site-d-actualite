<?php

declare(strict_types=1);

namespace App\Models;

final class StatusArticle
{
    private ?int $idStatusArticle = null;
    private string $status = '';

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdStatusArticle(isset($data['Id_status_article']) ? (int) $data['Id_status_article'] : null);
        $item->setStatus((string) ($data['status'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_status_article' => $this->idStatusArticle,
            'status' => $this->status,
        ];
    }

    public function getIdStatusArticle(): ?int
    {
        return $this->idStatusArticle;
    }

    public function setIdStatusArticle(?int $idStatusArticle): void
    {
        $this->idStatusArticle = $idStatusArticle;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = trim($status);
    }
}