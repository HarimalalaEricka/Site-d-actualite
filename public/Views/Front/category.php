<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
        <h1><?php echo e((string) $category['categorie']); ?></h1>

        <?php if ($articles === []): ?>
            <p>Aucun article publie dans cette categorie.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($articles as $article): ?>
                    <li>
                        <a href="/<?php echo e($lang); ?>/<?php echo e((string) $article['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $article['date_publication']))); ?>/<?php echo e((string) $article['Id_Article']); ?>-<?php echo e((string) $article['slug']); ?>.html">
                            <?php echo e((string) $article['titre']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>

    <footer>
        <a href="/<?php echo e($lang); ?>/archives">Archives</a>
    </footer>
</body>
</html>
