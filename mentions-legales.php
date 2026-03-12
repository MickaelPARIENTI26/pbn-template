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
    <title>Mentions légales — <?= escape(SITE_NAME) ?></title>
    <meta name="description" content="Mentions légales du site <?= escape(SITE_NAME) ?>. Informations sur l'éditeur, l'hébergement et la propriété intellectuelle.">
    <meta name="robots" content="<?= SITE_ROBOTS ?>">
    <link rel="canonical" href="<?= SITE_URL ?>/mentions-legales">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= escape(SITE_NAME) ?>">
    <meta property="og:title" content="Mentions légales — <?= escape(SITE_NAME) ?>">
    <meta property="og:description" content="Mentions légales du site <?= escape(SITE_NAME) ?>. Informations sur l'éditeur, l'hébergement et la propriété intellectuelle.">
    <meta property="og:url" content="<?= SITE_URL ?>/mentions-legales">
    <meta property="og:locale" content="<?= SITE_LOCALE ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= url('favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('favicon.svg') ?>">

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

    <main role="main" id="main-content">
        <div class="legal-page">
            <h1>Mentions légales</h1>
            <div class="section-divider"></div>
            <p class="legal-date">Dernière mise à jour : <?= date('d/m/Y') ?></p>

            <div class="legal-content">
                <h2>1. Éditeur du site</h2>
                <p>Le site <?= escape(SITE_DOMAIN) ?> est édité par :</p>
                <p><strong><?= escape(SITE_NAME) ?></strong><br>
                URL : <?= escape(SITE_URL) ?><br>
                Directeur de la publication : <?= escape(SITE_AUTHOR) ?><br>
                Email : contact@<?= escape(SITE_DOMAIN) ?></p>

                <h2>2. Hébergement</h2>
                <p>Le site est hébergé par :</p>
                <p><strong>Railway Corporation</strong><br>
                548 Market Street, PMB 68956<br>
                San Francisco, CA 94104<br>
                États-Unis<br>
                Site web : <a href="https://railway.app" target="_blank" rel="noopener">railway.app</a></p>

                <h2>3. Propriété intellectuelle</h2>
                <h3>3.1 Droits d'auteur</h3>
                <p>L'ensemble du contenu présent sur le site <?= escape(SITE_DOMAIN) ?> (textes, articles, photographies, illustrations, graphismes, logos, icônes, sons, logiciels, etc.) est protégé par les dispositions du Code de la propriété intellectuelle et notamment par le droit d'auteur.</p>
                <p>Ce contenu est la propriété exclusive de <?= escape(SITE_NAME) ?> ou de tiers ayant autorisé <?= escape(SITE_NAME) ?> à l'utiliser.</p>

                <h3>3.2 Marques</h3>
                <p>Les marques et logos présents sur le site sont des marques déposées. Toute reproduction, imitation ou usage de ces marques sans autorisation préalable constitue une contrefaçon passible de poursuites.</p>

                <h3>3.3 Utilisation du contenu</h3>
                <p>Toute reproduction, représentation, modification, publication, transmission, dénaturation, totale ou partielle du site ou de son contenu, par quelque procédé que ce soit, et sur quelque support que ce soit, est interdite sans l'autorisation écrite préalable de <?= escape(SITE_NAME) ?>.</p>
                <p>Sont autorisés :</p>
                <ul>
                    <li>La consultation du site pour un usage personnel et privé</li>
                    <li>Le partage des liens vers les articles sur les réseaux sociaux</li>
                    <li>La citation de courts extraits avec mention obligatoire de la source et lien vers l'article original</li>
                </ul>

                <h2>4. Limitation de responsabilité</h2>
                <h3>4.1 Exactitude des informations</h3>
                <p><?= escape(SITE_NAME) ?> s'efforce de fournir sur le site des informations aussi précises que possible. Toutefois, il ne pourra être tenu responsable des omissions, des inexactitudes et des carences dans la mise à jour, qu'elles soient de son fait ou du fait des tiers partenaires qui lui fournissent ces informations.</p>

                <h3>4.2 Disponibilité du site</h3>
                <p><?= escape(SITE_NAME) ?> ne peut garantir que le site sera accessible de manière continue et sans interruption. En cas d'interruption pour maintenance ou mise à jour, <?= escape(SITE_NAME) ?> s'efforcera de prévenir les utilisateurs.</p>

                <h3>4.3 Liens hypertextes</h3>
                <p>Le site peut contenir des liens hypertextes vers d'autres sites. <?= escape(SITE_NAME) ?> n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu ou leurs pratiques.</p>

                <h2>5. Cookies</h2>
                <p>Ce site utilise des cookies pour améliorer l'expérience utilisateur. Pour plus d'informations sur l'utilisation des cookies, veuillez consulter notre <a href="<?= url('politique-confidentialite') ?>">Politique de confidentialité</a>.</p>

                <h2>6. Protection des données personnelles</h2>
                <p>Conformément au Règlement Général sur la Protection des Données (RGPD) du 25 mai 2018, vous disposez de droits sur vos données personnelles (accès, rectification, suppression, etc.).</p>
                <p>Pour plus d'informations sur la collecte et le traitement de vos données, veuillez consulter notre <a href="<?= url('politique-confidentialite') ?>">Politique de confidentialité</a>.</p>

                <h2>7. Droit applicable et juridiction</h2>
                <p>Les présentes mentions légales sont régies par le droit français. En cas de litige, et après tentative de recherche d'une solution amiable, compétence est attribuée aux tribunaux français.</p>

                <h2>8. Contact</h2>
                <p>Pour toute question concernant ces mentions légales ou le site en général, vous pouvez nous contacter :</p>
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
