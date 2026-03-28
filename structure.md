news-site/
│
├── public/                 # Dossier accessible via le navigateur
│   ├── index.php           # Point d’entrée FrontOffice
│   ├── admin.php           # Point d’entrée BackOffice
│   ├── .htaccess           # URL rewriting + redirections
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── uploads/            # Stockage des images/médias
│
├── app/                    # Contient toute la logique PHP orientée objet
│   ├── Controllers/
│   │   ├── Front/          # Contrôleurs du FrontOffice
│   │   │   ├── HomeController.php
│   │   │   ├── ArticleController.php
│   │   │   └── CategoryController.php
│   │   └── Admin/          # Contrôleurs du BackOffice
│   │       ├── DashboardController.php
│   │       ├── ArticleController.php
│   │       ├── CategoryController.php
│   │       ├── UserController.php
│   │       └── MediaController.php
│   │
│   ├── Models/             # Modèles pour ORM ou requêtes SQL
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Article.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Media.php
│   │   ├── Status.php
│   │   └── Collaboration.php
│   │
│   ├── Core/               # Classes de base, helpers, database
│   │   ├── Database.php    # Gestion PDO / singleton
│   │   ├── Router.php      # Gestion des routes si tu ne prends pas Laravel
│   │   └── Session.php     # Gestion des sessions/utilisateur connecté
│   │
│   └── Views/              # Vues HTML / templates
│       ├── Front/          # FrontOffice
│       │   ├── home.php
│       │   ├── article.php
│       │   └── category.php
│       │
│       └── Admin/          # BackOffice
│           ├── dashboard.php
│           ├── articles/
│           │   ├── list.php
│           │   ├── create.php
│           │   └── edit.php
│           ├── categories/
│           ├── users/
│           └── media/
│
├── config/                 # Configuration globale
│   ├── config.php          # DB host, user, mot de passe, etc.
│   └── settings.php        # Autres settings (SEO, chemins, langues)
│
├── vendor/                 # Composer packages (si tu utilises Composer)
│
├── composer.json
└── README.md