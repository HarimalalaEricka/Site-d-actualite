<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    private ?int $idUser = null;
    private string $email = '';
    private string $nom = '';
    private string $prenom = '';
    private string $mdp = '';
    private string $numeroTel = '';
    private string $adresse = '';
    private ?int $idRole = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdUser(isset($data['Id_User']) ? (int) $data['Id_User'] : null);
        $item->setEmail((string) ($data['email'] ?? ''));
        $item->setNom((string) ($data['nom'] ?? ''));
        $item->setPrenom((string) ($data['prenom'] ?? ''));
        $item->setMdp((string) ($data['mdp'] ?? ''));
        $item->setNumeroTel((string) ($data['numero_tel'] ?? ''));
        $item->setAdresse((string) ($data['adresse'] ?? ''));
        $item->setIdRole(isset($data['Id_Role']) ? (int) $data['Id_Role'] : null);

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_User' => $this->idUser,
            'email' => $this->email,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'mdp' => $this->mdp,
            'numero_tel' => $this->numeroTel,
            'adresse' => $this->adresse,
            'Id_Role' => $this->idRole,
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = trim($email);
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = trim($nom);
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = trim($prenom);
    }

    public function getMdp(): string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): void
    {
        $this->mdp = trim($mdp);
    }

    public function getNumeroTel(): string
    {
        return $this->numeroTel;
    }

    public function setNumeroTel(string $numeroTel): void
    {
        $this->numeroTel = trim($numeroTel);
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): void
    {
        $this->adresse = trim($adresse);
    }

    public function getIdRole(): ?int
    {
        return $this->idRole;
    }

    public function setIdRole(?int $idRole): void
    {
        $this->idRole = $idRole;
    }
}