<?php

declare(strict_types=1);

$featured = is_array($homeData['featured'] ?? null) ? $homeData['featured'] : null;
$latest = is_array($homeData['latest'] ?? null) ? $homeData['latest'] : [];
$popular = is_array($homeData['popular'] ?? null) ? $homeData['popular'] : [];
$categories = is_array($homeData['categories'] ?? null) ? $homeData['categories'] : [];

$pageTitle = $lang === 'en' ? 'Home' : 'Accueil';
$metaDescription = $lang === 'en'
    ? 'Latest news and analysis: featured stories, categories, and most-read articles.'
    : 'Actualites recentes, articles a la une, categories et contenus les plus lus.';
$canonicalPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ('/' . $lang)), PHP_URL_PATH);
if ($canonicalPath === '') {
    $canonicalPath = '/' . $lang;
}

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
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e($pageTitle); ?> | Site d'actualite">
    <meta property="og:description" content="<?php echo e($metaDescription); ?>">
    <link rel="canonical" href="<?php echo e($canonicalPath); ?>">
    <link rel="stylesheet" href="/assets/css/Front/home.css">
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

        <h1>Actualites</h1>

        <?php if ($featured !== null): ?>
            <section class="news-grid popular-news">
                <h2>A la une</h2>
                <article class="featured-article">
                    <?php if (!empty($featured['image_url'])): ?>
                        <img src="<?php echo e($featured['image_url']); ?>" alt="Image principale" style="width:100%;max-width:700px;height:auto;display:block;margin:0 auto 16px auto;border-radius:8px;object-fit:cover;">
                    <?php endif; ?>
                    <h3>
                        <a href="/<?php echo e($lang); ?>/<?php echo e((string) $featured['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $featured['date_publication']))); ?>/<?php echo e((string) $featured['Id_Article']); ?>_<?php echo e((string) $featured['slug']); ?>.html">
                            <?php echo e((string) $featured['titre']); ?>
                        </a>
                    </h3>
                    <div class="excerpt">
                        <?php echo isset($featured['contenu']) ? mb_substr(strip_tags((string) $featured['contenu']), 0, 120) . '...' : ''; ?>
                    </div>
                </article>
            </section>
        <?php endif; ?>


        <section class="news-grid latest-news">
            <h2>Dernieres actualites</h2>
            <?php if ($latest === []): ?>
                <p>Aucun article publie pour le moment.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($latest as $item): ?>
                        <li>
                            <a href="/<?php echo e($lang); ?>/<?php echo e((string) $item['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $item['date_publication']))); ?>/<?php echo e((string) $item['Id_Article']); ?>_<?php echo e((string) $item['slug']); ?>.html">
                                <?php echo e((string) $item['titre']); ?>
                            </a>
                            <div class="excerpt" style="display:flex;align-items:flex-start;gap:10px;">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo e($item['image_url']); ?>" alt="Image actu" style="width:70px;height:45px;object-fit:cover;border-radius:4px;">
                                <?php endif; ?>
                                <span>
                                    <?php echo isset($item['contenu']) ? mb_substr(strip_tags((string) $item['contenu']), 0, 120) . '...' : ''; ?>
                                </span>
                            </div>
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
                            <a href="/<?php echo e($lang); ?>/<?php echo e((string) $item['category_slug']); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) $item['date_publication']))); ?>/<?php echo e((string) $item['Id_Article']); ?>_<?php echo e((string) $item['slug']); ?>.html">
                                <?php echo e((string) $item['titre']); ?>
                            </a>
                            <span>(<?php echo e((string) ((int) ($item['nbr_vues'] ?? 0))); ?> vues)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if ($categories !== []): ?>
            <section class="categories-list">
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
