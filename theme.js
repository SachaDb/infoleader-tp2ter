/* ============================================================
   theme.js - Theme clair / sombre
   ------------------------------------------------------------
   Charge SANS defer, avant la feuille de style : le theme est
   applique avant le premier affichage, ce qui evite le flash
   de couleurs claires au chargement d'une page en mode sombre.

   La bascule elle-meme est geree dans script.js.
   ============================================================ */
(function () {
    'use strict';

    /* Marqueur "js" : pose avant le rendu pour que le CSS sache que
       JavaScript fonctionne. Sans lui, aucune section ne doit etre
       masquee. Etait un script en ligne dans l entete des pages. */
    document.documentElement.classList.add("js");

    try {
        var choix = window.localStorage.getItem('il-theme');
        if (choix === 'dark' || choix === 'light') {
            /* Choix explicite de l'utilisateur : il a la priorite
               sur le reglage du systeme d'exploitation. */
            document.documentElement.setAttribute('data-theme', choix);
        }
    } catch (e) {
        /* Navigation privee ou stockage bloque : on laisse le theme
           du systeme s'appliquer via prefers-color-scheme. */
    }
})();
