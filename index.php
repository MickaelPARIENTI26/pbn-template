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

// Blocs SEO homepage depuis homepage_content
$blocs = [];
try {
    $rows = getDB()->query(
        "SELECT * FROM homepage_content ORDER BY ordre ASC"
    )->fetchAll();
    foreach ($rows as $row) {
        $blocs[$row['section']] = $row;
    }
} catch (PDOException $e) {
    // Table n'existe pas encore, on la crée
    getDB()->exec("
        CREATE TABLE IF NOT EXISTS homepage_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            section VARCHAR(50) NOT NULL,
            titre VARCHAR(500),
            texte TEXT,
            image VARCHAR(500),
            lien VARCHAR(500),
            lien_texte VARCHAR(200),
            ordre INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

// Migration : si anciennes sections ou données obsolètes → insérer nouvelles sections
$needs_migration = empty($blocs)
    || isset($blocs['alt_1'])
    || isset($blocs['dark'])
    || !isset($blocs['bloc_1_col_1'])
    || (isset($blocs['bloc_2']) && substr($blocs['bloc_2']['lien'] ?? '', 0, 1) !== '[')
    || (isset($blocs['bloc_4']) && substr($blocs['bloc_4']['lien'] ?? '', 0, 1) === '[');

if ($needs_migration) {
    // Vider la table pour la migration
    getDB()->exec("TRUNCATE TABLE homepage_content");

    $test_data = [
        ['bloc_1_col_1', '💪', 'CBD et Récupération',
         'Le CBD agit sur les récepteurs CB1 et CB2 pour réduire l\'inflammation musculaire post-effort. Les sportifs l\'intègrent pour accélérer la récupération et réduire les courbatures après l\'entraînement.',
         '/articles', 'En savoir plus', 1],

        ['bloc_1_col_2', '🏃', 'CBD et Performance',
         'Le cannabidiol optimise la gestion du stress avant la compétition sans effet psychotrope. Légal depuis 2018, il améliore la concentration et réduit l\'anxiété de performance chez les athlètes de tous niveaux.',
         '/articles', 'En savoir plus', 2],

        ['bloc_1_col_3', '😴', 'CBD et Sommeil',
         'Un sommeil réparateur est essentiel à la progression sportive. Le CBD favorise l\'endormissement et améliore la qualité des cycles de sommeil pour une régénération musculaire optimale durant la nuit.',
         '/articles', 'En savoir plus', 3],

        ['bloc_2', '', 'Le CBD Sport : légal, naturel et efficace',
         "L'Agence Mondiale Antidopage a retiré le CBD de la liste des substances interdites en 2018. Les sportifs peuvent le consommer légalement, à condition que le taux de THC soit inférieur à 0,3%.\n\nHuile de CBD, gélules, baumes topiques — les formats adaptés au sport sont nombreux. Choisissez des produits certifiés avec analyse laboratoire indépendant.",
         '[{"num":"2018","label":"CBD retiré de la liste des substances interdites par l\'AMA"},{"num":"0,3%","label":"Taux de THC maximum légal en France"},{"num":"72%","label":"Des sportifs ayant testé le CBD notent une meilleure récupération"}]', '', 4],

        ['bloc_dark', '', 'Récupération • Performance • Bien-être',
         'Le CBD s\'impose progressivement comme un allié incontournable des sportifs de tous niveaux. Une approche naturelle et holistique pour optimiser vos performances sans compromis.',
         '/articles', 'Tous nos articles', 5],

        ['bloc_4', '', 'CBD antidouleur : une alternative naturelle aux anti-inflammatoires',
         "L'interaction du CBD avec le système endocannabinoïde module la perception de la douleur chronique. Ce composé végétal cible les récepteurs CB2 pour calmer les tensions musculaires et articulaires sans risque de dépendance.\n\nContrairement aux anti-inflammatoires non stéroïdiens (AINS), le CBD n'agresse pas la muqueuse gastrique et peut être utilisé sur le long terme. Les études cliniques montrent une réduction significative des douleurs inflammatoires après 4 semaines d'utilisation régulière.\n\nLe cannabidiol agit également sur les voies de la sérotonine, contribuant à une meilleure gestion du stress souvent associé aux douleurs chroniques. Cette approche holistique en fait un complément idéal pour les personnes recherchant une solution naturelle et durable.",
         '', '', 6],

        ['bloc_5', '', 'Nutrition sportive et CBD',
         'Le CBD s\'intègre naturellement dans votre protocole nutritionnel sportif. Associé à une alimentation riche en protéines et acides gras essentiels, il optimise la récupération musculaire et soutient les performances sur la durée.',
         '/articles', 'Explorer nos articles', 7],
    ];

    $stmt = getDB()->prepare(
        "INSERT INTO homepage_content
         (section, image, titre, texte, lien, lien_texte, ordre)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    foreach ($test_data as $row) {
        $stmt->execute($row);
    }

    // Recharger les blocs après insertion
    $blocs = [];
    $rows = getDB()->query(
        "SELECT * FROM homepage_content ORDER BY ordre ASC"
    )->fetchAll();
    foreach ($rows as $row) {
        $blocs[$row['section']] = $row;
    }
}

$b1c1  = $blocs['bloc_1_col_1'] ?? null;
$b1c2  = $blocs['bloc_1_col_2'] ?? null;
$b1c3  = $blocs['bloc_1_col_3'] ?? null;
$b2    = $blocs['bloc_2']       ?? null;
$bdark = $blocs['bloc_dark']    ?? null;
$b4    = $blocs['bloc_4']       ?? null;
$b5    = $blocs['bloc_5']       ?? null;

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
    <?php if ($hero): ?><link rel="preload" as="image" href="<?= escape($hero['image']) ?>"><?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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

    <!-- HEADER TOP BAR -->
    <div class="k-topbar">
        <div class="k-container">
            <?= escape(SITE_NICHE) ?> — Guides, conseils & actualités
        </div>
    </div>

    <!-- NAVBAR -->
    <header class="k-header">
        <div class="k-container">
            <nav class="k-nav">
                <a href="<?= url() ?>" class="k-logo"><?= escape(SITE_LOGO_TEXT) ?></a>
                <ul class="k-nav-links">
                    <li><a href="<?= url() ?>">Accueil</a></li>
                    <?php
                    $cats = $pdo->query("SELECT DISTINCT categorie FROM articles WHERE statut='publie' ORDER BY categorie LIMIT 6")->fetchAll();
                    foreach($cats as $c):
                    ?>
                    <li><a href="/categorie/<?= categorie_slug($c['categorie']) ?>"><?= escape($c['categorie']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= url('articles') ?>">Blog</a></li>
                    <li><a href="<?= url('articles') ?>" class="k-nav-cta">Découvrir</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main role="main" id="main-content">

    <!-- HERO SECTION — 2 colonnes -->
    <?php if ($hero): ?>
    <?php
    // Stats dynamiques
    $total_pub = (int)$pdo->query("SELECT COUNT(*) FROM articles WHERE statut='publie'")->fetchColumn();
    ?>
    <section class="k-hero">
        <div class="k-container">
            <div class="k-hero-grid">
                <div class="k-hero-content">
                    <span class="k-hero-badge">⭐ <?= escape(SITE_TAGLINE) ?></span>
                    <h1><?= escape($hero['titre']) ?></h1>
                    <p class="k-hero-desc"><?= escape(substr(strip_tags($hero['contenu_html']), 0, 200)) ?>...</p>
                    <div class="k-hero-buttons">
                        <a href="<?= url(escape($hero['slug'])) ?>" class="k-btn k-btn-primary">Lire l'article →</a>
                        <a href="<?= url('articles') ?>" class="k-btn k-btn-secondary">Tous nos guides</a>
                    </div>
                    <div class="k-hero-stats">
                        <div class="k-hero-stat">
                            <span class="k-hero-stat-num"><?= $total_pub ?>+</span>
                            <span class="k-hero-stat-label">Articles publiés</span>
                        </div>
                        <div class="k-hero-stat">
                            <span class="k-hero-stat-num"><?= count($cats) ?></span>
                            <span class="k-hero-stat-label">Catégories</span>
                        </div>
                        <div class="k-hero-stat">
                            <span class="k-hero-stat-num">4.9/5</span>
                            <span class="k-hero-stat-label">Note moyenne</span>
                        </div>
                    </div>
                </div>
                <div class="k-hero-image">
                    <img src="<?= escape($hero['image']) ?>" alt="<?= escape($hero['titre']) ?>" width="600" height="400" fetchpriority="high">
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- TRUST BAR -->
    <section class="k-trust-bar">
        <div class="k-container">
            <div class="k-trust-grid">
                <div class="k-trust-item">
                    <span class="k-trust-icon">🌿</span>
                    <div><strong>100% Naturel</strong><br><span>Produits certifiés</span></div>
                </div>
                <div class="k-trust-item">
                    <span class="k-trust-icon">🚚</span>
                    <div><strong>Guides complets</strong><br><span>Conseils d'experts</span></div>
                </div>
                <div class="k-trust-item">
                    <span class="k-trust-icon">🔒</span>
                    <div><strong>Sources vérifiées</strong><br><span>Études scientifiques</span></div>
                </div>
                <div class="k-trust-item">
                    <span class="k-trust-icon">✅</span>
                    <div><strong>Mis à jour</strong><br><span>Contenu régulier</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CATEGORIES — depuis BDD -->
    <?php if (!empty($cats)): ?>
    <section class="k-categories">
        <div class="k-container">
            <div class="k-section-header">
                <span class="k-section-tag">Découvrez nos thématiques</span>
                <h2>Nos catégories</h2>
                <p>Une sélection complète de guides et articles pour tous vos besoins</p>
            </div>
            <div class="k-cat-grid">
                <?php
                $cat_icons = ['💧', '🌸', '✨', '🍬', '🌿', '🔬', '💪', '🧘'];
                $i = 0;
                foreach($cats as $c):
                    $icon = $cat_icons[$i % count($cat_icons)];
                    $cat_count = (int)$pdo->prepare("SELECT COUNT(*) FROM articles WHERE statut='publie' AND categorie = ?");
                    $stmt_cat = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE statut='publie' AND categorie = ?");
                    $stmt_cat->execute([$c['categorie']]);
                    $cat_count = (int)$stmt_cat->fetchColumn();
                ?>
                <a href="/categorie/<?= categorie_slug($c['categorie']) ?>" class="k-cat-card">
                    <div class="k-cat-icon"><?= $icon ?></div>
                    <h3><?= escape($c['categorie']) ?></h3>
                    <p><?= $cat_count ?> article<?= $cat_count > 1 ? 's' : '' ?> disponible<?= $cat_count > 1 ? 's' : '' ?></p>
                    <span class="k-cat-link">Découvrir →</span>
                </a>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- BENEFITS — 3 colonnes depuis homepage_content -->
    <?php if ($b1c1 && $b1c2 && $b1c3): ?>
    <section class="k-benefits">
        <div class="k-container">
            <div class="k-section-header">
                <span class="k-section-tag">Bienfaits</span>
                <h2>Les bienfaits du <?= escape(SITE_NICHE) ?></h2>
                <p>Découvrez comment améliorer votre quotidien</p>
            </div>
            <div class="k-benefits-grid">
                <div class="k-benefit-card">
                    <div class="k-benefit-icon"><?= escape($b1c1['image']) ?></div>
                    <h3><?= escape($b1c1['titre']) ?></h3>
                    <p><?= escape($b1c1['texte']) ?></p>
                    <?php if ($b1c1['lien']): ?>
                    <a href="<?= escape($b1c1['lien']) ?>" class="k-link-arrow"><?= escape($b1c1['lien_texte']) ?> →</a>
                    <?php endif; ?>
                </div>
                <div class="k-benefit-card">
                    <div class="k-benefit-icon"><?= escape($b1c2['image']) ?></div>
                    <h3><?= escape($b1c2['titre']) ?></h3>
                    <p><?= escape($b1c2['texte']) ?></p>
                    <?php if ($b1c2['lien']): ?>
                    <a href="<?= escape($b1c2['lien']) ?>" class="k-link-arrow"><?= escape($b1c2['lien_texte']) ?> →</a>
                    <?php endif; ?>
                </div>
                <div class="k-benefit-card">
                    <div class="k-benefit-icon"><?= escape($b1c3['image']) ?></div>
                    <h3><?= escape($b1c3['titre']) ?></h3>
                    <p><?= escape($b1c3['texte']) ?></p>
                    <?php if ($b1c3['lien']): ?>
                    <a href="<?= escape($b1c3['lien']) ?>" class="k-link-arrow"><?= escape($b1c3['lien_texte']) ?> →</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- WHY US — Section sombre avec engagements -->
    <?php if ($bdark): ?>
    <section class="k-why-us">
        <div class="k-container">
            <div class="k-section-header k-section-header-light">
                <h2><?= escape($bdark['titre']) ?></h2>
                <p><?= escape($bdark['texte']) ?></p>
            </div>
            <?php
            // Chiffres clés depuis bloc_2
            $chiffres = $b2 ? json_decode($b2['lien'] ?? '[]', true) : [];
            if (!empty($chiffres)):
            ?>
            <div class="k-why-grid">
                <?php
                $why_icons = ['🌱', '⚖️', '🔬', '📚'];
                $wi = 0;
                foreach ($chiffres as $ch):
                ?>
                <div class="k-why-card">
                    <div class="k-why-icon"><?= $why_icons[$wi % 4] ?></div>
                    <h3><?= escape($ch['num']) ?></h3>
                    <p><?= escape($ch['label']) ?></p>
                </div>
                <?php $wi++; endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- BLOC CONTENU SEO — bloc_2 texte + image -->
    <?php if ($b2): ?>
    <section class="k-content-section">
        <div class="k-container">
            <div class="k-content-grid">
                <div class="k-content-body">
                    <h2><?= escape($b2['titre']) ?></h2>
                    <div class="k-content-text"><?= nl2br(escape($b2['texte'])) ?></div>
                </div>
                <div class="k-content-img">
                    <img src="images/article-2.webp" alt="<?= escape($b2['titre']) ?>" width="600" height="420" loading="lazy">
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- BLOC CONTENU SEO — bloc_4 image + texte (inversé) -->
    <?php if ($b4): ?>
    <section class="k-content-section k-content-reverse">
        <div class="k-container">
            <div class="k-content-grid">
                <div class="k-content-img">
                    <img src="images/article-3.webp" alt="<?= escape($b4['titre']) ?>" width="600" height="520" loading="lazy">
                </div>
                <div class="k-content-body">
                    <h2><?= escape($b4['titre']) ?></h2>
                    <div class="k-content-text"><?= nl2br(escape($b4['texte'])) ?></div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- BLOG — Derniers articles -->
    <?php
    $recents = $pdo->query("
        SELECT * FROM articles
        WHERE statut='publie' AND est_hero=0
        ORDER BY date_publication DESC LIMIT 3
    ")->fetchAll();
    ?>
    <?php if (!empty($recents)): ?>
    <section class="k-blog">
        <div class="k-container">
            <div class="k-section-header">
                <span class="k-section-tag">Blog</span>
                <h2>Derniers articles</h2>
                <p>Conseils, guides et actualités</p>
            </div>
            <div class="k-blog-grid">
                <?php foreach($recents as $a): ?>
                <a href="<?= url(escape($a['slug'])) ?>" class="k-blog-card">
                    <div class="k-blog-image">
                        <img src="<?= escape($a['image']) ?>" alt="<?= escape($a['titre']) ?>" width="400" height="180" loading="lazy">
                    </div>
                    <div class="k-blog-content">
                        <div class="k-blog-meta">
                            <span><?= escape($a['categorie']) ?></span>
                            <span><?= date('d M Y', strtotime($a['date_publication'])) ?></span>
                            <span><?= $a['read_time'] ?? 5 ?> min</span>
                        </div>
                        <h3><?= escape($a['titre']) ?></h3>
                        <p><?= escape(substr(strip_tags($a['contenu_html']), 0, 130)) ?>...</p>
                        <span class="k-link-arrow">Lire l'article →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="k-blog-more">
                <a href="<?= url('articles') ?>" class="k-btn k-btn-secondary">Voir tous les articles</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA SECTION -->
    <?php if ($b5): ?>
    <section class="k-cta">
        <div class="k-container">
            <h2><?= escape($b5['titre']) ?></h2>
            <p><?= escape($b5['texte']) ?></p>
            <a href="<?= escape($b5['lien']) ?>" class="k-btn k-btn-white"><?= escape($b5['lien_texte']) ?></a>
        </div>
    </section>
    <?php endif; ?>

    <!-- FAQ SECTION -->
    <section class="k-faq">
        <div class="k-container">
            <div class="k-section-header">
                <span class="k-section-tag">FAQ</span>
                <h2>Questions fréquentes</h2>
            </div>
            <div class="k-faq-grid">
                <div class="k-faq-item">
                    <h4>Le CBD est-il légal en France ?</h4>
                    <p>Oui, le CBD est légal en France à condition que les produits contiennent moins de 0,3% de THC. Tous les produits recommandés respectent cette législation.</p>
                </div>
                <div class="k-faq-item">
                    <h4>Le CBD fait-il planer ?</h4>
                    <p>Non, le CBD n'a pas d'effet psychoactif et ne provoque pas de sensation planante. Contrairement au THC, le CBD n'affecte pas la conscience.</p>
                </div>
                <div class="k-faq-item">
                    <h4>Quelle dose de CBD pour commencer ?</h4>
                    <p>Il est recommandé de commencer par une dose faible (10-20mg par jour) et d'augmenter progressivement jusqu'à trouver la dose optimale.</p>
                </div>
                <div class="k-faq-item">
                    <h4>Combien de temps durent les effets ?</h4>
                    <p>Les effets du CBD durent généralement 4 à 6 heures selon le mode de consommation et la dose ingérée.</p>
                </div>
            </div>
        </div>
    </section>

    </main>

    <!-- FOOTER -->
    <?php
    $footer_cats = $pdo->query(
        "SELECT DISTINCT categorie FROM articles WHERE statut='publie' LIMIT 5"
    )->fetchAll();
    ?>
    <footer class="k-footer">
        <div class="k-container">
            <div class="k-footer-grid">
                <div class="k-footer-col k-footer-brand-col">
                    <div class="k-footer-brand"><?= escape(SITE_LOGO_TEXT) ?></div>
                    <p><?= escape(SITE_FOOTER_DESC) ?></p>
                </div>
                <div class="k-footer-col">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="<?= url() ?>">Accueil</a></li>
                        <li><a href="<?= url('articles') ?>">Blog</a></li>
                    </ul>
                </div>
                <div class="k-footer-col">
                    <h4>Catégories</h4>
                    <ul>
                        <?php foreach($footer_cats as $fc): ?>
                        <li><a href="/categorie/<?= categorie_slug($fc['categorie']) ?>"><?= escape($fc['categorie']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="k-footer-col">
                    <h4>Légal</h4>
                    <ul>
                        <li><a href="<?= url('mentions-legales') ?>">Mentions légales</a></li>
                        <li><a href="<?= url('politique-confidentialite') ?>">Confidentialité</a></li>
                        <li><a href="<?= url('cgu') ?>">CGU</a></li>
                    </ul>
                </div>
            </div>
            <div class="k-footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= escape(SITE_NAME) ?>. Tous droits réservés.</p>
                <div class="k-disclaimer">
                    <strong>Avertissement :</strong> Les informations présentes sur ce site sont fournies à titre informatif uniquement et ne constituent pas un avis médical. Le CBD n'est pas un médicament et ne se substitue pas à un traitement médical. Consultez toujours un professionnel de santé avant de consommer du CBD. Les produits CBD vendus en France contiennent moins de 0,3% de THC conformément à la législation en vigueur.
                </div>
            </div>
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