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

// === Récupération de l'utilisateur à éditer ===
$idUserTarget = isset($_GET['idUser']) ? (int) $_GET['idUser'] : 0;

if ($idUserTarget <= 0) {
    $_SESSION['flash_error'] = 'Utilisateur invalide.';
    header('Location: /Views/Admin/User/gestion.php');
    exit;
}

$userController = new UserController();
$user = $userController->getUserById($idUserTarget);

if ($user === null) {
    $_SESSION['flash_error'] = 'Utilisateur introuvable.';
    header('Location: /Views/Admin/User/gestion.php');
    exit;
}

$roleController = new RoleController();
$roles = $roleController->getAllRoles();

// === Variables pour le formulaire (pré-remplissage) ===
$formEmail       = $user->getEmail() ?? '';
$formNom         = $user->getNom() ?? '';
$formPrenom      = $user->getPrenom() ?? '';
$formNumeroTel   = $user->getNumeroTel() ?? '';
$formAdresse     = $user->getAdresse() ?? '';
$formIdRole      = $user->getIdRole() ?? null;

$errorMessage = '';

// === Traitement du formulaire ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim((string) ($_POST['email'] ?? ''));
    $nom        = trim((string) ($_POST['nom'] ?? ''));
    $prenom     = trim((string) ($_POST['prenom'] ?? ''));
    $mdp        = trim((string) ($_POST['mdp'] ?? ''));
    $numeroTel  = trim((string) ($_POST['numeroTel'] ?? ''));
    $adresse    = trim((string) ($_POST['adresse'] ?? ''));
    $idRoleForm = isset($_POST['idRole']) ? (int) $_POST['idRole'] : null;

    if ($email === '' || $idRoleForm === null || $idRoleForm <= 0) {
        $errorMessage = 'Email et rôle sont requis. Le mot de passe peut rester vide pour le conserver tel quel.';
    } else {
        try {
            $finalMdp = $mdp !== '' ? $mdp : $user->getMdp();

            $updatedUser = new User(
                email: $email,
                nom: $nom,
                prenom: $prenom,
                mdp: $finalMdp,
                numeroTel: $numeroTel,
                adresse: $adresse,
                idRole: $idRoleForm
            );

            $isUpdated = $userController->updateUser($idUserTarget, $updatedUser);

            if ($isUpdated) {
                $_SESSION['flash_success'] = 'Utilisateur mis à jour avec succès.';
                header('Location: /Views/Admin/User/gestion.php');
                exit;
            } else {
                $errorMessage = 'Erreur lors de la mise à jour de l\'utilisateur.';
            }
        } catch (Exception $e) {
            $errorMessage = 'Erreur : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer utilisateur - Back-office</title>
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
            <h2>Éditer utilisateur</h2>
            <p class="welcome">Mettez à jour les informations du compte utilisateur.</p>
        </header>

        <?php if ($errorMessage !== ''): ?>
            <div class="notice error"><?php echo escape($errorMessage); ?></div>
        <?php endif; ?>

        <section class="card user-form-card">
            <form action="editer.php?idUser=<?= (int)$idUserTarget ?>" method="post" class="user-form">
                <div class="form-grid">
                    <div>
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?= escape($formEmail) ?>" required>
                    </div>

                    <div>
                        <label for="idRole">Rôle <span class="required">*</span></label>
                        <select id="idRole" name="idRole" required>
                            <option value="">Sélectionner un rôle</option>
                            <?php foreach ($roles as $itemRole): ?>
                                <option value="<?= (int)$itemRole->getIdRole() ?>"
                                    <?= ($itemRole->getIdRole() === $formIdRole) ? 'selected' : '' ?>>
                                    <?= escape($itemRole->getRole()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?= escape($formNom) ?>">
                    </div>

                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?= escape($formPrenom) ?>">
                    </div>

                    <div>
                        <label for="mdp">Mot de passe (laisser vide pour conserver l'actuel)</label>
                        <input type="password" id="mdp" name="mdp">
                    </div>

                    <div>
                        <label for="numeroTel">Téléphone</label>
                        <input type="tel" id="numeroTel" name="numeroTel" value="<?= escape($formNumeroTel) ?>">
                    </div>
                </div>

                <div>
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?= escape($formAdresse) ?>">
                </div>

                <div class="actions-row">
                    <button type="submit" class="action-link">Mettre à jour l'utilisateur</button>
                    <a href="gestion.php" class="ghost-link">Annuler</a>
                </div>
            </form>
        </section>
    </main>
</div>

</body>
</html>