<?php
/**
 * 403.php - Page « acces refuse », servie par Apache (ErrorDocument)
 *
 * S'affiche notamment quand un visiteur tente d'atteindre
 * storage/, includes/ ou vendor/, interdits en HTTP.
 * Ajoutee au TP 2ter (action 6.1 de TODO_LIST.md).
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

http_response_code(403);

il_page_start([
    'title'       => 'Accès refusé',
    'description' => 'Vous n\'avez pas l\'autorisation d\'accéder à cette ressource.',
    'current'     => '',
    'robots'      => 'noindex, nofollow',
]);
?>

    <section class="page-title">
        <h1>Accès refusé</h1>
        <p>Erreur 403 — cette ressource n'est pas consultable publiquement.</p>
    </section>

    <main id="contenu">
        <div class="section">
            <div class="page-legal">
                <h2>Pourquoi ce message ?</h2>
                <p>
                    Certains dossiers du site contiennent du code et des données
                    qui ne doivent jamais être servis à un navigateur. Toute
                    tentative d'accès direct est bloquée volontairement.
                </p>
                <p>
                    Si vous cherchez l'espace d'administration, il se trouve
                    <a href="admin/">ici</a> et demande une connexion.
                </p>
                <p><a href="index.php">Retour à l'accueil</a></p>
            </div>
        </div>
    </main>

<?php il_page_end(); ?>
