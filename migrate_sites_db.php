<?php
/**
 * Script de migration - Table sites (registre central PBN)
 * Crée les tables sites et articles_sites + 3 sites de démo
 *
 * À EXÉCUTER UNE SEULE FOIS puis SUPPRIMER CE FICHIER
 */

/*
 * FORMAT INSERTION SITE PAR ARMY_V2.PY :
 *
 * INSERT INTO sites (
 *   domaine, site_name, site_tagline, site_desc, site_niche,
 *   theme, categories, color_primary, color_primary_light, color_accent,
 *   db_name, db_user, db_pass, ip_vps, provider_vps, chemin_site, statut
 * ) VALUES (
 *   'mon-domaine.fr',
 *   'Mon Site',
 *   'Le meilleur guide sur X',
 *   'Description meta homepage',
 *   'Niche éditoriale',
 *   'cbd',
 *   '["Categorie1","Categorie2","Categorie3"]',
 *   '#2C5F2E',
 *   '#4a8a4d',
 *   '#C8A96E',
 *   'mon_domaine_fr',
 *   'mon_domaine_user',
 *   'motdepasse_genere',
 *   '192.168.1.1',
 *   'Hetzner',
 *   '/var/www/mon-domaine.fr',
 *   'en_creation'
 * );
 *
 * COULEURS PAR THÈME (référence pour les agents) :
 *
 * cbd      → primary #2C5F2E / light #4a8a4d  / accent #C8A96E
 * crypto   → primary #1a1a2e / light #16213e  / accent #F7B731
 * finance  → primary #1B3A6B / light #2952a3  / accent #E8B84B
 * sante    → primary #C0392B / light #e74c3c  / accent #F39C12
 * sport    → primary #1a1a2e / light #2d2d44  / accent #E74C3C
 * voyage   → primary #0077B6 / light #0096c7  / accent #F77F00
 * tech     → primary #2D3436 / light #636e72  / accent #00B894
 * mode     → primary #2D2D2D / light #4a4a4a  / accent #E91E8C
 * food     → primary #6B3A2A / light #8B4513  / accent #FF6B35
 * immo     → primary #1B3A6B / light #2952a3  / accent #27AE60
 */

require_once __DIR__ . '/config.php';

$rapport = [];
$erreur = false;

try {
    $pdo = getDB();
    $rapport[] = ['type' => 'success', 'message' => 'Connexion base de données : OK'];

    // 1. Créer la table sites
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sites (
            id                    INT AUTO_INCREMENT PRIMARY KEY,

            -- Identité du domaine
            domaine               VARCHAR(255) UNIQUE NOT NULL,
            site_name             VARCHAR(255) NOT NULL,
            site_tagline          VARCHAR(500),
            site_desc             TEXT,
            site_niche            VARCHAR(255),

            -- Thème et catégories
            theme                 VARCHAR(100) NOT NULL,
            categories            JSON,
            langue                VARCHAR(10) DEFAULT 'fr',

            -- Couleurs (uniques par site, générées par agent)
            color_primary         VARCHAR(20) DEFAULT '#2C5F2E',
            color_primary_light   VARCHAR(20) DEFAULT '#4a8a4d',
            color_accent          VARCHAR(20) DEFAULT '#C8A96E',

            -- Config BDD du site
            db_name               VARCHAR(255),
            db_user               VARCHAR(255),
            db_pass               VARCHAR(255),

            -- Config serveur
            ip_vps                VARCHAR(45),
            provider_vps          VARCHAR(100),
            ssh_user              VARCHAR(100) DEFAULT 'root',
            ssh_port              INT DEFAULT 22,
            chemin_site           VARCHAR(500),

            -- Métriques SEO (mises à jour par cron_audit.py)
            tf                    INT DEFAULT 0,
            cf                    INT DEFAULT 0,
            dr                    INT DEFAULT 0,
            backlinks_count       INT DEFAULT 0,
            referring_domains     INT DEFAULT 0,

            -- Statut
            statut                ENUM('en_creation', 'actif', 'inactif', 'erreur') DEFAULT 'en_creation',
            est_expire            TINYINT DEFAULT 0,
            est_en_ligne          TINYINT DEFAULT 0,
            tier                  ENUM('premium', 'standard', 'basic') DEFAULT 'basic',
            prix_backlink         DECIMAL(10,2) DEFAULT 0.00,

            -- Contenu
            nb_articles           INT DEFAULT 0,
            dernier_article_at    DATETIME DEFAULT NULL,
            prochain_article_at   DATETIME DEFAULT NULL,

            -- Timestamps
            created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            dernier_audit_at      DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $rapport[] = ['type' => 'success', 'message' => 'Table "sites" créée : OK'];

    // 2. Créer la table articles_sites
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS articles_sites (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            site_id     INT NOT NULL,
            article_id  INT NOT NULL,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
            UNIQUE KEY unique_article_site (site_id, article_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $rapport[] = ['type' => 'success', 'message' => 'Table "articles_sites" créée : OK'];

    // 3. Vérifier si des sites existent déjà
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sites");
    $count = $stmt->fetch()['total'];

    if ($count > 0) {
        $rapport[] = ['type' => 'info', 'message' => "Sites existants : {$count} (aucune insertion de démo)"];
    } else {
        // Insérer les 3 sites de démo
        $sites_demo = [
            [
                'domaine' => 'cbd-sport.fr',
                'site_name' => 'CBD Sport',
                'site_tagline' => 'Votre guide CBD pour sportifs',
                'site_desc' => 'Guide complet sur le CBD pour les sportifs : récupération, performance et bien-être.',
                'site_niche' => 'CBD pour sportifs',
                'theme' => 'cbd',
                'categories' => '["Sport","Récupération","Bien-être","Dosage"]',
                'color_primary' => '#2C5F2E',
                'color_primary_light' => '#4a8a4d',
                'color_accent' => '#C8A96E',
                'tier' => 'standard',
                'prix_backlink' => 45.00,
                'statut' => 'actif',
                'est_en_ligne' => 1,
                'tf' => 0,
                'dr' => 0
            ],
            [
                'domaine' => 'crypto-debutant.fr',
                'site_name' => 'Crypto Débutant',
                'site_tagline' => 'La crypto expliquée simplement',
                'site_desc' => 'Guides et tutoriels pour comprendre Bitcoin, Ethereum et les cryptomonnaies.',
                'site_niche' => 'Cryptomonnaies pour débutants',
                'theme' => 'crypto',
                'categories' => '["Bitcoin","Ethereum","DeFi","NFT","Trading"]',
                'color_primary' => '#1a1a2e',
                'color_primary_light' => '#16213e',
                'color_accent' => '#F7B731',
                'tier' => 'basic',
                'prix_backlink' => 30.00,
                'statut' => 'en_creation',
                'est_en_ligne' => 0,
                'tf' => 0,
                'dr' => 0
            ],
            [
                'domaine' => 'epargne-malin.fr',
                'site_name' => 'Épargne Malin',
                'site_tagline' => 'Optimisez votre patrimoine',
                'site_desc' => 'Conseils et stratégies pour épargner intelligemment et faire fructifier votre argent.',
                'site_niche' => 'Épargne et investissement',
                'theme' => 'finance',
                'categories' => '["Épargne","Immobilier","Bourse","Retraite","Fiscalité"]',
                'color_primary' => '#1B3A6B',
                'color_primary_light' => '#2952a3',
                'color_accent' => '#E8B84B',
                'tier' => 'premium',
                'prix_backlink' => 75.00,
                'statut' => 'actif',
                'est_en_ligne' => 1,
                'tf' => 24,
                'dr' => 31
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO sites (
                domaine, site_name, site_tagline, site_desc, site_niche,
                theme, categories, color_primary, color_primary_light, color_accent,
                tier, prix_backlink, statut, est_en_ligne, tf, dr
            ) VALUES (
                :domaine, :site_name, :site_tagline, :site_desc, :site_niche,
                :theme, :categories, :color_primary, :color_primary_light, :color_accent,
                :tier, :prix_backlink, :statut, :est_en_ligne, :tf, :dr
            )
        ");

        foreach ($sites_demo as $site) {
            $stmt->execute($site);
        }

        $rapport[] = ['type' => 'success', 'message' => 'Sites de démo insérés : 3'];

        // Détail des sites
        $rapport[] = ['type' => 'info', 'message' => '→ cbd-sport.fr (CBD, standard, actif)'];
        $rapport[] = ['type' => 'info', 'message' => '→ crypto-debutant.fr (Crypto, basic, en création)'];
        $rapport[] = ['type' => 'info', 'message' => '→ epargne-malin.fr (Finance, premium, actif, TF=24)'];
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
    <title>Migration BDD - Table Sites</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 750px;
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
        .rapport-item::before { font-size: 1.2em; }
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
        .schema-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85em;
        }
        .schema-info h4 { margin-top: 0; color: #495057; }
        .schema-info code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .theme-colors {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        .theme-color {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85em;
        }
        .color-swatch {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Migration BDD — Table Sites</h1>
        <p><strong>Objectif :</strong> Créer le registre central des sites PBN</p>

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
            Supprime ce fichier migrate_sites_db.php maintenant !<br>
            <code>rm migrate_sites_db.php</code>
        </div>

        <div class="schema-info">
            <h4>Tables créées</h4>
            <p><code>sites</code> — Registre central de tous les sites du réseau PBN</p>
            <p><code>articles_sites</code> — Liaison articles ↔ sites (relation N:N)</p>

            <h4>Colonnes clés de la table sites</h4>
            <ul>
                <li><code>domaine</code> — Nom de domaine unique</li>
                <li><code>theme</code> — cbd, crypto, finance, sante, etc.</li>
                <li><code>tier</code> — premium, standard, basic</li>
                <li><code>statut</code> — en_creation, actif, inactif, erreur</li>
                <li><code>tf</code> / <code>dr</code> — Métriques SEO</li>
                <li><code>prix_backlink</code> — Prix calculé selon TF/tier</li>
            </ul>

            <h4>Couleurs par thème</h4>
            <div class="theme-colors">
                <div class="theme-color"><span class="color-swatch" style="background:#2C5F2E"></span> cbd</div>
                <div class="theme-color"><span class="color-swatch" style="background:#1a1a2e"></span> crypto</div>
                <div class="theme-color"><span class="color-swatch" style="background:#1B3A6B"></span> finance</div>
                <div class="theme-color"><span class="color-swatch" style="background:#C0392B"></span> sante</div>
                <div class="theme-color"><span class="color-swatch" style="background:#0077B6"></span> voyage</div>
                <div class="theme-color"><span class="color-swatch" style="background:#2D3436"></span> tech</div>
                <div class="theme-color"><span class="color-swatch" style="background:#2D2D2D"></span> mode</div>
                <div class="theme-color"><span class="color-swatch" style="background:#6B3A2A"></span> food</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
