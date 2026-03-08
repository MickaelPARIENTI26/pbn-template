# Prompts Claude Code — Site Template PHP

Chaque prompt est indépendant et court.
Les lancer dans l'ordre dans Claude Code.
Toujours commencer une session en collant le contexte ci-dessous.

---

## CONTEXTE À COLLER EN DÉBUT DE CHAQUE SESSION

```
Contexte projet :
Je construis un réseau de blogs PHP + MySQL (PBN).
Chaque site est identique structurellement mais avec des variables différentes.
Stack : PHP 8.1 vanilla, MySQL 8.0, Bootstrap 5.3 CDN, jQuery 3.7 CDN, Apache .htaccess.
Polices : Cormorant Garamond + DM Sans (Google Fonts).
Les couleurs sont des variables CSS injectées par PHP : --primary, --primary-light, --accent.
Les valeurs de config sont des {{PLACEHOLDERS}} remplacés par un script Python.
Lis le fichier docs/SITES_TEMPLATE.md pour les specs complètes.
```

---

## PROMPT 1 — .htaccess + config.php

```
Crée 2 fichiers :

1. .htaccess
Apache mod_rewrite. Règles :
- Ne pas réécrire si le fichier/dossier existe (images, css, js)
- Ne pas réécrire install.php et config.php
- cbd-sport.fr/                    → index.php
- cbd-sport.fr/un-slug-darticle    → article.php?slug=un-slug-darticle
- cbd-sport.fr/mentions-legales    → mentions-legales.php
- Tout le reste                    → article.php?slug=$1

2. config.php
Uniquement des constantes PHP et 2 fonctions helper.
Pas de HTML. Pas de logique métier.

Constantes (toutes en {{PLACEHOLDER}}) :
DB_HOST = 'localhost'
DB_NAME = '{{DB_NAME}}'
DB_USER = '{{DB_USER}}'
DB_PASS = '{{DB_PASS}}'
SITE_NAME = '{{SITE_NAME}}'
SITE_DOMAIN = '{{SITE_DOMAIN}}'
SITE_URL = 'https://{{SITE_DOMAIN}}'
SITE_TAGLINE = '{{SITE_TAGLINE}}'
SITE_DESC = '{{SITE_DESC}}'
SITE_NICHE = '{{SITE_NICHE}}'
COLOR_PRIMARY = '{{COLOR_PRIMARY}}'
COLOR_PRIMARY_LIGHT = '{{COLOR_PRIMARY_LIGHT}}'
COLOR_ACCENT = '{{COLOR_ACCENT}}'

Fonction getDB() :
- PDO singleton
- charset utf8mb4
- ERRMODE_EXCEPTION
- FETCH_ASSOC par défaut

Fonction escape($str) :
- htmlspecialchars($str, ENT_QUOTES, 'UTF-8')

Fonction url($slug = '') :
- Retourne SITE_URL.'/'.$slug
- Si slug vide : retourne juste SITE_URL
```

---

## PROMPT 2 — install.php

```
Crée install.php.
Requiert config.php.
Pas de HTML complexe, juste un rapport lisible.

Ce qu'il doit faire dans l'ordre :

1. Vérifier si déjà installé :
   - Essayer de compter les articles dans la table
   - Si table existe et COUNT > 0 → afficher "Déjà installé" et stop

2. Créer la table articles si elle n'existe pas :
CREATE TABLE articles (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    slug             VARCHAR(255) UNIQUE NOT NULL,
    titre            VARCHAR(500) NOT NULL,
    meta_description TEXT,
    categorie        VARCHAR(100),
    contenu_html     LONGTEXT,
    image            VARCHAR(255),
    tags             VARCHAR(500),
    read_time        INT DEFAULT 5,
    date_publication DATE,
    est_hero         TINYINT DEFAULT 0,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

3. Insérer 5 articles de démo sur le thème CBD.
Chaque article doit avoir :
- Un vrai slug propre (ex: "cbd-bienfaits-sport")
- Un vrai contenu HTML long (minimum 500 mots)
- Contenu structuré : intro, 3 H2 minimum, paragraphes, 1 blockquote, 1 liste ul
- Une catégorie différente par article
- Des tags en JSON string : '["cbd","sport","récupération"]'
- Une date de publication différente (les 5 derniers mois)
- Le premier article : est_hero = 1

4. Afficher un rapport HTML simple :
✅ Installation réussie
- Base de données : OK
- Table créée : OK
- Articles insérés : 5
⚠️ Supprime ce fichier install.php de ton serveur maintenant !
```

---

## PROMPT 3 — assets/css/style.css

```
Crée assets/css/style.css
C'est le CSS custom qui s'ajoute PAR-DESSUS Bootstrap 5.
Bootstrap est chargé depuis CDN, ne pas le réimporter ici.
Ne pas écrire les styles que Bootstrap gère déjà.

Variables CSS (les valeurs réelles viennent de PHP, ici mettre les démos) :
:root {
  --primary: #2C5F2E;
  --primary-light: #4a8a4d;
  --accent: #C8A96E;
  --dark: #1a1a18;
  --mid: #4a4a46;
  --light: #f7f5f0;
  --border: #e8e4dc;
  --font-display: 'Cormorant Garamond', Georgia, serif;
  --font-body: 'DM Sans', system-ui, sans-serif;
}

Sections à écrire dans cet ordre avec commentaires :

/* === BASE === */
body : background var(--light), font-family var(--font-body)
Tous les titres h1-h4 : font-family var(--font-display)
Les liens dans .article-content : color primary + underline

/* === NAVBAR === */
.navbar : fond blanc, border-bottom 1px var(--border)
.navbar.scrolled : box-shadow subtil
.navbar-brand : Cormorant Garamond, color primary, taille 1.5rem
.nav-link : uppercase, font-size 0.82rem, letter-spacing 0.08em, color mid
.nav-link:hover : color primary

/* === BANNIÈRE THÉMATIQUE === */
.site-banner : background primary, color white, padding 0.5rem,
               text-align center, font-size 0.75rem, letter-spacing 0.15em, uppercase

/* === HERO ARTICLE === */
.hero-card : fond blanc, border 1px var(--border), overflow hidden
.hero-card:hover : box-shadow 0 12px 40px rgba(0,0,0,0.1)
.hero-card .card-img-wrap : overflow hidden
.hero-card .card-img-wrap img : transition transform 0.6s
.hero-card:hover .card-img-wrap img : transform scale(1.03)
.hero-badge : position absolute, top 1rem, left 1rem, background accent, color white,
              font-size 0.7rem, font-weight 600, uppercase, letter-spacing 0.1em, padding 0.3rem 0.8rem
.hero-divider : width 40px, height 2px, background accent, margin 1rem 0 1.5rem
.hero-title : Cormorant Garamond, 2.4rem, font-weight 700, line-height 1.15, letter-spacing -0.02em
.btn-read-more : color primary, border-bottom 1px solid primary, padding-bottom 2px,
                 font-size 0.82rem, font-weight 600, uppercase, letter-spacing 0.1em,
                 transition all 0.2s, display inline-flex, align-items center, gap 0.5rem
.btn-read-more:hover : gap 0.9rem

/* === CARDS ARTICLES === */
.article-card : fond blanc, border 1px var(--border), overflow hidden, height 100%,
                transition transform 0.3s, box-shadow 0.3s
.article-card:hover : transform translateY(-4px), box-shadow 0 8px 24px rgba(0,0,0,0.08)
.card-img-wrap : overflow hidden, aspect-ratio 16/10
.card-img-wrap img : height 100%, object-fit cover, transition transform 0.5s
.article-card:hover .card-img-wrap img : transform scale(1.06)
.card-category-badge : position absolute, bottom 0.7rem, left 0.7rem, background primary,
                        color white, font-size 0.65rem, font-weight 600, uppercase,
                        letter-spacing 0.1em, padding 0.2rem 0.6rem
.card-title : Cormorant Garamond, 1.2rem, font-weight 600, line-height 1.3,
              transition color 0.2s
.article-card:hover .card-title : color primary
.card-excerpt : font-size 0.83rem, color mid, line-height 1.65,
                display -webkit-box, -webkit-line-clamp 3, overflow hidden

/* === PAGE ARTICLE === */
.breadcrumb-custom : font-size 0.78rem, color mid
.article-header : max-width 720px, margin auto
.article-category-badge : background primary, color white, font-size 0.7rem,
                           uppercase, letter-spacing 0.12em, padding 0.3rem 0.9rem,
                           display inline-block, margin-bottom 1.2rem
.article-title : Cormorant Garamond, 3rem, font-weight 700, line-height 1.12, letter-spacing -0.02em
.article-intro : font-size 1.05rem, color mid, line-height 1.75, font-style italic,
                 border-left 3px solid accent, padding-left 1.5rem
.article-meta : font-size 0.8rem, color mid, border-top 1px var(--border), border-bottom 1px var(--border), padding 0.8rem 0
.article-hero-img : width 100%, aspect-ratio 21/9, object-fit cover
.article-content h2 : Cormorant Garamond, 1.9rem, margin-top 2.5rem, margin-bottom 1rem
.article-content h3 : Cormorant Garamond, 1.4rem, margin-top 2rem
.article-content p : font-size 1rem, line-height 1.8, margin-bottom 1.2rem
.article-content blockquote : background white, border-left 4px solid accent,
                               padding 1.5rem 2rem, font-style italic,
                               Cormorant Garamond, font-size 1.15rem, color mid
.article-content a : color primary, border-bottom 1px solid primary, text-decoration none
.article-content ul, ol : margin-left 1.5rem, margin-bottom 1.2rem
.article-tags .tag : border 1px solid var(--border), padding 0.2rem 0.8rem,
                     font-size 0.75rem, color mid, transition all 0.2s, display inline-block
.article-tags .tag:hover : background primary, color white, border-color primary

/* === SIDEBAR === */
.sidebar : position sticky, top 90px
.sidebar-widget : background white, border 1px var(--border), padding 1.5rem, margin-bottom 1.5rem
.widget-title : font-size 0.72rem, uppercase, letter-spacing 0.15em, font-weight 600,
                border-bottom 2px solid accent, padding-bottom 0.5rem, display inline-block, margin-bottom 1rem
.toc-link : font-size 0.83rem, color mid, display block, padding 0.4rem 0,
            border-bottom 1px solid var(--border), transition color 0.2s
.toc-link:hover : color primary, text-decoration none
.related-mini-item img : width 70px, height 70px, object-fit cover, flex-shrink 0
.related-mini-title : font-size 0.83rem, font-weight 500, line-height 1.3, transition color 0.2s
.related-mini-item:hover .related-mini-title : color primary

/* === NEWSLETTER === */
.newsletter-section : background primary, color white
.newsletter-section input : background rgba(255,255,255,0.12), border 1px solid rgba(255,255,255,0.2),
                             color white, padding 0.8rem 1rem
.newsletter-section input::placeholder : color rgba(255,255,255,0.5)
.newsletter-section .btn-subscribe : background accent, color white, border none,
                                     font-weight 600, uppercase, letter-spacing 0.08em

/* === FOOTER === */
footer : background #1a1a18, color rgba(255,255,255,0.5)
.footer-brand .navbar-brand : color white
footer h5 : font-size 0.72rem, uppercase, letter-spacing 0.15em, color white
footer a : color rgba(255,255,255,0.5), transition color 0.2s, text-decoration none
footer a:hover : color accent
.footer-bottom : border-top 1px solid rgba(255,255,255,0.08), font-size 0.75rem

/* === ANIMATIONS === */
.fade-up : opacity 0, transform translateY(24px), transition opacity 0.6s, transform 0.6s
.fade-up.visible : opacity 1, transform translateY(0)
Delays : .delay-1 0.1s, .delay-2 0.2s, .delay-3 0.3s, .delay-4 0.4s

/* === RESPONSIVE === */
Sous 768px :
- .hero-title : 1.8rem
- .article-title : 2rem
- .sidebar : position static
```

---

## PROMPT 4 — assets/js/main.js

```
Crée assets/js/main.js
Dépendance : jQuery (chargé avant ce fichier via CDN)
Pas de module ES6, du jQuery classique $(document).ready()

Fonctionnalités à implémenter :

1. Navbar scroll shadow
Au scroll de la page : ajouter/enlever la classe "scrolled" sur .navbar
Threshold : 50px de scroll

2. Animations fadeUp au scroll
Utiliser IntersectionObserver sur tous les éléments .fade-up
Quand ils entrent dans le viewport : ajouter la classe "visible"
rootMargin: "0px 0px -50px 0px"

3. Smooth scroll pour le sommaire d'article
Sur clic sur .toc-link :
- Empêcher le comportement par défaut
- Récupérer le href (#section-0, #section-1, etc.)
- Scroll smooth vers l'élément avec offset de 100px (header)

4. Active state sur les liens du sommaire
Au scroll, détecter quelle section H2 est visible
Ajouter la classe "active" sur le .toc-link correspondant
(color primary, font-weight 600)

5. Image placeholder si erreur de chargement
Sur toutes les img : si erreur de chargement,
remplacer le src par un SVG inline d'un rectangle coloré en primary

Garder le fichier court et propre. Pas de commentaires excessifs.
```

---

## PROMPT 5 — index.php

```
Crée index.php
Requiert config.php en première ligne.
Bootstrap 5.3 + jQuery 3.7 + Google Fonts chargés depuis CDN.
Fichiers locaux : assets/css/style.css et assets/js/main.js

Structure HTML complète dans cet ordre :

HEAD :
- <title> = SITE_NAME . ' — ' . SITE_TAGLINE
- meta description = SITE_DESC
- Google Fonts preconnect + Cormorant Garamond + DM Sans
- Bootstrap CSS CDN
- style.css local
- Balise <style> inline pour injecter les variables CSS PHP :
  :root { --primary: <?= COLOR_PRIMARY ?>; etc. }

PHP AVANT LE HTML :
- Requête hero : SELECT * FROM articles WHERE est_hero=1 LIMIT 1
- Fallback si vide : SELECT * FROM articles ORDER BY date_publication DESC LIMIT 1
- Requête secondaires : SELECT * FROM articles WHERE id != ? ORDER BY date_publication DESC LIMIT 4
- Toutes les requêtes en PDO préparé

BODY :

1. NAVBAR Bootstrap sticky-top :
- navbar-brand = logo avec point coloré en accent
- nav-links : Accueil, Articles, À propos, Contact
- burger menu mobile Bootstrap

2. BANNIÈRE :
<div class="site-banner"><?= escape(SITE_NICHE) ?> — Guides, conseils & actualités</div>

3. HERO ARTICLE (container, mt-4) :
Row Bootstrap : col-md-6 image + col-md-6 contenu
- Image : <img loading="eager" class="w-100 h-100 object-fit-cover">
- Badge "À la une" absolu sur l'image
- Contenu : meta (catégorie + date + read_time) + divider + titre H1 + excerpt + btn-read-more
- Lien : href="<?= url(escape($hero['slug'])) ?>"

4. SECTION ARTICLES (container, mt-5) :
- Titre "Derniers articles" + hr décoratif
- Row avec 4 col-md-3 (col-sm-6) : boucle sur $articles
- Chaque card :
  - position-relative pour le badge catégorie
  - Image avec card-img-wrap
  - Card body : date + titre H2 + excerpt
  - Card footer : lien "Lire →" + "X min"
- Ajouter classe fade-up + delay-1/2/3/4 sur chaque card

5. NEWSLETTER (mt-5) :
Section .newsletter-section avec padding 4rem 0
Row : col-md-6 texte + col-md-6 formulaire
Formulaire : input prénom + input email + bouton, action="#"

6. FOOTER :
3 colonnes Bootstrap : logo+desc | Navigation | Légal
Footer-bottom : copyright + domaine
```

---

## PROMPT 6 — article.php

```
Crée article.php
Requiert config.php en première ligne.
Même HEAD que index.php (copier exactement).

PHP AVANT LE HTML :
$slug = filter_input(INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
- Si slug vide : $not_found = true
- Sinon : SELECT * FROM articles WHERE slug = ? LIMIT 1
- Si résultat vide : $not_found = true
- Extraire les H2 du contenu avec preg_match_all pour le sommaire
- Modifier le contenu_html pour ajouter id="section-N" sur chaque H2
- SELECT 3 articles similaires : même catégorie, id != current, LIMIT 3
- Décoder les tags : json_decode($article['tags'])

BODY :

1. NAVBAR Bootstrap (identique index.php, copier)

2. SI $not_found :
   Afficher page 404 Bootstrap centrée :
   - Code "404" en grand (display-1, text-muted)
   - "Article introuvable"
   - Lien "← Retour à l'accueil"
   - Stop (pas le reste du template)

3. BREADCRUMB Bootstrap :
Accueil > <?= escape($article['categorie']) ?> > <?= escape(substr($article['titre'], 0, 40)) ?>...

4. EN-TÊTE ARTICLE (container, max-width 720px via CSS) :
- Badge catégorie (.article-category-badge)
- H1 .article-title
- p.article-intro : 200 premiers chars du contenu sans HTML (strip_tags)
- .article-meta : date + read_time + "La Rédaction"

5. IMAGE HERO (container-fluid px-0) :
<img src="images/<?= escape($article['image']) ?>" class="article-hero-img" loading="eager">

6. LAYOUT ARTICLE (container, mt-4) :
Row : col-lg-8 (contenu) + col-lg-4 (sidebar)

Colonne contenu :
- <div class="article-content"><?= $contenu_modifie ?></div>
  (pas de escape ici, c'est du HTML de confiance généré par IA)
- Section tags :
  <div class="article-tags mt-4">
  foreach json_decode tags : <a class="tag me-1">tag</a>
  </div>

Colonne sidebar (.sidebar) :
Widget 1 — Sommaire :
  <div class="sidebar-widget">
    <div class="widget-title">Sommaire</div>
    foreach $h2_matches : <a href="#section-N" class="toc-link">titre h2</a>
  </div>

Widget 2 — À lire aussi :
  <div class="sidebar-widget">
    <div class="widget-title">À lire aussi</div>
    foreach $related_sidebar (3 articles) :
      <a href="<?= url($art['slug']) ?>" class="d-flex gap-2 related-mini-item mb-2">
        <img src="images/<?= $art['image'] ?>">
        <div>
          <div class="related-mini-title"><?= escape($art['titre']) ?></div>
          <small><?= $art['date_publication'] ?></small>
        </div>
      </a>
  </div>

7. ARTICLES SIMILAIRES bas de page (container, mt-5) :
- Titre "Articles similaires" + hr
- Row 3 cards (col-md-4) : même structure que les cards index.php

8. FOOTER Bootstrap (identique index.php, copier)
```

---

## PROMPT 7 — mentions-legales.php

```
Crée mentions-legales.php
Fichier simple.
Requiert config.php.
Même header/footer que index.php.
Pas de BDD nécessaire.

Contenu : mentions légales génériques avec les constantes PHP :
- Éditeur du site : SITE_NAME, SITE_DOMAIN
- Directeur de publication : "La Rédaction"
- Hébergeur : "Hébergement mutualisé"
- Propriété intellectuelle
- Cookies (aucun cookie de tracking)
- Contact : contact@SITE_DOMAIN

Mise en page Bootstrap simple : container, h1, sections avec h2,
card Bootstrap avec padding pour chaque section.
```

---

## ORDRE DE GÉNÉRATION RECOMMANDÉ

```
Session 1 : Prompt 1 (config + htaccess)
Session 2 : Prompt 2 (install.php)
Session 3 : Prompt 3 (style.css)
Session 4 : Prompt 4 (main.js)
Session 5 : Prompt 5 (index.php)
Session 6 : Prompt 6 (article.php)
Session 7 : Prompt 7 (mentions-legales.php)
```

Tester après chaque session avant de passer à la suivante.
Lancer install.php en premier pour créer la BDD de démo.
