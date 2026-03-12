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

// Récupérer la catégorie
$cat = isset($_GET['cat']) ? trim(urldecode($_GET['cat'])) : '';
if (empty($cat)) {
    header('Location: ' . url('articles'));
    exit;
}

// Requête articles de cette catégorie (insensible à la casse)
$stmt = $pdo->prepare("
    SELECT * FROM articles
    WHERE statut = 'publie'
      AND LOWER(categorie) = LOWER(?)
      AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
    ORDER BY date_publication DESC
");
$stmt->execute([$cat]);
$articles = $stmt->fetchAll();

// Récupérer le nom exact de la catégorie depuis la BDD si articles trouvés
if (!empty($articles)) {
    $cat = $articles[0]['categorie'];
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
    <title><?= escape($cat) ?> — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="Tous nos articles sur <?= escape($cat) ?>">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <link rel="canonical" href="<?= SITE_URL ?>/categorie?cat=<?= urlencode($cat) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="<?= escape($cat) ?> — <?= escape(SITE_NAME) ?>">
    <meta property="og:description" content="Tous nos articles sur <?= escape($cat) ?>">
    <meta property="og:url" content="<?= SITE_URL ?>/categorie?cat=<?= urlencode($cat) ?>">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?= escape(SITE_NAME) ?> RSS" href="<?= url('feed.xml') ?>">

    <!-- Preload First Image -->
    <?php if (!empty($articles)): ?><link rel="preload" as="image" href="<?= escape($articles[0]['image']) ?>"><?php endif; ?>

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

    <main role="main" id="main-content">
        <!-- EN-TÊTE CATÉGORIE -->
        <div style="max-width:1200px; margin:60px auto 48px; padding:0 40px; text-align:center;">
            <h1 style="font-family:'Cormorant Garamond',serif; font-size:2.8rem; font-weight:700; color:var(--dark); margin:0;">
                <?= escape($cat) ?>
            </h1>
            <div class="section-divider"></div>
            <p style="color:var(--muted); font-size:0.9rem;">
                <?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?>
            </p>
        </div>

        <!-- LISTE DES ARTICLES -->
        <div style="max-width:1200px; margin:0 auto; padding:0 40px 80px;">
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $index => $a): ?>
                <a href="<?= url(escape($a['slug'])) ?>" class="article-row">
                    <img class="article-row-img" src="<?= escape($a['image']) ?>" alt="<?= escape($a['titre']) ?>" width="300" height="210" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                    <div class="article-row-body">
                        <span class="badge-cat"><?= escape($a['categorie']) ?></span>
                        <h3><?= escape($a['titre']) ?></h3>
                        <p class="excerpt"><?= escape(substr(strip_tags($a['contenu_html']), 0, 220)) ?>...</p>
                        <span class="lire-link">Lire l'article →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; color:var(--muted); padding:60px 0; font-size:1rem;">
                    Aucun article dans cette catégorie pour le moment.
                </p>
            <?php endif; ?>
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
