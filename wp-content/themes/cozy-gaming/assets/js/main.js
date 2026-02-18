/**
 * ============================================================================
 * COZY GAMING — main.js
 * ============================================================================
 *
 * Script principal : menu mobile, scroll header, animations.
 *
 * @since 1.0.0
 */

(function () {
    'use strict';

    /* ---------------------------------------------------------------
     * DOM Ready
     * --------------------------------------------------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        initMobileMenu();
        initStickyHeader();
        initSmoothScroll();
    });

    /* ---------------------------------------------------------------
     * 1. Menu mobile (hamburger toggle)
     * --------------------------------------------------------------- */
    function initMobileMenu() {
        var toggle = document.querySelector('.cozy-header__menu-toggle');
        var nav    = document.querySelector('.cozy-header__nav');

        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isExpanded));
            nav.classList.toggle('is-open');
            document.body.classList.toggle('menu-open');
        });

        // Fermer en cliquant un lien
        var links = nav.querySelectorAll('a');
        links.forEach(function (link) {
            link.addEventListener('click', function () {
                toggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-open');
                document.body.classList.remove('menu-open');
            });
        });

        // Fermer avec Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                toggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-open');
                document.body.classList.remove('menu-open');
                toggle.focus();
            }
        });
    }

    /* ---------------------------------------------------------------
     * 2. Header sticky — ajoute une classe au scroll
     * --------------------------------------------------------------- */
    function initStickyHeader() {
        var header = document.querySelector('.cozy-header');
        if (!header) return;

        var threshold = 10;

        function onScroll() {
            if (window.scrollY > threshold) {
                header.classList.add('cozy-header--scrolled');
            } else {
                header.classList.remove('cozy-header--scrolled');
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // état initial
    }

    /* ---------------------------------------------------------------
     * 3. Smooth scroll pour les ancres internes
     * --------------------------------------------------------------- */
    function initSmoothScroll() {
        var anchors = document.querySelectorAll('a[href^="#"]');
        anchors.forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href');
                if (targetId === '#' || !targetId) return;

                var target = document.querySelector(targetId);
                if (!target) return;

                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    }

})();
