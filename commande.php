<?php
/**
 * commande.php - Tunnel de commande SIMULE
 *
 * TP 2quater.
 *
 * SIMULATION. Aucun paiement reel : pas de passerelle bancaire,
 * pas de champ de carte, aucun debit, aucune transmission a un
 * service tiers. La commande n'est pas envoyee au serveur : tout
 * se passe dans le navigateur, comme le panier.
 *
 * Les trois etats de la page (panier vide, formulaire,
 * confirmation) sont presents dans le HTML et reveles un a un par
 * assets/js/commande.js.
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

il_page_start([
    'title'       => 'Commande',
    'description' => 'Finalisez votre commande simulée InfoLeader : coordonnées de livraison et récapitulatif.',
    'current'     => '',
    'robots'      => 'noindex, nofollow',
]);
?>

    <section class="page-title no-print">
        <h1>Votre commande</h1>
        <p>Dernière étape : vos coordonnées de livraison.</p>
    </section>

    <main id="contenu">
        <div class="section section--haut">

            <noscript>
                <div class="success-msg success-msg--warn" role="status">
                    <strong>JavaScript est désactivé.</strong>
                    La commande se déroule entièrement dans votre navigateur et
                    nécessite JavaScript. Vous pouvez nous adresser votre demande
                    par <a href="contact.php">le formulaire de contact</a>.
                </div>
            </noscript>

            <!-- ============ ETAT 1 : panier vide ============ -->
            <div class="no-results" id="etapeVide" hidden>
                <p><strong>Votre panier est vide.</strong></p>
                <p>Il n'y a rien à commander pour le moment.</p>
                <p class="bloc-bouton">
                    <a href="products.php" class="cta">Voir le catalogue</a>
                </p>
            </div>

            <!-- ============ ETAT 2 : formulaire ============ -->
            <div id="etapeFormulaire" hidden>

                <div class="rappel-simulation" role="note">
                    <strong>Commande simulée.</strong>
                    Aucun paiement ne vous sera demandé, aucune donnée bancaire
                    n'est collectée et rien n'est envoyé à un serveur. Ce parcours
                    existe pour démontrer le fonctionnement d'un tunnel de commande.
                </div>

                <div class="commande-grille">

                    <section class="commande-form" aria-labelledby="titreForm">
                        <h2 id="titreForm">Coordonnées de livraison</h2>

                        <?php /* Resume d'erreurs : recoit le focus quand la
                                  validation echoue, et chaque message est un
                                  lien vers le champ concerne. */ ?>
                        <div class="success-msg success-msg--error error-summary"
                             id="commandeErreurs" role="alert" tabindex="-1" hidden></div>

                        <?php /* novalidate : les messages du navigateur sont
                                  remplaces par les notres, plus explicites. */ ?>
                        <form id="formCommande" class="contact-form" novalidate>

                            <p class="form-legende">Tous les champs sont obligatoires.</p>

                            <div class="form-group">
                                <label for="nom">Nom et prénom</label>
                                <input type="text" id="nom" name="nom" maxlength="120"
                                       autocomplete="name"
                                       aria-required="true" aria-describedby="err-nom">
                                <p class="error" id="err-nom" hidden></p>
                            </div>

                            <div class="form-group">
                                <label for="email">Adresse e-mail</label>
                                <input type="email" id="email" name="email" maxlength="190"
                                       autocomplete="email"
                                       aria-required="true" aria-describedby="err-email">
                                <p class="error" id="err-email" hidden></p>
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse</label>
                                <input type="text" id="adresse" name="adresse" maxlength="200"
                                       autocomplete="street-address"
                                       aria-required="true" aria-describedby="err-adresse">
                                <p class="error" id="err-adresse" hidden></p>
                            </div>

                            <div class="form-duo">
                                <div class="form-group">
                                    <label for="cp">Code postal</label>
                                    <input type="text" id="cp" name="cp"
                                           maxlength="5" inputmode="numeric"
                                           autocomplete="postal-code"
                                           aria-required="true" aria-describedby="err-cp">
                                    <p class="error" id="err-cp" hidden></p>
                                </div>

                                <div class="form-group">
                                    <label for="ville">Ville</label>
                                    <input type="text" id="ville" name="ville" maxlength="100"
                                           autocomplete="address-level2"
                                           aria-required="true" aria-describedby="err-ville">
                                    <p class="error" id="err-ville" hidden></p>
                                </div>
                            </div>

                            <button type="submit" class="submit">Valider la commande</button>

                            <p class="panier-note">
                                En validant, vous obtenez un numéro de commande
                                de démonstration. Rien n'est facturé.
                            </p>
                        </form>
                    </section>

                    <?php /* Recapitulatif AVANT validation : l'utilisateur voit
                              ce qu'il commande pendant qu'il saisit. */ ?>
                    <aside class="commande-recap" aria-labelledby="titreRecap">
                        <h2 id="titreRecap">Votre commande</h2>
                        <div id="commandeRecap"></div>
                        <p class="bloc-bouton">
                            <a href="panier.php" class="lien-discret">Modifier le panier</a>
                        </p>
                    </aside>
                </div>
            </div>

            <!-- ============ ETAT 3 : confirmation ============ -->
            <div id="etapeConfirmation" class="zone-impression" hidden>

                <div class="confirmation-entete">
                    <?php /* tabindex="-1" : le titre recoit le focus apres la
                              validation, pour amener le clavier et le lecteur
                              d'ecran sur la confirmation. */ ?>
                    <h2 id="confTitre" tabindex="-1">Commande enregistrée</h2>
                    <p class="confirmation-numero">
                        Numéro : <strong id="confNumero">—</strong>
                    </p>
                    <p class="confirmation-date">
                        Passée le <span id="confDate">—</span>
                    </p>
                </div>

                <div class="rappel-simulation rappel-simulation--print" role="note">
                    <strong>Document de démonstration.</strong>
                    Cette commande est une simulation pédagogique : elle n'a fait
                    l'objet d'aucun paiement, ne sera pas livrée, et n'a été
                    transmise à aucun serveur. Ce n'est pas une facture.
                </div>

                <div class="confirmation-corps">
                    <section aria-labelledby="titreLivraison">
                        <h3 id="titreLivraison">Livraison</h3>
                        <p>
                            <span id="confNom">—</span><br>
                            <span id="confAdresse">—</span><br>
                            <span id="confEmail">—</span>
                        </p>
                    </section>

                    <section aria-labelledby="titreDetail">
                        <h3 id="titreDetail">Détail</h3>
                        <div id="confRecap"></div>
                    </section>
                </div>

                <div class="confirmation-actions no-print">
                    <button type="button" class="cta" id="confImprimer">
                        Imprimer le récapitulatif
                    </button>
                    <a href="products.php" class="lien-discret">Retour au catalogue</a>
                </div>
            </div>

        </div>
    </main>

    <?php /* commande.js a besoin de window.Panier, expose par
              assets/js/panier.js charge dans l'en-tete. */ ?>
    <script src="<?php echo e(il_asset('assets/js/commande.js')); ?>" defer></script>

<?php il_page_end(); ?>
