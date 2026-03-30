# Billet 7 : FO-007 Internationalisation FR/EN + Pagination unifiee

**Date:** 31 mars 2026  
**Statut:** Complete  
**Semaine:** 2  

---

## 1. Objectifs

Finaliser la separation FR/EN sur tout le Front et unifier la pagination pour les pages de listing.

- **Internationalisation:** contenu strictement filtre par langue (`fr` / `en`)
- **Navigation langue:** switch FR/EN present sur Home, Category, Article, Archives, Search
- **Pagination centralisee:** un service unique pour calculer `page`, `offset`, `totalPages`
- **Cohesion:** meme comportement de pagination sur Search, Archives et Category

**Definition of Done:**
- [x] URLs Front contraintes a `/(fr|en)`
- [x] Changement de langue stable en conservant le chemin et les query params
- [x] Aucun melange de contenu FR/EN
- [x] Pagination centralisee dans un service reutilisable
- [x] Pagination active sur toutes les pages de listing Front

---

## 2. Fichiers impliques

### Services
- **app/Services/PaginationService.php** (NOUVEAU)
  - `calculate()` normalise la pagination
  - `buildQueryString()` preserve les filtres
  - `buildPageUrl()` construit les liens de page
  - `renderNav()` helper HTML reutilisable

### Controllers Front
- **app/Controllers/Front/SearchController.php**
  - Remplacement du calcul manuel page/offset par `PaginationService::calculate()`

- **app/Controllers/Front/ArchiveController.php**
  - Remplacement du calcul manuel `totalPages/safePage` par `PaginationService::calculate()`

- **app/Controllers/Front/CategoryController.php**
  - Pagination ajoutee au listing categorie (`page`, `perPage`, `LIMIT/OFFSET`)
  - Resultat renvoye sous forme structuree (`articles`, `total`, `page`, `totalPages`)

### Routes / Bootstrap
- **public/index.php**
  - `require_once` du nouveau `PaginationService.php`
  - Route categorie: prise en charge de `?page=`

### Vue
- **public/Views/Front/category.php**
  - Affichage du total d'articles
  - Controles precedent/suivant + indicateur de page

---

## 3. Details implementation

### 3.1 Internationalisation

- Routes principales en `/(fr|en)` uniquement:
  - `/{lang}`
  - `/{lang}/search`
  - `/{lang}/archives`
  - `/{lang}/{category}`
  - `/{lang}/{category}/article/...`

- Le switch de langue conserve le contexte courant (path + query string), ex:
  - `/fr/search?q=Dupont` -> `/en/search?q=Dupont`
  - `/fr/archives/2026/03` -> `/en/archives/2026/03`

### 3.2 Pagination centralisee

Le service impose une seule logique de calcul:

```php
$pagination = PaginationService::calculate($total, $page, $perPage);
$offset = $pagination['offset'];
```

Puis chaque controller renvoie:

```php
[
  'articles' => $rows,
  'total' => $pagination['total'],
  'page' => $pagination['page'],
  'perPage' => $pagination['perPage'],
  'totalPages' => $pagination['totalPages'],
]
```

### 3.3 Pages couvertes par la pagination

- **Search:** deja paginee, migree sur `PaginationService`
- **Archives:** deja paginee, migree sur `PaginationService`
- **Category:** pagination ajoutee et branchee

Remarque: Home et Article detail ne sont pas des listings a pagination continue.

---

## 4. Validation

Checks effectues:
- `php -l` sur services/controllers/routes/vues modifies: OK
- Test route categorie: affichage du total + structure pagination: OK
- Test pages Search/Archives/Category apres centralisation: chargement OK

---

## 5. Impact

- Logique de pagination homogene dans toute la couche Front listing
- Maintenance plus simple (une seule source de verite)
- Reduction du risque d'incoherence entre pages
- Base prete pour FO-008 (SEO/performance) sans refonte pagination
