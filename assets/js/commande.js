/* ============================================================
   commande.js - Tunnel de commande SIMULE
   ------------------------------------------------------------
   TP 2quater.

   SIMULATION UNIQUEMENT. Aucun paiement reel :
   - aucune passerelle bancaire,
   - aucun champ de carte bancaire,
   - aucun debit,
   - aucune transmission a un service tiers.
   La commande n'est pas envoyee a un serveur : elle reste dans
   le navigateur, comme le panier.

   Parcours : formulaire valide -> ecran de confirmation portant
   un numero de commande, puis vidage du panier.

   Depend de assets/js/panier.js (objet window.Panier), charge
   avant ce fichier.
   ============================================================ */
(function (window, document) {
    'use strict';

    /* Sans le panier, cette page n'a rien a faire */
    if (!window.Panier) {
        return;
    }

    var Panier = window.Panier;

    /** Derniere commande validee, pour survivre a un rechargement */
    var CLE_COMMANDE = 'il-derniere-commande';

    /** Compteur du numero de commande */
    var CLE_SEQUENCE = 'il-commande-sequence';

    /* ==========================================================
       1. NUMERO DE COMMANDE
       ==========================================================
       Format : CMD-<annee>-<4 chiffres>, par exemple CMD-2026-0001.

       Le compteur est stocke dans le navigateur. Sur un vrai site
       il serait tenu par le serveur : ici, deux visiteurs
       differents obtiendraient chacun leur propre numerotation.
       C'est assume, le TP demandant une simulation sans serveur.
       ========================================================== */

    function numeroSuivant() {
        var sequence;
        try {
            sequence = parseInt(window.localStorage.getItem(CLE_SEQUENCE), 10);
        } catch (e) {
            sequence = 0;
        }
        if (!isFinite(sequence) || sequence < 0) {
            sequence = 0;
        }

        sequence += 1;

        try {
            window.localStorage.setItem(CLE_SEQUENCE, String(sequence));
        } catch (e) {
            /* Stockage indisponible : le numero reste valable pour
               l'affichage courant, il repartira simplement a 1. */
        }

        var rang = String(sequence);
        while (rang.length < 4) {
            rang = '0' + rang;
        }
        return 'CMD-' + new Date().getFullYear() + '-' + rang;
    }

    /* ==========================================================
       2. VALIDATION DU FORMULAIRE
       ==========================================================
       Chaque regle renvoie un message d'erreur, ou une chaine
       vide si le champ est correct.
       ========================================================== */

    var ORDRE_CHAMPS = ['nom', 'email', 'adresse', 'cp', 'ville'];

    var regles = {
        nom: function (valeur) {
            if (valeur === '') {
                return 'Votre nom est requis.';
            }
            if (valeur.length < 2) {
                return 'Le nom doit contenir au moins 2 caractères.';
            }
            if (valeur.length > 120) {
                return 'Le nom ne doit pas dépasser 120 caractères.';
            }
            return '';
        },

        email: function (valeur) {
            if (valeur === '') {
                return 'L’adresse e-mail est requise.';
            }
            /* Controle volontairement simple : du texte, un @, un
               domaine avec un point. Inutile d'etre plus strict :
               seul un envoi reel prouve qu'une adresse existe. */
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valeur)) {
                return 'Cette adresse e-mail n’est pas valide (exemple : nom@domaine.fr).';
            }
            if (valeur.length > 190) {
                return 'L’adresse e-mail est trop longue.';
            }
            return '';
        },

        adresse: function (valeur) {
            if (valeur === '') {
                return 'L’adresse de livraison est requise.';
            }
            if (valeur.length < 5) {
                return 'L’adresse semble incomplète.';
            }
            if (valeur.length > 200) {
                return 'L’adresse ne doit pas dépasser 200 caractères.';
            }
            return '';
        },

        cp: function (valeur) {
            if (valeur === '') {
                return 'Le code postal est requis.';
            }
            if (!/^[0-9]{5}$/.test(valeur)) {
                return 'Le code postal doit comporter exactement 5 chiffres.';
            }
            return '';
        },

        ville: function (valeur) {
            if (valeur === '') {
                return 'La ville est requise.';
            }
            if (valeur.length < 2) {
                return 'Le nom de la ville est trop court.';
            }
            if (valeur.length > 100) {
                return 'Le nom de la ville ne doit pas dépasser 100 caractères.';
            }
            return '';
        }
    };

    /** Affiche ou efface le message d'erreur d'un champ */
    function marquerChamp(champ, message) {
        var zone = document.getElementById('err-' + champ.id);

        if (message === '') {
            champ.removeAttribute('aria-invalid');
            champ.classList.remove('champ-invalide');
            if (zone) {
                zone.textContent = '';
                zone.hidden = true;
            }
            return;
        }

        champ.setAttribute('aria-invalid', 'true');
        champ.classList.add('champ-invalide');
        if (zone) {
            zone.textContent = message;
            zone.hidden = false;
        }
    }

    /**
     * Valide tout le formulaire.
     * Retourne { ok, valeurs, erreurs }, erreurs etant une liste
     * de { champ, message } dans l'ordre d'affichage des champs.
     */
    function validerFormulaire(formulaire) {
        var valeurs = {};
        var erreurs = [];

        ORDRE_CHAMPS.forEach(function (nomChamp) {
            var champ = formulaire.elements[nomChamp];
            if (!champ) {
                return;
            }

            var valeur = String(champ.value).trim();
            valeurs[nomChamp] = valeur;

            var message = regles[nomChamp](valeur);
            marquerChamp(champ, message);

            if (message !== '') {
                erreurs.push({ champ: champ, message: message });
            }
        });

        return { ok: erreurs.length === 0, valeurs: valeurs, erreurs: erreurs };
    }

    /** Remplit le resume d'erreurs et lui donne le focus */
    function afficherResumeErreurs(erreurs) {
        var resume = document.getElementById('commandeErreurs');
        if (!resume) {
            return;
        }

        resume.textContent = '';

        if (erreurs.length === 0) {
            resume.hidden = true;
            return;
        }

        var titre = document.createElement('strong');
        titre.textContent = erreurs.length === 1
            ? 'Un champ doit être corrigé :'
            : erreurs.length + ' champs doivent être corrigés :';
        resume.appendChild(titre);

        var liste = document.createElement('ul');
        erreurs.forEach(function (erreur) {
            var item = document.createElement('li');
            /* Lien vers le champ fautif : un clic y amene le focus */
            var lien = document.createElement('a');
            lien.href = '#' + erreur.champ.id;
            lien.textContent = erreur.message;
            lien.addEventListener('click', function (evenement) {
                evenement.preventDefault();
                erreur.champ.focus();
            });
            item.appendChild(lien);
            liste.appendChild(item);
        });
        resume.appendChild(liste);

        resume.hidden = false;
        resume.focus();
    }

    /* ==========================================================
       3. RECAPITULATIF (avant validation et sur la confirmation)
       ========================================================== */

    /** Construit un recapitulatif de lignes et de totaux */
    function rendreRecap(conteneur, lignes, t) {
        if (!conteneur) {
            return;
        }
        conteneur.textContent = '';

        var liste = document.createElement('ul');
        liste.className = 'recap-lignes';

        lignes.forEach(function (ligne) {
            var item = document.createElement('li');

            var designation = document.createElement('span');
            designation.className = 'recap-nom';
            designation.textContent = ligne.nom;
            item.appendChild(designation);

            var quantite = document.createElement('span');
            quantite.className = 'recap-qte';
            quantite.textContent = '× ' + ligne.qte;
            item.appendChild(quantite);

            var montant = document.createElement('span');
            montant.className = 'recap-montant';
            montant.textContent = Panier.formaterPrix(Panier.sousTotal(ligne));
            item.appendChild(montant);

            liste.appendChild(item);
        });

        conteneur.appendChild(liste);

        var totaux = document.createElement('dl');
        totaux.className = 'recap-totaux';
        ajouterTotal(totaux, 'Total HT', Panier.formaterPrix(t.ht), false);
        ajouterTotal(totaux, 'TVA 20 %', Panier.formaterPrix(t.tva), false);
        ajouterTotal(totaux, 'Total TTC', Panier.formaterPrix(t.ttc), true);
        conteneur.appendChild(totaux);
    }

    function ajouterTotal(dl, libelle, valeur, fort) {
        var dt = document.createElement('dt');
        dt.textContent = libelle;
        if (fort) {
            dt.className = 'recap-final';
        }

        var dd = document.createElement('dd');
        if (fort) {
            dd.className = 'recap-final';
            var strong = document.createElement('strong');
            strong.textContent = valeur;
            dd.appendChild(strong);
        } else {
            dd.textContent = valeur;
        }

        dl.appendChild(dt);
        dl.appendChild(dd);
    }

    /* ==========================================================
       4. ECRAN DE CONFIRMATION
       ========================================================== */

    /** Enregistre la commande validee dans le navigateur */
    function memoriserCommande(commande) {
        try {
            window.localStorage.setItem(CLE_COMMANDE, JSON.stringify(commande));
        } catch (e) {
            /* Sans stockage, la confirmation reste affichee mais ne
               survivra pas a un rechargement de la page. */
        }
    }

    /** Relit la derniere commande validee, ou null */
    function lireCommande() {
        var brut;
        try {
            brut = window.localStorage.getItem(CLE_COMMANDE);
        } catch (e) {
            return null;
        }
        if (!brut) {
            return null;
        }

        var commande;
        try {
            commande = JSON.parse(brut);
        } catch (e) {
            return null;
        }

        if (!commande || typeof commande !== 'object'
            || !commande.numero || !Array.isArray(commande.lignes)) {
            return null;
        }
        return commande;
    }

    /** Affiche l'ecran de confirmation a partir d'une commande */
    function afficherConfirmation(commande) {
        var etapeForm    = document.getElementById('etapeFormulaire');
        var etapeConfirm = document.getElementById('etapeConfirmation');
        var etapeVide    = document.getElementById('etapeVide');

        if (etapeForm)   { etapeForm.hidden = true; }
        if (etapeVide)   { etapeVide.hidden = true; }
        if (!etapeConfirm) {
            return;
        }
        etapeConfirm.hidden = false;

        ecrireDans('confNumero', commande.numero);
        ecrireDans('confDate', commande.date);
        ecrireDans('confNom', commande.client.nom);
        ecrireDans('confEmail', commande.client.email);
        ecrireDans('confAdresse',
            commande.client.adresse + ', ' + commande.client.cp + ' ' + commande.client.ville);

        rendreRecap(
            document.getElementById('confRecap'),
            commande.lignes,
            commande.totaux
        );

        /* Le titre recoit le focus : l'utilisateur au clavier et le
           lecteur d'ecran arrivent sur la confirmation, pas en haut
           de page. */
        var titre = document.getElementById('confTitre');
        if (titre) {
            titre.focus();
        }
    }

    function ecrireDans(id, texte) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = texte;
        }
    }

    /* ==========================================================
       5. VALIDATION DE LA COMMANDE
       ========================================================== */

    function validerCommande(formulaire) {
        var lignes = Panier.lire();
        if (lignes.length === 0) {
            /* Le panier a pu etre vide dans un autre onglet */
            afficherEtapeVide();
            return;
        }

        var resultat = validerFormulaire(formulaire);
        afficherResumeErreurs(resultat.erreurs);

        if (!resultat.ok) {
            return;
        }

        var maintenant = new Date();
        var commande = {
            numero: numeroSuivant(),
            date: maintenant.toLocaleString('fr-FR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }),
            client: resultat.valeurs,
            lignes: lignes,
            totaux: Panier.totaux(lignes),
            simulation: true
        };

        memoriserCommande(commande);

        /* Le panier est vide APRES avoir pris l'instantane :
           la confirmation reste donc complete et imprimable. */
        Panier.vider();

        /* L'ancre permet de retrouver la confirmation apres un F5 */
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', 'commande.php#confirmation');
        } else {
            window.location.hash = 'confirmation';
        }

        afficherConfirmation(commande);
    }

    /* ==========================================================
       6. ETATS DE LA PAGE
       ========================================================== */

    function afficherEtapeVide() {
        basculer('etapeVide');
    }

    function afficherEtapeFormulaire() {
        basculer('etapeFormulaire');

        var lignes = Panier.lire();
        rendreRecap(
            document.getElementById('commandeRecap'),
            lignes,
            Panier.totaux(lignes)
        );
    }

    function basculer(idVisible) {
        ['etapeVide', 'etapeFormulaire', 'etapeConfirmation'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.hidden = id !== idVisible;
            }
        });
    }

    /* ==========================================================
       7. IMPRESSION
       ========================================================== */

    function brancherImpression() {
        var bouton = document.getElementById('confImprimer');
        if (!bouton) {
            return;
        }
        bouton.addEventListener('click', function () {
            /* La mise en page d'impression est entierement geree par
               le bloc @media print de style.css : menus, en-tete,
               pied de page et boutons y sont masques. */
            window.print();
        });
    }

    /* ==========================================================
       8. INITIALISATION
       ========================================================== */

    function init() {
        var formulaire = document.getElementById('formCommande');
        if (!formulaire) {
            return;
        }

        brancherImpression();

        /* Validation a la sortie d'un champ : l'utilisateur est
           averti au fil de la saisie, sans attendre l'envoi. */
        ORDRE_CHAMPS.forEach(function (nomChamp) {
            var champ = formulaire.elements[nomChamp];
            if (!champ) {
                return;
            }
            champ.addEventListener('blur', function () {
                marquerChamp(champ, regles[nomChamp](String(champ.value).trim()));
            });
        });

        formulaire.addEventListener('submit', function (evenement) {
            /* Pas d'envoi au serveur : tout se passe ici */
            evenement.preventDefault();
            validerCommande(formulaire);
        });

        /* Quel ecran afficher a l'ouverture de la page ? */
        var commande = lireCommande();

        if (window.location.hash === '#confirmation' && commande) {
            /* Retour ou rechargement sur une commande validee */
            afficherConfirmation(commande);
        } else if (Panier.lire().length === 0) {
            afficherEtapeVide();
        } else {
            afficherEtapeFormulaire();
        }

        /* Le panier peut changer dans un autre onglet pendant que
           le formulaire est ouvert. */
        document.addEventListener('panier:maj', function () {
            var confirmation = document.getElementById('etapeConfirmation');
            if (confirmation && !confirmation.hidden) {
                return;   /* on ne touche pas a une confirmation affichee */
            }
            if (Panier.lire().length === 0) {
                afficherEtapeVide();
            } else {
                afficherEtapeFormulaire();
            }
        });

        window.addEventListener('storage', function (evenement) {
            if (evenement.key === Panier.CLE) {
                document.dispatchEvent(new CustomEvent('panier:maj'));
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})(window, document);
