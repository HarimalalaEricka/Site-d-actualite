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

$currentPage = (int) ($categoryData['page'] ?? 1);
$totalPages = (int) ($categoryData['totalPages'] ?? 1);
$basePath = '/' . $lang . '/' . (string) ($category['slug'] ?? '');
$canonicalPath = $currentPage > 1 ? $basePath . '?page=' . $currentPage : $basePath;
$pageTitle = (string) $category['categorie'] . ($currentPage > 1 ? ' - Page ' . $currentPage : '');
$metaDescription = 'Articles de la categorie ' . (string) $category['categorie'] . '. Total: ' . (string) ((int) ($categoryData['total'] ?? 0));
?>
<!doctype html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?php echo e($canonicalPath); ?>">
    <link rel="stylesheet" href="/assets/css/Front/category.css">
    <?php if ($currentPage > 1): ?>
        <link rel="prev" href="<?php echo e($currentPage === 2 ? $basePath : ($basePath . '?page=' . ($currentPage - 1))); ?>">
    <?php endif; ?>
    <?php if ($currentPage < $totalPages): ?>
        <link rel="next" href="<?php echo e($basePath . '?page=' . ($currentPage + 1)); ?>">
    <?php endif; ?>
    <title><?php echo e($pageTitle); ?> | Site d'actualite</title>
</head>
<body>
    <main>
        <nav aria-label="Navigation principale">
            <a href="/<?= htmlspecialchars($lang) ?>">Accueil</a>
            <span> | </span>
            <a href="/<?= htmlspecialchars($lang) ?>/archives">Archives</a>
            <span> | </span>
            <a href="/admin.php">Acceder au BackOffice</a>
            <span> | </span>
            <a href="/<?php echo e($lang); ?>/search">Rechercher des articles</a>
            <span> | </span>
            <span aria-label="Language switch">&#127760;</span>
            <?php if ($lang === 'fr'): ?>
                <strong>FR</strong> <span>/</span> <a href="<?php echo e(switchLangUrl($lang, 'en')); ?>">EN</a>
            <?php else: ?>
                <a href="<?php echo e(switchLangUrl($lang, 'fr')); ?>">FR</a> <span>/</span> <strong>EN</strong>
            <?php endif; ?>
        </nav>

        <h1><?php echo e((string) $category['categorie']); ?></h1>

        <?php if (empty($categoryData['articles'])): ?>
            <p>Aucun article publie dans cette categorie.</p>
        <?php else: ?>
            <p class="category-info">Total: <?= $categoryData['total'] ?> article<?= $categoryData['total'] > 1 ? 's' : '' ?></p>
            
            <ul>
                <?php foreach ($categoryData['articles'] as $article): ?>
                    <li>
                        <a href="/<?php echo e($lang); ?>/<?php echo e((string) $article['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $article['date_publication']))); ?>/<?php echo e((string) $article['Id_Article']); ?>-<?php echo e((string) $article['slug']); ?>.html">
                            <?php echo e((string) $article['titre']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Pagination -->
            <?php if ($categoryData['totalPages'] > 1): ?>
                <nav class="pagination">
                    <?php if ($categoryData['page'] > 1): ?>
                        <a href="?page=<?= $categoryData['page'] - 1 ?>">← Précédent</a>
                    <?php endif; ?>

                    <span class="page-info">
                        Page <?= $categoryData['page'] ?>/<?= $categoryData['totalPages'] ?>
                    </span>

                    <?php if ($categoryData['page'] < $categoryData['totalPages']): ?>
                        <a href="?page=<?= $categoryData['page'] + 1 ?>">Suivant →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        <a href="/<?= htmlspecialchars($lang) ?>">Accueil</a>
        <span> | </span>
        <a href="/<?php echo e($lang); ?>/archives">Archives</a>
        <span> | </span>
        <a href="/<?php echo e($lang); ?>/search">Recherche</a>
        <span> | </span>
        <a href="/admin.php">BackOffice</a>
    </footer>
</body>
</html>
