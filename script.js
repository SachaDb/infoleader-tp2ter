/**
 * script.js - Animations et interactions du site InfoLeader
 *
 * JavaScript vanilla, aucun framework :
 *   1. en-tete qui se densifie au defilement
 *   2. menu deployable sur mobile
 *   3. apparition progressive des sections au defilement
 *   4. compteurs animes du bandeau de chiffres
 *   5. produits phares de la page d'accueil, lus dans products.json
 *
 * Toutes les animations sont desactivees si le visiteur a demande une
 * reduction des animations dans son systeme.
 */

(function () {
    'use strict';

    var reduceMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ==============================================================
       1. En-tete au defilement
       ============================================================== */

    function setupHeader() {
        var header = document.querySelector('header.site-header');
        if (!header) {
            return;
        }

        var apply = function () {
            header.classList.toggle('scrolled', window.scrollY > 40);
        };

        apply();
        window.addEventListener('scroll', apply, { passive: true });
    }

    /* ==============================================================
       2. Menu mobile
       ============================================================== */

    function setupBurger() {
        var burger = document.querySelector('.burger');
        var menu = document.getElementById('menu-principal');
        if (!burger || !menu) {
            return;
        }

        burger.addEventListener('click', function () {
            var open = burger.getAttribute('aria-expanded') === 'true';
            burger.setAttribute('aria-expanded', open ? 'false' : 'true');
            menu.classList.toggle('open', !open);
        });

        /* Un clic sur un lien referme le menu */
        menu.addEventListener('click', function (event) {
            if (event.target.tagName === 'A') {
                burger.setAttribute('aria-expanded', 'false');
                menu.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menu.classList.contains('open')) {
                burger.setAttribute('aria-expanded', 'false');
                menu.classList.remove('open');
                burger.focus();
            }
        });
    }

    /* ==============================================================
       3. Apparition des sections au defilement
       ============================================================== */

    function revealAll(elements) {
        Array.prototype.forEach.call(elements, function (el) {
            el.classList.add('visible');
        });
    }

    function setupReveal(root) {
        var targets = (root || document).querySelectorAll('.reveal:not(.visible)');
        if (!targets.length) {
            return;
        }

        /* Navigateur ancien ou animations reduites : tout est affiche */
        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealAll(targets);
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        Array.prototype.forEach.call(targets, function (el) {
            observer.observe(el);
        });
    }

    /* ==============================================================
       4. Compteurs animes
       ==============================================================
       <span class="valeur" data-count="250" data-suffix="+">0</span>
       ============================================================== */

    function countUp(el) {
        var raw = el.getAttribute('data-count');
        var target = parseFloat(raw);
        var suffix = el.getAttribute('data-suffix') || '';
        var decimals = (raw.split('.')[1] || '').length;

        if (isNaN(target)) {
            return;
        }
        if (reduceMotion) {
            el.textContent = target.toFixed(decimals) + suffix;
            return;
        }

        var duration = 1500;
        var start = null;

        var step = function (timestamp) {
            if (start === null) {
                start = timestamp;
            }
            var progress = Math.min((timestamp - start) / duration, 1);
            /* Courbe douce en sortie */
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = (target * eased).toFixed(decimals) + suffix;

            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };

        window.requestAnimationFrame(step);
    }

    function setupCounters() {
        var counters = document.querySelectorAll('[data-count]');
        if (!counters.length) {
            return;
        }

        if (!('IntersectionObserver' in window)) {
            Array.prototype.forEach.call(counters, countUp);
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    countUp(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        Array.prototype.forEach.call(counters, function (el) {
            observer.observe(el);
        });
    }

    /* ==============================================================
       5. Initialisation
       ==============================================================
       Les fiches produit sont rendues par PHP (index.php et
       products.php) : la page reste lisible sans JavaScript, et il n'y
       a aucune requete reseau supplementaire a l'affichage.
       ============================================================== */

    /* ==============================================================
       6. TP 2ter - Bascule du theme clair / sombre
       ==============================================================
       theme.js a deja applique le theme avant le premier affichage.
       Ici on gere le bouton et la memorisation du choix.
       ============================================================== */

    function themeCourant() {
        var force = document.documentElement.getAttribute('data-theme');
        if (force === 'dark' || force === 'light') {
            return force;
        }
        /* Aucun choix enregistre : on suit le systeme d'exploitation */
        return window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    function appliquerTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);

        var bouton = document.getElementById('themeToggle');
        if (!bouton) {
            return;
        }

        var sombre = theme === 'dark';
        bouton.setAttribute('aria-pressed', sombre ? 'true' : 'false');

        /* Le libelle annonce l'action a venir, pas l'etat courant */
        var label = bouton.querySelector('.theme-label');
        if (label) {
            label.textContent = sombre ? 'Theme clair' : 'Theme sombre';
        }
        bouton.setAttribute('title', sombre
            ? 'Revenir au theme clair'
            : 'Passer au theme sombre');
    }

    function setupTheme() {
        var bouton = document.getElementById('themeToggle');
        if (!bouton) {
            return;
        }

        /* Le bouton n'est revele que si JavaScript fonctionne :
           sans lui, il n'aurait aucun effet. */
        bouton.hidden = false;

        appliquerTheme(themeCourant());

        bouton.addEventListener('click', function () {
            var nouveau = themeCourant() === 'dark' ? 'light' : 'dark';
            appliquerTheme(nouveau);
            try {
                window.localStorage.setItem('il-theme', nouveau);
            } catch (e) {
                /* Navigation privee : le theme s'applique quand meme,
                   il ne sera simplement pas retenu. */
            }
        });

        /* Si l'utilisateur n'a rien choisi, on suit les changements de
           reglage du systeme en direct. */
        var media = window.matchMedia('(prefers-color-scheme: dark)');

        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', function (event) {
                var choix = null;
                try {
                    choix = window.localStorage.getItem('il-theme');
                } catch (e) { /* stockage indisponible */ }

                if (choix !== 'dark' && choix !== 'light') {
                    appliquerTheme(event.matches ? 'dark' : 'light');
                }
            });
        }
    }

    /* ==============================================================
       7. TP 2ter - Resume d'erreurs du formulaire de contact
       ==============================================================
       Quand la validation serveur echoue, le clavier et le lecteur
       d'ecran sont amenes directement sur la liste des erreurs au
       lieu de rester en haut de page.
       ============================================================== */

    function setupResumeErreurs() {
        var resume = document.getElementById('resume-erreurs');
        if (resume) {
            resume.focus();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupHeader();
        setupBurger();
        setupReveal(document);
        setupCounters();
        setupTheme();
        setupResumeErreurs();
    });
})();
