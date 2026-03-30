# FO-003: Page Article complete (version simplifiee)

## 1) Objectif du billet

Objectif fonctionnel:
- Afficher une page article complete et lisible.
- Garder une implementation simple, robuste et maintenable.

Ce qui doit etre visible sur la page article:
- Auteur principal
- Co-auteurs (si existants)
- Media principal
- Galerie secondaire (si existante)
- Tags (si existants)
- Articles similaires (si existants)

---

## 2) Fichiers concernes

- app/Controllers/Front/ArticleController.php
- public/Views/Front/article.php
- script/28-03-2026/V6__add_all_indexes.sql

---

## 3) Implementation retenue (simple)

### 3.1 Controleur article

Le controleur charge d'abord l'article publie par:
- id article
- langue
- statut publie

Ensuite, il enrichit avec des requetes simples et separees:
1. Tags de l'article
2. Collaborateurs (hors auteur principal)
3. Medias secondaires (galerie)
4. Articles similaires (meme categorie, meme langue, top 3)

Avantage:
- Code lisible
- Debug facile
- Pas de requete geante difficile a maintenir

### 3.2 Sanitisation du contenu TinyMCE

Le contenu HTML est nettoye de facon pragmatique:
- suppression des balises script
- suppression des balises style
- conservation d'une liste blanche de balises utiles (p, strong, em, h1..h4, ul, li, a, img, etc.)

But:
- Eviter les injections evidentes
- Garder le rendu editorial

### 3.3 Vue article

La vue affiche chaque bloc uniquement s'il existe:
- Co-auteurs: section "Avec:"
- Galerie: section "Galerie"
- Tags: section "Tags"
- Similaires: section "Articles similaires"

Comportement degrade:
- Si un bloc est vide, il n'est pas affiche
- La page reste propre et sans erreur

---

## 4) Requetes cle

### Article detail
- Source: Article + status_article + Categorie + User_ + Media(priorite=1)
- Condition: article publie, langue demandee

### Tags
- Source: article_tag + tag
- Tri alphabetique par nom

### Collaborations
- Source: collaboration + User_
- Exclusion de l'auteur principal

### Galerie secondaire
- Source: Media
- Condition: priorite = 0 ou NULL

### Similaires
- Source: Article + Categorie + status_article
- Condition: meme categorie, meme langue, publie, article different
- Limite: 3

---

## 5) Index utiles (FO-003)

Indexes poses via migration V3:
- Media(Id_Article, priorite)
- article_tag(Id_tag, Id_Article)
- collaboration(Id_Article, Id_User)
- Article(Id_Categorie, lang, Id_status_article, date_publication DESC)

Pourquoi:
- Accelere chargement media principal/galerie
- Accelere recuperation tags
- Accelere recuperation co-auteurs
- Accelere requete similaires

---

## 6) Critere d'acceptation (etat)

- [x] Requete article detail par id + statut publie
- [x] Auteur principal + collaborations
- [x] Media prioritaire + secondaires
- [x] Tags + similaires
- [x] Sanitisation HTML simple
- [x] Affichage degrade propre sans erreur

---

## 7) Comment tester rapidement

### Test 1: article detail
1. Ouvrir une URL article valide
2. Verifier titre, auteur, date, contenu

### Test 2: tags
1. Verifier presence section Tags
2. Verifier coherence avec table article_tag

### Test 3: similaires
1. Verifier section Articles similaires
2. Verifier qu'on ne retrouve pas l'article courant dans la liste

### Test 4: robustesse
1. Tester un article sans galerie
2. Tester un article sans tags
3. Verifier absence d'erreur et rendu propre

---

## 8) Limites volontaires (MVP)

- Pas de scoring complexe des similaires (version simple: meme categorie)
- Pas de pagination dans similaires
- Sanitisation basique (suffisante MVP, pas un WAF complet)

---

## 9) Conclusion

FO-003 est livre en version simple:
- Page article complete
- Donnees essentielles affichees
- Performance correcte avec index adaptes
- Code lisible et facile a maintenir
