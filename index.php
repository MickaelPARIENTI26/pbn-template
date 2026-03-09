<?php
// Compression GZIP
if (!ob_start("ob_gzhandler")) {
    ob_start();
}

// DEBUG TEMPORAIRE
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {

// ===== ROUTING PHP BUILT-IN SERVER =====
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$filepath = __DIR__ . '/' . $uri;

// Servir les fichiers statiques directement (PHP built-in server)
if ($uri !== '' && file_exists($filepath) && !is_dir($filepath)) {
    // Fichiers statiques : laisser le serveur les servir
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $uri)) {
        return false;
    }
    // Fichiers PHP autres que index.php
    if (str_ends_with($uri, '.php')) {
        return false;
    }
}

require_once __DIR__ . '/config.php';

// Configuration environnement
date_default_timezone_set(SITE_TIMEZONE);

// Headers HTTP sécurité et cache
header('Cache-Control: public, max-age=' . (int)SITE_CACHE_TTL);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

if ($uri !== '' && $uri !== 'index.php') {
    if ($uri === 'mentions-legales') {
        require __DIR__ . '/mentions-legales.php';
        exit;
    }
    if ($uri === 'articles') {
        require __DIR__ . '/articles.php';
        exit;
    }
    if ($uri === 'sitemap.xml') {
        require __DIR__ . '/sitemap.php';
        exit;
    }
    if ($uri === 'humans.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        $content = file_get_contents(__DIR__ . '/humans.txt');
        $content = str_replace(['{{SITE_NAME}}', '{{SITE_DOMAIN}}', '{{DATE}}'], [SITE_NAME, SITE_DOMAIN, date('Y-m-d')], $content);
        echo $content;
        exit;
    }
    if ($uri === 'robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        $content = file_get_contents(__DIR__ . '/robots.txt');
        $content = str_replace('{{SITE_URL}}', SITE_URL, $content);
        echo $content;
        exit;
    }
    if ($uri === 'feed.xml' || $uri === 'rss' || $uri === 'feed') {
        require __DIR__ . '/feed.php';
        exit;
    }
    $_GET['slug'] = $uri;
    require __DIR__ . '/article.php';
    exit;
}
// ==============================

$pdo = getDB();

// Requête hero : article à la une (publié uniquement)
$stmt = $pdo->prepare("
    SELECT * FROM articles
    WHERE est_hero = 1
      AND statut = 'publie'
      AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
    LIMIT 1
");
$stmt->execute();
$hero = $stmt->fetch();

// Fallback si pas de hero (publié uniquement)
if (!$hero) {
    $stmt = $pdo->prepare("
        SELECT * FROM articles
        WHERE statut = 'publie'
          AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
        ORDER BY date_publication DESC
        LIMIT 1
    ");
    $stmt->execute();
    $hero = $stmt->fetch();
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = (int)SITE_ARTICLES_PAR_PAGE;
$offset = ($page - 1) * $per_page;

// Compter le total d'articles publiés (sans le hero)
$total_articles = 0;
if ($hero) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM articles
        WHERE id != ?
          AND statut = 'publie'
          AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
    ");
    $stmt->execute([$hero['id']]);
    $total_articles = (int)$stmt->fetchColumn();
}
$total_pages = max(1, ceil($total_articles / $per_page));

// Requête articles secondaires publiés (sans le hero) avec pagination
$articles = [];
if ($hero) {
    $stmt = $pdo->prepare("
        SELECT * FROM articles
        WHERE id != :hero_id
          AND statut = 'publie'
          AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
        ORDER BY date_publication DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':hero_id', $hero['id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll();
}

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
    <title><?= escape(SITE_NAME) ?> — <?= escape(SITE_TAGLINE) ?></title>
    <meta name="description" content="<?= escape(SITE_DESC) ?>">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <meta name="author" content="<?= escape(SITE_AUTHOR) ?>">
    <link rel="canonical" href="<?= SITE_URL ?><?= $page > 1 ? '?page=' . $page : '' ?>">
    <?php if ($page > 1): ?><link rel="prev" href="<?= url() ?>?page=<?= $page - 1 ?>"><?php endif; ?>
    <?php if ($page < $total_pages): ?><link rel="next" href="<?= url() ?>?page=<?= $page + 1 ?>"><?php endif; ?>

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="<?= escape(SITE_NAME) ?> — <?= escape(SITE_TAGLINE) ?>">
    <meta property="og:description" content="<?= escape(SITE_DESC) ?>">
    <meta property="og:url" content="<?= SITE_URL ?>">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">
    <meta property="og:image" content="<?= $hero ? escape($hero['image']) : SITE_URL . '/' . SITE_OG_IMAGE ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?= escape(SITE_TWITTER_HANDLE) ?>">
    <meta name="twitter:title" content="<?= escape(SITE_NAME) ?> — <?= escape(SITE_TAGLINE) ?>">
    <meta name="twitter:description" content="<?= escape(SITE_DESC) ?>">
    <meta name="twitter:image" content="<?= $hero ? escape($hero['image']) : SITE_URL . '/' . SITE_OG_IMAGE ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?= escape(SITE_NAME) ?> RSS" href="<?= url('feed.xml') ?>">

    <!-- JSON-LD WebSite Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= escape(SITE_NAME) ?>",
        "url": "<?= SITE_URL ?>",
        "description": "<?= escape(SITE_DESC) ?>",
        "inLanguage": "<?= SITE_LOCALE ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= SITE_URL ?>/?s={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- JSON-LD Organization Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?= escape(SITE_NAME) ?>",
        "url": "<?= SITE_URL ?>",
        "description": "<?= escape(SITE_DESC) ?>"
    }
    </script>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="assets/css/style.css" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" as="style">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (async loading) -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></noscript>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

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
                    <li class="nav-item"><a class="nav-link" href="<?= url() ?>" aria-current="page">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('articles') ?>">Articles</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- BANNIÈRE -->
    <div class="site-banner" role="banner"><?= escape(SITE_NICHE) ?> — Guides, conseils & actualités</div>

    <main role="main" id="main-content">
    <!-- HERO ARTICLE -->
    <?php if ($hero): ?>
    <section class="container mt-4" aria-labelledby="hero-title">
        <a href="<?= url(escape($hero['slug'])) ?>" class="text-decoration-none" aria-label="Lire l'article : <?= escape($hero['titre']) ?>">
            <article class="hero-card">
                <div class="row g-0">
                    <div class="col-md-6">
                        <div class="card-img-wrap position-relative h-100">
                            <span class="hero-badge">À la une</span>
                            <img src="<?= escape($hero['image']) ?>" alt="<?= escape($hero['titre']) ?>" loading="eager" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="p-4 p-lg-5">
                            <div class="article-meta-info small mb-2">
                                <span class="text-uppercase"><?= escape($hero['categorie']) ?></span>
                                <span class="mx-2" aria-hidden="true">•</span>
                                <span><?= formatDate($hero['date_publication']) ?></span>
                                <span class="mx-2" aria-hidden="true">•</span>
                                <span><?= (int)$hero['read_time'] ?> min de lecture</span>
                            </div>
                            <div class="hero-divider" aria-hidden="true"></div>
                            <h1 class="hero-title text-dark" id="hero-title"><?= escape($hero['titre']) ?></h1>
                            <p class="article-excerpt mt-3"><?= escape(excerpt($hero['contenu_html'], 200)) ?></p>
                            <span class="btn-read-more mt-3">Lire l'article <span aria-hidden="true">→</span></span>
                        </div>
                    </div>
                </div>
            </article>
        </a>
    </section>
    <?php endif; ?>

    <!-- SECTION ARTICLES -->
    <?php if (!empty($articles)): ?>
    <section class="container mt-5" aria-labelledby="section-articles-title">
        <div class="d-flex align-items-center mb-4">
            <h2 class="h4 mb-0" id="section-articles-title">Derniers articles</h2>
            <hr class="flex-grow-1 ms-3" style="border-color: var(--border);" aria-hidden="true">
        </div>
        <div class="row g-4">
            <?php foreach ($articles as $index => $article): ?>
            <div class="col-sm-6 col-md-3">
                <a href="<?= url(escape($article['slug'])) ?>" class="text-decoration-none" aria-label="Lire l'article : <?= escape($article['titre']) ?>">
                    <article class="article-card fade-up delay-<?= ($index % 4) + 1 ?>">
                        <div class="card-img-wrap position-relative">
                            <span class="card-category-badge"><?= escape($article['categorie']) ?></span>
                            <img src="<?= escape($article['image']) ?>" alt="" loading="lazy">
                        </div>
                        <div class="p-3">
                            <div class="article-date small mb-2"><?= formatDate($article['date_publication']) ?></div>
                            <h3 class="card-title text-dark"><?= escape($article['titre']) ?></h3>
                            <p class="card-excerpt mt-2"><?= escape(excerpt($article['contenu_html'], 100)) ?></p>
                        </div>
                        <div class="px-3 pb-3 mt-auto d-flex justify-content-between align-items-center">
                            <span class="btn-read-more small">Lire <span aria-hidden="true">→</span></span>
                            <span class="article-read-time small"><?= (int)$article['read_time'] ?> min</span>
                        </div>
                    </article>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <!-- Pagination -->
        <nav class="mt-5" aria-label="Navigation des articles">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url() ?>?page=<?= $page - 1 ?>" aria-label="Précédent">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url() ?>?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url() ?>?page=<?= $page + 1 ?>" aria-label="Suivant">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </section>
    <?php endif; ?>
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
                        <li class="mb-2"><a href="#">Politique de confidentialité</a></li>
                        <li class="mb-2"><a href="#">CGU</a></li>
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
    <script defer src="assets/js/main.js"></script>

</body>
</html>
<?php
} catch (Throwable $e) {
    echo "<h1>ERREUR</h1>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>