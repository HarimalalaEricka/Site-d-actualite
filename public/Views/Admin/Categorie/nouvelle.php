<?php
    
declare(strict_types=1);

use App\Controllers\Admin\CategorieController;
use App\Models\SessionLogin;
use App\Models\Categorie;

require_once dirname(__DIR__, 4) . '/app/Core/Database.php';
require_once dirname(__DIR__, 4) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__, 4) . '/app/Models/Categorie.php';
require_once dirname(__DIR__, 4) . '/app/Controllers/Admin/CategorieController.php';

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
$errorMessage = '';
$successMessage = '';

if( $_SERVER['REQUEST_METHOD'] === 'POST')
{
    $categorie = trim((string) ($_POST['categorie'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    if ($categorie === '') {
        $errorMessage = 'Le nom de la catégorie est requis.';
    } else {
        $categorieController = new CategorieController();
        $newCategorie = new Categorie($categorie, $description);
        $result = $categorieController->createCategorie($newCategorie);

        if ($result) {
            $successMessage = 'Catégorie créée avec succès.';
            header('Location: /Views/Admin/Categorie/gestion_categories.php');
        } else {
            $errorMessage = 'Une erreur est survenue lors de la création de la catégorie.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ajout d'une nouvelle categorie d'articles.">
    <title>Nouvelle categorie</title>
    <link rel="stylesheet" href="/assets/css/Admin/dashboard.css">
    <link rel="stylesheet" href="/assets/css/Admin/categorie.css">
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
                <a class="nav-link active" href="gestion_categories.php">Categories</a>
                <a class="nav-link" href="../Tag/gestion.php">Tags</a>
                <a class="nav-link" href="../Media/gestion.php">Medias</a>
                <a class="nav-link" href="../User/gestion.php">Gestion utilisateurs</a>
                <?php if ($role === 'admin'): ?>
                    <a class="nav-link" href="../Role/role.php">Roles & permissions</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a class="footer-link" href="../profile.php">Mon profil</a>
                <a class="footer-link logout" href="/logout.php">Se deconnecter</a>
            </div>
        </aside>

        <main class="dashboard-main category-main">
            <header class="card main-header">
                <p class="subtitle">Categories</p>
                <h2>Nouvelle categorie</h2>
                <p class="welcome">Creez une categorie claire pour mieux organiser vos contenus.</p>
            </header>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error"><?php echo escape($errorMessage); ?></div>
            <?php endif; ?>

            <?php if ($successMessage !== ''): ?>
                <div class="notice success"><?php echo escape($successMessage); ?></div>
            <?php endif; ?>

            <section class="card category-form-card">
                <form action="nouvelle.php" method="post" class="category-form">
                    <div>
                        <label for="categorie">Nom de la categorie</label>
                        <input type="text" id="categorie" name="categorie" placeholder="Ex: Geopolitique" value="<?php echo escape((string) ($_POST['categorie'] ?? '')); ?>" required>
                    </div>

                    <div>
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Decrivez brievement cette categorie"><?php echo escape((string) ($_POST['description'] ?? '')); ?></textarea>
                    </div>

                    <div class="actions-row">
                        <button type="submit" class="action-link">Ajouter la categorie</button>
                        <a href="gestion_categories.php" class="ghost-link">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>