<?php

declare(strict_types=1);

$featured = is_array($homeData['featured'] ?? null) ? $homeData['featured'] : null;
$latest = is_array($homeData['latest'] ?? null) ? $homeData['latest'] : [];
$popular = is_array($homeData['popular'] ?? null) ? $homeData['popular'] : [];
$categories = is_array($homeData['categories'] ?? null) ? $homeData['categories'] : [];

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
    <meta name="description" content="Actualites recentes sur les conflits et la geopolitique.">
    <title>Accueil | Site d'actualite</title>
</head>
<body>
    <main>
        <nav>
            <a href="/admin.php">Acceder au BackOffice</a>
            <span> | </span>
            <a href="/<?php echo e($lang); ?>/search">Rechercher des articles</a>
        </nav>

        <h1>Actualites</h1>

        <?php if ($featured !== null): ?>
            <section>
                <h2>A la une</h2>
                <article>
                    <h3>
                        <a href="/<?php echo e($lang); ?>/<?php echo e((string) $featured['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $featured['date_publication']))); ?>/<?php echo e((string) $featured['Id_Article']); ?>-<?php echo e((string) $featured['slug']); ?>.html">
                            <?php echo e((string) $featured['titre']); ?>
                        </a>
                    </h3>
                </article>
            </section>
        <?php endif; ?>

        <section>
            <h2>Dernieres actualites</h2>
            <?php if ($latest === []): ?>
                <p>Aucun article publie pour le moment.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($latest as $item): ?>
                        <li>
                            <a href="/<?php echo e($lang); ?>/<?php echo e((string) $item['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $item['date_publication']))); ?>/<?php echo e((string) $item['Id_Article']); ?>-<?php echo e((string) $item['slug']); ?>.html">
                                <?php echo e((string) $item['titre']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <?php if ($popular !== []): ?>
            <section>
                <h2>Plus lus (derniere journee)</h2>
                <ul>
                    <?php foreach ($popular as $item): ?>
                        <li>
                            <a href="/<?php echo e($lang); ?>/<?php echo e((string) $item['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $item['date_publication']))); ?>/<?php echo e((string) $item['Id_Article']); ?>-<?php echo e((string) $item['slug']); ?>.html">
                                <?php echo e((string) $item['titre']); ?>
                            </a>
                            <span>(<?php echo e((string) ((int) ($item['nbr_vues'] ?? 0))); ?> vues)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if ($categories !== []): ?>
            <section>
                <h2>Categories</h2>
                <ul>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/<?php echo e($lang); ?>/<?php echo e((string) $cat['category_slug']); ?>">
                                <?php echo e((string) $cat['categorie']); ?>
                            </a>
                            <span>(<?php echo e((string) ((int) ($cat['article_count'] ?? 0))); ?> articles)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <a href="/<?php echo e($lang); ?>/archives">Archives</a>
    </footer>
</body>
</html>
