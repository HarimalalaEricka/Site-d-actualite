# 📝 To-Do Liste Complète – Module BackOffice Laravel

Ce document décrit toutes les tâches nécessaires pour développer le module BackOffice du site d’actualités. Il inclut la préparation du projet, la gestion des utilisateurs, articles, catégories, médias, tags, workflow, seeders, règles métier et bonnes pratiques.

---

1️⃣ Préparation du projet
    - **Tâches :**
        - Créer le projet Laravel
        - Configurer Docker (PHP-FPM, Nginx, MySQL)
        - Configurer `.env` pour la base de données

    - **Commandes :**
        ```bash
        composer create-project laravel/laravel news-site
        cd news-site
        docker-compose up -d
    Fichiers :
        docker-compose.yml (PHP, MySQL, Nginx)
        .env (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
    Règles métier :
        BackOffice accessible uniquement aux utilisateurs authentifiés
        Deux rôles : admin et journaliste

2️⃣ Authentification & Rôles
    Tâches :
        Installer Laravel Breeze ou Jetstream pour authentification
        Installer Spatie Laravel-Permission pour gestion des rôles
        Créer les rôles admin et journaliste
        Protéger routes BackOffice avec middleware
    Commandes :
        composer require spatie/laravel-permission
        php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
        php artisan migrate
    Fichiers à modifier :
        app/Models/User.php → use HasRoles;
        routes/web.php → routes BackOffice avec middleware role:admin
    Règles métier :
        Admin : accès complet
        Journaliste : création/modification d’articles, pas validation

3️⃣ Gestion des Articles (CRUD + Workflow)
    Tâches :
        Créer modèle Article + migration
        Créer contrôleur resource ArticleController
        Créer vues Blade : index, create, edit, show
        Intégrer TinyMCE pour contenu HTML
        Workflow : brouillon → validation → publication
        Historique modifications (hito_publication, histo_status)
    Commandes :
        php artisan make:model Article -m
        php artisan make:controller Admin/ArticleController --resource
        php artisan make:seeder ArticleSeeder
    Fichiers :
        app/Models/Article.php
        database/migrations/xxxx_create_articles_table.php
        database/seeders/ArticleSeeder.php
        resources/views/admin/articles/*.blade.php
        routes/web.php → routes admin/articles
    Règles métier :
        Article : titre, contenu HTML, catégorie, tags, médias, statut, auteur
        Journaliste : créer/modifier
        Admin : valider et publier

4️⃣ Gestion des Catégories
    Tâches :
        CRUD catégories
        Assignation aux articles
    Commandes :
        php artisan make:model Category -m
        php artisan make:controller Admin/CategoryController --resource
        php artisan make:seeder CategorySeeder
    Fichiers :
        app/Models/Category.php
        resources/views/admin/categories/*.blade.php
    Règles métier :
        Seul l’admin peut créer/modifier/supprimer
        Chaque article doit appartenir à une catégorie
        
5️⃣ Gestion des Utilisateurs (Journalistes)
    Tâches :
        Liste utilisateurs
        Modifier profil
        Gérer rôle
        Voir articles par journaliste
    Commandes :
        php artisan make:controller Admin/UserController --resource
        php artisan make:seeder UserSeeder
    Fichiers :
        app/Models/User.php
        resources/views/admin/users/*.blade.php
    Règles métier :
        Admin : gérer tous les utilisateurs
        Journaliste : voir/modifier son profil seulement
6️⃣ Gestion des Médias
    Tâches :
        Upload images/vidéos
        Assignation aux articles
        Priorité : image principale vs secondaire
    Commandes :
        php artisan make:model Media -m
        php artisan make:controller Admin/MediaController --resource
        php artisan make:seeder MediaSeeder
        php artisan storage:link
    Fichiers :
        app/Models/Media.php
        resources/views/admin/media/*.blade.php
    Stockage : 
        storage/app/public/media
    Règles métier :
        Article peut avoir plusieurs médias
        Priorité true → image principale
        Journaliste/admin peuvent uploader

7️⃣ Gestion des Tags
    Tâches :
        CRUD tags
        Assignation multiple aux articles
    Commandes :
        php artisan make:model Tag -m
        php artisan make:controller Admin/TagController --resource
        php artisan make:seeder TagSeeder
    Fichiers :
        app/Models/Tag.php
        resources/views/admin/tags/*.blade.php
    Règles métier :
        Tags uniques
        Assignation multiple possible
        Aide SEO

8️⃣ Historique & Collaboration
    Tâches :
        Historique publication (hito_publication)
        Historique statut (histo_status)
        Collaboration plusieurs auteurs (collaboration)
    Commandes :
        php artisan make:model HitoPublication -m
        php artisan make:model HistoStatus -m
        php artisan make:model Collaboration -m
    Fichiers :
        app/Models/HitoPublication.php
        app/Models/HistoStatus.php
        app/Models/Collaboration.php
    Règles métier :
        Suivi complet des actions sur articles
        Plusieurs journalistes peuvent collaborer sur un même article
        Historique conserve date, auteur et action

9️⃣ Seeders et données de test
    Tâches :
        Créer seeders pour rôles, utilisateurs, catégories, status, tags, médias, articles
        Tester workflow complet avec données fictives
    Commandes :
        php artisan db:seed --class=RoleSeeder
        php artisan db:seed --class=UserSeeder
        php artisan db:seed --class=CategorySeeder
        php artisan db:seed --class=StatusSeeder
        php artisan db:seed --class=TagSeeder
        php artisan db:seed --class=TypeMediaSeeder
        php artisan db:seed --class=ArticleSeeder
        php artisan db:seed --class=MediaSeeder
    Exemple d’ArticleSeeder :
        Article::create([
            'titre'=>'Tensions en Iran',
            'contenu'=>'<p>Le conflit en Iran s’intensifie...</p>',
            'nbr_vues'=>0,
            'user_id'=>2, // journaliste
            'status_id'=>1, // brouillon
            'category_id'=>1,
            'lang'=>'fr',
        ]);

🔟 SEO & Bonnes pratiques
    Utiliser <h1> pour titre article, <h2> pour sous-titres
    Ajouter alt à toutes les images
    URLs propres : /article/{slug}
    Multi-langue : FR/EN via champ lang
    TinyMCE pour contenu HTML correctement formaté

1️⃣1️⃣ Docker & Lancement
    Monter les containers :
        docker-compose up -d
    Exécuter migrations et seeders :
        docker exec -it laravel_app php artisan migrate --seed
    Tester BackOffice :
        http://localhost:8000/admin

1️⃣2️⃣ Résultat attendu
    CRUD complet : articles, catégories, tags, utilisateurs, médias
    Workflow brouillon → validation → publication
    Historique complet et collaboration fonctionnelle
    Données de test insérées
    BackOffice sécurisé et prêt pour Docker et déploiement