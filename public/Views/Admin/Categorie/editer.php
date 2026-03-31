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

$idCategorie = isset($_GET['idCat']) ? (int) $_GET['idCat'] : 0;

if ($idCategorie <= 0) {
	$_SESSION['flash_error'] = 'Categorie invalide.';
	header('Location: /Views/Admin/Categorie/gestion_categories.php');
	exit;
}

$categorieController = new CategorieController();
$categorie = $categorieController->getCategorieById($idCategorie);

if ($categorie === null) {
	$_SESSION['flash_error'] = 'Categorie introuvable.';
	header('Location: /Views/Admin/Categorie/gestion_categories.php');
	exit;
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nomCategorie = trim((string) ($_POST['categorie'] ?? ''));
	$description = trim((string) ($_POST['description'] ?? ''));

	if ($nomCategorie === '') {
		$errorMessage = 'Le nom de la categorie est requis.';
	} else {
		try {
			$updatedCategorie = new Categorie($nomCategorie, $description);
			$updatedCategorie->setIdCategorie($idCategorie);

			$isUpdated = $categorieController->updateCategorie($idCategorie, $updatedCategorie);

			if (!$isUpdated) {
				throw new RuntimeException('Impossible de mettre a jour la categorie.');
			}

			$_SESSION['flash_success'] = 'Categorie modifiee avec succes.';
			header('Location: /Views/Admin/Categorie/gestion_categories.php');
			exit;
		} catch (\Throwable $e) {
			$errorMessage = 'Erreur lors de la modification de la categorie: ' . $e->getMessage();
		}
	}
}

$formCategorie = ($_SERVER['REQUEST_METHOD'] === 'POST')
	? trim((string) ($_POST['categorie'] ?? ''))
	: $categorie->getCategorie();

$formDescription = ($_SERVER['REQUEST_METHOD'] === 'POST')
	? trim((string) ($_POST['description'] ?? ''))
	: $categorie->getDescription();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Edition d'une categorie d'articles.">
	<title>Editer Categorie</title>
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
				<h2>Editer categorie</h2>
				<p class="welcome">Mettez a jour le nom et la description de votre categorie.</p>
			</header>

			<?php if ($errorMessage !== ''): ?>
				<div class="notice error"><?php echo escape($errorMessage); ?></div>
			<?php endif; ?>

			<?php if ($successMessage !== ''): ?>
				<div class="notice success"><?php echo escape($successMessage); ?></div>
			<?php endif; ?>

			<section class="card category-form-card">
				<form action="editer.php?idCat=<?php echo (int) $idCategorie; ?>" method="post" class="category-form">
					<div>
						<label for="categorie">Nom de la categorie</label>
						<input
							type="text"
							id="categorie"
							name="categorie"
							placeholder="Nom de la categorie"
							value="<?php echo escape($formCategorie); ?>"
							required
						>
					</div>

					<div>
						<label for="description">Description</label>
						<textarea
							id="description"
							name="description"
							placeholder="Description"
						><?php echo escape($formDescription); ?></textarea>
					</div>

					<div class="actions-row">
						<button type="submit" class="action-link">Mettre a jour la categorie</button>
						<a href="gestion_categories.php" class="ghost-link">Annuler</a>
					</div>
				</form>
			</section>
		</main>
	</div>
</body>
</html>
