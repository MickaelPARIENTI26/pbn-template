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
                <h2>1. Objet et acceptation</h2>
                <p>Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») ont pour objet de définir les modalités et conditions d'accès et d'utilisation du site internet <?= escape(SITE_DOMAIN) ?> (ci-après « le Site »), édité par <?= escape(SITE_NAME) ?>.</p>
                <p>L'accès au Site et son utilisation impliquent l'acceptation pleine, entière et sans réserve des présentes CGU par l'utilisateur. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser ce Site.</p>
                <p>Ces CGU sont applicables à tout utilisateur du Site, qu'il soit simple visiteur ou utilisateur régulier.</p>

                <h2>2. Définitions</h2>
                <p>Dans les présentes CGU, les termes suivants ont la signification indiquée ci-dessous :</p>
                <ul>
                    <li><strong>« Site » :</strong> désigne le site internet accessible à l'adresse <?= escape(SITE_DOMAIN) ?></li>
                    <li><strong>« Éditeur » :</strong> désigne <?= escape(SITE_NAME) ?>, responsable de l'édition et du contenu du Site</li>
                    <li><strong>« Utilisateur » :</strong> désigne toute personne qui accède au Site et le consulte</li>
                    <li><strong>« Contenu » :</strong> désigne l'ensemble des informations, textes, images, vidéos et autres éléments présents sur le Site</li>
                </ul>

                <h2>3. Accès au site</h2>
                <h3>3.1 Conditions d'accès</h3>
                <p>Le Site est accessible gratuitement à tout Utilisateur disposant d'un accès à Internet. L'ensemble des coûts liés à l'accès au Site (matériel informatique, logiciels, connexion Internet, etc.) sont exclusivement à la charge de l'Utilisateur.</p>

                <h3>3.2 Disponibilité</h3>
                <p>L'Éditeur s'efforce de maintenir le Site accessible 24 heures sur 24, 7 jours sur 7. Toutefois, l'Éditeur se réserve le droit de suspendre, interrompre ou limiter l'accès à tout ou partie du Site pour des raisons de maintenance, de mise à jour, d'amélioration ou pour toute autre raison, notamment technique.</p>
                <p>L'Éditeur ne pourra en aucun cas être tenu responsable des conséquences d'une indisponibilité du Site.</p>

                <h3>3.3 Prérequis techniques</h3>
                <p>Pour accéder au Site dans des conditions optimales, l'Utilisateur doit disposer :</p>
                <ul>
                    <li>D'un navigateur web récent (Chrome, Firefox, Safari, Edge)</li>
                    <li>D'une connexion Internet stable</li>
                    <li>De JavaScript activé</li>
                </ul>

                <h2>4. Propriété intellectuelle</h2>
                <h3>4.1 Droits de l'Éditeur</h3>
                <p>L'ensemble des éléments constituant le Site (textes, articles, photographies, images, graphismes, logos, icônes, sons, logiciels, mise en page, base de données, etc.) est protégé par les dispositions du Code de la propriété intellectuelle relatives au droit d'auteur, au droit des marques et au droit des bases de données.</p>
                <p>Ces éléments sont la propriété exclusive de <?= escape(SITE_NAME) ?> ou de leurs auteurs respectifs ayant autorisé leur utilisation.</p>

                <h3>4.2 Utilisations interdites</h3>
                <p>Sans autorisation écrite préalable de l'Éditeur, sont strictement interdits :</p>
                <ul>
                    <li>La reproduction totale ou partielle du Contenu sur tout support</li>
                    <li>La représentation, diffusion ou communication au public du Contenu</li>
                    <li>La modification, adaptation ou transformation du Contenu</li>
                    <li>L'extraction ou la réutilisation d'une partie substantielle de la base de données</li>
                    <li>L'utilisation du Contenu à des fins commerciales</li>
                    <li>Le scraping, crawling ou toute extraction automatisée du Contenu</li>
                </ul>

                <h3>4.3 Utilisations autorisées</h3>
                <p>L'Utilisateur est autorisé à :</p>
                <ul>
                    <li>Consulter le Site pour son usage personnel et privé</li>
                    <li>Partager les liens vers les articles sur les réseaux sociaux</li>
                    <li>Citer de courts extraits du Contenu à condition de mentionner la source et d'inclure un lien vers l'article original</li>
                </ul>

                <h2>5. Contenu du site</h2>
                <h3>5.1 Nature du contenu</h3>
                <p>Le Site propose des articles informatifs et éducatifs. Les informations publiées sont fournies à titre purement indicatif et ne sauraient en aucun cas constituer :</p>
                <ul>
                    <li>Un conseil médical, juridique, financier ou professionnel</li>
                    <li>Un diagnostic ou un traitement</li>
                    <li>Une recommandation personnalisée</li>
                </ul>

                <h3>5.2 Exactitude des informations</h3>
                <p><?= escape(SITE_NAME) ?> s'efforce de fournir des informations exactes, complètes et actualisées. Cependant, l'Éditeur ne garantit pas l'exactitude, la complétude, l'actualité ou la pertinence des informations diffusées sur le Site.</p>
                <p>L'Utilisateur est invité à vérifier les informations auprès de sources officielles et à consulter des professionnels qualifiés pour toute décision importante.</p>

                <h3>5.3 Mise à jour</h3>
                <p>Le Contenu du Site peut être modifié ou mis à jour à tout moment sans préavis. Les articles sont datés afin de permettre à l'Utilisateur d'apprécier leur actualité.</p>

                <h2>6. Responsabilité</h2>
                <h3>6.1 Limitation de responsabilité</h3>
                <p><?= escape(SITE_NAME) ?> ne pourra être tenu responsable :</p>
                <ul>
                    <li>Des dommages directs ou indirects, matériels ou immatériels, résultant de l'accès ou de l'utilisation du Site</li>
                    <li>De l'impossibilité d'accéder au Site ou des interruptions temporaires</li>
                    <li>De tout préjudice résultant de l'utilisation des informations publiées sur le Site</li>
                    <li>De la présence de virus ou d'éléments nuisibles sur le Site</li>
                    <li>Des actes de tiers, notamment l'utilisation frauduleuse du Site</li>
                    <li>Du contenu des sites tiers accessibles via des liens hypertextes</li>
                    <li>Des décisions prises sur la base des informations du Site</li>
                </ul>

                <h3>6.2 Responsabilité de l'Utilisateur</h3>
                <p>L'Utilisateur est seul responsable :</p>
                <ul>
                    <li>De l'utilisation qu'il fait du Site et de son Contenu</li>
                    <li>De la protection de son équipement informatique contre les virus</li>
                    <li>Du respect des présentes CGU</li>
                </ul>

                <h2>7. Liens hypertextes</h2>
                <h3>7.1 Liens sortants</h3>
                <p>Le Site peut contenir des liens vers des sites tiers. Ces liens sont fournis à titre informatif. <?= escape(SITE_NAME) ?> n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu, leur disponibilité ou leurs pratiques en matière de protection des données.</p>
                <p>L'activation de ces liens se fait sous la seule responsabilité de l'Utilisateur.</p>

                <h3>7.2 Liens entrants</h3>
                <p>La création de liens vers le Site est autorisée sans autorisation préalable, sous réserve que :</p>
                <ul>
                    <li>Les liens ne portent pas atteinte à l'image de <?= escape(SITE_NAME) ?></li>
                    <li>Les pages du Site ne soient pas intégrées dans les pages d'un autre site (framing)</li>
                    <li>La source soit clairement identifiée</li>
                </ul>

                <h2>8. Comportement de l'utilisateur</h2>
                <p>L'Utilisateur s'engage à utiliser le Site de manière responsable et à ne pas :</p>
                <ul>
                    <li>Porter atteinte à la sécurité ou à l'intégrité du Site</li>
                    <li>Tenter d'accéder à des zones non autorisées du Site</li>
                    <li>Perturber le fonctionnement normal du Site</li>
                    <li>Utiliser le Site à des fins illégales ou contraires à l'ordre public</li>
                    <li>Collecter des données personnelles sans autorisation</li>
                    <li>Utiliser des robots, scrapers ou tout autre moyen automatisé</li>
                </ul>

                <h2>9. Données personnelles</h2>
                <p>La collecte et le traitement des données personnelles sont régis par notre <a href="<?= url('politique-confidentialite') ?>">Politique de confidentialité</a>, qui fait partie intégrante des présentes CGU.</p>
                <p>En utilisant le Site, vous reconnaissez avoir pris connaissance de cette politique et acceptez les pratiques qui y sont décrites.</p>

                <h2>10. Cookies</h2>
                <p>Le Site utilise des cookies et technologies similaires. Pour plus d'informations sur les cookies utilisés et la manière de les gérer, veuillez consulter notre <a href="<?= url('politique-confidentialite') ?>">Politique de confidentialité</a>.</p>

                <h2>11. Modification des CGU</h2>
                <p><?= escape(SITE_NAME) ?> se réserve le droit de modifier les présentes CGU à tout moment, afin notamment de les adapter aux évolutions légales ou techniques.</p>
                <p>Les CGU applicables sont celles en vigueur à la date de votre connexion au Site. La date de dernière mise à jour est indiquée en haut de cette page.</p>
                <p>Il appartient à l'Utilisateur de consulter régulièrement les CGU. La poursuite de l'utilisation du Site après modification des CGU vaut acceptation des nouvelles conditions.</p>

                <h2>12. Nullité partielle</h2>
                <p>Si une ou plusieurs stipulations des présentes CGU venaient à être déclarées nulles ou inapplicables par une décision de justice, les autres stipulations conserveraient toute leur force et leur portée.</p>

                <h2>13. Droit applicable et juridiction</h2>
                <p>Les présentes CGU sont régies par le droit français.</p>
                <p>En cas de litige relatif à l'interprétation, la validité ou l'exécution des présentes CGU, les parties s'efforceront de résoudre leur différend à l'amiable.</p>
                <p>À défaut d'accord amiable dans un délai de 30 jours, le litige sera soumis aux tribunaux compétents du ressort de la Cour d'appel de Paris.</p>

                <h2>14. Contact</h2>
                <p>Pour toute question, réclamation ou demande d'information concernant les présentes CGU ou le Site, vous pouvez nous contacter :</p>
                <p><strong>Email :</strong> contact@<?= escape(SITE_DOMAIN) ?></p>
                <p>Nous nous engageons à répondre à votre demande dans les meilleurs délais.</p>
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
