<?php

declare(strict_types=1);
use App\Models\SessionLogin;
require_once dirname(__DIR__, 3) . '/app/Models/SessionLogin.php';

session_start();

if (!isset($_SESSION['login']) || !($_SESSION['login'] instanceof SessionLogin) || $_SESSION['login']->getUserLoggedIn() !== true) {
    header('Location: /admin.php');
    exit;
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BackOffice d'un site d'actualite sur la guerre en Iran.">
    <title>Dashboard Admin</title>
    <style>
        :root {
            --bg: #f8fafc;
            --panel: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #0f766e;
            --primary-hover: #115e59;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 10% 10%, #dbeafe 0%, transparent 35%),
                radial-gradient(circle at 90% 20%, #ccfbf1 0%, transparent 30%),
                var(--bg);
            min-height: 100vh;
            padding: 24px;
        }

        .layout {
            max-width: 980px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .brand {
            margin: 0;
            font-size: 1.4rem;
        }

        .logout {
            text-decoration: none;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
        }

        .logout:hover {
            background: var(--primary-hover);
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 14px 36px rgba(2, 6, 23, 0.08);
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
        }

        .welcome {
            margin: 8px 0 0;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="layout">
        <header class="topbar">
            <h1 class="brand">Back-office Actualite</h1>
            <a class="logout" href="/logout.php">Se deconnecter</a>
        </header>

        <main class="card">
            <p class="subtitle">Dashboard admin</p>
            <p class="welcome">Bienvenue.</p>
        </main>
    </div>
</body>
</html>
