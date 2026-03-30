<?php

declare(strict_types=1);

/** @var array $searchData */
/** @var string $lang */
/** @var string|null $query */
/** @var int|null $selectedCategoryId */
/** @var string|null $selectedTagSlug */
/** @var string|null $dateFrom */
/** @var string|null $dateTo */
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - Actualités</title>
    <meta name="description" content="Recherchez parmi nos actualités par titre, contenu, catégorie, tag ou date">
    <link rel="canonical" href="/<?= htmlspecialchars($lang) ?>/search">
</head>
<body>
    <header>
        <h1>Recherche d'actualités</h1>
    </header>

    <main>
        <section class="search-form">
            <form method="GET" action="/<?= htmlspecialchars($lang) ?>/search">
                <!-- Recherche fulltext -->
                <fieldset>
                    <legend>Recherche textuelle</legend>
                    <div class="field">
                        <label for="query">Chercher par titre, contenu ou auteur:</label>
                        <input
                            type="text"
                            id="query"
                            name="q"
                            value="<?= htmlspecialchars($query ?? '') ?>"
                            placeholder="Ex: conflit régional, Jean Dupont, #iran..."
                            maxlength="200"
                            autofocus
                        >
                        <small>Exemples: <code>Jean Dupont</code>, <code>conflit #iran</code>, <code>#iran #usa</code>, <code>+economie -greve "marche libre"</code></small>
                    </div>
                </fieldset>

                <!-- Filtres -->
                <fieldset>
                    <legend>Filtres</legend>

                    <!-- Catégorie -->
                    <div class="field">
                        <label for="categorie">Catégorie:</label>
                        <select id="categorie" name="categorie">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($searchData['categories'] as $cat): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $cat['Id_Categorie']) ?>"
                                    <?= $selectedCategoryId === (int)$cat['Id_Categorie'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($cat['categorie']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date min/max -->
                    <div class="field">
                        <label for="date_from">Après:</label>
                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            value="<?= htmlspecialchars($dateFrom ?? '') ?>"
                        >
                    </div>

                    <div class="field">
                        <label for="date_to">Avant:</label>
                        <input
                            type="date"
                            id="date_to"
                            name="date_to"
                            value="<?= htmlspecialchars($dateTo ?? '') ?>"
                        >
                    </div>

                    <button type="submit">Rechercher</button>
                    <a href="/<?= htmlspecialchars($lang) ?>/search">Réinitialiser filtres</a>
                </fieldset>
            </form>
        </section>

        <?php if (!empty($searchData['popularTags'])): ?>
            <section class="popular-tags">
                <h2>Tags populaires</h2>
                <p>
                    <?php foreach ($searchData['popularTags'] as $tag): ?>
                        <a href="/<?= htmlspecialchars($lang) ?>/search?q=%23<?= urlencode((string) ($tag['nom'] ?? '')) ?>">#<?= htmlspecialchars((string) ($tag['nom'] ?? '')) ?></a>
                    <?php endforeach; ?>
                </p>
            </section>
        <?php endif; ?>

        <!-- Résultats -->
        <section class="search-results">
            <?php if ($query || $selectedCategoryId || $selectedTagSlug || $dateFrom || $dateTo): ?>
                <h2>Résultats (<?= $searchData['total'] ?> article<?= $searchData['total'] > 1 ? 's' : '' ?>)</h2>

                <?php if (empty($searchData['articles'])): ?>
                    <p class="empty-state">
                        Aucun article ne correspond à votre recherche. Essayez d'ajuster vos filtres.
                    </p>
                <?php else: ?>
                    <div class="articles-list">
                        <?php foreach ($searchData['articles'] as $article): ?>
                            <article class="article-card">
                                <h3>
                                    <a href="/<?= htmlspecialchars($lang) ?>/<?= htmlspecialchars($article['category_slug']) ?>/article/<?= date('Y/m/d', strtotime($article['date_publication'])) ?>/<?= htmlspecialchars((string) $article['Id_Article']) ?>-<?= htmlspecialchars($article['slug']) ?>.html">
                                        <?= htmlspecialchars($article['titre']) ?>
                                    </a>
                                </h3>

                                <p class="article-meta">
                                    Par <strong><?= htmlspecialchars($article['author_name']) ?></strong>
                                    • <?= date('d/m/Y', strtotime($article['date_publication'])) ?>
                                    • <?= $article['nbr_vues'] ?> vue<?= $article['nbr_vues'] > 1 ? 's' : '' ?>
                                    <?php if ($article['relevance'] > 0): ?>
                                        • Pertinence: <?= number_format($article['relevance'], 2) ?>
                                    <?php endif; ?>
                                </p>

                                <p class="article-resume">
                                    <?= htmlspecialchars(substr($article['resume'], 0, 200)) ?><?= strlen($article['resume']) > 200 ? '...' : '' ?>
                                </p>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($searchData['totalPages'] > 1): ?>
                        <nav class="pagination">
                            <?php if ($searchData['page'] > 1): ?>
                                <a href="?q=<?= htmlspecialchars($_GET['q'] ?? '') ?>&categorie=<?= htmlspecialchars($_GET['categorie'] ?? '') ?>&tag=<?= htmlspecialchars($_GET['tag'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>&page=<?= $searchData['page'] - 1 ?>">← Précédent</a>
                            <?php endif; ?>

                            <span class="page-info">
                                Page <?= $searchData['page'] ?>/<?= $searchData['totalPages'] ?>
                            </span>

                            <?php if ($searchData['page'] < $searchData['totalPages']): ?>
                                <a href="?q=<?= htmlspecialchars($_GET['q'] ?? '') ?>&categorie=<?= htmlspecialchars($_GET['categorie'] ?? '') ?>&tag=<?= htmlspecialchars($_GET['tag'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>&page=<?= $searchData['page'] + 1 ?>">Suivant →</a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <p class="help-text">
                    Utilisez la barre de recherche pour trouver des actualités par titre, contenu ou auteur.
                    Vous pouvez aussi filtrer par catégorie, tag, ou plage de dates.
                </p>
            <?php endif; ?>
        </section>

        <footer>
            <a href="/<?= htmlspecialchars($lang) ?>">Accueil</a> •
            <a href="/<?= htmlspecialchars($lang) ?>/archives">Archives</a>
        </footer>
    </main>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 1rem;
            background-color: #f9f9f9;
            color: #333;
        }

        header {
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2rem;
            margin: 0 0 1rem;
        }

        h2 {
            font-size: 1.5rem;
            margin-top: 2rem;
        }

        fieldset {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #fff;
        }

        legend {
            font-weight: bold;
            padding: 0 0.5rem;
        }

        .field {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            max-width: 300px;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        button {
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .empty-state {
            padding: 2rem;
            background-color: #fff;
            border-radius: 4px;
            text-align: center;
            color: #666;
        }

        .help-text {
            padding: 2rem;
            background-color: #e3f2fd;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .articles-list {
            display: grid;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .article-card {
            padding: 1.5rem;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .article-card h3 {
            margin: 0 0 0.5rem;
            font-size: 1.25rem;
        }

        .article-card a {
            text-decoration: none;
        }

        .article-card a:hover {
            text-decoration: underline;
        }

        .article-meta {
            font-size: 0.9rem;
            color: #666;
            margin: 0 0 1rem;
        }

        .article-resume {
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            align-items: center;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            background-color: #f0f0f0;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #e0e0e0;
        }

        .page-info {
            color: #666;
            font-size: 0.9rem;
        }

        small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.85rem;
        }

        code {
            background-color: #f5f5f5;
            padding: 0.1rem 0.3rem;
            border-radius: 3px;
            font-family: monospace;
        }

        footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }

            input[type="text"],
            input[type="date"],
            select {
                max-width: 100%;
            }

            .articles-list {
                gap: 1rem;
            }

            .article-card {
                padding: 1rem;
            }
        }
    </style>
</body>
</html>
