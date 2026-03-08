<?php
/**
 * Script d'installation
 * Crée la table articles et insère 5 articles de démo
 */

require_once __DIR__ . '/config.php';

// Désactiver l'affichage des erreurs PHP (sécurité)
ini_set('display_errors', 0);

$rapport = [];
$erreur = false;

try {
    $pdo = getDB();
    $rapport[] = "Connexion base de données : OK";

    // 1. Vérifier si déjà installé
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM articles");
        $count = $stmt->fetch()['total'];
        if ($count > 0) {
            echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Installation</title></head><body>";
            echo "<h1>Installation</h1>";
            echo "<p><strong>Déjà installé.</strong> La table articles contient {$count} article(s).</p>";
            echo "</body></html>";
            exit;
        }
    } catch (PDOException $e) {
        // Table n'existe pas, on continue
    }

    // 2. Créer la table articles
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS articles (
            id                      INT AUTO_INCREMENT PRIMARY KEY,
            slug                    VARCHAR(255) UNIQUE NOT NULL,
            titre                   VARCHAR(500) NOT NULL,
            meta_description        TEXT,
            categorie               VARCHAR(100),
            contenu_html            LONGTEXT,
            image                   VARCHAR(255),
            tags                    VARCHAR(500),
            read_time               INT DEFAULT 5,
            date_publication        DATE,
            est_hero                TINYINT DEFAULT 0,
            statut                  ENUM('brouillon', 'planifie', 'publie') DEFAULT 'publie',
            date_publication_prevue DATETIME DEFAULT NULL,
            created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $rapport[] = "Table articles créée : OK";

    // 3. Insérer les 5 articles de démo
    $articles = [
        [
            'slug' => 'cbd-bienfaits-sport',
            'titre' => 'Les bienfaits du CBD pour les sportifs : guide complet',
            'meta_description' => 'Découvrez comment le CBD peut améliorer vos performances sportives, accélérer la récupération et réduire les douleurs musculaires.',
            'categorie' => 'Sport',
            'image' => 'images/article-1.webp',
            'tags' => '["cbd","sport","récupération","performance"]',
            'read_time' => 8,
            'date_publication' => date('Y-m-d', strtotime('-1 month')),
            'est_hero' => 1,
            'contenu_html' => <<<HTML
<p class="intro">Le cannabidiol, plus connu sous le nom de CBD, s'impose progressivement comme un allié incontournable des sportifs de tous niveaux. Que vous soyez un athlète professionnel ou un amateur passionné, comprendre les mécanismes d'action du CBD sur votre corps peut transformer votre approche de l'entraînement et de la récupération. Dans ce guide complet, nous explorons les nombreux bienfaits du CBD pour optimiser vos performances sportives.</p>

<h2>Comment le CBD agit sur le corps des sportifs</h2>
<p>Le système endocannabinoïde présent dans notre organisme joue un rôle crucial dans la régulation de nombreuses fonctions physiologiques. Le CBD interagit avec les récepteurs CB1 et CB2 de ce système, influençant ainsi la gestion de la douleur, l'inflammation et même le stress oxydatif causé par l'effort intense.</p>
<p>Contrairement au THC, le CBD n'a aucun effet psychoactif, ce qui en fait une option légale et sûre pour les athlètes soumis aux contrôles antidopage. L'Agence Mondiale Antidopage (AMA) a d'ailleurs retiré le CBD de sa liste des substances interdites depuis 2018, reconnaissant ainsi son potentiel thérapeutique sans risque pour l'intégrité sportive.</p>
<p>Les études scientifiques démontrent que le CBD peut moduler la réponse inflammatoire de l'organisme après un effort intense. Cette propriété anti-inflammatoire naturelle permet de réduire les micro-lésions musculaires et d'accélérer le processus de réparation tissulaire.</p>

<h2>Récupération musculaire et réduction des douleurs</h2>
<p>L'un des avantages les plus documentés du CBD pour les sportifs concerne la récupération post-entraînement. Les courbatures, ou DOMS (Delayed Onset Muscle Soreness), peuvent considérablement affecter vos performances lors des séances suivantes.</p>
<blockquote>
<p>"Depuis que j'ai intégré le CBD dans ma routine de récupération, je constate une nette diminution des courbatures et une meilleure qualité de sommeil. Mes performances en compétition se sont améliorées de façon significative." — Thomas, coureur de marathon</p>
</blockquote>
<p>Le CBD agit sur plusieurs fronts pour optimiser la récupération. En réduisant l'inflammation locale, il diminue la sensation de douleur et permet aux fibres musculaires de se régénérer plus efficacement. De plus, ses propriétés relaxantes favorisent un sommeil réparateur, phase essentielle de la reconstruction musculaire.</p>
<p>Les formes topiques de CBD, comme les baumes et les huiles de massage, offrent une action ciblée sur les zones douloureuses. Appliquées directement après l'effort, elles pénètrent la peau pour agir localement sur les tissus sollicités.</p>

<h2>Gestion du stress et amélioration du sommeil</h2>
<p>La performance sportive ne se limite pas à l'aspect physique. Le mental joue un rôle déterminant, particulièrement lors des compétitions où la pression peut devenir paralysante. Le CBD possède des propriétés anxiolytiques reconnues qui aident les athlètes à gérer leur stress avant et pendant les épreuves.</p>
<p>Le sommeil constitue un pilier fondamental de la récupération sportive. C'est durant les phases de sommeil profond que l'organisme sécrète l'hormone de croissance, essentielle à la réparation musculaire. Le CBD favorise l'endormissement et améliore la qualité du sommeil en régulant les cycles circadiens.</p>
<ul>
<li>Réduction de l'anxiété pré-compétition</li>
<li>Amélioration de la concentration et de la clarté mentale</li>
<li>Endormissement plus rapide et sommeil plus profond</li>
<li>Diminution des réveils nocturnes</li>
<li>Sensation de repos accrue au réveil</li>
</ul>
<p>Pour bénéficier pleinement des effets du CBD sur le sommeil, il est recommandé de prendre votre dose environ une heure avant le coucher. Les huiles sublinguales offrent une absorption rapide et permettent un dosage précis adapté à vos besoins.</p>

<h2>Comment intégrer le CBD dans votre routine sportive</h2>
<p>L'intégration du CBD dans votre programme d'entraînement doit se faire de manière progressive et réfléchie. Commencez par de faibles doses et augmentez graduellement jusqu'à trouver le dosage optimal pour votre métabolisme et vos objectifs.</p>
<p>Les moments clés pour la prise de CBD varient selon les effets recherchés. Avant l'entraînement, une dose modérée peut améliorer la concentration et réduire l'appréhension. Après l'effort, une dose plus conséquente favorisera la récupération et la détente musculaire.</p>
<p>Choisissez des produits CBD de qualité, issus de chanvre cultivé biologiquement et testés en laboratoire. La transparence sur la composition et l'origine des produits garantit leur efficacité et leur sécurité pour votre santé de sportif.</p>
HTML
        ],
        [
            'slug' => 'huile-cbd-guide-debutant',
            'titre' => 'Huile de CBD : le guide ultime pour bien débuter',
            'meta_description' => 'Tout ce que vous devez savoir sur l\'huile de CBD : comment la choisir, la doser et l\'utiliser pour profiter de ses bienfaits.',
            'categorie' => 'Guide',
            'image' => 'images/article-2.webp',
            'tags' => '["cbd","huile","débutant","dosage"]',
            'read_time' => 10,
            'date_publication' => date('Y-m-d', strtotime('-2 months')),
            'est_hero' => 0,
            'contenu_html' => <<<HTML
<p class="intro">L'huile de CBD est devenue l'un des produits de bien-être les plus populaires ces dernières années. Pourtant, face à la multitude d'options disponibles sur le marché, il peut être difficile de s'y retrouver lorsqu'on débute. Ce guide complet vous accompagne pas à pas dans la découverte de l'huile de CBD, de son choix à son utilisation optimale.</p>

<h2>Qu'est-ce que l'huile de CBD exactement ?</h2>
<p>L'huile de CBD est un extrait naturel obtenu à partir des fleurs et des feuilles de chanvre (Cannabis sativa L.). Elle contient du cannabidiol (CBD), l'un des nombreux cannabinoïdes présents dans la plante, reconnu pour ses propriétés thérapeutiques sans effets psychoactifs.</p>
<p>Le processus d'extraction le plus courant est l'extraction au CO2 supercritique, considérée comme la méthode la plus pure et la plus efficace. Elle permet d'obtenir un extrait concentré tout en préservant les composés bénéfiques de la plante, notamment les terpènes et les flavonoïdes.</p>
<p>L'huile obtenue est ensuite diluée dans une huile porteuse, généralement de l'huile de chanvre, de coco ou d'olive. Cette dilution facilite le dosage et améliore l'absorption du CBD par l'organisme. Les concentrations varient généralement de 5% à 30% de CBD.</p>

<h2>Les différents types d'huiles CBD</h2>
<p>Comprendre les différentes formulations vous aidera à choisir le produit le plus adapté à vos besoins. Trois grandes catégories dominent le marché :</p>
<blockquote>
<p>Le spectre complet (Full Spectrum) contient tous les cannabinoïdes, terpènes et flavonoïdes naturellement présents dans le chanvre, y compris des traces de THC (moins de 0,3%). Cette synergie entre composés, appelée "effet d'entourage", potentialise les bienfaits du CBD.</p>
</blockquote>
<p>Le spectre large (Broad Spectrum) offre les mêmes avantages que le Full Spectrum, mais sans aucune trace de THC. C'est l'option idéale pour ceux qui souhaitent éviter totalement le THC tout en bénéficiant de l'effet d'entourage.</p>
<p>L'isolat de CBD est la forme la plus pure, contenant uniquement du cannabidiol à plus de 99%. Recommandé pour les personnes sensibles aux autres composés ou soumises à des tests de dépistage stricts.</p>
<ul>
<li>Full Spectrum : effet d'entourage maximal, traces de THC</li>
<li>Broad Spectrum : effet d'entourage sans THC</li>
<li>Isolat : CBD pur à 99%, sans autres composés</li>
<li>Huile porteuse : chanvre, MCT (coco) ou olive</li>
</ul>

<h2>Comment doser correctement votre huile CBD</h2>
<p>Le dosage du CBD est personnel et dépend de nombreux facteurs : votre poids, votre métabolisme, la condition traitée et votre sensibilité individuelle aux cannabinoïdes. La règle d'or est de commencer bas et d'augmenter progressivement.</p>
<p>Pour un débutant, une dose de départ de 10 à 20 mg de CBD par jour est généralement recommandée. Maintenez cette dose pendant une semaine en observant les effets, puis ajustez si nécessaire. Certaines personnes trouvent leur dosage idéal à 25 mg, d'autres ont besoin de 100 mg ou plus.</p>
<p>L'administration sublinguale est la méthode la plus efficace. Déposez les gouttes sous la langue, maintenez 60 à 90 secondes avant d'avaler. Cette technique permet une absorption directe dans la circulation sanguine via les muqueuses, avec des effets ressentis en 15 à 30 minutes.</p>
<p>Tenez un journal de vos prises et de vos ressentis. Notez la dose, l'heure, les effets observés et leur durée. Cette approche méthodique vous permettra d'identifier rapidement votre dosage optimal et les moments les plus propices à la prise.</p>

<h2>Critères de qualité pour choisir votre huile</h2>
<p>La qualité de l'huile CBD varie considérablement d'un fabricant à l'autre. Pour garantir efficacité et sécurité, plusieurs critères doivent guider votre choix. Privilégiez les marques transparentes qui publient les analyses de leurs produits.</p>
<p>Les certificats d'analyse (COA) émis par des laboratoires indépendants attestent de la concentration réelle en CBD et de l'absence de contaminants (pesticides, métaux lourds, solvants résiduels). Ces documents doivent être facilement accessibles sur le site du fabricant.</p>
<p>L'origine du chanvre est également déterminante. Le chanvre européen, cultivé selon les normes strictes de l'UE, offre généralement de meilleures garanties que des sources moins traçables. La culture biologique certifiée constitue un gage supplémentaire de qualité.</p>
<p>Enfin, méfiez-vous des prix anormalement bas. Une huile CBD de qualité représente un investissement, mais garantit des résultats. Les produits bon marché utilisent souvent des méthodes d'extraction moins efficaces ou du chanvre de qualité inférieure.</p>
HTML
        ],
        [
            'slug' => 'cbd-sommeil-insomnie',
            'titre' => 'CBD et sommeil : une solution naturelle contre l\'insomnie',
            'meta_description' => 'Découvrez comment le CBD peut vous aider à retrouver un sommeil réparateur et lutter naturellement contre l\'insomnie.',
            'categorie' => 'Bien-être',
            'image' => 'images/article-3.webp',
            'tags' => '["cbd","sommeil","insomnie","relaxation"]',
            'read_time' => 7,
            'date_publication' => date('Y-m-d', strtotime('-3 months')),
            'est_hero' => 0,
            'contenu_html' => <<<HTML
<p class="intro">Les troubles du sommeil touchent près d'un tiers de la population française. Face aux effets secondaires des somnifères classiques, de plus en plus de personnes se tournent vers des alternatives naturelles. Le CBD s'impose comme une solution prometteuse pour retrouver des nuits paisibles et réparatrices.</p>

<h2>Comprendre les mécanismes du sommeil</h2>
<p>Le sommeil se compose de plusieurs cycles successifs, chacun comprenant des phases de sommeil léger, profond et paradoxal (REM). Un adulte effectue en moyenne 4 à 6 cycles par nuit, chaque cycle durant environ 90 minutes. La qualité du sommeil dépend de l'équilibre entre ces différentes phases.</p>
<p>Le sommeil profond est particulièrement crucial pour la récupération physique. C'est durant cette phase que l'organisme sécrète l'hormone de croissance, répare les tissus et renforce le système immunitaire. Le sommeil paradoxal, quant à lui, joue un rôle essentiel dans la consolidation de la mémoire et la régulation émotionnelle.</p>
<p>L'insomnie peut prendre plusieurs formes : difficultés d'endormissement, réveils nocturnes fréquents ou réveil précoce. Quelle que soit sa manifestation, elle perturbe l'architecture du sommeil et prive l'organisme d'une récupération optimale, avec des conséquences sur la santé physique et mentale.</p>

<h2>Comment le CBD favorise un sommeil de qualité</h2>
<p>Le CBD agit sur le sommeil de manière indirecte, en ciblant les causes sous-jacentes des troubles. Son interaction avec le système endocannabinoïde influence plusieurs mécanismes impliqués dans la régulation du cycle veille-sommeil.</p>
<blockquote>
<p>"Après des années d'insomnie chronique et de dépendance aux somnifères, j'ai découvert le CBD. En quelques semaines, j'ai retrouvé un sommeil naturel et réparateur, sans sensation de fatigue au réveil." — Marie, 45 ans</p>
</blockquote>
<p>L'effet anxiolytique du CBD constitue son principal atout contre l'insomnie. En réduisant l'anxiété et les ruminations mentales, il facilite le lâcher-prise nécessaire à l'endormissement. Les pensées parasites qui empêchent de trouver le sommeil s'estompent progressivement.</p>
<p>Le CBD influence également la production de mélatonine, l'hormone du sommeil. En régulant le cycle circadien, il aide l'organisme à synchroniser son horloge biologique avec les rythmes naturels jour-nuit, favorisant un endormissement à heure régulière.</p>
<ul>
<li>Réduction de l'anxiété et du stress pré-sommeil</li>
<li>Diminution des douleurs qui perturbent le repos</li>
<li>Régulation du cycle circadien</li>
<li>Amélioration de la durée du sommeil profond</li>
<li>Réduction des réveils nocturnes</li>
</ul>

<h2>Protocole d'utilisation pour le sommeil</h2>
<p>Pour optimiser les effets du CBD sur votre sommeil, le timing et le dosage sont essentiels. Une prise environ une heure avant le coucher permet au cannabidiol d'atteindre sa concentration maximale dans le sang au moment de l'endormissement.</p>
<p>Commencez par une dose modérée de 20 à 30 mg et ajustez selon vos besoins. Certaines personnes répondent mieux à des doses plus faibles, d'autres nécessitent des quantités plus importantes. L'observation de vos réactions vous guidera vers le dosage optimal.</p>
<p>La constance est primordiale. Les effets du CBD sur le sommeil s'amplifient avec une utilisation régulière. Le système endocannabinoïde se rééquilibre progressivement, améliorant la qualité du sommeil sur le long terme. Accordez-vous au moins deux semaines d'utilisation quotidienne avant d'évaluer les résultats.</p>

<h2>Associer le CBD à une bonne hygiène de sommeil</h2>
<p>Le CBD ne peut pas compenser de mauvaises habitudes de sommeil. Pour des résultats optimaux, associez sa prise à une hygiène de vie favorable au repos. Ces bonnes pratiques potentialisent les effets du cannabidiol.</p>
<p>Établissez une routine de coucher régulière, même le week-end. Évitez les écrans au moins une heure avant de dormir, leur lumière bleue perturbant la sécrétion de mélatonine. Créez un environnement propice au sommeil : chambre fraîche (18-19°C), obscure et silencieuse.</p>
<p>Limitez la caféine après 14h et évitez l'alcool en soirée. Bien qu'il facilite l'endormissement, l'alcool fragmente le sommeil et réduit sa qualité globale. Une activité physique régulière améliore le sommeil, mais évitez les efforts intenses dans les 3 heures précédant le coucher.</p>
<p>Certaines infusions peuvent compléter l'action du CBD : camomille, valériane, passiflore ou mélisse possèdent des propriétés relaxantes reconnues. La combinaison de ces plantes avec le CBD crée une synergie favorable à un endormissement serein.</p>
HTML
        ],
        [
            'slug' => 'cbd-legal-france-2024',
            'titre' => 'CBD en France : tout savoir sur la législation en 2024',
            'meta_description' => 'Point complet sur la législation du CBD en France : ce qui est autorisé, les évolutions récentes et ce que dit la loi.',
            'categorie' => 'Législation',
            'image' => 'images/article-4.webp',
            'tags' => '["cbd","législation","france","legal"]',
            'read_time' => 6,
            'date_publication' => date('Y-m-d', strtotime('-4 months')),
            'est_hero' => 0,
            'contenu_html' => <<<HTML
<p class="intro">La réglementation du CBD en France a connu de nombreux rebondissements ces dernières années. Entre arrêtés ministériels et décisions de justice, il peut être difficile de s'y retrouver. Cet article fait le point sur le cadre légal actuel et vous aide à consommer en toute légalité.</p>

<h2>Le cadre juridique actuel du CBD</h2>
<p>En France, le CBD est légal sous certaines conditions strictement définies. La distinction fondamentale repose sur le taux de THC, la molécule psychoactive du cannabis. Pour être commercialisé légalement, un produit CBD doit contenir moins de 0,3% de THC, conformément à la réglementation européenne harmonisée.</p>
<p>Cette limite de 0,3% s'applique au produit fini et non plus seulement à la plante, comme c'était le cas auparavant. Cette évolution, effective depuis 2022, a clarifié le statut de nombreux produits qui se trouvaient dans une zone grise juridique.</p>
<p>La Cour de Justice de l'Union Européenne a joué un rôle déterminant dans l'évolution de la législation française. Son arrêt "Kanavape" de novembre 2020 a établi que le CBD ne pouvait être considéré comme un stupéfiant et que sa libre circulation au sein de l'UE devait être garantie.</p>

<h2>Les produits CBD autorisés à la vente</h2>
<p>La commercialisation de produits CBD est autorisée pour de nombreuses catégories, à condition de respecter les seuils de THC et les règles d'étiquetage. Les huiles, gélules, cosmétiques et e-liquides CBD sont les plus répandus sur le marché français.</p>
<blockquote>
<p>Attention : les allégations thérapeutiques sont interdites. Un produit CBD ne peut pas être vendu comme médicament ni prétendre traiter, guérir ou prévenir une maladie. Seules les mentions relatives au bien-être général sont autorisées.</p>
</blockquote>
<p>La vente de fleurs et feuilles de CBD a longtemps fait l'objet de controverses. Après une tentative d'interdiction par le gouvernement français, le Conseil d'État a suspendu cette mesure, jugeant qu'elle portait une atteinte disproportionnée à la liberté du commerce. Les fleurs CBD restent donc commercialisables.</p>
<ul>
<li>Huiles et teintures CBD : autorisées (moins de 0,3% THC)</li>
<li>Gélules et compléments alimentaires : autorisés avec déclaration</li>
<li>Cosmétiques au CBD : autorisés selon réglementation cosmétique</li>
<li>E-liquides CBD : autorisés pour vapotage</li>
<li>Fleurs et résines CBD : autorisées (moins de 0,3% THC)</li>
<li>Produits alimentaires infusés : réglementation Novel Food en cours</li>
</ul>

<h2>Les obligations des vendeurs et fabricants</h2>
<p>Les professionnels du secteur CBD sont soumis à des obligations strictes. Tout produit mis sur le marché doit pouvoir justifier de sa conformité, notamment par des analyses de laboratoire attestant du taux de THC et de l'absence de contaminants.</p>
<p>L'étiquetage doit mentionner clairement la composition, la concentration en CBD, les ingrédients et les précautions d'emploi. Les informations trompeuses ou les allégations de santé non autorisées exposent à des sanctions pouvant aller jusqu'à deux ans d'emprisonnement et 300 000 euros d'amende.</p>
<p>Les produits alimentaires contenant du CBD sont soumis à la réglementation "Novel Food" de l'Union Européenne. Leur mise sur le marché nécessite une autorisation préalable de la Commission Européenne, actuellement en cours d'instruction pour de nombreux dossiers.</p>

<h2>Conseils pour une consommation en règle</h2>
<p>En tant que consommateur, quelques précautions vous permettent de rester dans la légalité. Achetez uniquement auprès de vendeurs établis, capables de fournir les certificats d'analyse de leurs produits. Méfiez-vous des offres trop alléchantes provenant de sources douteuses.</p>
<p>Conservez les emballages et preuves d'achat de vos produits CBD. En cas de contrôle, vous pourrez démontrer que vous possédez des produits conformes à la législation. Le CBD étant visuellement similaire au cannabis illicite, cette précaution peut vous éviter des désagréments.</p>
<p>La conduite sous influence de CBD n'est pas interdite en soi, mais prudence : certains produits peuvent contenir des traces de THC susceptibles d'être détectées lors d'un dépistage salivaire. Choisissez des produits garantis sans THC (isolat ou broad spectrum) si vous devez conduire.</p>
<p>Enfin, restez informé des évolutions réglementaires. Le cadre juridique du CBD continue d'évoluer, avec des clarifications attendues au niveau européen dans les prochaines années. Les associations professionnelles du secteur constituent de bonnes sources d'information actualisée.</p>
HTML
        ],
        [
            'slug' => 'cbd-stress-anxiete',
            'titre' => 'CBD contre le stress et l\'anxiété : ce que dit la science',
            'meta_description' => 'Analyse des études scientifiques sur l\'efficacité du CBD pour réduire le stress et l\'anxiété au quotidien.',
            'categorie' => 'Santé',
            'image' => 'images/article-5.webp',
            'tags' => '["cbd","stress","anxiété","études"]',
            'read_time' => 9,
            'date_publication' => date('Y-m-d', strtotime('-5 months')),
            'est_hero' => 0,
            'contenu_html' => <<<HTML
<p class="intro">Dans un monde où le stress est devenu omniprésent, la recherche de solutions naturelles pour préserver notre équilibre mental s'intensifie. Le CBD suscite un intérêt croissant de la communauté scientifique pour ses propriétés anxiolytiques. Examinons ce que les études révèlent sur son efficacité contre le stress et l'anxiété.</p>

<h2>Les mécanismes d'action du CBD sur l'anxiété</h2>
<p>Le CBD interagit avec plusieurs systèmes de neurotransmission impliqués dans la régulation de l'anxiété. Sa cible principale est le récepteur 5-HT1A de la sérotonine, souvent surnommée "hormone du bonheur". En activant ce récepteur, le CBD produit des effets similaires à certains médicaments anxiolytiques, mais sans leurs effets secondaires.</p>
<p>Le système endocannabinoïde joue également un rôle central. Le CBD module l'activité de l'anandamide, un endocannabinoïde naturellement produit par notre organisme et impliqué dans la gestion du stress. En inhibant sa dégradation, le CBD prolonge ses effets apaisants sur le système nerveux.</p>
<p>Des études d'imagerie cérébrale ont montré que le CBD modifie l'activité de l'amygdale, structure cérébrale impliquée dans le traitement de la peur et de l'anxiété. Cette action expliquerait sa capacité à réduire les réponses anxieuses face aux situations stressantes.</p>

<h2>Les études cliniques sur le CBD et l'anxiété</h2>
<p>La recherche scientifique sur le CBD et l'anxiété s'est considérablement développée ces dernières années. Plusieurs essais cliniques rigoureux ont été menés, apportant des preuves encourageantes de son efficacité.</p>
<blockquote>
<p>Une étude publiée dans le Journal of Psychopharmacology a démontré qu'une dose unique de 600 mg de CBD réduisait significativement l'anxiété lors d'une simulation de prise de parole en public, par rapport au placebo. Les participants présentaient moins de signes d'anxiété cognitive et physiologique.</p>
</blockquote>
<p>Une méta-analyse de 2020 portant sur 7 études cliniques a conclu que le CBD présente un potentiel thérapeutique significatif pour plusieurs troubles anxieux, notamment l'anxiété sociale, le trouble panique et le trouble de stress post-traumatique. Les effets étaient observés à des doses variables selon les études.</p>
<p>Des recherches sur l'anxiété généralisée ont montré des résultats prometteurs. Dans une étude brésilienne, des patients souffrant d'anxiété généralisée ont rapporté une diminution significative de leurs symptômes après 4 semaines de traitement au CBD, avec une bonne tolérance du traitement.</p>
<ul>
<li>Anxiété sociale : réduction des symptômes lors de situations sociales</li>
<li>Anxiété généralisée : diminution de l'inquiétude chronique</li>
<li>Trouble panique : réduction de la fréquence des attaques</li>
<li>TSPT : amélioration des symptômes intrusifs</li>
<li>Anxiété liée à l'insomnie : facilitation de l'endormissement</li>
</ul>

<h2>CBD vs médicaments anxiolytiques classiques</h2>
<p>Les benzodiazépines et les antidépresseurs ISRS constituent les traitements de référence de l'anxiété. Bien qu'efficaces, ils présentent des inconvénients notables : risque de dépendance pour les benzodiazépines, délai d'action de plusieurs semaines pour les ISRS, effets secondaires parfois invalidants.</p>
<p>Le CBD se distingue par son profil de sécurité favorable. Les études n'ont pas mis en évidence de potentiel addictif ni de syndrome de sevrage à l'arrêt. Les effets secondaires rapportés sont généralement légers : fatigue, modifications de l'appétit, troubles digestifs occasionnels.</p>
<p>Il est crucial de noter que le CBD ne remplace pas un traitement médical prescrit. Si vous suivez un traitement pour l'anxiété, ne l'interrompez jamais sans avis médical. Le CBD peut toutefois constituer un complément intéressant ou une alternative pour les formes légères à modérées d'anxiété.</p>

<h2>Utilisation pratique du CBD contre le stress</h2>
<p>Pour une gestion efficace du stress quotidien, une approche régulière est préférable à une utilisation ponctuelle. Une dose quotidienne de CBD, généralement entre 25 et 75 mg, permet de maintenir un niveau stable dans l'organisme et d'optimiser ses effets anxiolytiques.</p>
<p>Le choix de la forme galénique dépend de vos besoins. L'huile sublinguale offre une action rapide (15-30 minutes) idéale pour les pics d'anxiété. Les gélules, à action plus progressive, conviennent mieux à une gestion au long cours. Les vaporisateurs procurent l'effet le plus immédiat pour les situations aiguës.</p>
<p>Associez le CBD à des techniques de gestion du stress validées : méditation de pleine conscience, exercices de respiration, activité physique régulière. Cette approche multimodale potentialise les bénéfices et favorise une résilience durable face au stress.</p>
<p>Consultez un professionnel de santé avant de débuter une supplémentation en CBD, particulièrement si vous prenez des médicaments. Le CBD peut interagir avec certains traitements en modifiant leur métabolisme hépatique. Un avis médical vous permettra d'utiliser le CBD en toute sécurité.</p>
HTML
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO articles (slug, titre, meta_description, categorie, contenu_html, image, tags, read_time, date_publication, est_hero)
        VALUES (:slug, :titre, :meta_description, :categorie, :contenu_html, :image, :tags, :read_time, :date_publication, :est_hero)
    ");

    foreach ($articles as $article) {
        $stmt->execute($article);
    }
    $rapport[] = "Articles insérés : 5";

} catch (PDOException $e) {
    $erreur = true;
    $rapport[] = "Erreur : " . $e->getMessage();
}

// 4. Afficher le rapport
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        h1 { color: #333; }
        .success { color: #2C5F2E; }
        .error { color: #c0392b; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-top: 20px; }
        ul { list-style: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #eee; }
        li:before { content: "✓ "; color: #2C5F2E; }
    </style>
</head>
<body>
    <h1><?= $erreur ? '<span class="error">Installation échouée</span>' : '<span class="success">Installation réussie</span>' ?></h1>

    <ul>
        <?php foreach ($rapport as $ligne): ?>
            <li><?= htmlspecialchars($ligne) ?></li>
        <?php endforeach; ?>
    </ul>

    <?php if (!$erreur): ?>
    <div class="warning">
        <strong>Supprime ce fichier install.php de ton serveur maintenant !</strong>
    </div>
    <?php endif; ?>
</body>
</html>
