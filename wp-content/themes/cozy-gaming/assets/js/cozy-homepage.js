/**
 * ============================================================================
 * COZY GAMING — Homepage Interactivity  (v2.2)
 * ============================================================================
 *
 * Scroll reveal, counter animation, Lucide re-init, hero parallax.
 */

(function () {
    'use strict';

    /* ──────────────────────────────────────────────────────────────
       1. SCROLL REVEAL  – IntersectionObserver sur [data-cozy-reveal]
       ────────────────────────────────────────────────────────────── */
    function initScrollReveal() {
        const els = document.querySelectorAll('[data-cozy-reveal]');
        if (!els.length) return;

        /* Respecter la préférence "reduced motion" */
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            els.forEach(el => el.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const delay = entry.target.dataset.cozyDelay || 0;
                        setTimeout(() => {
                            entry.target.classList.add('is-visible');
                        }, Number(delay));
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
        );

        els.forEach(el => observer.observe(el));
    }


    /* ──────────────────────────────────────────────────────────────
       2. COUNTER ANIMATION  – data-count sur .cozy-home-stats__number
       ────────────────────────────────────────────────────────────── */
    function initCounters() {
        const counters = document.querySelectorAll('.cozy-home-stats__number[data-count]');
        if (!counters.length) return;

        /* Réduire la motion si demandé */
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            counters.forEach(c => { c.textContent = c.dataset.count; });
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.5 }
        );

        counters.forEach(c => observer.observe(c));
    }

    function animateCounter(el) {
        const target = parseInt(el.dataset.count, 10);
        if (isNaN(target) || target === 0) { el.textContent = '0'; return; }

        const duration = 1600;                    // ms
        const steps = Math.min(target, 60);       // max 60 frames
        const stepTime = duration / steps;
        let current = 0;
        const increment = target / steps;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                el.textContent = target;
                clearInterval(timer);
            } else {
                el.textContent = Math.floor(current);
            }
        }, stepTime);
    }


    /* ──────────────────────────────────────────────────────────────
       3. HERO SUBTLE PARALLAX  – léger mouvement des brackets au scroll
       ────────────────────────────────────────────────────────────── */
    function initHeroParallax() {
        const hero = document.querySelector('.cozy-hero');
        if (!hero) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const brackets = hero.querySelectorAll('.cozy-hero__bracket');
        const gridDots = hero.querySelector('.cozy-hero__grid-dots');

        let ticking = false;

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    const scrollY = window.scrollY;
                    const heroH = hero.offsetHeight;

                    if (scrollY < heroH) {
                        const progress = scrollY / heroH;

                        brackets.forEach(function (b) {
                            b.style.transform = 'translate(' + (progress * 8) + 'px, ' + (progress * 12) + 'px)';
                        });

                        if (gridDots) {
                            gridDots.style.transform = 'translateY(' + (progress * 20) + 'px)';
                        }
                    }

                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }


    /* ──────────────────────────────────────────────────────────────
       4. LUCIDE RE-INIT  – s'assurer que les icônes de la homepage
          sont bien rendues (shortcodes insérés dynamiquement).
       ────────────────────────────────────────────────────────────── */
    function initLucideHomepage() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }


    /* ──────────────────────────────────────────────────────────────
       BOOT
       ────────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        initLucideHomepage();
        initScrollReveal();
        initCounters();
        initHeroParallax();
    });

})();
