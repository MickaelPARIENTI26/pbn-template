<?php
/**
 * Script de migration - Publication planifiée
 * Ajoute les colonnes statut et date_publication_prevue à la table articles
 *
 * À EXÉCUTER UNE SEULE FOIS puis SUPPRIMER CE FICHIER
 */

require_once __DIR__ . '/config.php';

$rapport = [];
$erreur = false;

try {
    $pdo = getDB();
    $rapport[] = ['type' => 'success', 'message' => 'Connexion base de données : OK'];

    // 1. Vérifier si la colonne statut existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM articles LIKE 'statut'");
    $colonne_statut_existe = $stmt->fetch() !== false;

    if ($colonne_statut_existe) {
        $rapport[] = ['type' => 'info', 'message' => 'Colonne "statut" existe déjà'];
    } else {
        $pdo->exec("ALTER TABLE articles ADD COLUMN statut ENUM('brouillon', 'planifie', 'publie') DEFAULT 'publie'");
        $rapport[] = ['type' => 'success', 'message' => 'Colonne "statut" ajoutée'];
    }

    // 2. Vérifier si la colonne date_publication_prevue existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM articles LIKE 'date_publication_prevue'");
    $colonne_date_existe = $stmt->fetch() !== false;

    if ($colonne_date_existe) {
        $rapport[] = ['type' => 'info', 'message' => 'Colonne "date_publication_prevue" existe déjà'];
    } else {
        $pdo->exec("ALTER TABLE articles ADD COLUMN date_publication_prevue DATETIME DEFAULT NULL");
        $rapport[] = ['type' => 'success', 'message' => 'Colonne "date_publication_prevue" ajoutée'];
    }

    // 3. Mettre à jour tous les articles existants : statut = 'publie'
    $stmt = $pdo->query("UPDATE articles SET statut = 'publie' WHERE statut IS NULL OR statut = ''");
    $articles_mis_a_jour = $stmt->rowCount();

    // Compter le total des articles
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM articles");
    $total = $stmt->fetch()['total'];

    $rapport[] = ['type' => 'success', 'message' => "Articles mis à jour : {$articles_mis_a_jour} / {$total} articles au statut 'publie'"];

    // 4. Résumé des statuts
    $stmt = $pdo->query("SELECT statut, COUNT(*) as nb FROM articles GROUP BY statut");
    $statuts = $stmt->fetchAll();
    foreach ($statuts as $s) {
        $rapport[] = ['type' => 'info', 'message' => "Statut '{$s['statut']}' : {$s['nb']} article(s)"];
    }

} catch (PDOException $e) {
    $erreur = true;
    $rapport[] = ['type' => 'error', 'message' => 'Erreur : ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration BDD - Publication planifiée</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }
        .rapport-item {
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rapport-item.success { background: #d4edda; color: #155724; }
        .rapport-item.info { background: #d1ecf1; color: #0c5460; }
        .rapport-item.error { background: #f8d7da; color: #721c24; }
        .rapport-item::before {
            font-size: 1.2em;
        }
        .rapport-item.success::before { content: "✓"; }
        .rapport-item.info::before { content: "ℹ"; }
        .rapport-item.error::before { content: "✗"; }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            text-align: center;
        }
        .warning strong {
            display: block;
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        .status-header {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status-header.success { background: #d4edda; }
        .status-header.error { background: #f8d7da; }
        .status-header h2 { margin: 0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Migration BDD</h1>
        <p><strong>Objectif :</strong> Ajout du système de publication planifiée</p>

        <div class="status-header <?= $erreur ? 'error' : 'success' ?>">
            <h2><?= $erreur ? 'Migration échouée' : 'Migration réussie' ?></h2>
        </div>

        <h3>Rapport d'exécution</h3>
        <?php foreach ($rapport as $item): ?>
            <div class="rapport-item <?= $item['type'] ?>">
                <?= htmlspecialchars($item['message']) ?>
            </div>
        <?php endforeach; ?>

        <?php if (!$erreur): ?>
        <div class="warning">
            <strong>⚠️ IMPORTANT</strong>
            Supprime ce fichier migrate_db.php après utilisation !<br>
            <code>rm migrate_db.php</code>
        </div>
        <?php endif; ?>

        <p style="margin-top: 25px; color: #666; font-size: 0.9em;">
            <strong>Nouvelles colonnes ajoutées :</strong><br>
            • <code>statut</code> : ENUM('brouillon', 'planifie', 'publie') DEFAULT 'publie'<br>
            • <code>date_publication_prevue</code> : DATETIME DEFAULT NULL
        </p>
    </div>
</body>
</html>
