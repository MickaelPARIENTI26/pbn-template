<?php
require_once __DIR__ . '/config.php';

date_default_timezone_set(SITE_TIMEZONE);

header('Cache-Control: public, max-age=' . (int)SITE_CACHE_TTL);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

$pdo = getDB();
?>
<!DOCTYPE html>
<html lang="<?= SITE_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de confidentialité — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="Politique de confidentialité de <?= escape(SITE_NAME) ?>. Découvrez comment nous collectons, utilisons et protégeons vos données personnelles.">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <link rel="canonical" href="<?= SITE_URL ?>/politique-confidentialite">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="Politique de confidentialité — <?= escape(SITE_NAME) ?>">
    <meta property="og:description" content="Politique de confidentialité de <?= escape(SITE_NAME) ?>. Découvrez comment nous collectons, utilisons et protégeons vos données personnelles.">
    <meta property="og:url" content="<?= SITE_URL ?>/politique-confidentialite">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

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
        <div class="legal-page">
            <h1>Politique de confidentialité</h1>
            <div class="section-divider"></div>
            <p class="legal-date">Dernière mise à jour : <?= date('d/m/Y') ?></p>

            <div class="legal-content">
                <h2>1. Introduction</h2>
                <p><?= escape(SITE_NAME) ?> (ci-après « nous », « notre » ou « le site ») s'engage à protéger la vie privée des utilisateurs de son site <?= escape(SITE_DOMAIN) ?>. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos informations personnelles conformément au Règlement Général sur la Protection des Données (RGPD).</p>

                <h2>2. Collecte des données</h2>
                <p>Nous collectons les données suivantes :</p>
                <ul>
                    <li><strong>Données de navigation :</strong> adresse IP, type de navigateur, pages visitées, durée de visite, via Google Analytics (si activé)</li>
                    <li><strong>Données techniques :</strong> informations sur votre appareil et votre connexion</li>
                </ul>
                <p>Ces données sont collectées de manière anonymisée et servent uniquement à améliorer l'expérience utilisateur et à analyser le trafic du site.</p>

                <h2>3. Utilisation des cookies</h2>
                <p>Notre site utilise des cookies pour :</p>
                <ul>
                    <li><strong>Cookies essentiels :</strong> nécessaires au fonctionnement du site</li>
                    <li><strong>Cookies analytiques :</strong> permettent de mesurer l'audience du site (Google Analytics)</li>
                </ul>
                <p>Vous pouvez configurer votre navigateur pour refuser les cookies. Cependant, certaines fonctionnalités du site pourraient ne plus être disponibles.</p>

                <h2>4. Vos droits RGPD</h2>
                <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul>
                    <li><strong>Droit d'accès :</strong> obtenir une copie de vos données personnelles</li>
                    <li><strong>Droit de rectification :</strong> corriger des données inexactes</li>
                    <li><strong>Droit à l'effacement :</strong> demander la suppression de vos données</li>
                    <li><strong>Droit à la limitation :</strong> restreindre le traitement de vos données</li>
                    <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré</li>
                    <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos données</li>
                </ul>

                <h2>5. Sécurité des données</h2>
                <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données contre tout accès non autorisé, modification, divulgation ou destruction.</p>

                <h2>6. Conservation des données</h2>
                <p>Les données de navigation sont conservées pour une durée maximale de 26 mois, conformément aux recommandations de la CNIL.</p>

                <h2>7. Contact</h2>
                <p>Pour toute question relative à cette politique de confidentialité ou pour exercer vos droits, vous pouvez nous contacter à l'adresse suivante :</p>
                <p><strong>Email :</strong> contact@<?= escape(SITE_DOMAIN) ?></p>

                <h2>8. Modifications</h2>
                <p>Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment. Les modifications entreront en vigueur dès leur publication sur cette page.</p>
            </div>
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
