<?php
require_once __DIR__ . '/config.php';

// Configuration timezone
date_default_timezone_set(SITE_TIMEZONE);

// Headers pour XML
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=' . (int)SITE_CACHE_TTL);

$pdo = getDB();

// Récupérer tous les articles publiés uniquement
$stmt = $pdo->prepare("
    SELECT slug, date_publication
    FROM articles
    WHERE statut = 'publie'
      AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
    ORDER BY date_publication DESC
");
$stmt->execute();
$articles = $stmt->fetchAll();

// Date de dernière modification du site (dernier article)
$lastmod_home = date('Y-m-d');
if (!empty($articles)) {
    $lastmod_home = date('Y-m-d', strtotime($articles[0]['date_publication']));
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Page d'accueil -->
    <url>
        <loc><?= SITE_URL ?></loc>
        <lastmod><?= $lastmod_home ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Page articles -->
    <url>
        <loc><?= SITE_URL ?>/articles</loc>
        <lastmod><?= $lastmod_home ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Articles individuels -->
<?php
// Récupérer les catégories
$categories = $pdo->query("SELECT DISTINCT categorie FROM articles WHERE statut='publie' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
?>

    <!-- Pages catégories -->
<?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= SITE_URL ?>/categorie/<?= htmlspecialchars(categorie_slug($cat), ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= $lastmod_home ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>

    <!-- Articles individuels -->
<?php foreach ($articles as $article):
    $lastmod = date('Y-m-d', strtotime($article['date_publication']));
?>
    <url>
        <loc><?= SITE_URL ?>/<?= htmlspecialchars($article['slug'], ENT_XML1, 'UTF-8') ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>

    <!-- Pages légales -->
    <url>
        <loc><?= SITE_URL ?>/mentions-legales</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/politique-confidentialite</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
    <url>
        <loc><?= SITE_URL ?>/cgu</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>
</urlset>
