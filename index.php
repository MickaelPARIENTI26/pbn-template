<?php
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data: https:; script-src 'self' 'unsafe-inline'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header_remove('X-Powered-By');

// Configuration erreurs selon environnement
if (defined('SITE_ENV') && SITE_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

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
    if ($uri === 'categorie') {
        require __DIR__ . '/categorie.php';
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
    if ($uri === 'politique-confidentialite' || $uri === 'politique-confidentialite.php') {
        require __DIR__ . '/politique-confidentialite.php';
        exit;
    }
    if ($uri === 'cgu' || $uri === 'cgu.php') {
        require __DIR__ . '/cgu.php';
        exit;
    }
    if (preg_match('#^categorie/([a-z0-9\-]+)$#', $uri, $m)) {
        $cat_slug = $m[1];
        // Retrouve la vraie catégorie depuis le slug
        $all_cats = getDB()->query(
            "SELECT DISTINCT categorie FROM articles WHERE statut='publie'"
        )->fetchAll(PDO::FETCH_COLUMN);
        $matched_cat = '';
        foreach ($all_cats as $c) {
            if (categorie_slug($c) === $cat_slug) {
                $matched_cat = $c;
                break;
            }
        }
        $_GET['cat'] = $matched_cat ?: $cat_slug;
        require __DIR__ . '/categorie.php';
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

// Sections éditoriales depuis homepage_content
$sections_edito = [];
$rows = getDB()->query(
    "SELECT * FROM homepage_content ORDER BY ordre ASC"
)->fetchAll();
foreach ($rows as $row) {
    $sections_edito[$row['section']] = $row;
}

// Insérer des données de test si la table est vide
$count = getDB()->query("SELECT COUNT(*) FROM homepage_content")->fetchColumn();
if ($count == 0) {
    $test_data = [
        [
            'section'    => 'alt_1',
            'titre'      => 'Les bienfaits naturels du CBD',
            'texte'      => 'Le cannabidiol est un composé naturel issu du chanvre industriel. Sans effet psychotrope, il agit sur le système endocannabinoïde pour favoriser la relaxation, améliorer le sommeil et réduire les inflammations. De plus en plus d\'athlètes et sportifs intègrent le CBD dans leur routine quotidienne de récupération.',
            'image'      => 'images/article-1.webp',
            'lien'       => '/articles',
            'lien_texte' => 'Découvrir nos guides',
            'ordre'      => 1,
        ],
        [
            'section'    => 'dark',
            'titre'      => 'Récupération • Performance • Bien-être',
            'texte'      => 'Le CBD s\'impose progressivement comme un allié incontournable des sportifs de tous niveaux. Que vous soyez un athlète professionnel ou un amateur passionné, le cannabidiol peut transformer votre approche de la récupération musculaire et de la gestion du stress.',
            'image'      => '',
            'lien'       => '/articles',
            'lien_texte' => 'Tous nos articles',
            'ordre'      => 2,
        ],
        [
            'section'    => 'alt_2',
            'titre'      => 'CBD et sport : une alliance naturelle',
            'texte'      => 'Les recherches scientifiques confirment l\'intérêt du CBD pour les sportifs : réduction des douleurs musculaires post-effort, amélioration de la qualité du sommeil réparateur, gestion du stress avant la compétition. Une approche naturelle, légale et sans danger pour optimiser vos performances.',
            'image'      => 'images/article-2.webp',
            'lien'       => '/articles',
            'lien_texte' => 'Lire nos conseils',
            'ordre'      => 3,
        ],
    ];

    $stmt = getDB()->prepare(
        "INSERT INTO homepage_content
         (section, titre, texte, image, lien, lien_texte, ordre)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    foreach ($test_data as $row) {
        $stmt->execute([
            $row['section'], $row['titre'], $row['texte'],
            $row['image'], $row['lien'], $row['lien_texte'], $row['ordre']
        ]);
    }

    // Recharger les sections après insertion
    $rows = getDB()->query(
        "SELECT * FROM homepage_content ORDER BY ordre ASC"
    )->fetchAll();
    foreach ($rows as $row) {
        $sections_edito[$row['section']] = $row;
    }
}

$s1   = $sections_edito['alt_1'] ?? null;
$dark = $sections_edito['dark']  ?? null;
$s2   = $sections_edito['alt_2'] ?? null;

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
        "name": "<?= SITE_NAME ?>",
        "url": "<?= SITE_URL ?>",
        "description": "<?= SITE_DESC ?>",
        "inLanguage": "fr",
        "publisher": {
            "@type": "Organization",
            "name": "<?= SITE_NAME ?>",
            "url": "<?= SITE_URL ?>"
        }
    }
    </script>

    <!-- JSON-LD Organization Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?= SITE_NAME ?>",
        "url": "<?= SITE_URL ?>",
        "description": "<?= SITE_DESC ?>",
        "foundingDate": "2026",
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "contact@<?= SITE_DOMAIN ?>",
            "contactType": "customer service",
            "availableLanguage": "French"
        },
        "logo": {
            "@type": "ImageObject",
            "url": "<?= SITE_URL ?>/images/og-default.svg",
            "width": 200,
            "height": 60
        }
    }
    </script>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="/assets/css/style.css" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" as="style">
    <?php if ($hero): ?><link rel="preload" as="image" href="<?= escape($hero['image']) ?>"><?php endif; ?>

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
            ?>
            <a href="/categorie/<?= categorie_slug($c['categorie']) ?>" class="nav-link"><?= escape($c['categorie']) ?></a>
            <?php endforeach; ?>
            <a href="<?= url('articles') ?>" class="nav-link nav-link-cta">Tous les articles</a>
        </div>
    </nav>

    <!-- BANNIÈRE -->
    <div class="site-banner" role="banner"><?= escape(SITE_NICHE) ?> — Guides, conseils & actualités</div>

    <main role="main" id="main-content">
    <!-- HERO ARTICLE -->
    <?php if ($hero): ?>
    <section class="hero-section">
        <img src="<?= escape($hero['image']) ?>" alt="<?= escape($hero['titre']) ?>" width="1200" height="580" fetchpriority="high">
        <div class="hero-overlay">
            <span class="hero-badge"><?= escape($hero['categorie']) ?></span>
            <h1><?= escape($hero['titre']) ?></h1>
            <p class="hero-excerpt"><?= escape(substr(strip_tags($hero['contenu_html']), 0, 180)) ?>...</p>
            <a href="<?= url(escape($hero['slug'])) ?>" class="hero-btn">Lire l'article →</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- SECTIONS ÉDITORIALES -->
    <?php if ($s1): ?>
    <section class="alt-section">
        <div class="alt-inner">
            <div class="alt-img">
                <img src="<?= escape($s1['image']) ?>" alt="<?= escape($s1['titre']) ?>" width="600" height="400" loading="lazy">
            </div>
            <div class="alt-body">
                <span class="badge-cat"><?= escape(SITE_NICHE) ?></span>
                <h2><?= escape($s1['titre']) ?></h2>
                <p><?= escape($s1['texte']) ?></p>
                <a href="<?= escape($s1['lien']) ?>" class="alt-link"><?= escape($s1['lien_texte']) ?> →</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($dark): ?>
    <section class="dark-band">
        <span class="editorial-badge"><?= escape(SITE_NAME) ?></span>
        <h2><?= escape($dark['titre']) ?></h2>
        <p><?= escape($dark['texte']) ?></p>
        <a href="<?= escape($dark['lien']) ?>" class="hero-btn"><?= escape($dark['lien_texte']) ?> →</a>
    </section>
    <?php endif; ?>

    <?php if ($s2): ?>
    <section class="alt-section alt-reverse">
        <div class="alt-inner">
            <div class="alt-img">
                <img src="<?= escape($s2['image']) ?>" alt="<?= escape($s2['titre']) ?>" width="600" height="400" loading="lazy">
            </div>
            <div class="alt-body">
                <span class="badge-cat"><?= escape(SITE_NICHE) ?></span>
                <h2><?= escape($s2['titre']) ?></h2>
                <p><?= escape($s2['texte']) ?></p>
                <a href="<?= escape($s2['lien']) ?>" class="alt-link"><?= escape($s2['lien_texte']) ?> →</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- DERNIERS ARTICLES -->
    <?php
    $recents = $pdo->query("
        SELECT * FROM articles
        WHERE statut='publie' AND est_hero=0
        ORDER BY date_publication DESC LIMIT 4
    ")->fetchAll();
    ?>
    <?php if (!empty($recents)): ?>
    <section class="recents-section">
        <div class="recents-inner">
            <h2 class="section-title">Derniers articles</h2>
            <div class="section-divider"></div>
            <div class="recents-grid">
                <?php foreach($recents as $a): ?>
                <a href="<?= url(escape($a['slug'])) ?>" class="article-card">
                    <div class="card-img-wrap">
                        <img src="<?= escape($a['image']) ?>" alt="<?= escape($a['titre']) ?>" width="600" height="400" loading="lazy">
                    </div>
                    <div class="card-body">
                        <span class="badge-cat"><?= escape($a['categorie']) ?></span>
                        <h3><?= escape($a['titre']) ?></h3>
                        <p class="card-excerpt"><?= escape(substr(strip_tags($a['contenu_html']), 0, 130)) ?>...</p>
                        <div class="card-meta">
                            <span><?= date('d M Y', strtotime($a['date_publication'])) ?></span>
                            <span><?= $a['read_time'] ?> min</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
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
<?php
} catch (Throwable $e) {
    echo "<h1>ERREUR</h1>";
    echo "<p><strong>" . $e->getMessage() . "</strong></p>";
    echo "<p>Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>