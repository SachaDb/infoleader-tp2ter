<?php
/**
 * layout-public.php - Gabarit commun des pages publiques
 *
 * Avant ce fichier, l'en-tete, la navigation et le pied de page
 * etaient recopies a l'identique dans index.php, products.php et
 * contact.php. Ajouter une entree au menu demandait trois
 * modifications ; avec sept pages publiques, il en aurait fallu sept.
 *
 * Le panneau d'administration utilise deja ce principe
 * (admin/_layout.php) : on l'applique ici au site public.
 *
 * Ce fichier vit dans includes/ qui est interdit en HTTP.
 */

/* ==================================================================
   1. NAVIGATION
   ================================================================== */

/** Entrees du menu principal : une seule source de verite */
function il_public_nav()
{
    return [
        'index'    => ['href' => 'index.php',    'label' => 'Accueil'],
        'products' => ['href' => 'products.php', 'label' => 'Nos produits'],
        'contact'  => ['href' => 'contact.php',  'label' => 'Contact'],
    ];
}

/** Pages legales, affichees dans le bas du pied de page */
function il_public_legal_nav()
{
    return [
        'mentions'        => ['href' => 'mentions-legales.php', 'label' => 'Mentions légales'],
        'confidentialite' => ['href' => 'confidentialite.php',  'label' => 'Politique de confidentialité'],
    ];
}

/* ==================================================================
   2. URL ABSOLUES (balises canonical et Open Graph)
   ================================================================== */

/**
 * URL absolue d'une page du site, deduite de la requete courante.
 *
 * Les balises canonical et Open Graph exigent une URL absolue. Comme
 * le site tourne en local et que son dossier peut etre renomme (ici
 * infoleader-tp2ter), l'URL est calculee et non codee en dur.
 *
 * HTTP_HOST vient du client : il est valide avant usage.
 */
function il_site_url($path = '')
{
    $scheme = il_is_https() ? 'https' : 'http';

    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d{1,5})?$/', $host)) {
        $host = 'localhost';
    }

    $dir  = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
    $base = rtrim($dir, '/');

    return $scheme . '://' . $host . $base . '/' . ltrim((string) $path, '/');
}

/* ==================================================================
   3. DEBUT DE PAGE
   ================================================================== */

/**
 * Ouvre une page publique : en-tete HTML complet, balises SEO,
 * lien d'evitement, barre de navigation et bouton de theme.
 *
 * Options attendues :
 *   title       (string) titre de la page, sans le nom du site
 *   description (string) meta description, propre a la page
 *   current     (string) cle de il_public_nav() pour l'entree active
 *   image       (string) visuel de partage, relatif a la racine
 *   robots      (string) valeur de la meta robots (defaut : index, follow)
 */
function il_page_start(array $options = [])
{
    $title       = $options['title'] ?? 'InfoLeader';
    $description = $options['description'] ?? '';
    $current     = $options['current'] ?? '';
    $image       = $options['image'] ?? 'assets/banner.jpg';
    $robots      = $options['robots'] ?? 'index, follow';

    $titreComplet = $title === 'InfoLeader'
        ? 'InfoLeader — Votre partenaire technologique'
        : 'InfoLeader — ' . $title;

    /* URL de la page courante, pour canonical et og:url */
    $script  = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $pageUrl = il_site_url($script === 'index.php' ? '' : $script);
    ?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php /* Titre et description propres a chaque page (SEO) */ ?>
    <title><?php echo e($titreComplet); ?></title>
    <meta name="description" content="<?php echo e($description); ?>">
    <meta name="robots" content="<?php echo e($robots); ?>">
    <link rel="canonical" href="<?php echo e($pageUrl); ?>">

    <?php /* Open Graph : apercu lors d'un partage (reseaux, messageries) */ ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="InfoLeader">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="<?php echo e($titreComplet); ?>">
    <meta property="og:description" content="<?php echo e($description); ?>">
    <meta property="og:url" content="<?php echo e($pageUrl); ?>">
    <meta property="og:image" content="<?php echo e(il_site_url($image)); ?>">
    <meta property="og:image:alt" content="InfoLeader, matériel et services informatiques">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($titreComplet); ?>">
    <meta name="twitter:description" content="<?php echo e($description); ?>">
    <meta name="twitter:image" content="<?php echo e(il_site_url($image)); ?>">

    <meta name="theme-color" content="#1e3c72">

    <?php /* theme.js pose la classe "js" ET applique le theme choisi,
              avant le premier affichage : pas de flash de couleurs, et
              aucun script en ligne dans la page. Volontairement sans defer. */ ?>
    <script src="<?php echo e(il_asset('theme.js')); ?>"></script>

    <link rel="stylesheet" href="<?php echo e(il_asset('style.css')); ?>">
    <script src="<?php echo e(il_asset('script.js')); ?>" defer></script>

    <?php /* Panier : charge sur toutes les pages, car le badge de
              l'en-tete doit etre a jour partout. Le panier lui-meme
              vit dans le localStorage, jamais sur le serveur. */ ?>
    <script src="<?php echo e(il_asset('assets/js/panier.js')); ?>" defer></script>
</head>
<body>

    <a class="skip-link" href="#contenu">Aller au contenu</a>

    <header class="site-header">
        <nav aria-label="Navigation principale">
            <a href="index.php" class="logo">InfoLeader</a>

            <button class="burger" type="button" aria-expanded="false"
                    aria-controls="menu-principal" aria-label="Ouvrir le menu">
                <span></span><span></span><span></span>
            </button>

            <ul class="menu" id="menu-principal">
                <?php foreach (il_public_nav() as $key => $item): ?>
                    <li>
                        <a href="<?php echo e($item['href']); ?>"
                           class="<?php echo $key === $current ? 'active' : ''; ?>"
                           <?php echo $key === $current ? 'aria-current="page"' : ''; ?>>
                            <?php echo e($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php /* Panier : l'icone porte un badge avec le nombre
                          d'articles. Le badge lui-meme est aria-hidden, et
                          l'information est portee par une region aria-live
                          dediee : un lecteur d'ecran annonce ainsi
                          « Panier : 3 articles » plutot que « 3 ». */ ?>
                <li class="menu-panier">
                    <a class="panier-lien" href="panier.php">
                        <span class="panier-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 focusable="false">
                                <circle cx="9" cy="20" r="1.6"></circle>
                                <circle cx="18" cy="20" r="1.6"></circle>
                                <path d="M2 3h2.2l2.6 12.1h12.4l2.2-8.4H6"></path>
                            </svg>
                        </span>
                        <span class="panier-texte">Panier</span>
                        <span class="panier-badge" id="panierBadge" aria-hidden="true" hidden>0</span>
                    </a>
                    <p class="sr-only" id="panierAnnonce" role="status" aria-live="polite">Panier vide</p>
                </li>

                <?php /* Bascule de theme : aria-pressed indique l'etat au
                          lecteur d'ecran. Masquee tant que JavaScript n'a pas
                          pris la main, puisqu'elle n'aurait aucun effet. */ ?>
                <li class="menu-theme">
                    <button type="button" class="theme-toggle" id="themeToggle"
                            aria-pressed="false" hidden>
                        <span class="theme-ico" aria-hidden="true"></span>
                        <span class="theme-label">Thème sombre</span>
                    </button>
                </li>
            </ul>
        </nav>
    </header>
    <?php
}

/* ==================================================================
   4. FIN DE PAGE
   ================================================================== */

/** Ferme la page : pied de page commun et fermeture du document */
function il_page_end()
{
    ?>
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <h3>InfoLeader</h3>
                <p>
                    Société informatique : matériel, conseil et accompagnement
                    pour les particuliers et les professionnels.
                </p>
            </div>
            <div>
                <h3>Navigation</h3>
                <ul>
                    <?php foreach (il_public_nav() as $item): ?>
                        <li><a href="<?php echo e($item['href']); ?>"><?php echo e($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h3>Nous joindre</h3>
                <ul>
                    <li>12 rue de l'Informatique, 75000 Paris</li>
                    <li><a href="tel:+33123456789">01 23 45 67 89</a></li>
                    <li><a href="mailto:contact@infoleader.local">contact@infoleader.local</a></li>
                    <li>Du lundi au vendredi, 9h00 – 18h00</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> InfoLeader. Tous droits réservés.</p>

            <nav class="footer-legal" aria-label="Informations légales">
                <?php foreach (il_public_legal_nav() as $item): ?>
                    <a href="<?php echo e($item['href']); ?>"><?php echo e($item['label']); ?></a>
                <?php endforeach; ?>
                <a href="admin/">Espace d'administration</a>
            </nav>
        </div>
    </footer>

</body>
</html>
    <?php
}
