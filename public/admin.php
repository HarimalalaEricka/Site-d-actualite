<?php

declare(strict_types=1);

use App\Controllers\Admin\UserController;
use App\Controllers\Admin\RoleController;
use App\Models\SessionLogin;
use App\Models\Role;

require_once dirname(__DIR__) . '/app/Core/Database.php';
require_once dirname(__DIR__) . '/app/Models/User.php';
require_once dirname(__DIR__) . '/app/Models/SessionLogin.php';
require_once dirname(__DIR__) . '/app/Models/Role.php';
require_once dirname(__DIR__) . '/app/Controllers/Admin/UserController.php';
require_once dirname(__DIR__) . '/app/Controllers/Admin/RoleController.php';

session_start();

if (isset($_SESSION['login']) && $_SESSION['login']->getUserLoggedIn() === true) {
	header('Location: /Views/Admin/dashboard.php');
	exit;
}

$email = '';
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim((string) ($_POST['email'] ?? ''));
	$password = (string) ($_POST['password'] ?? '');

	if ($email === '' || $password === '') {
		$errorMessage = 'Veuillez renseigner votre email et votre mot de passe.';
	} else {
		try {
			$controller = new UserController();
			$idUser = $controller->checkLogin($email, $password);

            if( $idUser === null) {
                $isValidLogin = false;
            } else {
                $isValidLogin = true;
            }

			if ($isValidLogin) {
                $roleController = new RoleController();
                $role = $roleController->getRoleByIdUser($idUser);
                $login = new SessionLogin($idUser, true, $role->getRole());
				$_SESSION['login'] = $login;
				header('Location: /Views/Admin/dashboard.php');
				exit;
			} else {
				$errorMessage = 'Email ou mot de passe invalide.';
			}
		} catch (Throwable $e) {
			$errorMessage = 'Erreur interne: ' . $e->getMessage();
		}
	}
}

function escape(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Page de login d'un BackOffice d'un site d'actualite sur la guerre en Iran.">
	<title>Connexion Admin</title>
    <link rel="stylesheet" href="/assets/css/Admin/admin.css">
</head>
<body>
	<main class="card">
		<h1>Connexion admin</h1>
		<p>Utilise ton compte pour acceder au back-office.</p>

		<?php if ($errorMessage !== ''): ?>
			<div class="notice error"><?php echo escape($errorMessage); ?></div>
		<?php endif; ?>

		<?php if ($successMessage !== ''): ?>
			<div class="notice success"><?php echo escape($successMessage); ?></div>
		<?php endif; ?>

		<form method="post" action="admin.php" novalidate>
			<label for="email">Email</label>
			<input id="email" name="email" type="email" value="<?php echo escape($email); ?>" required>

			<label for="password">Mot de passe</label>
			<input id="password" name="password" type="password" required>

			<button type="submit">Se connecter</button>
		</form>
	</main>
</body>
</html>
