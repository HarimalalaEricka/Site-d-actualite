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

            <?php if (is_array($article['collaborations'] ?? null) && $article['collaborations'] !== []): ?>
                <p>
                    Avec:
                    <?php foreach ($article['collaborations'] as $index => $collaborator): ?>
                        <?php if ($index > 0): ?>, <?php endif; ?>
                        <?php echo e(trim((string) (($collaborator['prenom'] ?? '') . ' ' . ($collaborator['nom'] ?? '')))); ?>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <?php if (isset($article['image_url']) && (string) $article['image_url'] !== ''): ?>
                <img src="<?php echo e((string) $article['image_url']); ?>" alt="Image principale de l'article">
            <?php endif; ?>

            <section>
                <?php echo (string) $article['contenu']; ?>
            </section>

            <?php if (is_array($article['media_gallery'] ?? null) && $article['media_gallery'] !== []): ?>
                <section>
                    <h2>Galerie</h2>
                    <ul>
                        <?php foreach ($article['media_gallery'] as $media): ?>
                            <li>
                                <img src="<?php echo e((string) ($media['url'] ?? '')); ?>" alt="<?php echo e((string) (($media['description'] ?? '') !== '' ? $media['description'] : 'Media secondaire')); ?>">
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (is_array($article['tags'] ?? null) && $article['tags'] !== []): ?>
                <section>
                    <h2>Tags</h2>
                    <ul>
                        <?php foreach ($article['tags'] as $tag): ?>
                            <li>#<?php echo e((string) ($tag['nom'] ?? '')); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (is_array($article['similar_articles'] ?? null) && $article['similar_articles'] !== []): ?>
                <section>
                    <h2>Articles similaires</h2>
                    <ul>
                        <?php foreach ($article['similar_articles'] as $similar): ?>
                            <li>
                                <a href="/<?php echo e((string) ($article['lang'] ?? 'fr')); ?>/<?php echo e((string) ($similar['category_slug'] ?? 'actualite')); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) ($similar['date_publication'] ?? 'now')))); ?>/<?php echo e((string) ($similar['Id_Article'] ?? '0')); ?>-<?php echo e((string) ($similar['slug'] ?? 'article')); ?>.html">
                                    <?php echo e((string) ($similar['titre'] ?? 'Article')); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </article>
    </main>

    <footer>
        <a href="/<?php echo e((string) ($article['lang'] ?? 'fr')); ?>/archives">Archives</a>
    </footer>
</body>
</html>
