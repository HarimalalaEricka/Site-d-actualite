# Billet 8 : FO-008 SEO technique + performance web

**Date:** 31 mars 2026  
**Statut:** Implémenté (validation Lighthouse à lancer)  
**Semaine:** 2  

---

## 1. Objectifs

Améliorer le SEO technique et les performances perçues des pages Front afin de préparer un score Lighthouse supérieur à 70.

- Meta `title` et `description` contextualisées
- `canonical` sur toutes les pages Front
- Balises `robots` adaptées selon le contexte
- Pagination SEO (`rel=prev/next`) sur les pages listées
- Enrichissement social SEO (`og:*`) et données structurées article (`NewsArticle`)
- Optimisation de chargement image (`loading`, `decoding`, `fetchpriority`)

---

## 2. Fichiers modifiés

### Vues Front

- `public/Views/Front/home.php`
  - Meta description dynamique
  - Canonical home par langue
  - OpenGraph de base (`og:type`, `og:title`, `og:description`)
  - Meta robots

- `public/Views/Front/category.php`
  - Title dynamique (inclut page N)
  - Meta description avec total d'articles
  - Canonical paginé
  - `rel=prev` / `rel=next` quand pagination active

- `public/Views/Front/archives.php`
  - Meta description dynamique (année/mois)
  - Canonical avec filtre catégorie + page
  - `rel=prev` / `rel=next` quand pagination active

- `public/Views/Front/search.php`
  - Title dynamique (requête + page)
  - Canonical basé sur les filtres actifs
  - Robots conditionnel:
    - `index,follow` sur page de recherche vide
    - `noindex,follow` sur résultats filtrés
  - `rel=prev` / `rel=next` pour la pagination

- `public/Views/Front/article.php`
  - Meta description durcie
  - Meta robots + OpenGraph article (`og:type`, `og:title`, `og:description`, `og:url`, `og:image`)
  - JSON-LD `NewsArticle` injecté (`application/ld+json`)
  - Date avec `<time datetime>`
  - Images optimisées (`decoding="async"`, `loading="lazy"`, `fetchpriority="high"`)

### Infra HTTP

- `public/.htaccess` était déjà conforme sur compression et cache assets:
  - gzip/brotli
  - expires headers
  - cache-control pour assets statiques

---

## 3. Couverture FO-008

### Tâches FO-008

- [x] 1 h1 par page
- [x] meta description + title uniques par page
- [x] canonical URL sur article/categorie (+ home, archives, search)
- [x] alt sur images
- [x] cache-control css/js/images

### Critères d'acceptation

- [x] Aucun blocage SEO critique détecté dans le code Front
- [ ] Score Lighthouse > 70 (mobile + desktop) à mesurer sur environnement running

---

## 4. Validation manuelle recommandée

1. Lancer le conteneur: `docker-compose up -d --build --force-recreate`
2. Vérifier les metas dans le HTML source des routes:
   - `/fr`
   - `/fr/search`
   - `/fr/archives`
   - `/fr/politique`
   - `/fr/politique/article/...`
3. Exécuter Lighthouse (mobile + desktop) sur Home, Category, Article
4. Archiver le rapport (JSON/HTML) pour clôturer FO-008
