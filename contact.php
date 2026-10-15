<?php
/**
 * contact.php - Formulaire de contact
 *
 * Le formulaire ne simule plus l'envoi : le message est validé côté
 * serveur, enregistré dans storage/data/contact.json (consultable
 * depuis Maintenance > Journaux) et, si la configuration des e-mails
 * est renseignée, notifié par e-mail.
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout-public.php';

il_session_start();

$errors  = [];
$values  = ['nom' => '', 'email' => '', 'objet' => '', 'message' => ''];

/* L'objet peut etre pre-rempli depuis une fiche produit
   (lien « Demander un devis » de products.php). */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['objet'])) {
    $values['objet'] = mb_substr(trim((string) $_GET['objet']), 0, IL_CONTACT_MAX_SUBJECT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    il_csrf_require();

    $values = [
        'nom'     => trim((string) ($_POST['nom'] ?? '')),
        'email'   => trim((string) ($_POST['email'] ?? '')),
        'objet'   => trim((string) ($_POST['objet'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];

    /* Validation côté serveur, indépendante du JavaScript */
    $errors = il_contact_validate($values);

    /* Consentement RGPD : contrôlé ici, et pas seulement par l'attribut
       HTML required, qu'un navigateur peut ignorer ou qu'on peut retirer. */
    if (!isset($_POST['consentement'])) {
        $errors['consentement'] = 'Vous devez accepter l\'enregistrement de vos '
            . 'données pour que nous puissions traiter votre demande.';
    }

    if (!$errors) {
        $stored = il_contact_save($values);

        if ($stored === null) {
            $errors['global'] = 'Votre message n\'a pas pu être enregistré. Réessayez plus tard.';
        } else {
            /* Notification facultative : son échec ne fait pas perdre le message */
            if (il_mail_is_configured()) {
                $sent = il_mail_send_contact_notification($stored);
                if (!$sent['ok']) {
                    il_flash('warning', 'Votre message est bien enregistré, mais la '
                        . 'notification interne n\'a pas pu être envoyée.');
                }
            }

            /* Motif « POST puis redirection » : sans cette redirection, un
               simple F5 après l'envoi renvoyait le formulaire et enregistrait
               le message une seconde fois. */
            il_flash('success', 'Merci pour votre message : il a bien été enregistré. '
                . 'Notre équipe vous répondra au plus vite.');
            il_redirect('contact.php');
        }
    }
}

/* Messages déposés avant la redirection */
$flashes = il_take_flash();

il_page_start([
    'title'       => 'Contact',
    'description' => 'Contactez InfoLeader : conseil, devis et accompagnement sur votre équipement informatique.',
    'current'     => 'contact',
]);
?>

    <section class="page-title">
        <h1>Contactez-nous</h1>
        <p>
            Une question sur nos produits, un devis, un conseil avant achat ?
            Notre équipe vous répond rapidement.
        </p>
    </section>

    <main id="contenu">
        <div class="section section--haut">

            <?php /* Messages uniformises : une seule classe, un seul style,
                      des modificateurs pour le succes, l'alerte et l'erreur. */ ?>
            <?php foreach ($flashes as $flash): ?>
                <div class="success-msg <?php echo $flash['type'] === 'success' ? '' : 'success-msg--warn'; ?>"
                     role="status">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endforeach; ?>

            <?php if ($errors): ?>
                <?php /* tabindex="-1" : le bloc recoit le focus au chargement,
                          ce qui amene directement le clavier et le lecteur
                          d'ecran sur la liste des erreurs. */ ?>
                <div class="success-msg success-msg--error error-summary"
                     id="resume-erreurs" role="alert" tabindex="-1">
                    <strong>Votre message n'a pas été envoyé :</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <section class="contact-grid">

                <div class="info reveal">
                    <h2>Voici comment nous joindre</h2>
                    <p>
                        Par téléphone, par e-mail ou directement via le formulaire :
                        choisissez ce qui vous arrange.
                    </p>

                    <div class="info-item">
                        <div class="icon" aria-hidden="true">&#128205;</div>
                        <p>12 rue de l'Informatique, 75000 Paris</p>
                    </div>
                    <div class="info-item">
                        <div class="icon" aria-hidden="true">&#128222;</div>
                        <p><a href="tel:+33123456789">01 23 45 67 89</a></p>
                    </div>
                    <div class="info-item">
                        <div class="icon" aria-hidden="true">&#9993;</div>
                        <p>
                            <a href="mailto:contact@infoleader.local">
                                contact@infoleader.local
                            </a>
                        </p>
                    </div>
                    <div class="info-item">
                        <div class="icon" aria-hidden="true">&#128336;</div>
                        <p>Du lundi au vendredi, 9h00 – 18h00</p>
                    </div>
                </div>

                <form id="contactForm" class="contact-form reveal" data-delay="2"
                      action="contact.php" method="post" novalidate>
                    <?php /* Jeton anti-CSRF : le formulaire est traité par le serveur */ ?>
                    <?php echo il_csrf_field(); ?>

                    <p class="form-legende">
                        Les champs marqués d'un <span class="champ-requis" aria-hidden="true">*</span>
                        sont obligatoires.
                    </p>

                    <div class="form-group">
                        <label for="nom">
                            Nom <span class="champ-requis" aria-hidden="true">*</span>
                        </label>
                        <input type="text" id="nom" name="nom" maxlength="120"
                               value="<?php echo e($values['nom']); ?>"
                               aria-required="true" aria-describedby="err-nom"
                               <?php echo isset($errors['nom']) ? 'aria-invalid="true"' : ''; ?>>
                        <p class="error" id="err-nom" data-error="nom">Le nom est requis.</p>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            Adresse e-mail <span class="champ-requis" aria-hidden="true">*</span>
                        </label>
                        <input type="email" id="email" name="email" maxlength="190"
                               value="<?php echo e($values['email']); ?>"
                               aria-required="true" aria-describedby="err-email"
                               <?php echo isset($errors['email']) ? 'aria-invalid="true"' : ''; ?>>
                        <p class="error" id="err-email" data-error="email">
                            Veuillez saisir une adresse e-mail valide.
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="objet">
                            Objet
                            <span class="champ-facultatif">(facultatif)</span>
                        </label>
                        <input type="text" id="objet" name="objet" maxlength="150"
                               value="<?php echo e($values['objet']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="message">
                            Message <span class="champ-requis" aria-hidden="true">*</span>
                        </label>
                        <textarea id="message" name="message" maxlength="5000"
                                  aria-required="true" aria-describedby="err-message"
                                  <?php echo isset($errors['message']) ? 'aria-invalid="true"' : ''; ?>><?php echo e($values['message']); ?></textarea>
                        <p class="error" id="err-message" data-error="message">Le message est requis.</p>
                    </div>

                    <?php /* Consentement RGPD : non coché par défaut, et vérifié
                              côté serveur (voir le traitement en haut du fichier). */ ?>
                    <div class="form-group form-consentement">
                        <input type="checkbox" id="consentement" name="consentement" value="1"
                               aria-required="true"
                               <?php echo isset($errors['consentement']) ? 'aria-invalid="true"' : ''; ?>>
                        <label for="consentement">
                            J'accepte que mes données soient enregistrées afin de
                            traiter ma demande. <span class="champ-requis" aria-hidden="true">*</span>
                            <a href="confidentialite.php">Politique de confidentialité</a>
                        </label>
                    </div>

                    <button type="submit" class="submit">Envoyer le message</button>
                </form>

            </section>
        </div>
    </main>

<?php il_page_end(); ?>
