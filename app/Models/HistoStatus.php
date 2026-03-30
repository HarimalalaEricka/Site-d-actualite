<?php

declare(strict_types=1);

namespace App\Models;

final class HistoStatus
{
    private ?int $idArticle = null;
    private ?int $idStatusArticle = null;
    private string $date = '';

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdArticle(isset($data['Id_Article']) ? (int) $data['Id_Article'] : null);
        $item->setIdStatusArticle(isset($data['Id_status_article']) ? (int) $data['Id_status_article'] : null);
        $item->setDate((string) ($data['date_'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_Article' => $this->idArticle,
            'Id_status_article' => $this->idStatusArticle,
            'date_' => $this->date,
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

    public function getIdStatusArticle(): ?int
    {
        return $this->idStatusArticle;
    }

    public function setIdStatusArticle(?int $idStatusArticle): void
    {
        $this->idStatusArticle = $idStatusArticle;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = trim($date);
    }
}