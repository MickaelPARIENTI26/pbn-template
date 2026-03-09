<?php
// Compression GZIP (seulement si pas déjà actif via index.php)
if (ob_get_level() === 0) {
    if (!ob_start("ob_gzhandler")) {
        ob_start();
    }
}

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

    <!-- Bootstrap CSS (async loading) -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></noscript>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Variables CSS injectées -->
    <style>
        :root {
            --primary: <?= COLOR_PRIMARY ?>;
            --primary-light: <?= COLOR_PRIMARY_LIGHT ?>;
            --accent: <?= COLOR_ACCENT ?>;
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg sticky-top" role="navigation" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand" href="<?= url() ?>"><?= escape(SITE_LOGO_TEXT) ?><span style="color: var(--accent);" aria-hidden="true">.</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Ouvrir le menu de navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="<?= url() ?>">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('articles') ?>" aria-current="page">Articles</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- BANNIÈRE -->
    <div class="site-banner" role="banner"><?= escape(SITE_NICHE) ?> — Tous nos articles</div>

    <main role="main" id="main-content">
    <!-- LISTE DES ARTICLES -->
    <section class="container mt-5 mb-5" aria-labelledby="page-title">
        <h1 class="mb-4" style="font-family: var(--font-display);" id="page-title">Tous les articles</h1>

        <?php if (!empty($articles)): ?>
        <div class="row g-4">
            <?php foreach ($articles as $index => $article): ?>
            <div class="col-sm-6 col-md-4">
                <a href="<?= url(escape($article['slug'])) ?>" class="text-decoration-none" aria-label="Lire l'article : <?= escape($article['titre']) ?>">
                    <article class="article-card fade-up delay-<?= ($index % 4) + 1 ?>">
                        <div class="card-img-wrap position-relative">
                            <span class="card-category-badge"><?= escape($article['categorie']) ?></span>
                            <img src="<?= escape($article['image']) ?>" alt="" loading="lazy">
                        </div>
                        <div class="p-3">
                            <div class="text-muted small mb-2"><?= formatDate($article['date_publication']) ?></div>
                            <h2 class="card-title text-dark"><?= escape($article['titre']) ?></h2>
                            <p class="card-excerpt mt-2"><?= escape(excerpt($article['contenu_html'], 100)) ?></p>
                        </div>
                        <div class="px-3 pb-3 mt-auto d-flex justify-content-between align-items-center">
                            <span class="btn-read-more small">Lire <span aria-hidden="true">→</span></span>
                            <span class="text-muted small"><?= (int)$article['read_time'] ?> min</span>
                        </div>
                    </article>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">Aucun article pour le moment.</p>
        <?php endif; ?>
    </section>
    </main>

    <!-- FOOTER -->
    <footer class="pt-5 pb-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-brand">
                        <a class="navbar-brand" href="<?= url() ?>"><?= escape(SITE_LOGO_TEXT) ?><span style="color: var(--accent);">.</span></a>
                    </div>
                    <p class="mt-3 small"><?= escape(SITE_FOOTER_DESC) ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <p class="footer-heading">Navigation</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url() ?>">Accueil</a></li>
                        <li class="mb-2"><a href="<?= url('articles') ?>">Articles</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <p class="footer-heading">Légal</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url('mentions-legales') ?>">Mentions légales</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom pt-4 mt-4 text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= escape(SITE_NAME) ?> — <?= escape(SITE_DOMAIN) ?></p>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script defer src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script defer src="/assets/js/main.js"></script>

</body>
</html>
