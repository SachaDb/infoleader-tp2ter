<?php
/**
 * products.php - Catalogue produits
 *
 * Les fiches sont rendues côté serveur depuis products.json : la page
 * fonctionne sans JavaScript, et la recherche en temps réel se contente
 * de filtrer les cartes déjà présentes.
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

$produits = il_products_all();
$nombre   = count($produits);

il_page_start([
    'title'       => 'Nos produits',
    'description' => 'Catalogue InfoLeader : claviers, souris, écrans, casques, webcams et accessoires informatiques sélectionnés.',
    'current'     => 'products',
]);
?>

    <section class="page-title">
        <h1>Nos produits</h1>
        <p>
            Une sélection de périphériques et d'écrans testés par notre équipe.
            Utilisez la recherche pour trouver rapidement votre matériel.
        </p>
    </section>

    <main id="contenu">
        <div class="section section--haut">

            <div class="search-bar reveal">
                <label class="sr-only" for="searchInput">Rechercher un produit</label>
                <input type="search" id="searchInput" placeholder="Rechercher un produit…"
                       autocomplete="off" aria-describedby="searchCount">
            </div>

            <p class="search-count" id="searchCount" role="status">
                <?php echo $nombre; ?> produit<?php echo $nombre > 1 ? 's' : ''; ?> au catalogue
            </p>

            <?php if (!$produits): ?>

                <p class="no-results">
                    Le catalogue est en cours de mise à jour. Revenez très bientôt.
                </p>

            <?php else: ?>

                <section class="products" id="productGrid">
                    <?php foreach ($produits as $index => $produit): ?>
                        <article class="product reveal" data-delay="<?php echo ($index % 3) + 1; ?>"
                                 data-nom="<?php echo e(mb_strtolower($produit['nom'])); ?>"
                                 data-description="<?php echo e(mb_strtolower($produit['description'])); ?>">
                            <div class="thumb">
                                <?php /* width et height reservent la place de l'image
                                          avant son chargement : la mise en page ne
                                          sursaute plus (decalage cumulatif). */ ?>
                                <img src="<?php echo e($produit['image']); ?>"
                                     alt="<?php echo e($produit['nom']); ?>"
                                     width="600" height="400"
                                     loading="lazy" decoding="async">
                            </div>
                            <div class="body">
                                <h3><?php echo e($produit['nom']); ?></h3>
                                <p><?php echo e($produit['description']); ?></p>
                                <div class="price">
                                    <span class="montant">
                                        <?php echo e(number_format((float) $produit['prix'], 2, ',', ' ')); ?>&nbsp;&euro;
                                    </span>
                                    <span class="mention-ttc">TTC</span>
                                </div>

                                <div class="fiche-actions">
                                    <?php /* Les attributs data-* portent les informations
                                              du produit. assets/js/panier.js les lit au clic
                                              et enregistre la ligne dans le localStorage.
                                              data-prix utilise un point decimal, pour etre
                                              exploitable directement en JavaScript. */ ?>
                                    <button type="button" class="btn-ajouter"
                                            data-id="<?php echo e($produit['id']); ?>"
                                            data-nom="<?php echo e($produit['nom']); ?>"
                                            data-prix="<?php echo e(number_format((float) $produit['prix'], 2, '.', '')); ?>"
                                            data-image="<?php echo e($produit['image']); ?>"
                                            aria-label="Ajouter <?php echo e($produit['nom']); ?> au panier">
                                        Ajouter au panier
                                    </button>

                                    <?php /* Le bouton s'appelait « Commander » mais ne
                                              commandait rien : il menait au formulaire de
                                              contact. Il reste utile pour une demande de
                                              conseil, sous un intitule honnete. */ ?>
                                    <a class="buy buy--secondaire"
                                       href="contact.php?objet=<?php echo e(urlencode('Devis : ' . $produit['nom'])); ?>"
                                       aria-label="Demander un devis pour <?php echo e($produit['nom']); ?>">
                                        Demander un devis
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <?php /* Corrige un defaut du site d'origine : ce message
                          n'avait pas d'etat masque par defaut, et s'affichait
                          donc SOUS les 6 produits des l'ouverture de la page.
                          L'attribut hidden le masque ; products.js le revele
                          uniquement quand la recherche ne donne rien. */ ?>
                <p class="no-results" id="noResults" role="status" hidden>
                    Aucun produit ne correspond à votre recherche.
                </p>

            <?php endif; ?>
        </div>

        <section class="cta-band reveal">
            <h2>Besoin d'un conseil avant d'acheter ?</h2>
            <p>
                Décrivez-nous votre usage : nous vous orientons vers le matériel
                qui correspond vraiment à votre besoin.
            </p>
            <a href="contact.php" class="cta">Demander un conseil</a>
        </section>
    </main>

    <?php /* Filtre de recherche : code deplace dans products.js, a
              l'identique. Plus aucun script en ligne dans cette page. */ ?>
    <script src="<?php echo e(il_asset('products.js')); ?>" defer></script>

<?php il_page_end(); ?>
