<?php
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
    <meta property="og:image" content="<?= $hero ? SITE_URL . '/' . escape($hero['image']) : SITE_URL . '/' . SITE_OG_IMAGE ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?= escape(SITE_TWITTER_HANDLE) ?>">
    <meta name="twitter:title" content="<?= escape(SITE_NAME) ?> — <?= escape(SITE_TAGLINE) ?>">
    <meta name="twitter:description" content="<?= escape(SITE_DESC) ?>">
    <meta name="twitter:image" content="<?= $hero ? SITE_URL . '/' . escape($hero['image']) : SITE_URL . '/' . SITE_OG_IMAGE ?>">

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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
    <nav style="background:white;border-bottom:1px solid #e8e4dc;padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1000;">
        <a href="<?= url() ?>" style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:700;color:#1a1a18;text-decoration:none;"><?= escape(SITE_LOGO_TEXT) ?></a>
        <div style="display:flex;gap:8px;">
            <a href="<?= url() ?>" style="font-size:0.8rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#4a4a46;text-decoration:none;padding:0 12px;">Accueil</a>
            <a href="<?= url('articles') ?>" style="font-size:0.8rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:#4a4a46;text-decoration:none;padding:0 12px;">Articles</a>
        </div>
    </nav>

    <!-- BANNIÈRE -->
    <div class="site-banner" role="banner"><?= escape(SITE_NICHE) ?> — Guides, conseils & actualités</div>

    <main role="main" id="main-content">
    <!-- HERO ARTICLE -->
    <?php if ($hero): ?>
    <section class="hero-section" aria-labelledby="hero-title">
        <img src="/<?= escape($hero['image']) ?>" alt="<?= escape($hero['titre']) ?>" loading="eager">
        <div class="hero-overlay">
            <span class="badge-cat"><?= escape($hero['categorie']) ?></span>
            <h1 id="hero-title"><?= escape($hero['titre']) ?></h1>
            <p class="excerpt"><?= escape(excerpt($hero['contenu_html'], 160)) ?></p>
            <a href="<?= url(escape($hero['slug'])) ?>" class="btn-hero">
                Lire l'article <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- SECTION ARTICLES -->
    <?php if (!empty($articles)): ?>
    <section class="articles-list" aria-labelledby="section-articles-title">
        <h2 class="section-title" id="section-articles-title">Derniers articles</h2>
        <div class="section-title-line"></div>

        <?php foreach ($articles as $a): ?>
        <a href="<?= url(escape($a['slug'])) ?>" class="article-row" aria-label="Lire l'article : <?= escape($a['titre']) ?>">
            <img class="article-row-img" src="/<?= escape($a['image']) ?>" alt="<?= escape($a['titre']) ?>" loading="lazy">
            <div class="article-row-body">
                <span class="badge-cat"><?= escape($a['categorie']) ?></span>
                <h3><?= escape($a['titre']) ?></h3>
                <p class="excerpt"><?= escape(excerpt($a['contenu_html'], 200)) ?></p>
                <span class="lire-link">Lire l'article <span aria-hidden="true">→</span></span>
            </div>
        </a>
        <?php endforeach; ?>

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
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-col">
                <div class="footer-brand"><?= escape(SITE_LOGO_TEXT) ?></div>
                <p><?= escape(SITE_FOOTER_DESC) ?></p>
            </div>
            <div class="footer-col">
                <p class="footer-heading">Navigation</p>
                <a href="<?= url() ?>">Accueil</a>
                <a href="<?= url('articles') ?>">Articles</a>
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

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

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