<?php
/**
 * Configuration du site
 * - Railway : lit les variables d'environnement
 * - Local/army_v2.py : utilise les placeholders {{...}}
 */

/*
 * FORMAT INSERTION ARTICLE PAR AGENT IA :
 *
 * statut = 'publie'    → publié immédiatement
 * statut = 'planifie'  → publié à date_publication_prevue
 * statut = 'brouillon' → jamais affiché sur le site
 *
 * Exemples date_publication_prevue :
 * NULL                    → publié immédiatement
 * '2025-06-15 08:00:00'   → publié le 15 juin 2025 à 8h
 * '2025-12-31 00:00:00'   → publié le 31 décembre 2025 à minuit
 *
 * Insertion agent exemple :
 * INSERT INTO articles (slug, titre, contenu_html, statut, date_publication_prevue)
 * VALUES ('mon-slug', 'Mon titre', '<p>contenu</p>', 'planifie', '2025-06-15 08:00:00');
 */

// Base de données
define('DB_HOST', getenv('DB_HOST') ?: '{{DB_HOST}}');
define('DB_NAME', getenv('DB_NAME') ?: '{{DB_NAME}}');
define('DB_USER', getenv('DB_USER') ?: '{{DB_USER}}');
define('DB_PASS', getenv('DB_PASS') ?: '{{DB_PASS}}');

// Site
define('SITE_NAME', getenv('SITE_NAME') ?: '{{SITE_NAME}}');
define('SITE_DOMAIN', getenv('SITE_DOMAIN') ?: '{{SITE_DOMAIN}}');
define('SITE_URL', getenv('SITE_URL') ?: 'https://{{SITE_DOMAIN}}');
define('SITE_TAGLINE', getenv('SITE_TAGLINE') ?: '{{SITE_TAGLINE}}');
define('SITE_DESC', getenv('SITE_DESC') ?: '{{SITE_DESC}}');
define('SITE_NICHE', getenv('SITE_NICHE') ?: '{{SITE_NICHE}}');

// Couleurs
define('COLOR_PRIMARY', getenv('COLOR_PRIMARY') ?: '#2C5F2E');
define('COLOR_PRIMARY_LIGHT', getenv('COLOR_PRIMARY_LIGHT') ?: '#4a8a4d');
define('COLOR_ACCENT', getenv('COLOR_ACCENT') ?: '#C8A96E');

// Identité étendue
define('SITE_AUTHOR', getenv('SITE_AUTHOR') ?: '{{SITE_AUTHOR}}');
define('SITE_LOGO_TEXT', getenv('SITE_LOGO_TEXT') ?: '{{SITE_LOGO_TEXT}}');
define('SITE_INITIAL', getenv('SITE_INITIAL') ?: '{{SITE_INITIAL}}');
define('SITE_LANG', getenv('SITE_LANG') ?: 'fr');
define('SITE_LOCALE', getenv('SITE_LOCALE') ?: 'fr_FR');

// Contenu
define('SITE_ARTICLES_PAR_PAGE', getenv('SITE_ARTICLES_PAR_PAGE') ?: '8');
define('SITE_NEWSLETTER_TITRE', getenv('SITE_NEWSLETTER_TITRE') ?: '{{SITE_NEWSLETTER_TITRE}}');
define('SITE_NEWSLETTER_TEXTE', getenv('SITE_NEWSLETTER_TEXTE') ?: '{{SITE_NEWSLETTER_TEXTE}}');
define('SITE_FOOTER_DESC', getenv('SITE_FOOTER_DESC') ?: '{{SITE_FOOTER_DESC}}');

// SEO
define('SITE_TWITTER_HANDLE', getenv('SITE_TWITTER_HANDLE') ?: '{{SITE_TWITTER_HANDLE}}');
define('SITE_OG_IMAGE', getenv('SITE_OG_IMAGE') ?: 'images/og-default.svg');
define('SITE_ROBOTS', getenv('SITE_ROBOTS') ?: 'index, follow');

// Technique
define('SITE_ENV', getenv('SITE_ENV') ?: 'production');
define('SITE_TIMEZONE', getenv('SITE_TIMEZONE') ?: 'Europe/Paris');
define('SITE_CACHE_TTL', getenv('SITE_CACHE_TTL') ?: '3600');

/**
 * Connexion PDO singleton
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    return $pdo;
}

/**
 * Escape HTML pour éviter les XSS
 */
function escape(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Génère une URL complète
 */
function url(string $slug = ''): string
{
    if ($slug === '') {
        return SITE_URL;
    }
    return SITE_URL . '/' . $slug;
}

/**
 * Génère un slug URL-friendly pour une catégorie
 */
function categorie_slug($cat) {
    $cat = mb_strtolower($cat, 'UTF-8');
    $replacements = [
        'à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e',
        'ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i','ô'=>'o',
        'ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c',
        ' '=>'-','\''=>'-','&'=>'et'
    ];
    $cat = strtr($cat, $replacements);
    $cat = preg_replace('/[^a-z0-9\-]/', '', $cat);
    $cat = preg_replace('/-+/', '-', $cat);
    return trim($cat, '-');
}
