<?php
/**
 * 404.php - Page « introuvable », servie par Apache (ErrorDocument)
 *
 * Avant : une URL inexistante affichait la page d'erreur brute
 * d'Apache, qui annonce le serveur et sa version.
 * Ajoutee au TP 2ter (action 6.1 de TODO_LIST.md).
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

/* Le code HTTP doit rester 404 : sinon un moteur de recherche
   indexerait la page d'erreur comme une page normale. */
http_response_code(404);

il_page_start([
    'title'       => 'Page introuvable',
    'description' => 'Cette page n\'existe pas ou a été déplacée.',
    'current'     => '',
    'robots'      => 'noindex, follow',
]);
?>

    <section class="page-title">
        <h1>Cette page n'existe pas</h1>
        <p>Erreur 404 — le lien est peut-être incomplet ou la page a été déplacée.</p>
    </section>

    <main id="contenu">
        <div class="section">
            <div class="page-legal">
                <h2>Où aller maintenant ?</h2>
                <ul>
                    <li><a href="index.php">Revenir à l'accueil</a></li>
                    <li><a href="products.php">Parcourir le catalogue</a></li>
                    <li><a href="contact.php">Nous poser la question directement</a></li>
                </ul>
                <p>
                    Si vous êtes arrivé ici en suivant un lien de notre site,
                    signalez-le-nous : nous le corrigerons.
                </p>
            </div>
        </div>
    </main>

<?php il_page_end(); ?>
