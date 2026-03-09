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
$not_found = false;
$article = null;
$h2_titles = [];
$contenu_modifie = '';
$tags = [];
$related = [];
$latest = [];

// Récupérer le slug (compatible avec routing PHP manuel)
$slug = isset($_GET['slug']) ? htmlspecialchars($_GET['slug'], ENT_QUOTES, 'UTF-8') : '';

if (empty($slug)) {
    $not_found = true;
} else {
    // Requête article (publié uniquement)
    $stmt = $pdo->prepare("
        SELECT * FROM articles
        WHERE slug = ?
          AND statut = 'publie'
          AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();

    if (!$article) {
        // Article non trouvé OU non encore publié → 404
        $not_found = true;
    } else {
        // Extraire les H2 pour le sommaire
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $article['contenu_html'], $matches);
        $h2_titles = $matches[1] ?? [];

        // Ajouter les id sur chaque H2
        $i = 0;
        $contenu_modifie = preg_replace_callback(
            '/<h2([^>]*)>(.*?)<\/h2>/si',
            function($m) use (&$i) {
                return '<h2' . $m[1] . ' id="section-' . ($i++) . '">' . $m[2] . '</h2>';
            },
            $article['contenu_html']
        );

        // Articles similaires (même catégorie, publiés uniquement)
        $stmt = $pdo->prepare("
            SELECT * FROM articles
            WHERE categorie = ?
              AND id != ?
              AND statut = 'publie'
              AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
            ORDER BY date_publication DESC
            LIMIT 3
        ");
        $stmt->execute([$article['categorie'], $article['id']]);
        $related = $stmt->fetchAll();

        // Derniers articles (pour sidebar, publiés uniquement)
        $stmt = $pdo->prepare("
            SELECT * FROM articles
            WHERE id != ?
              AND statut = 'publie'
              AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
            ORDER BY date_publication DESC
            LIMIT 5
        ");
        $stmt->execute([$article['id']]);
        $latest = $stmt->fetchAll();

        // Décoder les tags
        $tags = json_decode($article['tags'] ?? '[]', true) ?: [];
    }
}

// Helper pour formater la date
// Helpers (déclarés seulement si pas déjà déclarés via index.php)
if (!function_exists('formatDate')) {
    function formatDate($date) {
        $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $d = new DateTime($date);
        return $d->format('j') . ' ' . $months[$d->format('n') - 1] . ' ' . $d->format('Y');
    }
}

if (!function_exists('excerpt')) {
    function excerpt($html, $length = 150) {
        $text = strip_tags($html);
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
}?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($article): ?>
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
    <meta property="og:image" content="<?= escape($article['image']) ?>">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">
    <meta property="article:published_time" content="<?= $article['date_publication'] ?>">
    <meta property="article:modified_time" content="<?= $article['date_modification'] ?? $article['date_publication'] ?>">
    <meta property="article:section" content="<?= escape($article['categorie']) ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?= escape(SITE_TWITTER_HANDLE) ?>">
    <meta name="twitter:title" content="<?= escape($article['titre']) ?>">
    <meta name="twitter:description" content="<?= escape($article['meta_description']) ?>">
    <meta name="twitter:image" content="<?= escape($article['image']) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?= escape(SITE_NAME) ?> RSS" href="<?= url('feed.xml') ?>">

    <!-- JSON-LD BreadcrumbList Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Accueil",
                "item": "<?= SITE_URL ?>"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "<?= escape($article['categorie']) ?>",
                "item": "<?= SITE_URL ?>/articles"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "<?= escape($article['titre']) ?>",
                "item": "<?= SITE_URL ?>/<?= escape($article['slug']) ?>"
            }
        ]
    }
    </script>

    <!-- JSON-LD Article Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= escape($article['titre']) ?>",
        "description": "<?= escape($article['meta_description']) ?>",
        "image": "<?= escape($article['image']) ?>",
        "datePublished": "<?= $article['date_publication'] ?>",
        "dateModified": "<?= $article['date_modification'] ?? $article['date_publication'] ?>",
        "wordCount": <?= str_word_count(strip_tags($article['contenu_html'])) ?>,
        "timeRequired": "PT<?= (int)$article['read_time'] ?>M",
        "inLanguage": "<?= SITE_LOCALE ?>",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= SITE_URL ?>/<?= escape($article['slug']) ?>"
        },
        "author": {
            "@type": "Person",
            "name": "<?= escape(SITE_AUTHOR) ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "<?= escape(SITE_NAME) ?>",
            "url": "<?= SITE_URL ?>"
        }
    }
    </script>
    <?php else: ?>
    <title>Page introuvable — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="<?= escape(SITE_DESC) ?>">
    <?php endif; ?>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="<?= url('assets/css/style.css') ?>" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" as="style">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (async loading) -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></noscript>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">

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
                    <li class="nav-item"><a class="nav-link" href="<?= url('articles') ?>">Articles</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main role="main" id="main-content">
    <?php if ($not_found): ?>
    <!-- PAGE 404 -->
    <div class="container text-center py-5">
        <div class="py-5">
            <div class="display-1 text-muted fw-bold">404</div>
            <h1 class="h3 mt-3">Article introuvable</h1>
            <p class="text-muted">La page que vous recherchez n'existe pas ou a été déplacée.</p>
            <a href="<?= url() ?>" class="btn-read-more mt-3">← Retour à l'accueil</a>
        </div>
    </div>

    <?php else: ?>

    <!-- BREADCRUMB -->
    <div class="container mt-3">
        <nav class="breadcrumb-custom" aria-label="Fil d'Ariane">
            <a href="<?= url() ?>">Accueil</a>
            <span class="mx-2" aria-hidden="true">›</span>
            <a href="<?= url('articles') ?>"><?= escape($article['categorie']) ?></a>
            <span class="mx-2" aria-hidden="true">›</span>
            <span aria-current="page"><?= escape(mb_substr($article['titre'], 0, 40)) ?>...</span>
        </nav>
    </div>

    <!-- EN-TÊTE ARTICLE -->
    <header class="container mt-4">
        <div class="article-header text-center">
            <span class="article-category-badge"><?= escape($article['categorie']) ?></span>
            <h1 class="article-title"><?= escape($article['titre']) ?></h1>
            <p class="article-intro mt-4"><?= escape(excerpt($article['contenu_html'], 200)) ?></p>
            <div class="article-meta mt-4 d-flex justify-content-center gap-3 flex-wrap">
                <span><?= formatDate($article['date_publication']) ?></span>
                <span>•</span>
                <span><?= (int)$article['read_time'] ?> min de lecture</span>
                <span>•</span>
                <span><?= escape(SITE_AUTHOR) ?></span>
            </div>
        </div>
    </header>

    <!-- IMAGE HERO -->
    <div class="container mt-4">
        <img src="<?= escape($article['image']) ?>" alt="<?= escape($article['titre']) ?>" class="article-hero-img" loading="eager">
    </div>

    <!-- LAYOUT ARTICLE -->
    <div class="container mt-4">
        <div class="row">
            <!-- Colonne contenu -->
            <div class="col-lg-8">
                <div class="article-content">
                    <?= $contenu_modifie ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                <div class="article-tags mt-4">
                    <?php foreach ($tags as $tag): ?>
                    <a href="#" class="tag me-1 mb-1"><?= escape($tag) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <aside class="sidebar">
                    <!-- Widget Sommaire -->
                    <?php if (!empty($h2_titles)): ?>
                    <nav class="sidebar-widget" role="navigation" aria-labelledby="widget-toc-title">
                        <div class="widget-title" id="widget-toc-title">Sommaire</div>
                        <?php foreach ($h2_titles as $index => $title): ?>
                        <a href="#section-<?= $index ?>" class="toc-link"><?= strip_tags($title) ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>

                    <!-- Widget Nouveaux articles -->
                    <?php if (!empty($latest)): ?>
                    <div class="sidebar-widget" role="complementary" aria-labelledby="widget-latest-title">
                        <div class="widget-title" id="widget-latest-title">Nouveaux articles</div>
                        <?php foreach ($latest as $index => $art): ?>
                        <a href="<?= url(escape($art['slug'])) ?>" class="sidebar-article-item <?= $index < count($latest) - 1 ? 'border-bottom' : '' ?>" aria-label="Lire : <?= escape($art['titre']) ?>">
                            <img src="<?= escape($art['image']) ?>" alt="">
                            <div>
                                <div class="sidebar-article-title"><?= escape($art['titre']) ?></div>
                                <small class="text-muted"><?= formatDate($art['date_publication']) ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Widget Articles similaires -->
                    <?php if (!empty($related)): ?>
                    <div class="sidebar-widget" role="complementary" aria-labelledby="widget-related-title">
                        <div class="widget-title" id="widget-related-title">Articles similaires</div>
                        <?php foreach ($related as $index => $art): ?>
                        <a href="<?= url(escape($art['slug'])) ?>" class="sidebar-article-item <?= $index < count($related) - 1 ? 'border-bottom' : '' ?>" aria-label="Lire : <?= escape($art['titre']) ?>">
                            <img src="<?= escape($art['image']) ?>" alt="">
                            <div>
                                <div class="sidebar-article-title"><?= escape($art['titre']) ?></div>
                                <small class="text-muted"><?= formatDate($art['date_publication']) ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>

    <!-- ARTICLES SIMILAIRES -->
    <?php if (!empty($related)): ?>
    <section class="container mt-5 mb-5" aria-labelledby="section-related-title">
        <div class="d-flex align-items-center mb-4">
            <h2 class="h4 mb-0" id="section-related-title">Articles similaires</h2>
            <hr class="flex-grow-1 ms-3" style="border-color: var(--border);" aria-hidden="true">
        </div>
        <div class="row g-4">
            <?php foreach ($related as $index => $art): ?>
            <div class="col-md-4">
                <a href="<?= url(escape($art['slug'])) ?>" class="text-decoration-none" aria-label="Lire l'article : <?= escape($art['titre']) ?>">
                    <article class="article-card fade-up delay-<?= $index + 1 ?>">
                        <div class="card-img-wrap position-relative">
                            <span class="card-category-badge"><?= escape($art['categorie']) ?></span>
                            <img src="<?= escape($art['image']) ?>" alt="" loading="lazy">
                        </div>
                        <div class="p-3">
                            <div class="text-muted small mb-2"><?= formatDate($art['date_publication']) ?></div>
                            <h3 class="card-title text-dark"><?= escape($art['titre']) ?></h3>
                            <p class="card-excerpt mt-2"><?= escape(excerpt($art['contenu_html'], 100)) ?></p>
                        </div>
                        <div class="px-3 pb-3 mt-auto d-flex justify-content-between align-items-center">
                            <span class="btn-read-more small">Lire <span aria-hidden="true">→</span></span>
                            <span class="text-muted small"><?= (int)$art['read_time'] ?> min</span>
                        </div>
                    </article>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

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
    <script defer src="<?= url('assets/js/main.js') ?>"></script>

</body>
</html>
