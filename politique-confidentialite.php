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
                <p><?= escape(SITE_NAME) ?> (ci-après « nous », « notre » ou « le site ») s'engage à protéger la vie privée des utilisateurs de son site <?= escape(SITE_DOMAIN) ?>. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos informations personnelles conformément au Règlement Général sur la Protection des Données (RGPD) du 25 mai 2018.</p>
                <p>En naviguant sur notre site, vous acceptez les pratiques décrites dans la présente politique. Nous vous invitons à la lire attentivement et à la consulter régulièrement, car elle peut être mise à jour.</p>

                <h2>2. Responsable du traitement</h2>
                <p>Le responsable du traitement des données personnelles collectées sur le site <?= escape(SITE_DOMAIN) ?> est :</p>
                <p><strong><?= escape(SITE_NAME) ?></strong><br>
                Email : contact@<?= escape(SITE_DOMAIN) ?></p>

                <h2>3. Données collectées</h2>
                <p>Dans le cadre de votre navigation sur notre site, nous sommes susceptibles de collecter les catégories de données suivantes :</p>

                <h3>3.1 Données de navigation</h3>
                <ul>
                    <li>Adresse IP (anonymisée)</li>
                    <li>Type et version du navigateur</li>
                    <li>Système d'exploitation</li>
                    <li>Pages visitées et durée de consultation</li>
                    <li>Source de trafic (moteur de recherche, lien direct, etc.)</li>
                    <li>Date et heure de connexion</li>
                </ul>

                <h3>3.2 Données techniques</h3>
                <ul>
                    <li>Résolution d'écran</li>
                    <li>Langue du navigateur</li>
                    <li>Informations sur l'appareil utilisé (mobile, tablette, ordinateur)</li>
                </ul>
                <p>Ces données sont collectées de manière automatique lors de votre navigation et ne permettent pas de vous identifier directement.</p>

                <h2>4. Finalités du traitement</h2>
                <p>Les données collectées sont utilisées pour les finalités suivantes :</p>
                <ul>
                    <li><strong>Analyse statistique :</strong> comprendre comment les visiteurs utilisent notre site afin d'améliorer son contenu et son ergonomie</li>
                    <li><strong>Amélioration de l'expérience utilisateur :</strong> adapter l'affichage et le contenu à vos préférences</li>
                    <li><strong>Sécurité :</strong> détecter et prévenir les activités frauduleuses ou malveillantes</li>
                    <li><strong>Performance :</strong> optimiser le temps de chargement et la stabilité du site</li>
                </ul>

                <h2>5. Base légale du traitement</h2>
                <p>Le traitement de vos données repose sur les bases légales suivantes :</p>
                <ul>
                    <li><strong>Votre consentement :</strong> pour les cookies non essentiels (analytics)</li>
                    <li><strong>Notre intérêt légitime :</strong> pour assurer la sécurité et le bon fonctionnement du site</li>
                </ul>

                <h2>6. Cookies et technologies similaires</h2>
                <p>Notre site utilise des cookies, qui sont de petits fichiers texte stockés sur votre appareil. Voici les types de cookies utilisés :</p>

                <h3>6.1 Cookies strictement nécessaires</h3>
                <p>Ces cookies sont indispensables au fonctionnement du site. Ils ne peuvent pas être désactivés. Ils incluent notamment les cookies de session et de préférences techniques.</p>

                <h3>6.2 Cookies analytiques (Google Analytics)</h3>
                <p>Si activés, ces cookies nous permettent de mesurer l'audience du site et d'analyser le comportement des visiteurs. Les données collectées sont :</p>
                <ul>
                    <li>Anonymisées (les deux derniers octets de l'adresse IP sont supprimés)</li>
                    <li>Non croisées avec d'autres données Google</li>
                    <li>Non utilisées à des fins publicitaires</li>
                </ul>

                <h3>6.3 Gestion des cookies</h3>
                <p>Vous pouvez à tout moment gérer vos préférences en matière de cookies :</p>
                <ul>
                    <li><strong>Via votre navigateur :</strong> la plupart des navigateurs permettent de bloquer ou supprimer les cookies dans leurs paramètres</li>
                    <li><strong>Via les outils Google :</strong> vous pouvez désactiver Google Analytics en installant le module complémentaire de navigateur disponible sur <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">tools.google.com/dlpage/gaoptout</a></li>
                </ul>
                <p>Note : la désactivation de certains cookies peut affecter votre expérience de navigation.</p>

                <h2>7. Partage des données</h2>
                <p>Vos données ne sont jamais vendues à des tiers. Elles peuvent toutefois être partagées avec :</p>
                <ul>
                    <li><strong>Google Analytics :</strong> pour l'analyse d'audience (données anonymisées)</li>
                    <li><strong>Hébergeur web :</strong> dans le cadre technique de l'hébergement du site</li>
                    <li><strong>Autorités compétentes :</strong> si requis par la loi</li>
                </ul>

                <h2>8. Transferts internationaux</h2>
                <p>Certaines données peuvent être transférées vers des pays situés hors de l'Union Européenne (notamment les États-Unis pour Google Analytics). Ces transferts sont encadrés par des garanties appropriées (clauses contractuelles types, certification Privacy Shield ou équivalent).</p>

                <h2>9. Durée de conservation</h2>
                <p>Les données sont conservées pour les durées suivantes :</p>
                <ul>
                    <li><strong>Données de navigation :</strong> 26 mois maximum (conformément aux recommandations CNIL)</li>
                    <li><strong>Cookies :</strong> 13 mois maximum à compter de leur dépôt</li>
                    <li><strong>Logs serveur :</strong> 12 mois</li>
                </ul>

                <h2>10. Vos droits</h2>
                <p>Conformément au RGPD, vous disposez des droits suivants concernant vos données personnelles :</p>
                <ul>
                    <li><strong>Droit d'accès (art. 15) :</strong> obtenir la confirmation que des données vous concernant sont traitées et en recevoir une copie</li>
                    <li><strong>Droit de rectification (art. 16) :</strong> demander la correction de données inexactes ou incomplètes</li>
                    <li><strong>Droit à l'effacement (art. 17) :</strong> demander la suppression de vos données dans certaines conditions</li>
                    <li><strong>Droit à la limitation (art. 18) :</strong> demander la suspension temporaire du traitement</li>
                    <li><strong>Droit à la portabilité (art. 20) :</strong> recevoir vos données dans un format structuré et couramment utilisé</li>
                    <li><strong>Droit d'opposition (art. 21) :</strong> vous opposer au traitement de vos données</li>
                    <li><strong>Droit de retirer votre consentement :</strong> à tout moment, sans affecter la licéité du traitement antérieur</li>
                </ul>
                <p>Pour exercer ces droits, contactez-nous à : <strong>contact@<?= escape(SITE_DOMAIN) ?></strong></p>
                <p>Nous nous engageons à répondre à votre demande dans un délai d'un mois. Ce délai peut être prolongé de deux mois supplémentaires pour les demandes complexes.</p>

                <h2>11. Réclamation auprès de la CNIL</h2>
                <p>Si vous estimez que le traitement de vos données ne respecte pas la réglementation, vous avez le droit d'introduire une réclamation auprès de la Commission Nationale de l'Informatique et des Libertés (CNIL) :</p>
                <p>CNIL - 3 Place de Fontenoy - TSA 80715 - 75334 Paris Cedex 07<br>
                Site web : <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a></p>

                <h2>12. Sécurité des données</h2>
                <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données :</p>
                <ul>
                    <li>Connexion sécurisée HTTPS</li>
                    <li>Serveurs sécurisés et régulièrement mis à jour</li>
                    <li>Accès restreint aux données</li>
                    <li>Anonymisation des données sensibles</li>
                </ul>

                <h2>13. Mineurs</h2>
                <p>Notre site n'est pas destiné aux personnes de moins de 16 ans. Nous ne collectons pas sciemment de données concernant des mineurs. Si vous êtes parent ou tuteur et pensez que votre enfant nous a fourni des informations, contactez-nous pour que nous puissions les supprimer.</p>

                <h2>14. Modifications de la politique</h2>
                <p>Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment. En cas de modification substantielle, nous vous en informerons par une mention visible sur le site. La date de dernière mise à jour est indiquée en haut de cette page.</p>

                <h2>15. Contact</h2>
                <p>Pour toute question relative à cette politique de confidentialité, à vos données personnelles ou pour exercer vos droits, contactez-nous :</p>
                <p><strong>Email :</strong> contact@<?= escape(SITE_DOMAIN) ?></p>
                <p>Nous nous efforçons de répondre à toutes les demandes dans les meilleurs délais.</p>
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
