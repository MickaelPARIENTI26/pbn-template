# Images à créer pour le template PBN

## Analyse des balises `<img>` dans le code

### index.php
| Contexte | Classe CSS | Ratio | Taille recommandée |
|----------|------------|-------|-------------------|
| Hero article | `w-100 h-100 object-fit-cover` | 16:9 | 1200x675px |
| Cards articles | (dans `.card-img-wrap`) | 16:10 | 800x500px |

### article.php
| Contexte | Classe CSS | Ratio | Taille recommandée |
|----------|------------|-------|-------------------|
| Image hero article | `article-hero-img` | 16:9, max-height 400px | 1200x675px |
| Sidebar thumbnails | `.sidebar-article-item img` | 1:1 | 80x80px |
| Cards articles similaires | (dans `.card-img-wrap`) | 16:10 | 800x500px |

### articles.php
| Contexte | Classe CSS | Ratio | Taille recommandée |
|----------|------------|-------|-------------------|
| Cards articles | (dans `.card-img-wrap`) | 16:10 | 800x500px |

---

## Images de démonstration actuelles (seed.php)

Les articles de démo utilisent actuellement `https://picsum.photos/800/600?random=X` (placeholder).

| Article | Slug | Catégorie | Image actuelle |
|---------|------|-----------|----------------|
| Bienvenue sur notre site | bienvenue-sur-notre-site | Actualités | picsum.photos/800/600?random=1 |
| Guide complet pour débuter | guide-complet-pour-debuter | Guides | picsum.photos/800/600?random=2 |
| Les tendances 2026 | les-tendances-2026 | Tendances | picsum.photos/800/600?random=3 |

---

## Spécifications pour génération IA (Midjourney, fal.ai, etc.)

### Format recommandé
- **Format**: WebP (pour performance) ou JPG
- **Qualité**: 80-85%
- **Couleurs**: Harmonisées avec la palette du site (tons naturels, beige, vert olive)

### Images à générer

#### 1. Hero / Image principale d'article
**Taille**: 1200x675px (ratio 16:9)
**Utilisation**: Hero en page d'accueil, image principale des articles

```
Prompt template:
"[Sujet lié à la niche], professional photography, soft natural lighting,
warm tones, editorial style, high quality, 16:9 aspect ratio --ar 16:9 --v 6"
```

**Exemples pour une niche "bien-être" :**
- article-hero-bienvenue.webp : "Cozy wellness scene with herbal tea, candles, soft morning light, minimalist aesthetic"
- article-hero-guide.webp : "Person reading a book in a peaceful garden, natural daylight, calm atmosphere"
- article-hero-tendances.webp : "Modern wellness products flat lay, clean white background, natural materials"

#### 2. Cards / Vignettes d'articles
**Taille**: 800x500px (ratio 16:10)
**Utilisation**: Cartes d'articles sur accueil et listing

```
Prompt template:
"[Sujet], editorial photography, clean composition, soft shadows,
neutral background, lifestyle photography style --ar 16:10 --v 6"
```

#### 3. Thumbnails sidebar
**Taille**: 80x80px (ratio 1:1)
**Utilisation**: Miniatures dans la sidebar
**Note**: Peuvent être générées en recadrant les images principales

---

## Structure de fichiers recommandée

```
/images/
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
    ├── og-default.webp          (1200x630 pour Open Graph)
    └── placeholder.webp         (800x500 image par défaut)
```

---

## Mise à jour du seed.php

Une fois les images créées, modifier seed.php :

```php
'image' => '/images/articles/bienvenue-hero.webp',
// au lieu de
'image' => 'https://picsum.photos/800/600?random=1',
```

---

## Optimisation des images

Avant upload, optimiser avec :
- **Squoosh** (squoosh.app) - compression WebP
- **TinyPNG** - compression PNG/JPG
- **ImageOptim** (Mac) - optimisation batch

Objectif : < 100Ko par image pour les cards, < 200Ko pour les hero.
