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
?>
<!doctype html>
<html lang="<?php echo e($lang); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Liste des articles de la categorie.">
    <title><?php echo e((string) $category['categorie']); ?> | Site d'actualite</title>
</head>
<body>
    <main>
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
        <a href="/<?php echo e($lang); ?>/archives">Archives</a>
    </footer>
</body>
</html>
