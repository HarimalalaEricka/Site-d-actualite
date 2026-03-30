<?php

declare(strict_types=1);

namespace App\Models;

final class Article
{
    private ?int $idArticle = null;
    private string $titre = '';
    private string $slug = '';
    private string $datePublication = '';
    private string $contenu = '';
    private int $nbrVues = 0;
    private ?int $idUserPrincipal = null;
    private ?int $idStatusArticle = null;
    private ?int $idCategorie = null;
    private string $lang = '';

    public function __construct(
        ?int $idArticle = null,
        string $titre = '',
        string $slug = '',
        string $datePublication = '',
        string $contenu = '',
        int $nbrVues = 0,
        ?int $idUserPrincipal = null,
        ?int $idStatusArticle = null,
        ?int $idCategorie = null,
        string $lang = ''
    ) {
        $this->setIdArticle($idArticle);
        $this->setTitre($titre);
        $this->setSlug($slug);
        $this->setDatePublication($datePublication);
        $this->setContenu($contenu);
        $this->setNbrVues($nbrVues);
        $this->setIdUserPrincipal($idUserPrincipal);
        $this->setIdStatusArticle($idStatusArticle);
        $this->setIdCategorie($idCategorie);
        $this->setLang($lang);
    }

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->setIdArticle(isset($data['Id_Article']) ? (int) $data['Id_Article'] : null);
        $item->setTitre((string) ($data['titre'] ?? ''));
        $item->setSlug((string) ($data['slug'] ?? ''));
        $item->setDatePublication((string) ($data['date_publication'] ?? ''));
        $item->setContenu((string) ($data['contenu'] ?? ''));
        $item->setNbrVues(isset($data['nbr_vues']) ? (int) $data['nbr_vues'] : 0);
        $item->setIdUserPrincipal(isset($data['Id_User_principal']) ? (int) $data['Id_User_principal'] : null);
        $item->setIdStatusArticle(isset($data['Id_status_article']) ? (int) $data['Id_status_article'] : null);
        $item->setIdCategorie(isset($data['Id_Categorie']) ? (int) $data['Id_Categorie'] : null);
        $item->setLang((string) ($data['lang'] ?? ''));

        return $item;
    }

    public function toArray(): array
    {
        return [
            'Id_Article' => $this->idArticle,
            'titre' => $this->titre,
            'slug' => $this->slug,
            'date_publication' => $this->datePublication,
            'contenu' => $this->contenu,
            'nbr_vues' => $this->nbrVues,
            'Id_User_principal' => $this->idUserPrincipal,
            'Id_status_article' => $this->idStatusArticle,
            'Id_Categorie' => $this->idCategorie,
            'lang' => $this->lang,
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

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = trim($titre);

        if ($this->slug === '') {
            $this->slug = self::slugify($this->titre);
        }
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $cleanSlug = trim($slug);

        if ($cleanSlug === '') {
            $this->slug = self::slugify($this->titre);
            return;
        }

        $this->slug = self::slugify($cleanSlug);
    }

    public function getDatePublication(): string
    {
        return $this->datePublication;
    }

    public function setDatePublication(string $datePublication): void
    {
        $this->datePublication = trim($datePublication);
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): void
    {
        $this->contenu = trim($contenu);
    }

    public function getNbrVues(): int
    {
        return $this->nbrVues;
    }

    public function setNbrVues(int $nbrVues): void
    {
        $this->nbrVues = $nbrVues;
    }

    public function getIdUserPrincipal(): ?int
    {
        return $this->idUserPrincipal;
    }

    public function setIdUserPrincipal(?int $idUserPrincipal): void
    {
        $this->idUserPrincipal = $idUserPrincipal;
    }

    public function getIdStatusArticle(): ?int
    {
        return $this->idStatusArticle;
    }

    public function setIdStatusArticle(?int $idStatusArticle): void
    {
        $this->idStatusArticle = $idStatusArticle;
    }

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function setIdCategorie(?int $idCategorie): void
    {
        $this->idCategorie = $idCategorie;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = trim($lang);
    }

    public static function slugify(string $value): string
    {
        $text = trim($value);

        if ($text == '') {
            return 'article';
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');

        return $text === '' ? 'article' : $text;
    }

    public function getUrl(string $categorySlug): string
    {
        if ($this->idArticle === null) {
            return '/';
        }

        $slug = $this->slug !== '' ? $this->slug : self::slugify($this->titre);
        $timestamp = strtotime($this->datePublication);
        $year = $timestamp !== false ? date('Y', $timestamp) : date('Y');
        $month = $timestamp !== false ? date('m', $timestamp) : date('m');
        $day = $timestamp !== false ? date('d', $timestamp) : date('d');

        return sprintf(
            '/%s/%s/article/%s/%s/%s/%d-%s',
            $this->lang !== '' ? $this->lang : 'fr',
            $categorySlug,
            $year,
            $month,
            $day,
            $this->idArticle,
            $slug
        );
    }
}