<?php
/**
 * Configuration du site
 * Placeholders remplacés automatiquement par army_v2.py au déploiement
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
define('DB_HOST', '{{DB_HOST}}');
define('DB_NAME', '{{DB_NAME}}');
define('DB_USER', '{{DB_USER}}');
define('DB_PASS', '{{DB_PASS}}');

// Site
define('SITE_NAME', '{{SITE_NAME}}');
define('SITE_DOMAIN', '{{SITE_DOMAIN}}');
define('SITE_URL', 'https://{{SITE_DOMAIN}}');
define('SITE_TAGLINE', '{{SITE_TAGLINE}}');
define('SITE_DESC', '{{SITE_DESC}}');
define('SITE_NICHE', '{{SITE_NICHE}}');

// Couleurs
define('COLOR_PRIMARY', '{{COLOR_PRIMARY}}');
define('COLOR_PRIMARY_LIGHT', '{{COLOR_PRIMARY_LIGHT}}');
define('COLOR_ACCENT', '{{COLOR_ACCENT}}');

// Identité étendue
define('SITE_AUTHOR', '{{SITE_AUTHOR}}');            // ex: La Rédaction
define('SITE_LOGO_TEXT', '{{SITE_LOGO_TEXT}}');      // ex: CBD Sport
define('SITE_INITIAL', '{{SITE_INITIAL}}');          // ex: C (pour favicon)
define('SITE_LANG', '{{SITE_LANG}}');                // ex: fr
define('SITE_LOCALE', '{{SITE_LOCALE}}');            // ex: fr_FR

// Contenu
define('SITE_ARTICLES_PAR_PAGE', '{{SITE_ARTICLES_PAR_PAGE}}'); // ex: 8
define('SITE_NEWSLETTER_TITRE', '{{SITE_NEWSLETTER_TITRE}}');   // ex: Restez informé
define('SITE_NEWSLETTER_TEXTE', '{{SITE_NEWSLETTER_TEXTE}}');   // ex: Recevez nos articles
define('SITE_FOOTER_DESC', '{{SITE_FOOTER_DESC}}');  // ex: Guide complet CBD

// SEO
define('SITE_TWITTER_HANDLE', '{{SITE_TWITTER_HANDLE}}'); // ex: @cbdsport
define('SITE_OG_IMAGE', '{{SITE_OG_IMAGE}}');        // ex: images/og-default.jpg
define('SITE_ROBOTS', '{{SITE_ROBOTS}}');            // ex: index, follow

// Technique
define('SITE_ENV', '{{SITE_ENV}}');                  // ex: production
define('SITE_TIMEZONE', '{{SITE_TIMEZONE}}');        // ex: Europe/Paris
define('SITE_CACHE_TTL', '{{SITE_CACHE_TTL}}');      // ex: 3600

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