<?php

declare(strict_types=1);

use App\Controllers\Admin\ArticleController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\RoleController;
use App\Controllers\Admin\TagController;
use App\Controllers\Admin\MediaController;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 3) . '/app/Core/Database.php';
require_once dirname(__DIR__, 3) . '/app/Models/Article.php';
require_once dirname(__DIR__, 3) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 3) . '/app/Models/User.php';
require_once dirname(__DIR__, 3) . '/app/Models/Role.php';
require_once dirname(__DIR__, 3) . '/app/Models/Tag.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/ArticleController.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/UserController.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/RoleController.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/TagController.php';
require_once dirname(__DIR__, 3) . '/app/Controllers/Admin/MediaController.php';

session_start();

if (!isset($_SESSION['login']) || !($_SESSION['login'] instanceof SessionLogin) || $_SESSION['login']->getUserLoggedIn() !== true) {
    header('Location: /admin.php');
    exit;
}

$login = $_SESSION['login'];
$idUserConnected = $login->getIdUser();
$role = $login->getRole();

if ($idUserConnected === null) {
    header('Location: /logout.php');
    exit;
}

// Paramètre optionnel pour afficher un autre profil en lecture seule
$idUserTarget = isset($_GET['idUser']) ? (int) $_GET['idUser'] : $idUserConnected;
$isReadOnly = $idUserTarget !== $idUserConnected;

$userController = new UserController();
$viewedUser = $userController->getUserById($idUserTarget);

if ($viewedUser === null) {
    $_SESSION['flash_error'] = 'Utilisateur introuvable.';
    header('Location: /Views/Admin/User/gestion.php');
    exit;
}

$articleController = new ArticleController();
$publishedArticles = $articleController->getArticleByUser($idUserTarget, 'publie');
$mediaController = new MediaController();
$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Traitement de la modification du profil
$errorMessage = '';
$editMode = isset($_GET['edit']) && $_GET['edit'] === '1' && !$isReadOnly;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isReadOnly) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $nom = trim((string) ($_POST['nom'] ?? ''));
    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $mdp = trim((string) ($_POST['mdp'] ?? ''));
    $numeroTel = trim((string) ($_POST['numeroTel'] ?? ''));
    $adresse = trim((string) ($_POST['adresse'] ?? ''));

    if ($email === '') {
        $errorMessage = 'Email est requis.';
    } else {
        try {
            $finalMdp = $mdp !== '' ? $mdp : $viewedUser->getMdp();

            $updatedUser = new \App\Models\User(
                email: $email,
                nom: $nom,
                prenom: $prenom,
                mdp: $finalMdp,
                numeroTel: $numeroTel,
                adresse: $adresse,
                idRole: $viewedUser->getIdRole()
            );

            $isUpdated = $userController->updateUser($idUserConnected, $updatedUser);

            if (!$isUpdated) {
                throw new RuntimeException('Impossible de mettre a jour le profil.');
            }

            $_SESSION['flash_success'] = 'Profil modifie avec succes.';
            header('Location: /Views/Admin/profile.php');
            exit;
        } catch (\Throwable $e) {
            $errorMessage = 'Impossible de modifier le profil. Email existe peut-etre deja.';
        }
    }
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function nullableIntToString(?int $value): string
{
    return $value === null ? 'null' : (string) $value;
}

// Récupérer le rôle de l'utilisateur affiché
$roleController = new RoleController();
$userRole = $roleController->getRoleById($viewedUser->getIdRole());
$userRoleName = $userRole !== null ? $userRole->getRole() : 'Inconnu';

$initialNom = strtoupper(substr(trim($viewedUser->getNom()), 0, 1));
$initialPrenom = strtoupper(substr(trim($viewedUser->getPrenom()), 0, 1));
$avatarInitiales = trim($initialNom . $initialPrenom);
if ($avatarInitiales === '') {
    $avatarInitiales = 'U';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de profil d'un BackOffice d'un site d'actualite sur la guerre en Iran.">
    <title>Profil Utilisateur</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/profile.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h1>Back-office</h1>
                <p>Site d'actualite</p>
            </div>

            <nav class="sidebar-nav" aria-label="Navigation principale">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="Article/nouvelle.php">Articles</a>
                <a class="nav-link" href="Categorie/gestion_categories.php">Categories</a>
                <a class="nav-link" href="Tag/gestion.php">Tags</a>
                <a class="nav-link" href="Media/gestion.php">Medias</a>
                <a class="nav-link" href="User/gestion.php">Gestion utilisateurs</a>
                <?php
                    if ($role === 'admin') {
                        echo '<a class="nav-link" href="Role/role.php">Roles & permissions</a>';
                    }
                ?>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link active" href="profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main profile-main">
            <header class="card main-header">
                <p class="subtitle">Profil utilisateur</p>
                <h2><?php echo escape($viewedUser->getNom()); ?> <?php echo escape($viewedUser->getPrenom()); ?></h2>
                <p class="welcome">Role: <?php echo escape($userRoleName); ?><?php echo $isReadOnly ? ' | mode lecture seule' : ''; ?></p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="notice error"><?php echo escape($flashError); ?></div>
            <?php endif; ?>

            <?php if ($flashSuccess !== ''): ?>
                <div class="notice success"><?php echo escape($flashSuccess); ?></div>
            <?php endif; ?>

            <section class="card profile-card">
                <h3>Informations personnelles</h3>
                <?php if (!$isReadOnly && $editMode): ?>
                    <form action="/Views/Admin/profile.php" method="post" class="profile-form">
                        <div class="form-grid">
                            <div>
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo escape($viewedUser->getEmail()); ?>" required>
                            </div>

                            <div>
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" value="<?php echo escape($viewedUser->getNom()); ?>">
                            </div>

                            <div>
                                <label for="prenom">Prenom</label>
                                <input type="text" id="prenom" name="prenom" value="<?php echo escape($viewedUser->getPrenom()); ?>">
                            </div>

                            <div>
                                <label for="mdp">Mot de passe (laisser vide pour conserver)</label>
                                <input type="password" id="mdp" name="mdp">
                            </div>

                            <div>
                                <label for="numeroTel">Telephone</label>
                                <input type="tel" id="numeroTel" name="numeroTel" value="<?php echo escape($viewedUser->getNumeroTel()); ?>">
                            </div>

                            <div>
                                <label for="adresse">Adresse</label>
                                <input type="text" id="adresse" name="adresse" value="<?php echo escape($viewedUser->getAdresse()); ?>">
                            </div>
                        </div>

                        <p class="role-chip">Role: <?php echo escape($userRoleName); ?></p>

                        <div class="actions-row">
                            <button type="submit" class="action-link">Enregistrer les modifications</button>
                            <a href="/Views/Admin/profile.php" class="ghost-link">Annuler</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="profile-hero">
                        <div class="profile-avatar"><?php echo escape($avatarInitiales); ?></div>
                        <div class="profile-hero-content">
                            <p class="hero-kicker">Compte utilisateur</p>
                            <p class="hero-name"><?php echo escape($viewedUser->getNom()); ?> <?php echo escape($viewedUser->getPrenom()); ?></p>
                            <p class="hero-meta"><?php echo escape($viewedUser->getEmail()); ?></p>
                            <p class="hero-role"><?php echo escape($userRoleName); ?></p>
                        </div>
                    </div>

                    <div class="profile-overview">
                        <div class="info-item">
                            <p class="info-label">Email</p>
                            <p class="info-value"><?php echo escape($viewedUser->getEmail()); ?></p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">Nom complet</p>
                            <p class="info-value"><?php echo escape($viewedUser->getNom()); ?> <?php echo escape($viewedUser->getPrenom()); ?></p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">Telephone</p>
                            <p class="info-value"><?php echo escape($viewedUser->getNumeroTel()); ?></p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">Adresse</p>
                            <p class="info-value"><?php echo escape($viewedUser->getAdresse()); ?></p>
                        </div>

                        <div class="info-item">
                            <p class="info-label">Role</p>
                            <p class="info-value role-pill"><?php echo escape($userRoleName); ?></p>
                        </div>
                    </div>

                    <?php if (!$isReadOnly): ?>
                        <a href="/Views/Admin/profile.php?edit=1" class="action-link">Modifier mon profil</a>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="card quick-links">
                <?php if (!$isReadOnly): ?>
                    <a class="quick-btn quick-btn-primary" href="Article/nouvelle.php">Nouvel article</a>
                    <a class="quick-btn quick-btn-secondary" href="Article/brouillon.php">Mes brouillons</a>
                <?php else: ?>
                    <a class="ghost-link" href="/Views/Admin/User/gestion.php">Retour a la liste des utilisateurs</a>
                <?php endif; ?>
            </section>

            <section class="card">
                <h3><?php echo escape($viewedUser->getNom()); ?> <?php echo escape($viewedUser->getPrenom()); ?> - Articles publies</h3>

                <?php if ($publishedArticles === []): ?>
                    <p class="empty-state">Aucun article trouve pour cet utilisateur.</p>
                <?php else: ?>
                    <ul class="article-list">
                        <?php foreach ($publishedArticles as $article): ?>
                            <li class="article-item">
                                <div class="article-head">
                                    <h4><?php echo escape($article->getTitre()); ?></h4>
                                    <p class="article-date"><?php echo escape($article->getDatePublication()); ?></p>
                                </div>
                                <?php
                                    $tagController = new TagController();
                                    $tags = $tagController->getTagsByArticle($article->getIdArticle());
                                    $mediaItems = $mediaController->getMediaByArticle((int) $article->getIdArticle());
                                    $primaryMedia = null;
                                    $secondaryMedia = [];
                                    $videoMedia = [];
                                    $audioMedia = [];
                                    foreach ($mediaItems as $mediaItem) {
                                        $typeMedia = strtolower(trim((string) ($mediaItem['typeMedia'] ?? '')));
                                        if ($typeMedia === 'video') {
                                            $videoMedia[] = $mediaItem;
                                        } elseif ($typeMedia === 'audio') {
                                            $audioMedia[] = $mediaItem;
                                        } else {
                                            if ($primaryMedia === null && $mediaItem['priorite'] === true) {
                                                $primaryMedia = $mediaItem;
                                            } else {
                                                $secondaryMedia[] = $mediaItem;
                                            }
                                        }
                                    }
                                    if ($primaryMedia === null && $secondaryMedia !== []) {
                                        $primaryMedia = $secondaryMedia[0];
                                        $secondaryMedia = array_slice($secondaryMedia, 1);
                                    }
                                ?>
                                <?php if ($primaryMedia !== null): ?>
                                    <div class="media-primary">
                                        <p><strong>Image prioritaire</strong></p>
                                        <img src="<?php echo escape($primaryMedia['url']); ?>" alt="Image prioritaire" class="media-image primary">
                                    </div>
                                <?php endif; ?>
                                <?php if ($secondaryMedia !== []): ?>
                                    <div class="media-secondary">
                                        <p><strong>Images secondaires</strong></p>
                                        <div class="media-row">
                                            <?php foreach ($secondaryMedia as $mediaSecondaire): ?>
                                                <img src="<?php echo escape($mediaSecondaire['url']); ?>" alt="Image secondaire" class="media-image secondary">
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($videoMedia !== []): ?>
                                    <div class="media-videos">
                                        <p><strong>Videos</strong></p>
                                        <?php foreach ($videoMedia as $video): ?>
                                            <video controls class="media-video">
                                                <source src="<?php echo escape($video['url']); ?>">
                                            </video>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($audioMedia !== []): ?>
                                    <div class="media-audios">
                                        <p><strong>Audios</strong></p>
                                        <?php foreach ($audioMedia as $audio): ?>
                                            <audio controls class="media-audio">
                                                <source src="<?php echo escape($audio['url']); ?>">
                                            </audio>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($tags)): ?>
                                    <p class="tags">
                                        <?php foreach ($tags as $tag): ?>
                                            <span>#<?php echo escape($tag->getNom()); ?></span>
                                        <?php endforeach; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="article-meta-grid">
                                    <div class="meta-item">
                                        <span class="meta-label">ID article</span>
                                        <strong><?php echo escape(nullableIntToString($article->getIdArticle())); ?></strong>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Vues</span>
                                        <strong><?php echo escape((string) $article->getNbrVues()); ?></strong>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Langue</span>
                                        <strong><?php echo escape($article->getLang()); ?></strong>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Categorie</span>
                                        <strong><?php echo escape(nullableIntToString($article->getIdCategorie())); ?></strong>
                                    </div>
                                </div>

                                <?php if (!$isReadOnly): ?>
                                    <div class="actions-row">
                                        <a class="ghost-link" href="Article/editer.php?idArticle=<?php echo $article->getIdArticle(); ?>">Editer</a>
                                        <a class="ghost-link" href="Article/supprimer.php?idArticle=<?php echo $article->getIdArticle(); ?>">Supprimer</a>
                                    </div>
                                <?php endif; ?>

                                <p class="content-label">Contenu</p>
                                <div class="article-content"><?php echo $article->getContenu(); ?></div>

                                <p class="article-footnote">
                                    Auteur principal: <?php echo escape(nullableIntToString($article->getIdUserPrincipal())); ?>
                                    | Statut: <?php echo escape(nullableIntToString($article->getIdStatusArticle())); ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

        </main>
    </div>
</body>
</html>