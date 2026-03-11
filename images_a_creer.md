# Images a creer pour le template PBN

## Analyse des balises `<img>` dans le code

### index.php

| Ligne | Contexte | Classe CSS | Ratio CSS | Taille recommandee |
|-------|----------|------------|-----------|-------------------|
| 274 | Hero article | `w-100 h-100 object-fit-cover` | libre (remplit col-md-6) | 1200x675px (16:9) |
| 312 | Cards articles | `.card-img-wrap` | `aspect-ratio: 16/10` | 800x500px |

### article.php

| Ligne | Contexte | Classe CSS | Ratio CSS | Taille recommandee |
|-------|----------|------------|-----------|-------------------|
| 296 | Image hero article | `.article-hero-img` | `aspect-ratio: 16/9`, max-height 400px | 1200x675px |
| 337 | Sidebar - Nouveaux articles | `.sidebar-article-item img` | `80x80px` fixe | 80x80px (1:1) |
| 353 | Sidebar - Articles similaires | `.sidebar-article-item img` | `80x80px` fixe | 80x80px (1:1) |
| 381 | Cards articles similaires | `.card-img-wrap` | `aspect-ratio: 16/10` | 800x500px |

### articles.php

| Ligne | Contexte | Classe CSS | Ratio CSS | Taille recommandee |
|-------|----------|------------|-----------|-------------------|
| 141 | Cards liste articles | `.card-img-wrap` | `aspect-ratio: 16/10` | 800x500px |

---

## Images de demonstration actuelles (seed.php)

Les articles de demo utilisent actuellement des placeholders picsum.photos (800x600).

| Article | Slug | Categorie | Image actuelle |
|---------|------|-----------|----------------|
| Bienvenue sur notre site | bienvenue-sur-notre-site | Actualites | picsum.photos/800/600?random=1 |
| Guide complet pour debuter | guide-complet-pour-debuter | Guides | picsum.photos/800/600?random=2 |
| Les tendances 2026 | les-tendances-2026 | Tendances | picsum.photos/800/600?random=3 |

---

## Specifications pour generation IA (Midjourney, fal.ai, DALL-E)

### Format recommande
- **Format**: WebP (pour performance) ou JPG
- **Qualite**: 80-85%
- **Couleurs**: Harmonisees avec la palette du site (tons naturels, beige, vert olive)

---

## Types d'images a generer

### 1. Image Hero / Article principal
**Taille**: 1200x675px (ratio 16:9)
**Utilisation**:
- Hero en page d'accueil (index.php ligne 274)
- Image principale des articles (article.php ligne 296)
- Open Graph / Twitter Cards

**Prompt template Midjourney:**
```
[Sujet lie a la niche], professional photography, soft natural lighting,
warm tones, editorial style, high quality, clean composition --ar 16:9 --v 6
```

### 2. Images Cards / Vignettes articles
**Taille**: 800x500px (ratio 16:10)
**Utilisation**:
- Cartes d'articles sur accueil (index.php ligne 312)
- Liste des articles (articles.php ligne 141)
- Articles similaires (article.php ligne 381)

**Prompt template Midjourney:**
```
[Sujet], editorial photography, clean composition, soft shadows,
neutral background, lifestyle photography style --ar 16:10 --v 6
```

### 3. Thumbnails sidebar
**Taille**: 80x80px (ratio 1:1)
**Utilisation**:
- Miniatures sidebar nouveaux articles (article.php ligne 337)
- Miniatures sidebar articles similaires (article.php ligne 353)

**Note**: Peuvent etre generees en recadrant les images principales au centre.

### 4. Image Open Graph par defaut
**Taille**: 1200x630px (ratio 1.91:1)
**Utilisation**:
- Partage reseaux sociaux quand pas d'article specifique
- Defini dans config.php: `SITE_OG_IMAGE`

---

## Structure de fichiers recommandee

```
/assets/images/
├── articles/
│   ├── bienvenue-hero.webp      (1200x675)
│   ├── bienvenue-card.webp      (800x500)
│   ├── bienvenue-thumb.webp     (80x80)
│   ├── guide-hero.webp
│   ├── guide-card.webp
│   ├── guide-thumb.webp
│   ├── tendances-hero.webp
│   ├── tendances-card.webp
│   └── tendances-thumb.webp
└── defaults/
    ├── og-default.webp          (1200x630)
    └── placeholder.webp         (800x500)
```

---

## Exemples de prompts par niche

### Niche "Bien-etre / Sante"
```
Cozy wellness scene with herbal tea and candles, soft morning light,
minimalist aesthetic, warm beige tones --ar 16:9 --v 6
```

### Niche "Tech / Digital"
```
Modern workspace with laptop and smartphone, clean white desk,
soft natural lighting, tech lifestyle --ar 16:9 --v 6
```

### Niche "Finance / Business"
```
Professional business meeting, modern office interior,
natural daylight, corporate but warm atmosphere --ar 16:9 --v 6
```

### Niche "Voyage / Tourisme"
```
Beautiful travel destination landscape, golden hour lighting,
wanderlust aesthetic, vibrant but natural colors --ar 16:9 --v 6
```

### Niche "Cuisine / Food"
```
Gourmet food photography, overhead shot, rustic wooden table,
soft natural light, appetizing presentation --ar 16:9 --v 6
```

---

## Mise a jour du seed.php

Une fois les images creees, modifier seed.php :

```php
// Avant
'image' => 'https://picsum.photos/800/600?random=1',

// Apres (chemin relatif)
'image' => '/assets/images/articles/bienvenue-hero.webp',
```

---

## Optimisation des images

Avant upload, optimiser avec :
- **Squoosh** (squoosh.app) - compression WebP
- **TinyPNG** (tinypng.com) - compression PNG/JPG
- **ImageOptim** (Mac) - optimisation batch

**Objectifs de poids:**
- Hero/Article: < 150 Ko
- Cards: < 80 Ko
- Thumbnails: < 10 Ko
- Total page d'accueil: < 500 Ko d'images

---

## Checklist avant mise en production

- [ ] Generer images hero pour chaque article
- [ ] Generer variantes cards (800x500)
- [ ] Generer thumbnails (80x80) ou recadrer
- [ ] Creer image OG par defaut
- [ ] Optimiser toutes les images (WebP, compression)
- [ ] Mettre a jour les URLs dans la base de donnees
- [ ] Tester sur PageSpeed Insights (score images)
