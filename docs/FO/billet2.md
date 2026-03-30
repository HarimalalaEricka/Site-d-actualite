# FO-002: Home FrontOffice performante (version simplifiee)

## 1) Objectif du billet

Objectif fonctionnel:
- Afficher une Home claire avec:
  - A la une
  - Dernieres actualites
  - Plus lus
  - Categories

Objectif technique:
- Garder une implementation simple et robuste.
- Eviter les doublons de vues sans mecanisme trop complexe.

Dans la version retenue:
- Le compteur principal est Article.nbr_vues.
- A chaque ouverture valide d'un article, Article.nbr_vues augmente.
- L'anti-doublon est gere par session sur une fenetre de 30 minutes.

---

## 2) Composants utilises

### 2.1 HomeController
Fichier: app/Controllers/Front/HomeController.php

Responsabilites:
- Charger les donnees Home en 3 requetes SQL fixes (pas de N+1):
  - Requete recents (A la une + dernieres)
  - Requete plus lus (tri sur Article.nbr_vues)
  - Requete categories avec compteur
- Mettre en cache les donnees Home pendant 90 secondes.

Points importants:
- Le bloc Plus lus s'appuie sur Article.nbr_vues.
- Le tri plus lus est: nbr_vues DESC puis date_publication DESC.

### 2.2 ViewCounterService
Fichier: app/Services/ViewCounterService.php

Responsabilites:
- Compter une vue article de facon simple.
- Eviter le double comptage trop rapide.

Mecanisme:
1. Generer une empreinte visiteur (IP + User-Agent) hash SHA-256.
2. Construire une cle de garde:
   - article_id + visitor_hash + bucket_30m
3. Stocker cette garde en session PHP.
4. Si la garde existe deja, ne pas incrémenter.
5. Sinon, incrementer Article.nbr_vues de +1.

Resultat:
- Meme session + meme article + meme fenetre 30 min => 1 vue max.
- Au bucket suivant (ou autre client), une nouvelle vue peut etre comptee.

### 2.3 Entree Front
Fichier: public/index.php

Responsabilites:
- Demarrer la session PHP.
- Appeler ViewCounterService::recordView() sur la route article, apres validation canonique.

### 2.4 Vue Home
Fichier: public/Views/Front/home.php

Responsabilites:
- Afficher les 4 blocs Home.
- Afficher le compteur de plus lus depuis item[nbr_vues].

---

## 3) Requetes SQL utilisees (Home)

### 3.1 Recents (A la une + dernieres)
- Source: table Article + status_article + Categorie + Media.
- Filtre: status publie + langue.
- Tri: date_publication DESC.
- Limite: 1 + 6.

### 3.2 Plus lus
- Source: table Article + joins metadata.
- Filtre: status publie + langue.
- Tri: COALESCE(a.nbr_vues, 0) DESC, a.date_publication DESC.
- Limite: 5.

### 3.3 Categories
- Source: table Categorie + LEFT JOIN Article.
- Resultat: categories avec article_count.

---

## 4) Strategie anti-doublon (version simple)

Fenetre anti-doublon:
- 30 minutes par article et par empreinte visiteur dans la session.

Consequence pratique:
- Deux refresh immediats dans la meme session: une seule vue.
- Deux clients differents (navigateur vs curl) peuvent produire des empreintes differentes.
- Une meme personne peut donc creer plusieurs vues si le contexte client change.

Ceci est un compromis volontaire MVP:
- Simple a maintenir.
- Suffisant pour le FrontOffice initial.

---

## 5) Cache Home

Cache applique:
- Cache applicatif court: 90 secondes.

But:
- Reduire la charge SQL sur la Home.
- Eviter de recalculer les memes blocs a chaque requete.

Note:
- Le cache Home n'est pas la source de verite des vues.
- La source de verite est Article.nbr_vues.

---

## 6) Indexation utile conservee

Indexes P0 utiles pour FO-002:
- Article(Id_status_article, date_publication DESC)
- Article(lang, Id_status_article, date_publication DESC)
- Article(Id_Categorie, Id_status_article, date_publication DESC)
- Media(Id_Article, priorite)

Ces indexes accelerent:
- Le chargement des recents
- Le chargement des plus lus
- Les joins categorie/media

---

## 7) Tables non necessaires pour la version simplifiee

Si on garde strictement cette strategie simple, ces tables ne sont plus necessaires au runtime:
- article_view_log
- article_view_stats

Important:
- Elles peuvent rester en base sans impact majeur.
- Elles ne sont simplement plus la source principale des compteurs Home.

---

## 8) Comment tester rapidement

### Test A: increment simple
1. Lire la valeur courante:
   SELECT nbr_vues FROM Article WHERE Id_Article = X;
2. Ouvrir la page article.
3. Relire la valeur:
   SELECT nbr_vues FROM Article WHERE Id_Article = X;
4. Attendu: +1 si vue valide.

### Test B: anti-doublon meme session
1. Faire deux requetes de suite avec le meme cookie de session.
2. Attendu: +1 total (pas +2).

### Test C: affichage Home
1. Ouvrir /fr.
2. Verifier que le bloc Plus lus affiche des valeurs coherentes avec Article.nbr_vues.

---

## 9) Limites connues

- L'anti-doublon repose sur session + empreinte client, pas sur identite utilisateur forte.
- Changement de navigateur, appareil ou contexte reseau peut compter une nouvelle vue.
- C'est acceptable pour MVP, et evolutif plus tard si besoin analytics avancees.

---

## 10) Conclusion

Le billet FO-002 est operationnel avec une architecture volontairement simple:
- Compteur central unique: Article.nbr_vues
- Anti-doublon pragmatique: session 30 min
- Home performante: 3 requetes bornees + cache 90s

Cette version est plus facile a comprendre, tester et maintenir.
