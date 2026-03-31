<?php

declare(strict_types=1);

use App\Controllers\Admin\UserController;
use App\Controllers\Admin\RoleController;
use App\Models\User;
use App\Models\SessionLogin;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/User.php';
require_once dirname(__DIR__, 4) . '/app/Models/Role.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/UserController.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/RoleController.php';

session_start();

if (!isset($_SESSION['login']) || !($_SESSION['login'] instanceof SessionLogin) || $_SESSION['login']->getUserLoggedIn() !== true) {
    header('Location: /admin.php');
    exit;
}

$login = $_SESSION['login'];
$idUser = $login->getIdUser();
$role = $login->getRole();

if ($idUser === null) {
    header('Location: /logout.php');
    exit;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$roleController = new RoleController();
$roles = $roleController->getAllRoles();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $nom = trim((string) ($_POST['nom'] ?? ''));
    $prenom = trim((string) ($_POST['prenom'] ?? ''));
    $mdp = trim((string) ($_POST['mdp'] ?? ''));
    $numeroTel = trim((string) ($_POST['numeroTel'] ?? ''));
    $adresse = trim((string) ($_POST['adresse'] ?? ''));
    $idRole = isset($_POST['idRole']) ? (int) $_POST['idRole'] : null;

    if ($email === '' || $mdp === '' || $idRole === null) {
        $errorMessage = 'Email, mot de passe et role sont requis.';
    } else {
        try {
            $userController = new UserController();
            $newUser = new User(
                email: $email,
                nom: $nom,
                prenom: $prenom,
                mdp: $mdp,
                numeroTel: $numeroTel,
                adresse: $adresse,
                idRole: $idRole
            );

            $createdId = $userController->createUser($newUser);

            if ($createdId === null) {
                $errorMessage = 'Une erreur est survenue lors de la creation de l\'utilisateur.';
            } else {
                $_SESSION['flash_success'] = 'Utilisateur cree avec succes.';
                header('Location: /Views/Admin/User/gestion.php');
                exit;
            }
        } catch (\Throwable $e) {
            $errorMessage = 'Impossible de creer cet utilisateur. Email existe peut-etre deja.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Creation d'un nouvel utilisateur.">
    <title>Nouveau Utilisateur</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/user.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h1>Back-office</h1>
                <p>Site d'actualite</p>
            </div>

            <nav class="sidebar-nav" aria-label="Navigation principale">
                <a class="nav-link" href="../dashboard.php">Dashboard</a>
                <a class="nav-link" href="../Article/nouvelle.php">Articles</a>
                <a class="nav-link" href="../Categorie/gestion_categories.php">Categories</a>
                <a class="nav-link" href="../Tag/gestion.php">Tags</a>
                <a class="nav-link" href="../Media/gestion.php">Medias</a>
                <a class="nav-link active" href="gestion.php">Gestion utilisateurs</a>
                <?php if ($role === 'admin'): ?>
                    <a class="nav-link" href="../Role/role.php">Roles & permissions</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link" href="../profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main user-main">
            <header class="card main-header">
                <p class="subtitle">Utilisateurs</p>
                <h2>Nouvel utilisateur</h2>
                <p class="welcome">Creez un compte et attribuez-lui un role.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <section class="card user-form-card">
                <form action="nouvelle.php" method="post" class="user-form">
                    <div class="form-grid">
                        <div>
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo escape((string) ($_POST['email'] ?? '')); ?>" required>
                        </div>

                        <div>
                            <label for="idRole">Role</label>
                            <select id="idRole" name="idRole" required>
                                <option value="">Selectionner un role</option>
                                <?php foreach ($roles as $itemRole): ?>
                                    <option value="<?php echo (int) $itemRole->getIdRole(); ?>" <?php echo (isset($_POST['idRole']) && (int) $_POST['idRole'] === (int) $itemRole->getIdRole()) ? 'selected' : ''; ?>>
                                        <?php echo escape($itemRole->getRole()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?php echo escape((string) ($_POST['nom'] ?? '')); ?>">
                        </div>

                        <div>
                            <label for="prenom">Prenom</label>
                            <input type="text" id="prenom" name="prenom" value="<?php echo escape((string) ($_POST['prenom'] ?? '')); ?>">
                        </div>

                        <div>
                            <label for="mdp">Mot de passe</label>
                            <input type="password" id="mdp" name="mdp" required>
                        </div>

                        <div>
                            <label for="numeroTel">Telephone</label>
                            <input type="tel" id="numeroTel" name="numeroTel" value="<?php echo escape((string) ($_POST['numeroTel'] ?? '')); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse" value="<?php echo escape((string) ($_POST['adresse'] ?? '')); ?>">
                    </div>

                    <div class="actions-row">
                        <button type="submit" class="action-link">Ajouter l'utilisateur</button>
                        <a href="gestion.php" class="ghost-link">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
