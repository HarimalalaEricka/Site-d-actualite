# Todo FrontOffice

## 0) Vision FrontOffice (ce que l'utilisateur doit ressentir)

Objectif: en moins de 5 secondes, l'utilisateur comprend:
- ce que couvre le site (actualite fiable, categories claires),
- ou cliquer ensuite (A la une, dernieres actualites, recherche),
- que le site charge vite et est lisible sur mobile.

Decision produit:
- On commence simple (MVP) avec une Home editoriale identique pour tout le monde.
- On reporte la personnalisation avancee (selon historique visiteur) en amelioration V2.

---

## 1) Priorisation globale (MVP puis V2)

### Phase MVP (obligatoire pour la livraison)
- [ ] Home claire et hierarchisee (A la une + dernieres actus + categories)
- [ ] Liste d'articles avec pagination
- [ ] Page article complete (titre, auteur, date, contenu, medias, tags)
- [ ] Recherche simple (titre + contenu)
- [ ] Filtrage par categorie et tag
- [ ] URL rewriting SEO (slug + route propre)
- [ ] Comptage des vues fiable sans surcharger la base
- [ ] Archives par mois/annee
- [ ] SEO technique minimum (title, meta description, h1 unique, alt images)
- [ ] Responsive mobile-first

### Phase V2 (ameliorations)
- [ ] Personnalisation de la Home basee sur interactions (cookie/local storage)
- [ ] Recommandations plus fines (articles similaires ponderes)
- [ ] Recherche avancee (multi-critere date + langue + pertinence)
- [ ] Dashboard analytics plus riche (temps de lecture, CTR blocs)

---

## 2) Premiere experience utilisateur (question: que montrer en premier?)

### Reponse decidee
- Premiere visite: afficher une Home editoriale neutre (pas de personnalisation agressive).
- Deuxieme visite et suivantes: ajuster legerement l'ordre des blocs selon categories consultees.
- Sans compte utilisateur: utiliser un identifiant visiteur anonyme (cookie ou local storage), jamais des donnees sensibles.

### Pourquoi ce choix
- Trop technique de faire un moteur de personnalisation complet maintenant.
- Mais utile de preparer les fondations des maintenant (tracking minimal, consentement, event schema).

### Todo implementation
- [ ] Definir le contenu du hero de Home
	- [ ] Bloc A la une (1 article principal)
	- [ ] Bloc Dernieres actualites (6 a 12 cartes)
	- [ ] Bloc Categories populaires
	- [ ] Bloc Articles les plus lus (24h/7j)
- [ ] Definir la regle premiere visite
	- [ ] Si pas de cookie visiteur: afficher ordre editorial par defaut
	- [ ] Si cookie present: remonter 1 ou 2 categories frequentes en haut
- [ ] Mettre en place la couche consentement (RGPD)
	- [ ] Bandeau cookies (accepter/refuser analytics)
	- [ ] Si refus: pas de tracking personnalise
	- [ ] Si acceptation: stocker visitor_id anonyme
- [ ] Cadrer les limites V1
	- [ ] Pas de profilage pousse
	- [ ] Pas d'empreinte appareil intrusive
	- [ ] Pas de cross-device sans compte

### Definition of Done
- [ ] Un nouvel utilisateur comprend la Home en 5 secondes
- [ ] Aucun compte requis
- [ ] Consentement cookies respecte

---

## 3) Archives (question: trop pousse ou faisable?)

### Reponse decidee
- Oui, c'est faisable des le MVP et tres utile pour un site d'actualite.
- Commencer simple: archives mensuelles + annuelles, filtrees par langue/categorie.

### Strategie base de donnees
- [ ] Reutiliser date_publication de la table Article
- [ ] Ajouter index SQL pour accelerer
	- [ ] INDEX(date_publication)
	- [ ] INDEX(lang, date_publication)
	- [ ] INDEX(id_categorie, date_publication)
- [ ] Exclure des archives les articles non publies

### Routes et UX
- [ ] Creer page liste archives: /archives
- [ ] Creer page mensuelle: /archives/2026/03
- [ ] Creer page annuelle: /archives/2026
- [ ] Ajouter bloc Archives dans footer + menu secondaire

### Requetes type
- [ ] Liste des mois disponibles (avec compteur)
- [ ] Articles d'un mois (ordonnes date desc)
- [ ] Pagination pour limiter la charge

### Definition of Done
- [ ] Navigation archive fluide (< 500 ms sur jeu de donnees normal)
- [ ] Pagination active
- [ ] Pages indexables SEO

---

## 4) Comptage des vues (question: quand compter et comment eviter saturation?)

### Reponse decidee
- Une vue est comptee quand la page article est reellement affichee (pas juste clic sur lien).
- Ne pas incrementer en base a chaque requete brute si trafic eleve.
- Utiliser un systeme hybride: anti-double comptage court + ecriture agregee.

### Regle metier recommandee
- [ ] Compter 1 vue si:
	- [ ] article page chargee avec succes
	- [ ] visiteur non deja compte pour cet article dans une fenetre de 30 min
- [ ] Ne pas compter:
	- [ ] prefetch robots evidents
	- [ ] refresh immediat en boucle

### Architecture technique pragmatique
- Option MVP simple (acceptable au debut):
	- [ ] UPDATE article SET nbr_vues = nbr_vues + 1 sur affichage article
	- [ ] Ajouter verrou anti-duplicat via table/cache visiteur_article_30min
- Option robuste (V2 si trafic augmente):
	- [ ] Ecrire evenement dans table article_view_event (ou Redis)
	- [ ] Job cron toutes les 1-5 min qui agrege vers article.nbr_vues
	- [ ] Purge des evenements anciens

### Todo implementation concret
- [ ] Creer mecanisme visitor_id anonyme
- [ ] Creer cle anti-duplication: article_id + visitor_id + time_bucket
- [ ] Ajouter service ViewCounterService
- [ ] Journaliser erreurs sans casser affichage article
- [ ] Tester charge (simulation 100+ hits simultanes)

### Definition of Done
- [ ] Les clics non ouverts ne sont pas comptes
- [ ] Les refresh rapides ne gonflent pas artificiellement
- [ ] La base reste stable en charge

---

## 5) Ecran global FrontOffice (question: a quoi doit ressembler l'ensemble?)

### Architecture d'ecran recommandee

#### Header
- [ ] Logo + baseline
- [ ] Navigation categories
- [ ] Selecteur langue FR/EN
- [ ] Barre de recherche

#### Home
- [ ] Zone hero A la une
- [ ] Grille dernieres actualites (cards)
- [ ] Bloc categories
- [ ] Bloc plus lus
- [ ] Bloc newsletter (optionnel)

#### Listing categorie
- [ ] Titre categorie (h1 unique)
- [ ] Filtres (tag, date)
- [ ] Liste cartes + pagination

#### Page article
- [ ] Fil d'ariane
- [ ] Titre, auteur, date, temps de lecture
- [ ] Media principal
- [ ] Corps article (TinyMCE HTML nettoye)
- [ ] Tags
- [ ] Articles similaires

#### Footer
- [ ] Liens utiles (archives, contact, mentions, politique cookies)
- [ ] Categories principales

### Contraintes UX/UI obligatoires
- [ ] Mobile-first
- [ ] Contraste et lisibilite (accessibilite)
- [ ] 1 seul h1 par page
- [ ] Images avec alt
- [ ] Temps de chargement optimise

---

## 6) URL Rewriting (question: stocker URL en base et creer getURL?)

### Reponse decidee
- Oui: generer une URL SEO propre pour chaque article publie.
- Eviter de stocker l'URL complete en dur si possible.
- Stocker plutot les elements stables: slug + date + id, puis construire l'URL via une methode dediee.

### Modele recommande
- [ ] Ajouter colonne slug dans Article (unique si possible)
- [ ] Garder id pour eviter collisions
- [ ] Construire URL via methode de domaine getUrl()

### Pattern d'URL propose
- [ ] /{lang}/{categorie}/article/{yyyy}/{mm}/{dd}/{id}-{slug}
- [ ] Exemple: /fr/monde/article/2026/03/28/125-conflit-regional-dernieres-analyses

### Gestion des changements de titre
- [ ] Le slug peut changer, mais l'id reste stable
- [ ] Si slug recu != slug courant, redirection 301 vers URL canonique
- [ ] Conserver eventuellement un historique de slugs (table article_slug_history)

### Router et methode applicative
- [ ] Definir route regex pour parser date + id + slug
- [ ] Recuperer article par id (source de verite)
- [ ] Verifier statut publie
- [ ] Exposer methode getUrl() cote modele/service

### Definition of Done
- [ ] Toutes les pages article ont une URL canonique unique
- [ ] Les anciennes URLs redirigent correctement
- [ ] Les URLs sont lisibles et SEO-friendly

---

## 7) Backlog technique detaille (ordre d'execution recommande)

### Sprint 1: Fondations navigation et SEO
- [ ] Implementer routes FrontOffice (home, categorie, article, archives)
- [ ] Implementer URL rewriting Apache + router PHP
- [ ] Ajouter metadata SEO de base dans templates
- [ ] Verifier balisage HTML semantique

### Sprint 2: Home et listings
- [ ] Home A la une + derniers articles + plus lus
- [ ] Listing categorie + tag + pagination
- [ ] Recherche simple titre/contenu

### Sprint 3: Article detail et vues
- [ ] Page article complete (medias, auteur, tags, similaires)
- [ ] Service compteur vues (anti-duplication)
- [ ] Logging et monitoring basique

### Sprint 4: Archives + finition UX
- [ ] Pages archives mois/annee
- [ ] Footer complet + liens legaux
- [ ] Optimisations perf (cache, compression)
- [ ] Tests Lighthouse mobile/desktop

### Sprint 5: V2 personnalisation legere
- [ ] Visitor profile anonyme (categories consultees)
- [ ] Reordonnancement leger de Home
- [ ] A/B test simple (optionnel)

---

## 8) Risques et garde-fous

- [ ] Risque: sur-ingenierie trop tot
	- [ ] Mitigation: garder V1 simple, reporter IA/reco avancee en V2
- [ ] Risque: surcharge DB sur compteur vues
	- [ ] Mitigation: anti-duplication + aggregation periodique
- [ ] Risque: SEO casse par URLs instables
	- [ ] Mitigation: URL canonique + redirections 301
- [ ] Risque: non-conformite cookies
	- [ ] Mitigation: consentement explicite avant analytics

---

## 9) Checklist finale de validation FrontOffice

- [ ] L'utilisateur comprend immediatement le site
- [ ] Navigation categories + recherche fonctionnelles
- [ ] Articles lisibles et complets
- [ ] Archives consultables
- [ ] Compteur vues fiable et performant
- [ ] URLs propres avec rewriting
- [ ] SEO technique respecte
- [ ] Lighthouse mobile et desktop > 70
- [ ] Docker: parcours FrontOffice teste de bout en bout

---

## 10) Tickets techniques prets a coder (FrontOffice)

Format ticket:
- Objectif
- Scope technique
- Taches implementation
- SQL/Index
- Critere d'acceptation
- Definition of Done

### FO-001 - Routing FrontOffice + URL rewriting SEO

Objectif:
- Exposer des URLs lisibles et stables pour Home, Categorie, Article, Archives.

Scope technique:
- Apache .htaccess + Router PHP.
- Routes cibles:
	- /{lang}
	- /{lang}/{categorie}
	- /{lang}/{categorie}/article/{yyyy}/{mm}/{dd}/{id}-{slug}
	- /{lang}/archives
	- /{lang}/archives/{yyyy}
	- /{lang}/archives/{yyyy}/{mm}

Taches implementation:
- [ ] Creer les regles rewrite vers public/index.php
- [ ] Ajouter parseur de route (lang, categorie, date, id, slug)
- [ ] Charger article par id (source de verite)
- [ ] Si slug URL != slug DB alors redirection 301 vers URL canonique
- [ ] Retourner 404 si article non publie/inexistant

SQL/Index:
- [ ] Ajouter colonne slug pour Article
- [ ] Contrainte unique sur (lang, slug)

Critere d'acceptation:
- [ ] URL article acces direct sans query string
- [ ] Changement de titre ne casse pas l'URL (id reste stable)
- [ ] Ancienne URL redirige en 301

Definition of Done:
- [ ] 100% des pages FrontOffice servies par routes propres

---

### FO-002 - Home FrontOffice performante (A la une, dernieres, plus lus)

Objectif:
- Afficher une Home utile en < 300 ms cote SQL (jeu de donnees standard).

Scope technique:
- Aggregation de blocs:
	- A la une
	- Dernieres actualites
	- Plus lus 24h/7j
	- Categories

Taches implementation:
- [ ] Creer requete A la une (status=publie, date desc)
- [ ] Creer requete dernieres actualites paginees
- [ ] Integrer top plus lus depuis metrique agregee
- [ ] Mettre cache applicatif court TTL (30-120 s)

SQL/Index:
- [ ] INDEX sur publication: (Id_status_article, date_publication DESC)
- [ ] INDEX sur langue/publication: (lang, Id_status_article, date_publication DESC)
- [ ] INDEX sur categorie/publication: (Id_Categorie, Id_status_article, date_publication DESC)

Critere d'acceptation:
- [ ] Home charge sans N+1 query
- [ ] Nombre de requetes SQL borne
- [ ] Time-to-first-byte stable sous charge moderee

Definition of Done:
- [ ] Profiling SQL valide + plan EXPLAIN sans full scan critique

---

### FO-003 - Page Article complete + robustesse contenu

Objectif:
- Afficher article complet (auteur, media principal, tags, similaires) de maniere fiable.

Scope technique:
- TinyMCE HTML sanitise.
- Media prioritaire + galerie secondaire.

Taches implementation:
- [x] Requete article detail par id + statut publie
- [x] Charger auteur principal + collaborations
- [x] Charger media prioritaire puis secondaires
- [x] Charger tags + similaires (meme categorie ou tags communs)
- [x] Sanitiser contenu HTML (liste blanche)

SQL/Index:
- [x] Media: INDEX(Id_Article, priorite)
- [x] article_tag: INDEX(Id_tag, Id_Article)
- [x] collaboration: INDEX(Id_Article, Id_User)

Critere d'acceptation:
- [x] 1 seul media principal garanti
- [x] Affichage degrade propre si media absent
- [x] Similaires retournes en < 200 ms SQL

Definition of Done:
- [x] Page article complete sans erreur PHP/SQL

---

### FO-004 - Compteur de vues anti-saturation (MVP solide)

Objectif:
- Compter les vues reelles sans gonflement artificiel et sans saturer la DB.

Scope technique:
- Fenetre anti-duplication 30 min par visiteur/article.
- Increment atomique controle.

Taches implementation:
- [ ] Creer visitor_id anonyme (cookie)
- [ ] Enregistrer empreinte vue dans table dediee (time bucket)
- [ ] Verifier non-duplication avant increment
- [ ] Incrementer Article.nbr_vues en transaction courte
- [ ] Exclure bots evidents (UA basique)

SQL/Index:
- [ ] Nouvelle table `article_view_guard`:
	- article_id INT
	- visitor_hash CHAR(64)
	- bucket_30m DATETIME
	- PRIMARY KEY(article_id, visitor_hash, bucket_30m)
	- INDEX(bucket_30m)
- [ ] Article: INDEX(Id_Article, nbr_vues)

Critere d'acceptation:
- [ ] Refresh immediat non recompte
- [ ] Concurrence elevee sans deadlock bloquant
- [ ] Plus lus coherent (ecart faible)

Definition of Done:
- [ ] Test charge > 100 hits simultanes passe

---

### FO-005 - Archives annuelles/mensuelles

Objectif:
- Naviguer rapidement dans l'historique des articles publies.

Scope technique:
- Pages archives + compteurs par mois.

Taches implementation:
- [x] Route /archives, /archives/{yyyy}, /archives/{yyyy}/{mm}
- [x] Requete mois disponibles avec COUNT(*)
- [x] Requete listing mois cible (pagination)
- [x] Filtres lang + categorie

SQL/Index:
- [x] Article: INDEX(date_publication)
- [x] Article: INDEX(Id_status_article, date_publication)
- [x] Article: INDEX(lang, Id_status_article, date_publication)

Critere d'acceptation:
- [x] Temps reponse archive stable sous pagination
- [x] Exclusion stricte des brouillons

Definition of Done:
- [x] Archives indexables SEO et navigables depuis footer

---

### FO-006 - Recherche et filtres

Objectif:
- Rechercher titre/contenu + filtrer categorie/tag/date efficacement.

Scope technique:
- MVP: LIKE optimise + filtres bornes.
- Evolution: FULLTEXT.

Taches implementation:
- [ ] Endpoint recherche avec pagination
- [ ] Filtres combines (categorie, tag, date)
- [ ] Debounce front sur champ recherche
- [ ] Logs requetes lentes

SQL/Index:
- [ ] Article: FULLTEXT(titre, contenu) (si moteur InnoDB actif)
- [ ] article_tag: INDEX(Id_tag, Id_Article)
- [ ] Article: INDEX(Id_Categorie, date_publication)

Critere d'acceptation:
- [ ] Recherche retourne des resultats pertinents
- [ ] Requete moyenne sous seuil defini (ex: < 300 ms)

Definition of Done:
- [ ] Recherche utilisable sur mobile/desktop sans blocage UI

---

### FO-007 - Internationalisation FR/EN cote contenu

Objectif:
- Servir des contenus correctement separes par langue.

Scope technique:
- Filtrage global par lang.

Taches implementation:
- [ ] Ajouter middleware/lang resolver
- [ ] Propager lang dans toutes les requetes Front
- [ ] Forcer URLs canonique par langue

SQL/Index:
- [ ] Article: INDEX(lang, Id_status_article, date_publication)
- [ ] UNIQUE(lang, slug)

Critere d'acceptation:
- [ ] Aucun article EN dans parcours FR (et inversement)

Definition of Done:
- [ ] Switch langue stable sur Home, listing, detail

---

### FO-008 - SEO technique + performance web

Objectif:
- Atteindre Lighthouse > 70 (mobile + desktop).

Scope technique:
- Meta, canonical, balisage, compression assets.

Taches implementation:
- [ ] 1 h1 par page
- [ ] meta description + title unique par page
- [ ] canonical URL sur article/categorie
- [ ] alt obligatoire sur images
- [ ] cache-control pour css/js/images

SQL/Index:
- [ ] Aucun index specifique

Critere d'acceptation:
- [ ] Scores Lighthouse conformes
- [ ] Aucun blocage SEO critique detecte

Definition of Done:
- [ ] Rapport Lighthouse capture et archive

---

## 11) Plan d'indexation SQL recommande (MVP)

Priorite P0 (a faire en premier):
- [ ] Article(Id_status_article, date_publication)
- [ ] Article(lang, Id_status_article, date_publication)
- [ ] Article(Id_Categorie, Id_status_article, date_publication)
- [ ] Media(Id_Article, priorite)
- [ ] article_tag(Id_tag, Id_Article)
- [ ] collaboration(Id_Article, Id_User)

Priorite P1 (selon usage recherche):
- [ ] Article FULLTEXT(titre, contenu)
- [ ] Article(lang, slug) UNIQUE

Exemple SQL (adapter les noms exacts de colonnes):

```sql
ALTER TABLE Article
	ADD COLUMN slug VARCHAR(300) NULL,
	ADD INDEX idx_article_status_date (Id_status_article, date_publication),
	ADD INDEX idx_article_lang_status_date (lang, Id_status_article, date_publication),
	ADD INDEX idx_article_cat_status_date (Id_Categorie, Id_status_article, date_publication),
	ADD UNIQUE KEY uq_article_lang_slug (lang, slug);

ALTER TABLE Media
	ADD INDEX idx_media_article_priorite (Id_Article, priorite);

ALTER TABLE article_tag
	ADD INDEX idx_article_tag_tag_article (Id_tag, Id_Article);

ALTER TABLE collaboration
	ADD INDEX idx_collab_article_user (Id_Article, Id_User);
```

---

## 12) Ameliorations base de donnees (robustesse + performance max)

### A. Integrite et qualite de schema
- [ ] Rendre explicites les NOT NULL pour colonnes critiques
- [ ] Ajouter contraintes CHECK quand possible (ou validations applicatives)
- [ ] Normaliser nommage (ex: hito_publication -> histo_publication)
- [ ] Corriger incoherences de casse/pluriel pour faciliter maintenance

### B. Types et tailles
- [ ] `titre` et `slug` avec tailles coherentes
- [ ] `nbr_vues` en INT UNSIGNED avec default 0
- [ ] datetime en timezone geree cote app (UTC recommande)

### C. Ecriture/lecture scalable
- [ ] Introduire table de lecture denormalisee pour Home si charge augmente:
	- article_read_model
- [ ] Introduire table d'agregats de vues (24h/7j)
- [ ] Rafraichissement par job periodique (1-5 min)

### D. Protection concurrence
- [ ] Requetes UPDATE atomiques pour compteurs
- [ ] Transactions courtes
- [ ] Retry limite sur deadlock

### E. Observabilite SQL
- [ ] Activer slow query log (seuil ex: 200 ms)
- [ ] Capturer EXPLAIN des requetes critiques
- [ ] Dashboard minimal: latence p95, erreurs DB, deadlocks

### F. Strategie migration
- [ ] Ecrire scripts versionnes: V2__add_indexes.sql, V3__slug.sql, etc.
- [ ] Tester migration sur copie de data
- [ ] Rollback script pour changements sensibles

---

## 13) Ordre d'execution recommande (2 semaines type)

Semaine 1:
- [x] FO-001 Routing + slug + canonical
- [x] FO-002 Home optimisee
- [x] FO-003 Article detail
- [x] FO-005 Archives

Semaine 2:
- [ ] FO-004 Compteur vues robuste
- [x] FO-006 Recherche + filtres
- [ ] FO-007 i18n FR/EN
- [ ] FO-008 SEO + Lighthouse + tuning final

Gate avant mise en prod:
- [ ] Toutes les requetes critiques avec EXPLAIN valide
- [ ] Aucun full scan non justifie sur endpoints Front principaux
- [ ] Test charge passe sur Home + Article + Recherche