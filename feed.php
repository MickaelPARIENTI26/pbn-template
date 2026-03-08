<?php
require_once __DIR__ . '/config.php';

// Configuration timezone
date_default_timezone_set(SITE_TIMEZONE);

// Headers pour RSS
header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=' . (int)SITE_CACHE_TTL);

$pdo = getDB();

// Récupérer les 20 derniers articles publiés
$stmt = $pdo->prepare("
    SELECT * FROM articles
    WHERE statut = 'publie'
      AND (date_publication_prevue IS NULL OR date_publication_prevue <= NOW())
    ORDER BY date_publication DESC
    LIMIT 20
");
$stmt->execute();
$articles = $stmt->fetchAll();

// Helper pour nettoyer le HTML pour RSS
if (!function_exists('cleanForRss')) {
    function cleanForRss($html) {
        $text = strip_tags($html);
        return htmlspecialchars($text, ENT_XML1, 'UTF-8');
    }
}

// Date de dernière modification
$lastBuildDate = date(DATE_RSS);
if (!empty($articles)) {
    $lastBuildDate = date(DATE_RSS, strtotime($articles[0]['date_publication']));
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><?= htmlspecialchars(SITE_NAME, ENT_XML1, 'UTF-8') ?></title>
        <link><?= SITE_URL ?></link>
        <description><?= htmlspecialchars(SITE_DESC, ENT_XML1, 'UTF-8') ?></description>
        <language><?= str_replace('_', '-', SITE_LOCALE) ?></language>
        <lastBuildDate><?= $lastBuildDate ?></lastBuildDate>
        <atom:link href="<?= SITE_URL ?>/feed.xml" rel="self" type="application/rss+xml"/>
        <image>
            <url><?= SITE_URL ?>/assets/apple-touch-icon.png</url>
            <title><?= htmlspecialchars(SITE_NAME, ENT_XML1, 'UTF-8') ?></title>
            <link><?= SITE_URL ?></link>
        </image>

<?php foreach ($articles as $article):
    $pubDate = date(DATE_RSS, strtotime($article['date_publication']));
    $excerpt = substr(strip_tags($article['contenu_html']), 0, 300) . '...';
?>
        <item>
            <title><?= htmlspecialchars($article['titre'], ENT_XML1, 'UTF-8') ?></title>
            <link><?= SITE_URL ?>/<?= htmlspecialchars($article['slug'], ENT_XML1, 'UTF-8') ?></link>
            <guid isPermaLink="true"><?= SITE_URL ?>/<?= htmlspecialchars($article['slug'], ENT_XML1, 'UTF-8') ?></guid>
            <pubDate><?= $pubDate ?></pubDate>
            <description><?= htmlspecialchars($excerpt, ENT_XML1, 'UTF-8') ?></description>
            <category><?= htmlspecialchars($article['categorie'], ENT_XML1, 'UTF-8') ?></category>
            <enclosure url="<?= htmlspecialchars($article['image'], ENT_XML1, 'UTF-8') ?>" type="image/jpeg"/>
        </item>
<?php endforeach; ?>
    </channel>
</rss>
