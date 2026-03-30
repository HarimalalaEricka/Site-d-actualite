<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="<?php echo e((string) $article['lang']); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo e(substr(strip_tags((string) $article['contenu']), 0, 160)); ?>">
    <link rel="canonical" href="<?php echo e($canonicalPath); ?>">
    <title><?php echo e((string) $article['titre']); ?> | Site d'actualite</title>
</head>
<body>
    <main>
        <nav>
            <a href="/<?php echo e((string) $article['lang']); ?>">Accueil</a>
            <span>/</span>
            <a href="/<?php echo e((string) $article['lang']); ?>/<?php echo e((string) $article['category_slug']); ?>"><?php echo e((string) $article['categorie']); ?></a>
        </nav>

        <article>
            <h1><?php echo e((string) $article['titre']); ?></h1>
            <p>
                Par <?php echo e(trim((string) $article['prenom'] . ' ' . (string) $article['nom'])); ?>
                - <?php echo e((string) $article['date_publication']); ?>
            </p>

            <?php if (isset($article['image_url']) && (string) $article['image_url'] !== ''): ?>
                <img src="<?php echo e((string) $article['image_url']); ?>" alt="Image principale de l'article">
            <?php endif; ?>

            <section>
                <?php echo (string) $article['contenu']; ?>
            </section>
        </article>
    </main>
</body>
</html>
