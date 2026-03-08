<?php
require_once __DIR__ . '/config.php';

$pdo = getDB();

// Vérifier si des articles existent déjà
$count = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();

if ($count > 0) {
    echo "La base contient déjà $count article(s). Seed annulé.";
    exit;
}

// Insérer des articles de test
$articles = [
    [
        'slug' => 'bienvenue-sur-notre-site',
        'titre' => 'Bienvenue sur notre site',
        'meta_description' => 'Découvrez notre nouveau site dédié à votre passion.',
        'contenu_html' => '<p>Bienvenue sur notre tout nouveau site ! Nous sommes ravis de vous accueillir.</p><h2>Notre mission</h2><p>Nous vous proposons des articles de qualité, rédigés par des experts passionnés.</p><h2>Ce que vous trouverez ici</h2><p>Des guides complets, des conseils pratiques et les dernières actualités du secteur.</p>',
        'image' => 'https://picsum.photos/800/600?random=1',
        'categorie' => 'Actualités',
        'tags' => '["bienvenue", "nouveau"]',
        'read_time' => 3,
        'est_hero' => 1,
        'statut' => 'publie'
    ],
    [
        'slug' => 'guide-complet-pour-debuter',
        'titre' => 'Guide complet pour débuter',
        'meta_description' => 'Tout ce que vous devez savoir pour bien commencer.',
        'contenu_html' => '<p>Vous débutez ? Ce guide est fait pour vous !</p><h2>Les bases essentielles</h2><p>Avant de vous lancer, il est important de comprendre les fondamentaux.</p><h2>Nos conseils</h2><p>Prenez votre temps, documentez-vous et n\'hésitez pas à poser des questions.</p>',
        'image' => 'https://picsum.photos/800/600?random=2',
        'categorie' => 'Guides',
        'tags' => '["guide", "débutant"]',
        'read_time' => 5,
        'est_hero' => 0,
        'statut' => 'publie'
    ],
    [
        'slug' => 'les-tendances-2026',
        'titre' => 'Les tendances 2026',
        'meta_description' => 'Découvrez les grandes tendances à suivre cette année.',
        'contenu_html' => '<p>L\'année 2026 s\'annonce riche en nouveautés !</p><h2>Tendance #1</h2><p>La première tendance majeure concerne...</p><h2>Tendance #2</h2><p>Une autre évolution importante est...</p>',
        'image' => 'https://picsum.photos/800/600?random=3',
        'categorie' => 'Tendances',
        'tags' => '["tendances", "2026"]',
        'read_time' => 4,
        'est_hero' => 0,
        'statut' => 'publie'
    ]
];

$stmt = $pdo->prepare("
    INSERT INTO articles (slug, titre, meta_description, contenu_html, image, categorie, tags, read_time, est_hero, statut, date_publication)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

foreach ($articles as $article) {
    $stmt->execute([
        $article['slug'],
        $article['titre'],
        $article['meta_description'],
        $article['contenu_html'],
        $article['image'],
        $article['categorie'],
        $article['tags'],
        $article['read_time'],
        $article['est_hero'],
        $article['statut']
    ]);
}

echo "✅ " . count($articles) . " articles de test insérés avec succès !<br>";
echo "<a href='/'>Voir le site</a>";
