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
    <title>Conditions Générales d'Utilisation — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="Conditions générales d'utilisation du site <?= escape(SITE_NAME) ?>. Consultez les règles d'utilisation de notre site.">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <link rel="canonical" href="<?= SITE_URL ?>/cgu">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="Conditions Générales d'Utilisation — <?= escape(SITE_NAME) ?>">
    <meta property="og:description" content="Conditions générales d'utilisation du site <?= escape(SITE_NAME) ?>. Consultez les règles d'utilisation de notre site.">
    <meta property="og:url" content="<?= SITE_URL ?>/cgu">
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
            <h1>Conditions Générales d'Utilisation</h1>
            <div class="section-divider"></div>
            <p class="legal-date">Dernière mise à jour : <?= date('d/m/Y') ?></p>

            <div class="legal-content">
                <h2>1. Objet</h2>
                <p>Les présentes Conditions Générales d'Utilisation (CGU) ont pour objet de définir les modalités d'accès et d'utilisation du site <?= escape(SITE_DOMAIN) ?> (ci-après « le Site »), édité par <?= escape(SITE_NAME) ?>.</p>
                <p>L'accès au Site implique l'acceptation pleine et entière des présentes CGU.</p>

                <h2>2. Accès au site</h2>
                <p>Le Site est accessible gratuitement à tout utilisateur disposant d'un accès à Internet. Tous les coûts liés à l'accès au Site (matériel informatique, connexion Internet, etc.) sont à la charge de l'utilisateur.</p>
                <p>Nous nous réservons le droit de suspendre, interrompre ou limiter l'accès au Site pour des raisons de maintenance ou pour toute autre raison, sans préavis ni indemnité.</p>

                <h2>3. Propriété intellectuelle</h2>
                <p>L'ensemble des contenus présents sur le Site (textes, images, graphismes, logo, icônes, etc.) est protégé par les lois relatives à la propriété intellectuelle.</p>
                <p>Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments du Site est strictement interdite sans autorisation écrite préalable de <?= escape(SITE_NAME) ?>.</p>
                <p>L'utilisation du Site à des fins personnelles et non commerciales est autorisée, sous réserve de citer la source.</p>

                <h2>4. Contenu du site</h2>
                <p>Les informations publiées sur le Site sont fournies à titre indicatif et ne sauraient constituer un conseil professionnel. <?= escape(SITE_NAME) ?> s'efforce de fournir des informations exactes et à jour, mais ne garantit pas l'exactitude, la complétude ou l'actualité des informations diffusées.</p>

                <h2>5. Responsabilité</h2>
                <p><?= escape(SITE_NAME) ?> ne pourra être tenu responsable :</p>
                <ul>
                    <li>Des dommages directs ou indirects résultant de l'utilisation du Site</li>
                    <li>Des interruptions temporaires du Site</li>
                    <li>De l'impossibilité d'accéder au Site</li>
                    <li>De l'utilisation frauduleuse du Site par un tiers</li>
                    <li>Du contenu des sites tiers vers lesquels des liens hypertextes peuvent renvoyer</li>
                </ul>

                <h2>6. Liens hypertextes</h2>
                <p>Le Site peut contenir des liens vers d'autres sites. <?= escape(SITE_NAME) ?> n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu.</p>

                <h2>7. Données personnelles</h2>
                <p>La collecte et le traitement des données personnelles sont régis par notre <a href="<?= url('politique-confidentialite') ?>">Politique de confidentialité</a>.</p>

                <h2>8. Modification des CGU</h2>
                <p><?= escape(SITE_NAME) ?> se réserve le droit de modifier les présentes CGU à tout moment. Les modifications entrent en vigueur dès leur publication sur le Site. Il appartient à l'utilisateur de consulter régulièrement les CGU.</p>

                <h2>9. Droit applicable et juridiction</h2>
                <p>Les présentes CGU sont régies par le droit français.</p>
                <p>En cas de litige, et après échec de toute tentative de recherche d'une solution amiable, les tribunaux français seront seuls compétents.</p>

                <h2>10. Contact</h2>
                <p>Pour toute question concernant les présentes CGU, vous pouvez nous contacter à l'adresse suivante :</p>
                <p><strong>Email :</strong> contact@<?= escape(SITE_DOMAIN) ?></p>
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
