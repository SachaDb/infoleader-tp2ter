/* ============================================================
   panier.js - Panier 100 % cote navigateur
   ------------------------------------------------------------
   TP 2quater. Aucune base de donnees, aucun stockage serveur :
   le panier vit dans le localStorage du navigateur.

   JavaScript natif, aucune bibliotheque.

   Ce fichier est charge sur TOUTES les pages publiques, car le
   badge de l'en-tete doit etre a jour partout. Les parties qui
   concernent une page precise (boutons d'ajout, lignes du
   panier) ne s'activent que si les elements existent.

   Il expose window.Panier, reutilise par commande.js.
   ============================================================ */
(function (window, document) {
    'use strict';

    /* ==========================================================
       1. REGLAGES
       ========================================================== */

    /** Cle de stockage dans le localStorage */
    var CLE_PANIER = 'il-panier';

    /** Taux de TVA applique au recapitulatif */
    var TAUX_TVA = 0.20;

    /** Quantite maximale par article */
    var QTE_MAX = 99;

    /**
     * Les prix du catalogue (products.json) sont des prix de vente
     * au public, donc TTC. On en deduit le HT et la TVA, et non
     * l'inverse : c'est la convention d'un site grand public, et
     * cela garantit que le prix affiche sur une fiche produit est
     * exactement celui porte au recapitulatif.
     */

    /* Formatage monetaire francais, fourni par le navigateur */
    var devise = new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    });

    /** Formate un montant exprime en centimes */
    function formaterPrix(centimes) {
        return devise.format(centimes / 100);
    }

    /* ==========================================================
       2. LECTURE ET VALIDATION DU PANIER STOCKE
       ==========================================================
       Le contenu du localStorage peut etre absent, tronque,
       modifie a la main, ou provenir d'une version precedente du
       site. Chaque ligne est donc revalidee a la lecture, et une
       ligne invalide est ecartee plutot que de casser la page.
       ========================================================== */

    /**
     * Valide une ligne de panier et en renvoie une version propre,
     * ou null si elle est inutilisable.
     */
    function nettoyerLigne(brut) {
        if (!brut || typeof brut !== 'object') {
            return null;
        }

        var id  = String(brut.id === undefined || brut.id === null ? '' : brut.id).trim();
        var nom = String(brut.nom === undefined || brut.nom === null ? '' : brut.nom).trim();

        if (id === '' || nom === '') {
            return null;
        }

        var prix = Number(String(brut.prix).replace(',', '.'));
        if (!isFinite(prix) || prix < 0 || prix > 1000000) {
            return null;
        }
        prix = Math.round(prix * 100) / 100;

        var qte = parseInt(brut.qte, 10);
        if (!isFinite(qte) || qte < 1) {
            qte = 1;
        }
        if (qte > QTE_MAX) {
            qte = QTE_MAX;
        }

        return {
            id:    id.slice(0, 64),
            nom:   nom.slice(0, 150),
            prix:  prix,
            qte:   qte,
            image: typeof brut.image === 'string' ? brut.image.slice(0, 255) : ''
        };
    }

    /** Contenu du panier, toujours un tableau de lignes valides */
    function lire() {
        var brut;
        try {
            brut = window.localStorage.getItem(CLE_PANIER);
        } catch (e) {
            /* Navigation privee ou stockage bloque */
            return [];
        }

        if (!brut) {
            return [];
        }

        var donnees;
        try {
            donnees = JSON.parse(brut);
        } catch (e) {
            /* Contenu corrompu : on repart d'un panier vide */
            return [];
        }

        if (!Array.isArray(donnees)) {
            return [];
        }

        var lignes = [];
        donnees.forEach(function (brute) {
            var ligne = nettoyerLigne(brute);
            if (!ligne) {
                return;
            }
            /* Deux lignes de meme identifiant sont fusionnees */
            var existante = chercher(lignes, ligne.id);
            if (existante) {
                existante.qte = Math.min(existante.qte + ligne.qte, QTE_MAX);
            } else {
                lignes.push(ligne);
            }
        });

        return lignes;
    }

    /** Cherche une ligne par identifiant */
    function chercher(lignes, id) {
        for (var i = 0; i < lignes.length; i++) {
            if (lignes[i].id === id) {
                return lignes[i];
            }
        }
        return null;
    }

    /* ==========================================================
       3. ECRITURE ET DIFFUSION DES CHANGEMENTS
       ========================================================== */

    /**
     * Enregistre le panier, puis previent toute la page.
     * L'evenement permet au badge et a la page panier de se mettre
     * a jour sans se connaitre l'un l'autre.
     */
    function ecrire(lignes) {
        try {
            window.localStorage.setItem(CLE_PANIER, JSON.stringify(lignes));
        } catch (e) {
            /* Stockage plein ou refuse : le panier reste utilisable
               pour la page courante mais ne survivra pas au
               rechargement. On ne bloque pas l'utilisateur. */
        }
        diffuser();
    }

    /** Signale un changement de panier a la page */
    function diffuser() {
        var evenement;
        try {
            evenement = new CustomEvent('panier:maj');
        } catch (e) {
            /* Navigateurs anciens */
            evenement = document.createEvent('Event');
            evenement.initEvent('panier:maj', true, true);
        }
        document.dispatchEvent(evenement);
    }

    /* ==========================================================
       4. OPERATIONS SUR LE PANIER
       ========================================================== */

    /**
     * Ajoute un article. Si l'identifiant est deja present, la
     * quantite augmente au lieu de creer une seconde ligne.
     * Retourne la quantite obtenue, ou 0 si l'article est invalide.
     */
    function ajouter(article) {
        var ligne = nettoyerLigne(article);
        if (!ligne) {
            return 0;
        }

        var lignes = lire();
        var existante = chercher(lignes, ligne.id);
        var quantiteObtenue;

        if (existante) {
            existante.qte = Math.min(existante.qte + ligne.qte, QTE_MAX);
            /* Le nom et le prix du catalogue font foi */
            existante.nom   = ligne.nom;
            existante.prix  = ligne.prix;
            existante.image = ligne.image || existante.image;
            quantiteObtenue = existante.qte;
        } else {
            lignes.push(ligne);
            quantiteObtenue = ligne.qte;
        }

        ecrire(lignes);
        return quantiteObtenue;
    }

    /** Fixe la quantite d'une ligne. Une quantite nulle la supprime. */
    function definirQuantite(id, qte) {
        var lignes = lire();
        var ligne = chercher(lignes, String(id));
        if (!ligne) {
            return false;
        }

        qte = parseInt(qte, 10);
        if (!isFinite(qte) || qte < 1) {
            return supprimer(id);
        }

        ligne.qte = Math.min(qte, QTE_MAX);
        ecrire(lignes);
        return true;
    }

    /** Supprime une ligne */
    function supprimer(id) {
        var lignes = lire();
        var restantes = lignes.filter(function (ligne) {
            return ligne.id !== String(id);
        });

        if (restantes.length === lignes.length) {
            return false;
        }
        ecrire(restantes);
        return true;
    }

    /** Vide entierement le panier */
    function vider() {
        ecrire([]);
    }

    /* ==========================================================
       5. TOTAUX
       ==========================================================
       Tous les calculs se font en CENTIMES, avec des entiers :
       additionner des nombres a virgule flottante ferait deriver
       le total de quelques centimes sur un gros panier.
       ========================================================== */

    /**
     * Totaux du panier.
     * Retourne des montants en centimes, plus le nombre d'articles.
     */
    function totaux(lignes) {
        lignes = lignes || lire();

        var ttc = 0;
        var articles = 0;

        lignes.forEach(function (ligne) {
            ttc += Math.round(ligne.prix * 100) * ligne.qte;
            articles += ligne.qte;
        });

        /* Prix catalogue = TTC, donc le HT s'en deduit */
        var ht = Math.round(ttc / (1 + TAUX_TVA));

        return {
            articles: articles,
            lignes:   lignes.length,
            ttc:      ttc,
            ht:       ht,
            tva:      ttc - ht
        };
    }

    /** Sous-total d'une ligne, en centimes */
    function sousTotal(ligne) {
        return Math.round(ligne.prix * 100) * ligne.qte;
    }

    /* ==========================================================
       6. BADGE DE L'EN-TETE
       ========================================================== */

    function majBadge() {
        var badge   = document.getElementById('panierBadge');
        var annonce = document.getElementById('panierAnnonce');
        if (!badge) {
            return;
        }

        var nombre = totaux().articles;

        badge.textContent = String(nombre);
        /* Badge masque quand le panier est vide */
        badge.hidden = nombre === 0;

        if (annonce) {
            /* Region aria-live : le changement est annonce au
               lecteur d'ecran sans deplacer le focus. */
            annonce.textContent = nombre === 0
                ? 'Panier vide'
                : 'Panier : ' + nombre + ' article' + (nombre > 1 ? 's' : '');
        }
    }

    /* ==========================================================
       7. BOUTONS « AJOUTER AU PANIER »
       ========================================================== */

    /**
     * Un seul ecouteur pose sur le document traite tous les
     * boutons : cela fonctionne aussi pour un bouton ajoute plus
     * tard dans la page (delegation d'evenement).
     */
    function brancherBoutonsAjouter() {
        document.addEventListener('click', function (evenement) {
            var cible = evenement.target;
            var bouton = cible && cible.closest ? cible.closest('.btn-ajouter') : null;

            if (!bouton) {
                return;
            }

            evenement.preventDefault();

            var quantite = ajouter({
                id:    bouton.getAttribute('data-id'),
                nom:   bouton.getAttribute('data-nom'),
                prix:  bouton.getAttribute('data-prix'),
                image: bouton.getAttribute('data-image'),
                qte:   1
            });

            if (quantite === 0) {
                retourBouton(bouton, 'Produit indisponible', false);
                return;
            }

            retourBouton(bouton, 'Ajouté' + (quantite > 1 ? ' (' + quantite + ')' : ''), true);
        });
    }

    /**
     * Confirmation visuelle sur le bouton lui-meme, puis retour au
     * libelle d'origine. L'utilisateur voit que son clic a eu un
     * effet, meme si le badge est loin dans la page.
     */
    function retourBouton(bouton, message, succes) {
        if (bouton.dataset.libelleOrigine === undefined) {
            bouton.dataset.libelleOrigine = bouton.textContent.trim();
        }

        bouton.textContent = message;
        if (succes === true) {
            bouton.classList.add('btn-ajouter--ok');
        } else {
            bouton.classList.remove('btn-ajouter--ok');
        }

        window.clearTimeout(Number(bouton.dataset.minuteur));
        bouton.dataset.minuteur = String(window.setTimeout(function () {
            bouton.textContent = bouton.dataset.libelleOrigine;
            bouton.classList.remove('btn-ajouter--ok');
        }, 1600));
    }

    /* ==========================================================
       8. PAGE PANIER
       ==========================================================
       Le contenu est construit avec createElement et textContent,
       jamais avec innerHTML : une valeur venue du localStorage ne
       peut donc pas etre interpretee comme du HTML.
       ========================================================== */

    function rendrePanier() {
        var zone = document.getElementById('panierContenu');
        if (!zone) {
            return;
        }

        var vide    = document.getElementById('panierVide');
        var resume  = document.getElementById('panierResume');
        var actions = document.getElementById('panierActions');
        var lignes  = lire();
        var t       = totaux(lignes);

        /* On repart d'une zone propre a chaque rendu */
        zone.textContent = '';

        var estVide = lignes.length === 0;
        if (vide)    { vide.hidden    = !estVide; }
        if (resume)  { resume.hidden  = estVide; }
        if (actions) { actions.hidden = estVide; }

        if (estVide) {
            return;
        }

        lignes.forEach(function (ligne) {
            zone.appendChild(construireLigne(ligne));
        });

        majTotaux(t);
    }

    /** Construit l'element DOM d'une ligne de panier */
    function construireLigne(ligne) {
        var article = document.createElement('article');
        article.className = 'panier-ligne';
        article.setAttribute('data-ligne', ligne.id);

        /* --- visuel --- */
        var vignette = document.createElement('div');
        vignette.className = 'panier-vignette';
        if (ligne.image) {
            var img = document.createElement('img');
            img.src = ligne.image;
            /* Decoratif : le nom du produit est juste a cote */
            img.alt = '';
            img.width = 600;
            img.height = 400;
            img.loading = 'lazy';
            vignette.appendChild(img);
        }
        article.appendChild(vignette);

        /* --- designation --- */
        var info = document.createElement('div');
        info.className = 'panier-info';

        var titre = document.createElement('h2');
        titre.className = 'panier-nom';
        titre.textContent = ligne.nom;
        info.appendChild(titre);

        var unitaire = document.createElement('p');
        unitaire.className = 'panier-unitaire';
        unitaire.textContent = formaterPrix(Math.round(ligne.prix * 100)) + ' l’unité';
        info.appendChild(unitaire);

        article.appendChild(info);

        /* --- quantite --- */
        article.appendChild(construireQuantite(ligne));

        /* --- sous-total --- */
        var total = document.createElement('div');
        total.className = 'panier-soustotal';

        var libelleTotal = document.createElement('span');
        libelleTotal.className = 'sr-only';
        libelleTotal.textContent = 'Sous-total : ';
        total.appendChild(libelleTotal);

        var montant = document.createElement('strong');
        montant.textContent = formaterPrix(sousTotal(ligne));
        total.appendChild(montant);

        article.appendChild(total);

        /* --- suppression --- */
        var zoneSupp = document.createElement('div');
        zoneSupp.className = 'panier-supprimer';

        var supp = document.createElement('button');
        supp.type = 'button';
        supp.className = 'btn-supprimer';
        supp.setAttribute('aria-label', 'Retirer ' + ligne.nom + ' du panier');
        supp.textContent = 'Retirer';
        supp.addEventListener('click', function () {
            supprimer(ligne.id);
            messageEtat('« ' + ligne.nom + ' » retiré du panier.');
        });
        zoneSupp.appendChild(supp);

        article.appendChild(zoneSupp);
        return article;
    }

    /** Groupe de controles de quantite d'une ligne */
    function construireQuantite(ligne) {
        var bloc = document.createElement('div');
        bloc.className = 'panier-quantite';

        var moins = document.createElement('button');
        moins.type = 'button';
        moins.className = 'qte-btn';
        moins.textContent = '−';
        moins.setAttribute('aria-label', 'Diminuer la quantité de ' + ligne.nom);
        moins.disabled = ligne.qte <= 1;

        var champ = document.createElement('input');
        champ.type = 'number';
        champ.className = 'qte-champ';
        champ.value = String(ligne.qte);
        champ.min = '1';
        champ.max = String(QTE_MAX);
        champ.step = '1';
        champ.inputMode = 'numeric';
        champ.setAttribute('aria-label', 'Quantité pour ' + ligne.nom);

        var plus = document.createElement('button');
        plus.type = 'button';
        plus.className = 'qte-btn';
        plus.textContent = '+';
        plus.setAttribute('aria-label', 'Augmenter la quantité de ' + ligne.nom);
        plus.disabled = ligne.qte >= QTE_MAX;

        moins.addEventListener('click', function () {
            definirQuantite(ligne.id, ligne.qte - 1);
        });
        plus.addEventListener('click', function () {
            definirQuantite(ligne.id, ligne.qte + 1);
        });
        champ.addEventListener('change', function () {
            definirQuantite(ligne.id, champ.value);
        });

        bloc.appendChild(moins);
        bloc.appendChild(champ);
        bloc.appendChild(plus);
        return bloc;
    }

    /** Remplit le bloc des totaux de la page panier */
    function majTotaux(t) {
        ecrireDans('totalArticles', t.articles + ' article' + (t.articles > 1 ? 's' : ''));
        ecrireDans('totalHT',  formaterPrix(t.ht));
        ecrireDans('totalTVA', formaterPrix(t.tva));
        ecrireDans('totalTTC', formaterPrix(t.ttc));
    }

    function ecrireDans(id, texte) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = texte;
        }
    }

    /** Message d'etat de la page panier, annonce par aria-live */
    function messageEtat(texte) {
        var zone = document.getElementById('panierMessage');
        if (!zone) {
            return;
        }
        zone.textContent = texte;
        window.clearTimeout(Number(zone.dataset.minuteur));
        zone.dataset.minuteur = String(window.setTimeout(function () {
            zone.textContent = '';
        }, 5000));
    }

    /* ==========================================================
       9. BOUTON « VIDER LE PANIER »
       ========================================================== */

    function brancherVider() {
        var bouton = document.getElementById('panierVider');
        if (!bouton) {
            return;
        }

        bouton.addEventListener('click', function () {
            var t = totaux();
            if (t.articles === 0) {
                return;
            }
            /* Confirmation avant une action destructrice */
            var ok = window.confirm(
                'Vider le panier ?\n\n'
                + t.articles + ' article' + (t.articles > 1 ? 's' : '')
                + ' seront retirés. Cette action est définitive.'
            );
            if (ok) {
                vider();
                messageEtat('Le panier a été vidé.');
            }
        });
    }

    /* ==========================================================
       10. INITIALISATION
       ========================================================== */

    function init() {
        majBadge();
        brancherBoutonsAjouter();
        brancherVider();
        rendrePanier();

        /* Tout changement de panier rafraichit le badge et la page */
        document.addEventListener('panier:maj', function () {
            majBadge();
            rendrePanier();
        });

        /* Panier modifie dans un AUTRE onglet du meme site :
           l'evenement storage permet de rester coherent. */
        window.addEventListener('storage', function (evenement) {
            if (evenement.key === CLE_PANIER || evenement.key === null) {
                majBadge();
                rendrePanier();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* ==========================================================
       11. INTERFACE PUBLIQUE (utilisee par commande.js)
       ========================================================== */

    window.Panier = {
        CLE:             CLE_PANIER,
        TAUX_TVA:        TAUX_TVA,
        QTE_MAX:         QTE_MAX,
        lire:            lire,
        ajouter:         ajouter,
        definirQuantite: definirQuantite,
        supprimer:       supprimer,
        vider:           vider,
        totaux:          totaux,
        sousTotal:       sousTotal,
        formaterPrix:    formaterPrix
    };

})(window, document);
