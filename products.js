/* ============================================================
   products.js - Recherche en temps reel du catalogue
   ------------------------------------------------------------
   Ce code etait ecrit en ligne, dans une balise <script> a la fin
   de products.php. Il est deplace ici a l'identique :
     - le comportement du filtre est inchange ;
     - le navigateur peut mettre le fichier en cache ;
     - la page n'a plus de script en ligne, ce qui ouvre la voie a
       un en-tete Content-Security-Policy strict.

   Il filtre les cartes DEJA rendues par PHP : aucune requete
   reseau, aucun rechargement.
   ============================================================ */
(function () {
    'use strict';

    var champ = document.getElementById('searchInput');
    var grille = document.getElementById('productGrid');
    var vide = document.getElementById('noResults');
    var compteur = document.getElementById('searchCount');

    if (!champ || !grille) {
        return;
    }

    var cartes = Array.prototype.slice.call(grille.querySelectorAll('.product'));
    var total = cartes.length;

    function libelle(nombre, recherche) {
        if (recherche === '') {
            return total + ' produit' + (total > 1 ? 's' : '') + ' au catalogue';
        }
        if (nombre === 0) {
            return 'Aucun résultat pour « ' + recherche + ' »';
        }
        return nombre + ' produit' + (nombre > 1 ? 's' : '')
            + ' sur ' + total + ' pour « ' + recherche + ' »';
    }

    function filtrer() {
        var recherche = champ.value.trim().toLowerCase();
        var visibles = 0;

        cartes.forEach(function (carte) {
            var correspond = recherche === ''
                || carte.dataset.nom.indexOf(recherche) !== -1
                || carte.dataset.description.indexOf(recherche) !== -1;

            carte.hidden = !correspond;
            if (correspond) {
                visibles++;
                /* Une carte filtree puis reaffichee reste visible */
                carte.classList.add('visible');
            }
        });

        if (vide) {
            vide.hidden = visibles !== 0;
        }
        if (compteur) {
            compteur.textContent = libelle(visibles, champ.value.trim());
        }
    }

    champ.addEventListener('input', filtrer);
})();
