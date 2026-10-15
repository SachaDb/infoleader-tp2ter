<?php
/**
 * mentions-legales.php - Mentions legales
 *
 * Page statique : aucune donnee n'est lue ni ecrite.
 * Ajoutee au TP 2ter (action 2.1 de TODO_LIST.md).
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

il_page_start([
    'title'       => 'Mentions légales',
    'description' => 'Mentions légales du site InfoLeader : éditeur, responsable de publication, hébergement et propriété intellectuelle.',
    'current'     => '',
]);
?>

    <section class="page-title">
        <h1>Mentions légales</h1>
        <p>Informations sur l'éditeur de ce site et les conditions de son utilisation.</p>
    </section>

    <main id="contenu">
        <div class="section">
            <div class="page-legal">

                <h2>Nature du site</h2>
                <p>
                    Ce site est un <strong>projet pédagogique</strong> réalisé dans le
                    cadre d'un BTS SIO (Services Informatiques aux Organisations).
                    La société « InfoLeader » est <strong>fictive</strong> : elle
                    n'existe pas, ne vend rien et n'encaisse aucun paiement.
                    Les produits présentés, les prix, l'adresse et le numéro de
                    téléphone sont des données d'exemple.
                </p>

                <h2>Éditeur du site</h2>
                <p>
                    Étudiant en BTS SIO, première année.
                    <br>
                    <em>Responsable de la publication : à compléter avec votre nom
                    et votre adresse e-mail de contact.</em>
                </p>

                <h2>Hébergement</h2>
                <p>
                    Le site <strong>n'est pas hébergé en ligne</strong>. Il fonctionne
                    localement sur un poste de travail, au moyen de WAMP
                    (serveur Apache et PHP installés sur l'ordinateur de l'étudiant).
                    Il n'est donc accessible depuis aucune adresse publique, et
                    aucun hébergeur tiers n'intervient.
                </p>

                <h2>Propriété intellectuelle</h2>
                <p>
                    Le code source de ce site a été écrit dans un cadre scolaire.
                    Les visuels des produits sont des images d'illustration utilisées
                    à des fins de démonstration. La seule bibliothèque externe employée
                    est PHPMailer, distribuée sous licence LGPL.
                </p>

                <h2>Données personnelles</h2>
                <p>
                    Le formulaire de contact enregistre des données personnelles.
                    Leur traitement est décrit en détail dans la
                    <a href="confidentialite.php">politique de confidentialité</a>.
                </p>

                <h2>Cookies</h2>
                <p>
                    Ce site <strong>n'utilise aucun cookie publicitaire ni aucune
                    mesure d'audience</strong>. Un seul cookie technique existe,
                    <code>INFOLEADERSESSID</code>, et il n'est créé que pour faire
                    fonctionner le formulaire de contact (jeton anti-CSRF) et la
                    connexion à l'espace d'administration. Le choix de thème
                    clair ou sombre est conservé dans le stockage local du
                    navigateur, et n'est jamais transmis au serveur.
                </p>

                <p class="maj">Dernière mise à jour : <?php echo date('d/m/Y'); ?>.</p>

            </div>
        </div>
    </main>

<?php il_page_end(); ?>
