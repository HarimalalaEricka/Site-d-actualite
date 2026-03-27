# Projet : Site d'Actualité – Gestion Frontoffice et Backoffice

## FRONT OFFICE

### 1. Traduction
- Permet à l’utilisateur de changer la langue (FR / EN).  
- Chaque article peut avoir plusieurs versions linguistiques.  
- Les menus et boutons sont traduits pour correspondre à la langue choisie.

### 2. Connexion
- Accès sécurisé pour les journalistes et administrateurs vers le backoffice.  
- Permet de différencier les rôles et les droits de publication.

### 3. À la une
- Affiche les articles principaux ou les événements majeurs.  
- Mise en avant avec image principale et titre.  
- Utilisation possible de tags pour filtrer ou mettre en avant certains sujets.

### 4. Catégories
- Organisation des articles par thématique : Monde, Politique, Conflit, Économie…  
- Permet aux utilisateurs de naviguer facilement dans un sujet précis.  
- Les catégories peuvent être gérées depuis le backoffice par un admin.

### 5. Liste d’articles (titre + image + extrait)
- Affiche le titre de l’article, la photo principale et un extrait du texte.  
- Sert à donner un aperçu rapide à l’utilisateur.  
- Utilisation de tags pour permettre un filtrage par mots-clés.

### 6. Articles les plus lus
- Met en avant les articles les plus consultés.  
- Permet à l’utilisateur de voir les sujets populaires.

### 7. Barre de recherche et filtres
- Recherche par mots-clés dans les titres et le contenu.  
- Filtrage possible par catégorie, date ou tag.

### 8. Partage sur réseaux sociaux (optionnel)
- Permet à l’utilisateur de partager un article sur Twitter, Facebook, etc.  
- Améliore la visibilité des articles.

### 9. Page d’un article
- **Titre de l’article**  
- **Auteur / journaliste**  
- **Date de publication**  
- **Images principales** et **autres images si disponibles**  
- **Vidéos** intégrées si présentes  
- **Texte complet** (HTML généré par TinyMCE pour la mise en forme)  
- **Articles similaires** basés sur catégories et tags  
- **Tags** pour filtrage ou navigation vers des articles liés  

---

## BACK OFFICE

### 1. Authentification
- Connexion sécurisée pour journalistes et administrateurs.  
- Gestion des rôles pour contrôler l’accès aux fonctionnalités.

### 2. Dashboard (vue globale)
- Affiche les statistiques principales :  
  - Nombre total d’articles  
  - Articles publiés vs brouillons  
  - Articles les plus lus  
  - Articles récents  
- Permet à l’administrateur d’avoir une vue d’ensemble rapide du contenu.

### 3. Gestion des articles
- Fonction similaire à un flux Facebook : création, modification, suppression.  
- Utilisation de **TinyMCE** pour l’édition des contenus riches (HTML).  
- Possibilité de définir le statut : brouillon ou publié.  
- Les articles peuvent être liés à des catégories et des tags pour faciliter le filtrage.

### 4. Gestion des journalistes
- Profil individuel de chaque journaliste.  
- Liste de tous les articles publiés ou en brouillon par le journaliste.  
- Possibilité de consulter et gérer les droits de publication.
- Collaboration entre journalistes sur un meme article

### 5. Gestion de profil 
->(journaliste)
    - Informations personnelles et professionnelles du journaliste.  
    - Liste des articles publiés et brouillons.  
    - Possibilité de modifier son profil.
->(admin)
    - gestion des journalistes
    - validation des articles
    - gestion des categories

### 6. Création et validation des articles
- Journaliste : crée un article et le sauvegarde en brouillon.  
- Admin : valide l’article pour publication.  
- Workflow : création → validation → publication.  
- Catégories et tags peuvent être assignés lors de la création.

### 7. Gestion des catégories (admin)
- Ajouter, modifier ou supprimer des catégories.  
- Permet d’organiser les articles et de faciliter la navigation frontoffice.

### 8. Gestion d’un article individuel
- Modifier le contenu ou les images.  
- Visualiser le nombre de vues de l’article.  
- Supprimer un article si nécessaire.  
- Assignation des tags et catégorie mise à jour.

### 9. Statistiques (optionnel)
- Graphiques sur les articles les plus lus.  
- Statistiques par catégorie, langue ou journaliste.  
- Aide à la prise de décision éditoriale.

---

## BONUS / NOTES METIER
- **Utilisation des tags** : pour filtrer, recommander ou regrouper des articles similaires.  
- **TinyMCE** : éditeur HTML riche pour saisir le contenu des articles avec mise en forme, images et vidéos.  
- **Workflow de publication** : assure que les articles passent par validation avant d’être visibles sur le frontoffice.  
- **Multi-langue** : chaque article peut avoir une version FR et EN pour internationalisation.
