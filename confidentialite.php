<?php
/**
 * confidentialite.php - Politique de confidentialite
 *
 * Page statique : aucune donnee n'est lue ni ecrite.
 * Le contenu decrit le traitement REEL effectue par le code
 * (voir il_contact_save() dans includes/store.php).
 * Ajoutee au TP 2ter (action 2.2 de TODO_LIST.md).
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

il_page_start([
    'title'       => 'Politique de confidentialité',
    'description' => 'Quelles données le site InfoLeader collecte, pourquoi, combien de temps il les conserve, et comment en demander la suppression.',
    'current'     => '',
]);
?>

    <section class="page-title">
        <h1>Politique de confidentialité</h1>
        <p>Ce que nous enregistrons, pourquoi, et comment vous gardez la main.</p>
    </section>

    <main id="contenu">
        <div class="section">
            <div class="page-legal">

                <h2>En résumé</h2>
                <p>
                    Ce site n'enregistre des données que lorsque vous envoyez
                    volontairement le formulaire de contact. Il n'y a
                    <strong>aucun traceur publicitaire, aucune mesure d'audience
                    et aucun transfert à un service tiers</strong>. Les données
                    restent dans un fichier sur l'ordinateur qui héberge le site.
                </p>

                <h2>Données collectées par le formulaire de contact</h2>
                <div class="table-scroll">
                    <table>
                        <caption class="sr-only">Données enregistrées par le formulaire de contact</caption>
                        <thead>
                            <tr>
                                <th scope="col">Donnée</th>
                                <th scope="col">Obligatoire</th>
                                <th scope="col">Pourquoi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nom</td>
                                <td>Oui</td>
                                <td>Vous identifier pour vous répondre</td>
                            </tr>
                            <tr>
                                <td>Adresse e-mail</td>
                                <td>Oui</td>
                                <td>Vous adresser notre réponse</td>
                            </tr>
                            <tr>
                                <td>Objet</td>
                                <td>Non</td>
                                <td>Orienter la demande vers la bonne personne</td>
                            </tr>
                            <tr>
                                <td>Message</td>
                                <td>Oui</td>
                                <td>Comprendre votre besoin</td>
                            </tr>
                            <tr>
                                <td>Adresse IP, tronquée</td>
                                <td>Automatique</td>
                                <td>
                                    Limiter les envois automatisés. L'adresse est
                                    tronquée avant enregistrement : la fin est
                                    remplacée par des zéros, de sorte que votre
                                    poste n'est pas identifiable individuellement.
                                    Seule une origine approximative est conservée.
                                </td>
                            </tr>
                            <tr>
                                <td>Date et heure de l'envoi</td>
                                <td>Automatique</td>
                                <td>Traiter les demandes dans l'ordre d'arrivée</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>Base légale</h2>
                <p>
                    Votre <strong>consentement</strong>. La case à cocher du
                    formulaire n'est pas pré-cochée, et le serveur refuse l'envoi
                    si elle n'a pas été validée. Vous pouvez donc consulter
                    l'intégralité du site sans transmettre aucune donnée.
                </p>

                <h2>Qui a accès aux données</h2>
                <p>
                    Uniquement l'administrateur du site, après connexion par mot
                    de passe à l'espace d'administration. Les messages sont
                    stockés dans un fichier placé hors de la portée du serveur
                    web : une requête directe depuis un navigateur reçoit une
                    erreur 403.
                </p>
                <p>
                    Si l'envoi d'e-mails est configuré, une notification contenant
                    votre message est adressée à l'administrateur. Elle transite
                    alors par le fournisseur de messagerie choisi (par exemple
                    Gmail), qui applique sa propre politique de confidentialité.
                </p>

                <h2>Durée de conservation</h2>
                <p>
                    Les messages sont conservés jusqu'à leur suppression par
                    l'administrateur. Le site n'en garde pas plus de
                    <strong>1 000</strong> : au-delà, les plus anciens sont
                    automatiquement effacés.
                </p>
                <p>
                    <em>Un effacement automatique après une durée fixe est prévu
                    mais pas encore en place : il est consigné comme action 2.4
                    dans le plan d'améliorations du projet.</em>
                </p>

                <h2>Vos droits</h2>
                <ul>
                    <li><strong>Accès</strong> : savoir ce que nous avons enregistré sur vous.</li>
                    <li><strong>Rectification</strong> : corriger une donnée inexacte.</li>
                    <li><strong>Effacement</strong> : demander la suppression de votre message.</li>
                    <li><strong>Retrait du consentement</strong> : à tout moment, sans justification.</li>
                </ul>
                <p>
                    Pour exercer ces droits, écrivez à l'adresse indiquée dans les
                    <a href="mentions-legales.php">mentions légales</a>. Comme le
                    site est un projet pédagogique non publié, la demande est
                    traitée manuellement.
                </p>

                <h2>Cookies et stockage du navigateur</h2>
                <ul>
                    <li>
                        <code>INFOLEADERSESSID</code> : cookie technique, nécessaire
                        au jeton anti-CSRF du formulaire et à la connexion
                        administrateur. Supprimé à la fermeture du navigateur.
                    </li>
                    <li>
                        <code>il-theme</code> : votre préférence de thème clair ou
                        sombre, conservée dans le stockage local du navigateur.
                        Elle ne quitte jamais votre appareil.
                    </li>
                </ul>
                <p>
                    Aucun cookie publicitaire, aucun cookie de mesure d'audience,
                    aucun cookie tiers.
                </p>

                <p class="maj">Dernière mise à jour : <?php echo date('d/m/Y'); ?>.</p>

            </div>
        </div>
    </main>

<?php il_page_end(); ?>
