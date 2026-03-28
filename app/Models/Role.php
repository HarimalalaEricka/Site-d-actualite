<?php

declare(strict_types=1);

namespace App\Models;

final class Role
{
    private ?int $idRole = null;
    private string $role = '';

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdRole(isset($data['Id_Role']) ? (int) $data['Id_Role'] : null);
        $item->setRole((string) ($data['role'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_Role' => $this->idRole,
            'role' => $this->role,
        ];
    }

    public function getIdRole(): ?int
    {
        return $this->idRole;
    }

    public function setIdRole(?int $idRole): void
    {
        $this->idRole = $idRole;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = trim($role);
    }
}
