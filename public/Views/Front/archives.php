<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function switchLangUrl(string $currentLang, string $targetLang): string
{
    $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? ('/' . $currentLang));
    $path = (string) parse_url($currentUri, PHP_URL_PATH);
    $query = (string) parse_url($currentUri, PHP_URL_QUERY);

    $targetPath = preg_replace('#^/' . preg_quote($currentLang, '#') . '(?=/|$)#', '/' . $targetLang, $path);
    if (!is_string($targetPath) || $targetPath === '') {
        $targetPath = '/' . $targetLang;
    }

    return $query !== '' ? $targetPath . '?' . $query : $targetPath;
}

$title = 'Archives';
if ($year !== null && $month !== null) {
    $title = sprintf('Archives %04d/%02d', $year, $month);
} elseif ($year !== null) {
    $title = sprintf('Archives %04d', $year);
}

$basePath = '/' . $lang . '/archives';
if ($year !== null) {
    $basePath .= '/' . $year;
}
if ($month !== null) {
    $basePath .= '/' . str_pad((string) $month, 2, '0', STR_PAD_LEFT);
}

$queryPrefix = '';
if (isset($selectedCategorySlug) && is_string($selectedCategorySlug) && $selectedCategorySlug !== '') {
    $queryPrefix = 'categorie=' . urlencode($selectedCategorySlug) . '&';
}

$canonicalPath = $basePath;
if ($queryPrefix !== '') {
    $canonicalPath .= '?' . rtrim($queryPrefix, '&');
}
if ($page > 1) {
    $canonicalPath .= ($queryPrefix !== '' ? '&' : '?') . 'page=' . $page;
}

$metaDescription = 'Consultez les archives des articles publies par mois et categorie.';
if ($year !== null && $month !== null) {
    $metaDescription = sprintf('Archives %04d/%02d des actualites publiees.', $year, $month);
} elseif ($year !== null) {
    $metaDescription = sprintf('Archives %04d des actualites publiees.', $year);
}
?>
<!doctype html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?php echo e($canonicalPath); ?>">
    <?php if ($totalPages > 1 && $page > 1): ?>
        <?php $prevPagePath = $basePath . '?' . $queryPrefix . 'page=' . ($page - 1); ?>
        <link rel="prev" href="<?php echo e($prevPagePath); ?>">
    <?php endif; ?>
    <?php if ($totalPages > 1 && $page < $totalPages): ?>
        <?php $nextPagePath = $basePath . '?' . $queryPrefix . 'page=' . ($page + 1); ?>
        <link rel="next" href="<?php echo e($nextPagePath); ?>">
    <?php endif; ?>
    <title><?php echo e($title); ?> | Site d'actualite</title>
</head>
<body>
    <main>
        <h1><?php echo e($title); ?></h1>

        <nav>
            <a href="/<?php echo e($lang); ?>">Accueil</a>
            <span> | </span>
            <a href="/<?php echo e($lang); ?>/search">Recherche</a>
            <span> | </span>
            <span aria-label="Language switch">&#127760;</span>
            <?php if ($lang === 'fr'): ?>
                <strong>FR</strong> <span>/</span> <a href="<?php echo e(switchLangUrl($lang, 'en')); ?>">EN</a>
            <?php else: ?>
                <a href="<?php echo e(switchLangUrl($lang, 'fr')); ?>">FR</a> <span>/</span> <strong>EN</strong>
            <?php endif; ?>
        </nav>

        <?php if (is_array($categories) && $categories !== []): ?>
            <section>
                <h2>Filtrer par categorie</h2>
                <ul>
                    <li>
                        <a href="<?php echo e($basePath); ?>">Toutes</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?php echo e($basePath); ?>?categorie=<?php echo e((string) ($category['category_slug'] ?? '')); ?>">
                                <?php echo e((string) ($category['categorie'] ?? 'Categorie')); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (is_array($availableMonths) && $availableMonths !== []): ?>
            <section>
                <h2>Mois disponibles</h2>
                <ul>
                    <?php foreach ($availableMonths as $item): ?>
                        <li>
                            <a href="/<?php echo e($lang); ?>/archives/<?php echo e((string) ($item['year'] ?? '0')); ?>/<?php echo e(str_pad((string) ($item['month'] ?? '0'), 2, '0', STR_PAD_LEFT)); ?>">
                                <?php echo e((string) ($item['year'] ?? '0')); ?>/<?php echo e(str_pad((string) ($item['month'] ?? '0'), 2, '0', STR_PAD_LEFT)); ?>
                            </a>
                            <span>(<?php echo e((string) ((int) ($item['total'] ?? 0))); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <section>
            <h2>Articles</h2>
            <?php if (!is_array($articles) || $articles === []): ?>
                <p>Aucun article publie pour ce filtre.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($articles as $articleItem): ?>
                        <li>
                            <a href="/<?php echo e((string) ($articleItem['lang'] ?? $lang)); ?>/<?php echo e((string) ($articleItem['category_slug'] ?? 'actualite')); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) ($articleItem['date_publication'] ?? 'now')))); ?>/<?php echo e((string) ($articleItem['Id_Article'] ?? '0')); ?>-<?php echo e((string) ($articleItem['slug'] ?? 'article')); ?>.html">
                                <?php echo e((string) ($articleItem['titre'] ?? 'Article')); ?>
                            </a>
                            <small>
                                - <?php echo e((string) ($articleItem['date_publication'] ?? '')); ?>
                                - <?php echo e(trim((string) (($articleItem['prenom'] ?? '') . ' ' . ($articleItem['nom'] ?? '')))); ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <?php if ($totalPages > 1): ?>
            <nav>
                <?php if ($page > 1): ?>
                    <a href="<?php echo e($basePath); ?>?<?php echo e($queryPrefix); ?>page=<?php echo e((string) ($page - 1)); ?>">Page precedente</a>
                <?php endif; ?>

                <span>Page <?php echo e((string) $page); ?> / <?php echo e((string) $totalPages); ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo e($basePath); ?>?<?php echo e($queryPrefix); ?>page=<?php echo e((string) ($page + 1)); ?>">Page suivante</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </main>

    <footer>
        <a href="/<?php echo e($lang); ?>/archives">Archives</a>
    </footer>
</body>
</html>
