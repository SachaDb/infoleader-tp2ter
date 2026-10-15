<?php
/**
 * panier.php - Page Panier
 *
 * TP 2quater. Le contenu du panier n'est PAS connu du serveur :
 * il vit dans le localStorage du navigateur. Cette page ne fournit
 * donc que la structure, remplie par assets/js/panier.js.
 *
 * Aucune base de donnees, aucun stockage serveur.
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

il_page_start([
    'title'       => 'Votre panier',
    'description' => 'Consultez les articles de votre panier InfoLeader, ajustez les quantités et lancez une commande simulée.',
    'current'     => '',
    /* Un panier est propre a chaque visiteur : rien a indexer. */
    'robots'      => 'noindex, follow',
]);
?>

    <section class="page-title">
        <h1>Votre panier</h1>
        <p>
            Ajustez les quantités, puis passez à la commande.
            Les prix affichés sont des prix TTC.
        </p>
    </section>

    <main id="contenu">
        <div class="section section--haut">

            <?php /* Region aria-live : les ajouts, retraits et vidages
                      sont annonces au lecteur d'ecran sans deplacer
                      le focus. */ ?>
            <p class="sr-only" id="panierMessage" role="status" aria-live="polite"></p>

            <noscript>
                <div class="success-msg success-msg--warn" role="status">
                    <strong>JavaScript est désactivé.</strong>
                    Le panier fonctionne entièrement dans votre navigateur :
                    il a besoin de JavaScript pour s'afficher. Vous pouvez
                    toutefois consulter
                    <a href="products.php">le catalogue</a> et nous écrire via
                    <a href="contact.php">le formulaire de contact</a>.
                </div>
            </noscript>

            <?php /* Etat vide : revele par le script si le panier ne
                      contient rien. */ ?>
            <div class="no-results" id="panierVide" hidden>
                <p><strong>Votre panier est vide.</strong></p>
                <p>Parcourez le catalogue pour y ajouter du matériel.</p>
                <p class="bloc-bouton">
                    <a href="products.php" class="cta">Voir le catalogue</a>
                </p>
            </div>

            <div class="panier-grille">

                <?php /* Les lignes sont construites par panier.js */ ?>
                <section class="panier-lignes" id="panierContenu"
                         aria-label="Articles du panier"></section>

                <aside class="panier-resume" id="panierResume" hidden
                       aria-labelledby="resumeTitre">
                    <h2 id="resumeTitre">Récapitulatif</h2>

                    <dl class="resume-totaux">
                        <dt>Articles</dt>
                        <dd id="totalArticles">—</dd>

                        <dt>Total HT</dt>
                        <dd id="totalHT">—</dd>

                        <dt>TVA 20 %</dt>
                        <dd id="totalTVA">—</dd>

                        <dt class="resume-final">Total TTC</dt>
                        <dd class="resume-final" id="totalTTC">—</dd>
                    </dl>

                    <a href="commande.php" class="cta btn-bloc">Commander</a>

                    <p class="panier-note">
                        <strong>Commande simulée.</strong> Aucun paiement n'est
                        demandé et aucune donnée bancaire n'est collectée :
                        ce site est un projet pédagogique.
                    </p>
                </aside>
            </div>

            <div class="panier-actions" id="panierActions" hidden>
                <button type="button" class="btn-vider" id="panierVider">
                    Vider le panier
                </button>
                <a href="products.php" class="lien-discret">Continuer mes achats</a>
            </div>

        </div>
    </main>

<?php il_page_end(); ?>
