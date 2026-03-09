<?php
// Compression GZIP (seulement si pas déjà actif via index.php)
if (ob_get_level() === 0) {
    if (!ob_start("ob_gzhandler")) {
        ob_start();
    }
}

require_once __DIR__ . '/config.php';

// Headers HTTP sécurité et cache
header('Cache-Control: public, max-age=3600');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions légales — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="Mentions légales du site <?= escape(SITE_NAME) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

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
                    <li class="nav-item"><a class="nav-link" href="<?= url('articles') ?>">Articles</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENU -->
    <main class="container py-5" role="main" id="main-content">
        <h1 class="mb-4">Mentions légales</h1>

        <!-- Éditeur du site -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Éditeur du site</h2>
                <p class="mb-1"><strong>Nom du site :</strong> <?= escape(SITE_NAME) ?></p>
                <p class="mb-1"><strong>URL :</strong> <?= escape(SITE_URL) ?></p>
                <p class="mb-0"><strong>Directeur de la publication :</strong> <?= escape(SITE_AUTHOR) ?></p>
            </div>
        </div>

        <!-- Hébergement -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Hébergement</h2>
                <p class="mb-0">Ce site est hébergé sur un serveur mutualisé. Pour toute question relative à l'hébergement, veuillez nous contacter via les coordonnées ci-dessous.</p>
            </div>
        </div>

        <!-- Propriété intellectuelle -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Propriété intellectuelle</h2>
                <p class="mb-2">L'ensemble du contenu de ce site (textes, images, vidéos, logos, icônes, etc.) est protégé par le droit d'auteur et le droit des marques.</p>
                <p class="mb-0">Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sauf autorisation écrite préalable de <?= escape(SITE_NAME) ?>.</p>
            </div>
        </div>

        <!-- Cookies -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Cookies</h2>
                <p class="mb-2">Ce site n'utilise aucun cookie de tracking ou de publicité.</p>
                <p class="mb-0">Seuls des cookies techniques strictement nécessaires au fonctionnement du site peuvent être déposés. Ces cookies ne collectent aucune donnée personnelle.</p>
            </div>
        </div>

        <!-- Protection des données -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Protection des données personnelles</h2>
                <p class="mb-2">Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant.</p>
                <p class="mb-0">Pour exercer ce droit, veuillez nous contacter à l'adresse indiquée ci-dessous.</p>
            </div>
        </div>

        <!-- Contact -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Contact</h2>
                <p class="mb-0">Pour toute question ou demande, vous pouvez nous contacter à l'adresse suivante : <a href="mailto:contact@<?= escape(SITE_DOMAIN) ?>">contact@<?= escape(SITE_DOMAIN) ?></a></p>
            </div>
        </div>
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
    <script defer src="/assets/js/main.js"></script>

</body>
</html>
