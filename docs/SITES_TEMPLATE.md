# PBN — Spec Template Site PHP + MySQL

## Vue d'ensemble

Chaque site du réseau est un blog PHP vanilla + MySQL avec Bootstrap 5.
1 seul template, dupliqué 3000 fois avec des variables différentes.
URLs propres via .htaccess.

---

## Stack

- PHP 8.1+ (vanilla, aucun framework)
- MySQL 8.0
- Bootstrap 5.3 (CDN)
- jQuery 3.7 (CDN, si besoin)
- Apache + mod_rewrite (.htaccess)
- Google Fonts : Cormorant Garamond + DM Sans

---

## Structure des fichiers

```
mon-site/
├── .htaccess
├── config.php
├── install.php
├── index.php
├── article.php
├── mentions-legales.php
├── assets/
│   ├── css/
│   │   └── style.css       ← tout le CSS custom (Bootstrap par-dessus)
│   └── js/
│       └── main.js         ← JS custom (menu mobile, animations, etc.)
└── images/
    ├── hero.webp
    ├── article-1.webp
    ├── article-2.webp
    ├── article-3.webp
    ├── article-4.webp
    └── article-5.webp
```

---

## Variables {{PLACEHOLDER}} dans config.php

Toutes remplacées automatiquement par army_v2.py au déploiement :

| Placeholder | Exemple | Description |
|---|---|---|
| {{DB_NAME}} | cbd_sport_fr | Nom de la BDD MySQL |
| {{DB_USER}} | cbd_sport_user | Utilisateur MySQL |
| {{DB_PASS}} | xK9#mP2!qR | Mot de passe MySQL |
| {{SITE_NAME}} | CBD Sport | Nom affiché du site |
| {{SITE_DOMAIN}} | cbd-sport.fr | Domaine sans https |
| {{SITE_TAGLINE}} | Votre guide CBD pour sportifs | Sous-titre |
| {{SITE_DESC}} | Guide complet CBD... | Meta description |
| {{SITE_NICHE}} | CBD pour sportifs | Niche éditoriale |
| {{COLOR_PRIMARY}} | #2C5F2E | Couleur principale |
| {{COLOR_PRIMARY_LIGHT}} | #4a8a4d | Couleur principale claire |
| {{COLOR_ACCENT}} | #C8A96E | Couleur d'accent |

---

## Base de données MySQL

### Table `articles`

```sql
CREATE TABLE articles (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    slug             VARCHAR(255) UNIQUE NOT NULL,
    titre            VARCHAR(500) NOT NULL,
    meta_description TEXT,
    categorie        VARCHAR(100),
    contenu_html     LONGTEXT,
    image            VARCHAR(255),
    tags             VARCHAR(500),        -- JSON string ["cbd","sport"]
    read_time        INT DEFAULT 5,
    date_publication DATE,
    est_hero         TINYINT DEFAULT 0,   -- 1 = article à la une
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Pas de table site_config

La config est dans config.php directement (constantes PHP).

---

## URLs réécrites (.htaccess)

| URL visitée | Fichier appelé |
|---|---|
| cbd-sport.fr/ | index.php |
| cbd-sport.fr/cbd-bienfaits-sport | article.php?slug=cbd-bienfaits-sport |
| cbd-sport.fr/mentions-legales | mentions-legales.php |
| Fichier existant (images, CSS, JS) | Servi directement |
| URL inconnue | article.php → affiche 404 propre |

---

## Design — Blog magazine pro

### Typographie
- Titres (H1, H2, logo) : Cormorant Garamond (Google Fonts)
- Corps de texte : DM Sans (Google Fonts)

### Couleurs
Injectées via PHP dans une balise `<style>` en haut de chaque page :
```html
<style>
  :root {
    --primary:       <?= COLOR_PRIMARY ?>;
    --primary-light: <?= COLOR_PRIMARY_LIGHT ?>;
    --accent:        <?= COLOR_ACCENT ?>;
  }
</style>
```
Bootstrap est customisé par-dessus ces variables.

### Layout général
- Max-width : 1180px centré
- Header sticky Bootstrap navbar
- Footer sombre

---

## Page d'accueil (index.php)

### Requêtes MySQL
```php
// Article hero (est_hero = 1 ou le plus récent)
SELECT * FROM articles WHERE est_hero = 1 LIMIT 1
// Fallback si pas de hero :
SELECT * FROM articles ORDER BY date_publication DESC LIMIT 1

// 4 articles secondaires (pas le hero)
SELECT * FROM articles WHERE id != $hero_id
ORDER BY date_publication DESC LIMIT 4
```

### Sections dans l'ordre
1. Header Bootstrap sticky (logo + nav)
2. Bannière colorée (fond primary) "SITE_NICHE — Guides & Conseils"
3. Article HERO : image gauche (col-md-6) + contenu droit (col-md-6)
   - Badge "À la une" en accent
   - Titre H1 Cormorant Garamond
   - Excerpt
   - Bouton "Lire l'article →"
4. Titre section "Derniers articles" + séparateur
5. Row Bootstrap : 4 cards (col-md-3 chacune)
   - Image + badge catégorie
   - Titre H2
   - Excerpt (3 lignes, CSS line-clamp)
   - Footer card : lien + temps de lecture
6. Section newsletter (formulaire, pas de backend)
7. Footer Bootstrap (3 colonnes)

---

## Page article (article.php)

### Récupération
```php
$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_STRING);
// SELECT * FROM articles WHERE slug = ? LIMIT 1
// Si vide ou non trouvé → afficher 404 propre
```

### Sections dans l'ordre
1. Header Bootstrap (identique index)
2. Breadcrumb Bootstrap : Accueil > Catégorie > Titre court
3. En-tête article (max-width 720px centré) :
   - Badge catégorie
   - Titre H1 Cormorant Garamond 3rem
   - Chapeau (intro) en italique avec bordure gauche accent
   - Meta : date + temps de lecture + "La Rédaction"
4. Image hero (ratio 21/9, pleine largeur)
5. Layout 2 colonnes Bootstrap (col-lg-8 + col-lg-4) :
   - Contenu HTML de l'article
   - Sidebar sticky : sommaire H2 + 3 articles similaires
6. Tags de l'article
7. Section "Articles similaires" (3 cards, même catégorie)
8. Footer Bootstrap (identique index)

### Génération du sommaire (sidebar)
```php
// Extraire les H2 du contenu
preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $contenu_html, $matches);
// Ajouter des id aux H2 pour les ancres
$contenu_html = preg_replace_callback(
    '/<h2([^>]*)>(.*?)<\/h2>/si',
    function($m) use (&$i) {
        return '<h2'.$m[1].' id="section-'.($i++).'">'.$m[2].'</h2>';
    },
    $contenu_html
);
```

---

## Fichier CSS (assets/css/style.css)

Contient UNIQUEMENT les styles custom par-dessus Bootstrap.
Bootstrap 5 est chargé depuis CDN.

Sections du CSS :
- Variables CSS (:root) → injectées via PHP
- Typographie (Cormorant Garamond sur titres)
- Header / navbar custom
- Bannière thématique
- Card hero (article à la une)
- Cards articles (hover effects)
- Page article (styles contenu)
- Sidebar
- Newsletter
- Footer
- Animations (fadeUp)
- Responsive overrides

---

## Fichier JS (assets/js/main.js)

Léger, pas de dépendances sauf jQuery (CDN).

Fonctionnalités :
- Navbar : ajouter classe scrolled au scroll (ombre)
- Animations fadeUp au scroll (IntersectionObserver)
- Smooth scroll vers ancres du sommaire
- Menu mobile (Bootstrap gère déjà, JS custom si besoin)

---

## Sécurité

- Toutes requêtes SQL : PDO + requêtes préparées
- Affichage : escape() = htmlspecialchars()
- Exception : contenu_html (HTML généré par IA, de confiance)
- Pas d'affichage d'erreurs PHP en prod
- install.php : vérifier si déjà installé avant d'agir

---

## install.php

- Crée la table si elle n'existe pas
- Vérifie si déjà installé (COUNT articles > 0)
- Insère 5 articles de démo (vrai contenu HTML long, 600+ mots)
- Article 1 : est_hero = 1
- Affiche rapport HTML lisible
- Avertissement "Supprime ce fichier !"
