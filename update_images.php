<?php
/**
 * Script de mise à jour des chemins d'images dans la BDD
 * Transforme les anciens chemins en nouveaux chemins
 *
 * Exécuter une seule fois : php update_images.php
 */

require_once __DIR__ . '/config.php';

$pdo = getDB();

// Mapping ancien chemin → nouveau chemin
$mappings = [
    'images/article-1-hero.svg' => 'images/article-1.svg',
    'images/article-2-hero.svg' => 'images/article-2.svg',
    'images/article-3-hero.svg' => 'images/article-3.svg',
    'images/article-4-hero.svg' => 'images/article-4.svg',
    'images/article-5-hero.svg' => 'images/article-5.svg',
    // URLs picsum.photos vers images locales
    'https://picsum.photos/seed/article1/1200/675' => 'images/article-1.svg',
    'https://picsum.photos/seed/article2/1200/675' => 'images/article-2.svg',
    'https://picsum.photos/seed/article3/1200/675' => 'images/article-3.svg',
    'https://picsum.photos/seed/article4/1200/675' => 'images/article-4.svg',
    'https://picsum.photos/seed/article5/1200/675' => 'images/article-5.svg',
];

echo "=== Mise à jour des chemins d'images ===\n\n";

// Afficher l'état actuel
$stmt = $pdo->query("SELECT id, slug, image FROM articles ORDER BY id");
$articles = $stmt->fetchAll();

echo "État actuel :\n";
foreach ($articles as $art) {
    echo "  [{$art['id']}] {$art['slug']} → {$art['image']}\n";
}
echo "\n";

// Effectuer les mises à jour
$updated = 0;
$stmt = $pdo->prepare("UPDATE articles SET image = ? WHERE image = ?");

foreach ($mappings as $old => $new) {
    $stmt->execute([$new, $old]);
    $count = $stmt->rowCount();
    if ($count > 0) {
        echo "Mis à jour : '{$old}' → '{$new}' ({$count} article(s))\n";
        $updated += $count;
    }
}

if ($updated === 0) {
    echo "Aucune mise à jour nécessaire. Les chemins sont déjà corrects.\n";
} else {
    echo "\nTotal : {$updated} article(s) mis à jour.\n";
}

// Afficher l'état final
echo "\nÉtat final :\n";
$stmt = $pdo->query("SELECT id, slug, image FROM articles ORDER BY id");
$articles = $stmt->fetchAll();
foreach ($articles as $art) {
    echo "  [{$art['id']}] {$art['slug']} → {$art['image']}\n";
}

echo "\n=== Terminé ===\n";
