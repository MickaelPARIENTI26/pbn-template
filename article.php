<?php
require_once __DIR__ . '/config.php';

// Configuration environnement
date_default_timezone_set(SITE_TIMEZONE);

// Headers HTTP sécurité et cache
header('Cache-Control: public, max-age=' . (int)SITE_CACHE_TTL);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

$pdo = getDB();

// Récupérer l'article par slug
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? AND statut='publie'");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    die('Article non trouvé');
}

// Articles similaires
$similaires = $pdo->prepare(
    "SELECT id, slug, titre, image, categorie, date_publication
     FROM articles
     WHERE categorie = ? AND slug != ? AND statut='publie'
     ORDER BY date_publication DESC LIMIT 4"
);
$similaires->execute([$article['categorie'], $article['slug']]);
$similaires = $similaires->fetchAll();

// Derniers articles
$derniers = $pdo->query(
    "SELECT id, slug, titre, image, categorie, date_publication
     FROM articles
     WHERE statut='publie'
     ORDER BY date_publication DESC LIMIT 4"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($article['titre']) ?> — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="<?= escape($article['meta_description']) ?>">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <meta name="author" content="<?= escape(SITE_AUTHOR) ?>">
    <link rel="canonical" href="<?= SITE_URL ?>/<?= escape($article['slug']) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="<?= escape($article['titre']) ?>">
    <meta property="og:description" content="<?= escape($article['meta_description']) ?>">
    <meta property="og:url" content="<?= SITE_URL ?>/<?= escape($article['slug']) ?>">
    <meta property="og:image" content="<?= SITE_URL . '/' . escape($article['image']) ?>">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">
    <meta property="article:published_time" content="<?= $article['date_publication'] ?>">
    <meta property="article:section" content="<?= escape($article['categorie']) ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?= escape(SITE_TWITTER_HANDLE) ?>">
    <meta name="twitter:title" content="<?= escape($article['titre']) ?>">
    <meta name="twitter:description" content="<?= escape($article['meta_description']) ?>">
    <meta name="twitter:image" content="<?= SITE_URL . '/' . escape($article['image']) ?>">

    <!-- JSON-LD BlogPosting Schema -->
    <?php
    $schema_article = [
        "@context" => "https://schema.org",
        "@type" => "BlogPosting",
        "headline" => $article['titre'],
        "description" => $article['meta_description'],
        "image" => url($article['image']),
        "datePublished" => date('c', strtotime($article['date_publication'])),
        "dateModified" => date('c', strtotime($article['date_publication'])),
        "author" => [
            "@type" => "Person",
            "name" => SITE_AUTHOR
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => SITE_NAME,
            "url" => SITE_URL
        ],
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => url($article['slug'])
        ],
        "articleSection" => $article['categorie'],
        "inLanguage" => "fr",
        "wordCount" => str_word_count(strip_tags($article['contenu_html']))
    ];
    ?>
    <script type="application/ld+json">
<?= json_encode($schema_article, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>

    <!-- JSON-LD BreadcrumbList Schema -->
    <?php
    $breadcrumb = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            ["@type" => "ListItem", "position" => 1, "name" => "Accueil", "item" => SITE_URL],
            ["@type" => "ListItem", "position" => 2, "name" => $article['categorie'], "item" => url('categorie') . '?cat=' . urlencode($article['categorie'])],
            ["@type" => "ListItem", "position" => 3, "name" => $article['titre'], "item" => url($article['slug'])]
        ]
    ];
    ?>
    <script type="application/ld+json">
<?= json_encode($breadcrumb, JSON_UNESCAPED_UNICODE) ?>
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?= escape(SITE_NAME) ?> RSS" href="<?= url('feed.xml') ?>">

    <!-- Preload Hero Image -->
    <link rel="preload" as="image" href="<?= escape($article['image']) ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Variables CSS injectées -->
    <style>
        :root {
            --color-primary: <?= COLOR_PRIMARY ?>;
            --color-primary-light: <?= COLOR_PRIMARY_LIGHT ?>;
            --color-accent: <?= COLOR_ACCENT ?>;
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <!-- NAVBAR -->
    <nav class="main-nav">
        <a href="<?= url() ?>" class="nav-brand"><?= escape(SITE_LOGO_TEXT) ?></a>
        <div class="nav-links">
            <a href="<?= url() ?>" class="nav-link">Accueil</a>
            <?php
            $cats = $pdo->query("SELECT DISTINCT categorie FROM articles WHERE statut='publie' ORDER BY categorie LIMIT 6")->fetchAll();
            foreach($cats as $c):
                $catSlug = urlencode($c['categorie']);
            ?>
            <a href="<?= url('categorie') ?>?cat=<?= $catSlug ?>" class="nav-link"><?= escape($c['categorie']) ?></a>
            <?php endforeach; ?>
            <a href="<?= url('articles') ?>" class="nav-link nav-link-cta">Tous les articles</a>
        </div>
    </nav>

    <!-- BREADCRUMB -->
    <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
        <a href="<?= url() ?>">Accueil</a>
        <span>›</span>
        <a href="<?= url('categorie') ?>?cat=<?= urlencode($article['categorie']) ?>"><?= escape($article['categorie']) ?></a>
        <span>›</span>
        <span><?= escape($article['titre']) ?></span>
    </nav>

    <main role="main" id="main-content">
        <!-- IMAGE HERO -->
        <div class="article-hero">
            <img src="<?= escape($article['image']) ?>" alt="<?= escape($article['titre']) ?>" width="1200" height="580" fetchpriority="high">
        </div>

        <!-- HEADER ARTICLE -->
        <div class="article-header">
            <span class="badge-cat"><?= escape($article['categorie']) ?></span>
            <h1><?= escape($article['titre']) ?></h1>
            <div class="article-meta">
                <span><?= date('d M Y', strtotime($article['date_publication'])) ?></span>
                <span>·</span>
                <span><?= $article['read_time'] ?> min de lecture</span>
                <span>·</span>
                <span><?= escape($article['categorie']) ?></span>
            </div>
            <div class="article-divider"></div>
        </div>

        <!-- LAYOUT 2 COLONNES -->
        <div class="article-layout">

            <!-- Contenu principal -->
            <div class="article-content">
                <?= $article['contenu_html'] ?>
            </div>

            <!-- Sidebar -->
            <aside class="article-sidebar">

                <?php if ($similaires): ?>
                <div class="sidebar-block">
                    <h4 class="sidebar-title">Articles similaires</h4>
                    <div class="sidebar-divider"></div>
                    <?php foreach($similaires as $s): ?>
                    <a href="<?= url(escape($s['slug'])) ?>" class="sidebar-card">
                        <img src="<?= escape($s['image']) ?>" alt="<?= escape($s['titre']) ?>" width="80" height="60" loading="lazy">
                        <div class="sidebar-card-body">
                            <span class="sidebar-cat"><?= escape($s['categorie']) ?></span>
                            <p><?= escape($s['titre']) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="sidebar-block">
                    <h4 class="sidebar-title">Derniers articles</h4>
                    <div class="sidebar-divider"></div>
                    <?php foreach($derniers as $d): ?>
                    <?php if ($d['slug'] === $article['slug']): continue; endif; ?>
                    <a href="<?= url(escape($d['slug'])) ?>" class="sidebar-card">
                        <img src="<?= escape($d['image']) ?>" alt="<?= escape($d['titre']) ?>" width="80" height="60" loading="lazy">
                        <div class="sidebar-card-body">
                            <span class="sidebar-cat"><?= escape($d['categorie']) ?></span>
                            <p><?= escape($d['titre']) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

            </aside>
        </div>
    </main>

    <!-- FOOTER -->
    <?php
    $footer_cats = $pdo->query(
        "SELECT DISTINCT categorie FROM articles WHERE statut='publie' LIMIT 5"
    )->fetchAll();
    ?>
    <footer class="site-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-brand"><?= escape(SITE_LOGO_TEXT) ?></div>
                <p><?= escape(SITE_FOOTER_DESC) ?></p>
            </div>
            <div class="footer-col">
                <p class="footer-heading">Navigation</p>
                <a href="<?= url() ?>">Accueil</a>
                <a href="<?= url('articles') ?>">Tous les articles</a>
            </div>
            <div class="footer-col">
                <p class="footer-heading">Catégories</p>
                <?php foreach($footer_cats as $fc): ?>
                <a href="<?= url('categorie') ?>?cat=<?= urlencode($fc['categorie']) ?>"><?= escape($fc['categorie']) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="footer-col">
                <p class="footer-heading">Légal</p>
                <a href="<?= url('mentions-legales') ?>">Mentions légales</a>
                <a href="<?= url('politique-confidentialite') ?>">Confidentialité</a>
                <a href="<?= url('cgu') ?>">CGU</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> <?= escape(SITE_NAME) ?> — <?= escape(SITE_DOMAIN) ?>
        </div>
    </footer>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

</body>
</html>
