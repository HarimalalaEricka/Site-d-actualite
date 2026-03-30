<?php

declare(strict_types=1);

namespace App\Models;

final class SessionLogin
{
    private ?int $idUser = null;
    private bool $user_logged_in = false;
    private string $role = '';

    public function __construct(
        ?int $idUser = null,
        bool $user_logged_in = false,
        string $role = ''
    ) {
        $this->setIdUser($idUser);
        $this->setUserLoggedIn($user_logged_in);
        $this->setRole($role);
    }

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdUser(isset($data['Id_User']) ? (int) $data['Id_User'] : null);
        $item->setUserLoggedIn((bool) ($data['user_logged_in'] ?? false));
        $item->setRole((string) ($data['role'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_User' => $this->idUser,
            'user_logged_in' => $this->user_logged_in,
            'role' => $this->role,
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

    public function getUserLoggedIn(): bool
    {
        return $this->user_logged_in;
    }

    public function setUserLoggedIn(bool $user_logged_in): void
    {
        $this->user_logged_in = $user_logged_in;
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