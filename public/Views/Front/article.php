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

function buildVideoEmbedUrl(string $url): ?string
{
    $trimmed = trim($url);

    if (preg_match('#^https?://(?:www\.)?youtube\.com/watch\?v=([a-zA-Z0-9_-]{6,})#', $trimmed, $matches) === 1) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('#^https?://youtu\.be/([a-zA-Z0-9_-]{6,})#', $trimmed, $matches) === 1) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('#^https?://(?:www\.)?youtube\.com/embed/([a-zA-Z0-9_-]{6,})#', $trimmed, $matches) === 1) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('#^https?://(?:www\.)?vimeo\.com/(\d+)#', $trimmed, $matches) === 1) {
        return 'https://player.vimeo.com/video/' . $matches[1];
    }

    if (preg_match('#^https?://player\.vimeo\.com/video/(\d+)#', $trimmed, $matches) === 1) {
        return 'https://player.vimeo.com/video/' . $matches[1];
    }

    return null;
}

function buildAutoplayEmbedUrl(string $url): string
{
    $separator = str_contains($url, '?') ? '&' : '?';

    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url . $separator . 'autoplay=1&mute=1&playsinline=1&rel=0';
    }

    if (strpos($url, 'player.vimeo.com/video/') !== false) {
        return $url . $separator . 'autoplay=1&muted=1&playsinline=1';
    }

    return $url;
}

function videoMimeTypeFromUrl(string $url): string
{
    $path = (string) parse_url($url, PHP_URL_PATH);
    $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

    return match ($extension) {
        'webm' => 'video/webm',
        'ogg', 'ogv' => 'video/ogg',
        'm4v' => 'video/mp4',
        'mov' => 'video/quicktime',
        default => 'video/mp4',
    };
}

$articleTitle = (string) $article['titre'];
$articleDescription = trim(substr(strip_tags((string) $article['contenu']), 0, 160));
if ($articleDescription === '') {
    $articleDescription = 'Article d actualite et analyse.';
}

$articleAuthor = trim((string) $article['prenom'] . ' ' . (string) $article['nom']);
if ($articleAuthor === '') {
    $articleAuthor = 'Redaction';
}

$articleJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $articleTitle,
    'description' => $articleDescription,
    'datePublished' => (string) ($article['date_publication'] ?? ''),
    'inLanguage' => (string) ($article['lang'] ?? 'fr'),
    'author' => [
        '@type' => 'Person',
        'name' => $articleAuthor,
    ],
    'mainEntityOfPage' => $canonicalPath,
];
if (($article['primary_media_kind'] ?? 'image') === 'image' && isset($article['image_url']) && (string) $article['image_url'] !== '') {
    $articleJsonLd['image'] = [(string) $article['image_url']];
}
?>
<!doctype html>
<html lang="<?php echo e((string) $article['lang']); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo e($articleDescription); ?>">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo e($articleTitle); ?>">
    <meta property="og:description" content="<?php echo e($articleDescription); ?>">
    <meta property="og:url" content="<?php echo e($canonicalPath); ?>">
    <?php if (($article['primary_media_kind'] ?? 'image') === 'image' && isset($article['image_url']) && (string) $article['image_url'] !== ''): ?>
        <meta property="og:image" content="<?php echo e((string) $article['image_url']); ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?php echo e($canonicalPath); ?>">
    <link rel="stylesheet" href="/assets/css/Front/article.css">
    <title><?php echo e($articleTitle); ?> | Site d'actualite</title>
    <script type="application/ld+json"><?php echo json_encode($articleJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<script>
function unsetArticleView() {
    if (window.navigator.sendBeacon) {
        const data = new FormData();
        data.append('article_id', <?php echo json_encode($article['Id_Article']); ?>);
        navigator.sendBeacon('/unset_article_view.php', data);
    } else {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/unset_article_view.php', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('article_id=' + encodeURIComponent(<?php echo json_encode($article['Id_Article']); ?>));
    }
}

window.addEventListener('unload', function() {
    unsetArticleView();
});
</script>
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

        <article>
            <p>
                Par <?php echo e(trim((string) $article['prenom'] . ' ' . (string) $article['nom'])); ?>
                - <time datetime="<?php echo e((string) $article['date_publication']); ?>"><?php echo e((string) $article['date_publication']); ?></time>
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

            <?php if (isset($article['primary_media_url']) && (string) $article['primary_media_url'] !== ''): ?>
                <?php if (($article['primary_media_kind'] ?? 'image') === 'video'): ?>
                    <?php $primaryVideoUrl = (string) $article['primary_media_url']; ?>
                    <?php $primaryEmbedUrl = buildVideoEmbedUrl($primaryVideoUrl); ?>
                    <?php if ($primaryEmbedUrl !== null): ?>
                        <?php $primaryEmbedAutoplayUrl = buildAutoplayEmbedUrl($primaryEmbedUrl); ?>
                        <iframe
                            src="<?php echo e($primaryEmbedAutoplayUrl); ?>"
                            title="Video principale"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    <?php else: ?>
                        <video controls autoplay muted preload="metadata" playsinline>
                            <source src="<?php echo e($primaryVideoUrl); ?>" type="<?php echo e(videoMimeTypeFromUrl($primaryVideoUrl)); ?>">
                            Votre navigateur ne prend pas en charge la lecture video.
                        </video>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="<?php echo e((string) $article['primary_media_url']); ?>" alt="Image principale de l'article" decoding="async" fetchpriority="high">
                <?php endif; ?>
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
                                <?php $mediaUrl = (string) ($media['url'] ?? ''); ?>
                                <?php $mediaDescription = (string) (($media['description'] ?? '') !== '' ? $media['description'] : 'Media secondaire'); ?>
                                <?php if (($media['media_kind'] ?? 'image') === 'video'): ?>
                                    <?php $embedUrl = buildVideoEmbedUrl($mediaUrl); ?>
                                    <?php if ($embedUrl !== null): ?>
                                        <?php $embedAutoplayUrl = buildAutoplayEmbedUrl($embedUrl); ?>
                                        <iframe
                                            src="<?php echo e($embedAutoplayUrl); ?>"
                                            title="<?php echo e($mediaDescription); ?>"
                                            loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                        ></iframe>
                                    <?php else: ?>
                                        <video controls autoplay muted preload="metadata" playsinline>
                                            <source src="<?php echo e($mediaUrl); ?>" type="<?php echo e(videoMimeTypeFromUrl($mediaUrl)); ?>">
                                            Votre navigateur ne prend pas en charge la lecture video.
                                        </video>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <img src="<?php echo e($mediaUrl); ?>" alt="<?php echo e($mediaDescription); ?>" loading="lazy" decoding="async">
                                <?php endif; ?>
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
                            <li>
                                <a href="/<?php echo e((string) ($article['lang'] ?? 'fr')); ?>/search?q=%23<?php echo urlencode((string) ($tag['nom'] ?? '')); ?>">
                                    #<?php echo e((string) ($tag['nom'] ?? '')); ?>
                                </a>
                            </li>
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
                                <a href="/<?php echo e((string) ($article['lang'] ?? 'fr')); ?>/<?php echo e((string) ($similar['category_slug'] ?? 'actualite')); ?>/article/<?php echo e(date('Y/m/d', strtotime((string) ($similar['date_publication'] ?? 'now')))); ?>/<?php echo e((string) ($similar['Id_Article'] ?? '0')); ?>_<?php echo e((string) ($similar['slug'] ?? 'article')); ?>.html">
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
