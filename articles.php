<?php
require_once __DIR__ . '/config.php';

// Configuration environnement
date_default_timezone_set(SITE_TIMEZONE);
if (SITE_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Headers HTTP sécurité et cache
header('Cache-Control: public, max-age=' . (int)SITE_CACHE_TTL);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

$pdo = getDB();

// Récupérer tous les articles publiés
$stmt = $pdo->prepare("
    SELECT * FROM articles
    WHERE statut = 'publie'
      AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
    ORDER BY date_publication DESC
");
$stmt->execute();
$articles = $stmt->fetchAll();

// Helper pour formater la date
if (!function_exists('formatDate')) {
    function formatDate($date) {
        $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $d = new DateTime($date);
        return $d->format('j') . ' ' . $months[$d->format('n') - 1] . ' ' . $d->format('Y');
    }
}

// Helper pour générer un extrait
if (!function_exists('excerpt')) {
    function excerpt($html, $length = 150) {
        $text = strip_tags($html);
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les articles — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="Découvrez tous nos articles sur <?= escape(SITE_NICHE) ?>">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <meta name="author" content="<?= escape(SITE_AUTHOR) ?>">
    <link rel="canonical" href="<?= SITE_URL ?>/articles">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="Tous les articles — <?= escape(SITE_NAME) ?>">
    <meta property="og:description" content="Découvrez tous nos articles sur <?= escape(SITE_NICHE) ?>">
    <meta property="og:url" content="<?= SITE_URL ?>/articles">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">
    <meta property="og:image" content="<?= SITE_URL ?>/<?= SITE_OG_IMAGE ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="<?= escape(SITE_TWITTER_HANDLE) ?>">
    <meta name="twitter:title" content="Tous les articles — <?= escape(SITE_NAME) ?>">
    <meta name="twitter:description" content="Découvrez tous nos articles sur <?= escape(SITE_NICHE) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?= escape(SITE_NAME) ?> RSS" href="<?= url('feed.xml') ?>">

    <!-- Preload Critical Resources -->
    <link rel="preload" href="/assets/css/style.css" as="style">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">

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
            <a href="/categorie/<?= categorie_slug($c['categorie']) ?>" class="nav-link"><?= escape($c['categorie']) ?></a>
            <?php endforeach; ?>
            <a href="<?= url('articles') ?>" class="nav-link nav-link-cta">Tous les articles</a>
        </div>
    </nav>

    <!-- BANNIÈRE -->
    <div class="site-banner" role="banner"><?= escape(SITE_NICHE) ?> — Tous nos articles</div>

    <main role="main" id="main-content">
    <!-- EN-TÊTE PAGE -->
    <section class="recents-section" style="padding-top: 60px;">
        <div class="recents-inner">
            <h1 class="section-title">Tous les articles</h1>
            <div class="section-divider"></div>
            <p style="text-align: center; color: var(--muted); margin-bottom: 40px;"><?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?> publiés</p>

            <?php if (!empty($articles)): ?>
            <div class="recents-grid">
                <?php foreach ($articles as $index => $article): ?>
                <a href="<?= url(escape($article['slug'])) ?>" class="article-card">
                    <div class="card-img-wrap">
                        <img src="/<?= escape($article['image']) ?>" alt="<?= escape($article['titre']) ?>" width="600" height="400" <?= $index < 3 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                    </div>
                    <div class="card-body">
                        <span class="badge-cat"><?= escape($article['categorie']) ?></span>
                        <h2 style="font-size: 1.1rem;"><?= escape($article['titre']) ?></h2>
                        <p class="card-excerpt"><?= escape(excerpt($article['contenu_html'], 100)) ?></p>
                        <div class="card-meta">
                            <span><?= formatDate($article['date_publication']) ?></span>
                            <span><?= (int)$article['read_time'] ?> min</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align: center; color: var(--muted); padding: 60px 0;">Aucun article pour le moment.</p>
            <?php endif; ?>
        </div>
    </section>
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
                <a href="/categorie/<?= categorie_slug($fc['categorie']) ?>"><?= escape($fc['categorie']) ?></a>
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
    <script src="/assets/js/main.js"></script>

</body>
</html>
