/**
 * ============================================================================
 * COZY GROVE — Articles Archive Filters (v1.0)
 * ============================================================================
 *
 * Filtrage interactif par pills avec rechargement URL (server-side).
 * Recherche avec debounce, suppression de filtres actifs,
 * réinitialisation complète.
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        var container  = document.querySelector('.cozy-articles-archive');
        if (!container) return;

        var filtersEl  = document.getElementById('cozy-articles-filters');
        var searchEl   = document.getElementById('cozy-articles-search');
        var clearBtn   = document.getElementById('cozy-articles-clear');
        var resetBtn   = document.getElementById('cozy-articles-reset');

        // État actuel des filtres (lu depuis l'URL)
        var params     = new URLSearchParams(window.location.search);
        var filters    = {
            type:     params.get('cozy_type')     || '',
            game:     params.get('cozy_game')      || '',
            category: params.get('cozy_category')  || '',
            sort:     params.get('cozy_sort')       || 'date-desc',
            search:   params.get('cozy_search')     || '',
        };

        // ── Appliquer les filtres (rechargement URL) ──
        function applyFilters() {
            var url = new URL(window.location.href);

            // Nettoyer les anciens params
            url.searchParams.delete('cozy_type');
            url.searchParams.delete('cozy_game');
            url.searchParams.delete('cozy_category');
            url.searchParams.delete('cozy_sort');
            url.searchParams.delete('cozy_search');
            url.searchParams.delete('paged');

            // Retirer la pagination du path (revenir page 1)
            url.pathname = url.pathname.replace(/\/page\/\d+\/?/, '/');

            // Ajouter les filtres actifs
            if (filters.type)                       url.searchParams.set('cozy_type', filters.type);
            if (filters.game)                       url.searchParams.set('cozy_game', filters.game);
            if (filters.category)                   url.searchParams.set('cozy_category', filters.category);
            if (filters.sort && filters.sort !== 'date-desc') url.searchParams.set('cozy_sort', filters.sort);
            if (filters.search)                     url.searchParams.set('cozy_search', filters.search);

            window.location.href = url.toString();
        }

        // ── Click sur une pill ──
        if (filtersEl) {
            filtersEl.addEventListener('click', function (e) {
                var pill = e.target.closest('.cozy-articles-archive__pill');
                if (!pill) return;

                var filterKey = pill.dataset.filter;
                var filterVal = pill.dataset.value;

                // Mettre à jour l'état
                filters[filterKey] = filterVal;

                // Highlight visuel immédiat
                pill.closest('.cozy-articles-archive__filter-pills')
                    .querySelectorAll('.cozy-articles-archive__pill')
                    .forEach(function (p) { p.classList.remove('is-active'); });
                pill.classList.add('is-active');

                // Recharger avec les filtres
                applyFilters();
            });
        }

        // ── Recherche avec Enter ──
        if (searchEl) {
            searchEl.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filters.search = this.value.trim();
                    applyFilters();
                }
            });
        }

        // ── Suppression d'un filtre actif (tag × ) ──
        var activeTags = container.querySelectorAll('.cozy-articles-archive__active-tag');
        activeTags.forEach(function (tag) {
            tag.addEventListener('click', function () {
                var removeKey = this.dataset.remove;
                if (removeKey) {
                    filters[removeKey] = '';
                    applyFilters();
                }
            });
        });

        // ── Réinitialiser tous les filtres ──
        function resetAll() {
            filters = { type: '', game: '', category: '', sort: 'date-desc', search: '' };
            applyFilters();
        }

        if (clearBtn) clearBtn.addEventListener('click', resetAll);
        if (resetBtn) resetBtn.addEventListener('click', resetAll);

        // ── Lucide refresh ──
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

})();
