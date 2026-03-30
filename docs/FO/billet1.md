# Billet 1 - Routing FrontOffice + Slug automatique + URL canonique

## 1. Objectif du billet
Mettre en place une base FrontOffice propre et SEO:
- URL lisibles
- routing centralise
- slug automatique (non saisi par l'utilisateur)
- canonical redirect (301)
- compatibilite avec l'existant Admin

## 2. Fichiers modifies / ajoutes

### Entree et routing
- public/index.php
- app/Core/Router.php
- public/.htaccess
- public/router.php
- docker-compose.yml

### Front controllers
- app/Controllers/Front/HomeController.php
- app/Controllers/Front/CategoryController.php
- app/Controllers/Front/ArticleController.php

### Views Front (dans public, selon votre demande)
- public/Views/Front/home.php
- public/Views/Front/category.php
- public/Views/Front/article.php

### Modele / Admin
- app/Models/Article.php
- app/Controllers/Admin/ArticleController.php
- public/Views/Admin/Article/nouvelle.php

### Base de donnees
- script/28-03-2026/script_v1.sql
- script/28-03-2026/test_data_1.sql
- script/28-03-2026/V2__add_slug_article.sql

## 3. Ce qui a ete implemente

### 3.1 Routing FrontOffice
Routes prises en charge:
- /fr
- /fr/{categorie}
- /fr/{categorie}/article/{yyyy}/{mm}/{dd}/{id}-{slug}
- /fr/archives (placeholder vers home temporairement)

Pourquoi ce choix:
- Un front controller unique simplifie la maintenance
- Les regex permettent des routes SEO strictes
- La logique de routage est centralisee dans un seul point d'entree

Impact performance:
- Moins de fichiers scripts disperses
- Moins de duplication de logique
- Debug plus rapide donc moins de risque de regressions de perf

### 3.2 Slug automatique
Le slug est genere automatiquement depuis le titre, en minuscule et avec tirets.
L'utilisateur ne saisit jamais le slug.

Implementation:
- app/Models/Article.php
  - slugify(string): normalise texte -> slug
  - setTitre(): si slug vide, genere automatiquement
  - setSlug(): nettoie/regenerer slug
- app/Controllers/Admin/ArticleController.php
  - createArticle(): genere un slug et le rend unique pour (lang, slug)

Pourquoi ce choix:
- Evite les erreurs de saisie manuelle
- Garantit coherence URL entre tous les articles
- Respecte votre exigence metier "slug = titre normalise"

Impact performance:
- Le calcul slug est O(n) sur une chaine courte: cout negligeable
- Fait a l'ecriture (insert), pas a chaque lecture

### 3.3 URL canonique + redirection 301
Sur la route article, l'article est resolu par ID (source de verite), puis:
- si URL demandee != URL canonique, redirection 301

Pourquoi ce choix:
- Evite contenu duplique en SEO
- Conserve liens anciens meme si slug change
- L'ID dans l'URL assure une resolution fiable

Impact performance:
- 1 requete article par page detail
- Eventuelle redirection unique (cas non canonique)
- Cout faible compare au gain SEO et robustesse

### 3.4 Compatibilite PHP serveur dev + Docker
- Ajout public/router.php pour php -S
- docker-compose passe en: php -S 0.0.0.0:8000 -t public public/router.php

Pourquoi ce choix:
- php -S ne lit pas .htaccess
- router.php reproduit le comportement "front controller"
- Meme routes en local et en production Apache

## 4. Choix techniques des fonctions cle

### app/Core/Router.php
- get(pattern, handler): enregistrement route GET
- dispatch(method, uri): parcours des routes et resolution via regex

Pourquoi:
- minimal, lisible, sans dependance externe
- adapte a votre architecture PHP orientee fichiers

### app/Models/Article.php
- slugify(): transformation standardisee
- getUrl(categorySlug): construction URL SEO

Pourquoi:
- la logique URL et slug est dans le domaine Article
- moins de duplication dans les controllers/views

### app/Controllers/Admin/ArticleController.php
- buildUniqueSlug(connection, baseSlug, lang)
- slugExists(connection, slug, lang)

Pourquoi:
- evite collisions de slug dans une meme langue
- preserve la contrainte unique SQL

### app/Controllers/Front/ArticleController.php
- getPublishedArticleById(id, lang): filtre status=publie + langue
- buildCanonicalPath(article): construit URL reference

Pourquoi:
- separation claire entre lecture DB et logique canonique
- controle du statut publie cote backend

## 5. Indexage et schema utilises

### Ajout schema
Dans Article:
- slug VARCHAR(300) NOT NULL
- UNIQUE KEY uq_article_lang_slug (lang, slug)

Pourquoi cet index unique:
- acceleration des verifications d'unicite
- acceleration recherche par couple (lang, slug)
- garantie d'integrite pour le routing SEO multilingue

Impact performance:
- Lecture: tres bon pour lookup slug/lang
- Ecriture: cout tres leger a l'insert/update (maintenance d'index)
- Compromis favorable pour un site de contenu

## 6. Requetes et optimisation

### Home/category/article
Les requetes front filtrent:
- status = publie
- lang = :lang
- tri date_publication DESC

Pourquoi:
- limite les donnees retournees
- garantit logique metier front

Amelioration recommande (billet suivant):
- INDEX(Id_status_article, lang, date_publication)
- INDEX(Id_Categorie, Id_status_article, lang, date_publication)

## 7. Robustesse

Points robustes deja integres:
- 404 centralisee si route inconnue
- 404 si article absent/non publie
- 301 sur URL non canonique
- slug non saisissable par utilisateur
- controle unicite slug au niveau applicatif + SQL

## 8. Validation effectuee
- Lint PHP sur tous les fichiers modifies: OK
- Validation de la presence des vues Front
- Migration SQL preparee pour base deja existante

## 9. Comment deployer ce billet

### Cas A - nouvelle base
- script_v1.sql contient deja la colonne slug
- test_data_1.sql contient deja les slugs

### Cas B - base existante
- executer script/28-03-2026/V2__add_slug_article.sql

## 10. Limites connues (volontaires)
- Archives routees mais pas encore implementees completement (placeholder)
- Views minimales (objectif billet 1: routing + SEO + structure)
- Pas encore de cache applicatif (sera traite billet 2)
