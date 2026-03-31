<?php

declare(strict_types=1);

/** @var array $searchData */
/** @var string $lang */
/** @var string|null $query */
/** @var int|null $selectedCategoryId */
/** @var string|null $selectedTagSlug */
/** @var string|null $dateFrom */
/** @var string|null $dateTo */

function switchLangUrl(string $currentLang, string $targetLang): string
{
    $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? ('/' . $currentLang . '/search'));
    $path = (string) parse_url($currentUri, PHP_URL_PATH);
    $query = (string) parse_url($currentUri, PHP_URL_QUERY);

    $targetPath = preg_replace('#^/' . preg_quote($currentLang, '#') . '(?=/|$)#', '/' . $targetLang, $path);
    if (!is_string($targetPath) || $targetPath === '') {
        $targetPath = '/' . $targetLang . '/search';
    }

    return $query !== '' ? $targetPath . '?' . $query : $targetPath;
}

$page = (int) ($searchData['page'] ?? 1);
$totalPages = (int) ($searchData['totalPages'] ?? 1);
$hasFilters = ($query !== null && trim($query) !== '')
    || $selectedCategoryId !== null
    || ($selectedTagSlug !== null && $selectedTagSlug !== '')
    || ($dateFrom !== null && $dateFrom !== '')
    || ($dateTo !== null && $dateTo !== '');

$titleParts = ['Recherche'];
if ($query !== null && trim($query) !== '') {
    $titleParts[] = '"' . trim($query) . '"';
}
if ($page > 1) {
    $titleParts[] = 'Page ' . $page;
}
$pageTitle = implode(' - ', $titleParts);

$metaDescription = $hasFilters
    ? 'Resultats de recherche filtres par mots-cles, categorie, tag et date.'
    : 'Recherchez parmi nos actualites par titre, contenu, categorie, tag ou date.';

$queryParams = [];
if ($query !== null && trim($query) !== '') {
    $queryParams['q'] = trim($query);
}
if ($selectedCategoryId !== null) {
    $queryParams['categorie'] = (string) $selectedCategoryId;
}
if ($selectedTagSlug !== null && $selectedTagSlug !== '') {
    $queryParams['tag'] = $selectedTagSlug;
}
if ($dateFrom !== null && $dateFrom !== '') {
    $queryParams['date_from'] = $dateFrom;
}
if ($dateTo !== null && $dateTo !== '') {
    $queryParams['date_to'] = $dateTo;
}
if ($page > 1) {
    $queryParams['page'] = (string) $page;
}

$canonicalPath = '/' . $lang . '/search';
if ($queryParams !== []) {
    $canonicalPath .= '?' . http_build_query($queryParams);
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Site d'actualite</title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="robots" content="<?= $hasFilters ? 'noindex,follow' : 'index,follow' ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalPath) ?>">
    <link rel="stylesheet" href="/assets/css/Front/search.css">
    <?php if ($totalPages > 1 && $page > 1): ?>
        <?php $prevParams = $queryParams; unset($prevParams['page']); if ($page > 2) { $prevParams['page'] = (string) ($page - 1); } ?>
        <link rel="prev" href="/<?= htmlspecialchars($lang) ?>/search<?= $prevParams !== [] ? '?' . htmlspecialchars(http_build_query($prevParams)) : '' ?>">
    <?php endif; ?>
    <?php if ($totalPages > 1 && $page < $totalPages): ?>
        <?php $nextParams = $queryParams; $nextParams['page'] = (string) ($page + 1); ?>
        <link rel="next" href="/<?= htmlspecialchars($lang) ?>/search?<?= htmlspecialchars(http_build_query($nextParams)) ?>">
    <?php endif; ?>
</head>
<body>
    <header>
        <nav>
            <a href="/<?= htmlspecialchars($lang) ?>">Accueil</a>
            <span> | </span>
            <a href="/<?= htmlspecialchars($lang) ?>/archives">Archives</a>
            <span> | </span>
            <a href="/admin.php">Acceder au BackOffice</a>
            <span> | </span>
            
            <span aria-label="Language switch">&#127760;</span>
            <?php if ($lang === 'fr'): ?>
                <strong>FR</strong> <span>/</span> <a href="<?= htmlspecialchars(switchLangUrl($lang, 'en')) ?>">EN</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars(switchLangUrl($lang, 'fr')) ?>">FR</a> <span>/</span> <strong>EN</strong>
            <?php endif; ?>
            
        </nav>
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

</body>
</html>
