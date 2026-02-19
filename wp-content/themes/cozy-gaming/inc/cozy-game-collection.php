<?php
/**
 * ============================================================================
 * MODULE : Collection de Jeux — Shortcode [cozy_game_collection]
 * ============================================================================
 *
 * Affiche une page collection répertoriant tous les jeux mentionnés
 * dans les articles, avec filtres par type et plateforme.
 *
 * Usage :
 *   [cozy_game_collection]
 *   [cozy_game_collection per_page="24" columns="4"]
 *   [cozy_game_collection type="test,coup_coeur"]
 *
 * @package CozyGaming
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* -----------------------------------------------
 * 1. SHORTCODE PRINCIPAL
 * ----------------------------------------------- */

function cozy_game_collection_shortcode( $atts ) {

    $atts = shortcode_atts( array(
        'per_page' => 24,
        'columns'  => 3,
        'platform' => '',         // slug plateforme : pc, ps5, switch…
        'orderby'  => 'name',     // name | count
        'order'    => 'ASC',
    ), $atts, 'cozy_game_collection' );

    $paged    = max( 1, get_query_var( 'paged', 1 ) );
    $per_page = (int) $atts['per_page'];
    $offset   = ( $paged - 1 ) * $per_page;

    // --- Query game taxonomy terms ---
    $term_args = array(
        'taxonomy'   => 'cozy_game',
        'hide_empty' => false,
        'number'     => $per_page,
        'offset'     => $offset,
        'orderby'    => $atts['orderby'] === 'count' ? 'count' : 'name',
        'order'      => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
    );

    $games     = get_terms( $term_args );
    $total     = (int) wp_count_terms( array( 'taxonomy' => 'cozy_game', 'hide_empty' => false ) );
    $max_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

    // Gather platforms for filter bar
    $all_platforms = cozy_get_platforms();

    ob_start();
    ?>
    <div class="cozy-collection" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">

        <!-- ── Header ── -->
        <div class="cozy-collection__header">
            <div class="cozy-collection__title-row">
                <h2 class="cozy-collection__title">
                    <i data-lucide="library"></i>
                    Collection de Jeux
                </h2>
                <span class="cozy-collection__count">
                    <?php echo esc_html( $total ); ?> jeu<?php echo $total > 1 ? 'x' : ''; ?>
                </span>
            </div>

            <!-- ── Search ── -->
            <div class="cozy-collection__search">
                <div class="cozy-form__input-icon">
                    <i data-lucide="search" class="lucide"></i>
                    <input type="text"
                           class="cozy-form__input"
                           id="cozy-collection-search"
                           placeholder="Rechercher un jeu…">
                </div>
            </div>

            <!-- ── Filters ── -->
            <div class="cozy-collection__filters" id="cozy-collection-filters">
                <div class="cozy-collection__filter-group">
                    <span class="cozy-collection__filter-label">Plateforme</span>
                    <div class="cozy-collection__filter-pills">
                        <button class="cozy-collection__pill is-active" data-filter="platform" data-value="all">Toutes</button>
                        <?php
                        $platform_short = array(
                            'pc'          => '🖥️ PC',
                            'ps5'         => '🎮 PS5',
                            'ps4'         => '🎮 PS4',
                            'xbox_series' => '🟢 Xbox',
                            'switch'      => '🔴 Switch',
                            'switch_2'    => '🔴 Switch 2',
                            'mobile'      => '📱 Mobile',
                        );
                        foreach ( $platform_short as $slug => $label ) : ?>
                            <button class="cozy-collection__pill" data-filter="platform" data-value="<?php echo esc_attr( $slug ); ?>">
                                <?php echo esc_html( $label ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Grid ── -->
        <div class="cozy-collection__grid">
            <?php
            if ( ! empty( $games ) && ! is_wp_error( $games ) ) :
                foreach ( $games as $game_term ) :
                    $game_data = function_exists( 'cozy_get_game_term_data' )
                        ? cozy_get_game_term_data( $game_term->term_id )
                        : array();

                    // Platforms as data attribute for JS filtering
                    $platforms_str = '';
                    if ( ! empty( $game_data['platforms'] ) && is_array( $game_data['platforms'] ) ) {
                        $platforms_str = implode( ',', $game_data['platforms'] );
                    }

                    $cover     = ! empty( $game_data['cover'] ) ? $game_data['cover'] : null;
                    $term_link = get_term_link( $game_term );

                    // Count only real articles (post type = post), not events
                    $posts_for_game = get_posts( array(
                        'post_type'      => 'post',
                        'posts_per_page' => -1,
                        'tax_query'      => array( array(
                            'taxonomy' => 'cozy_game',
                            'field'    => 'term_id',
                            'terms'    => $game_term->term_id,
                        ) ),
                        'fields' => 'ids',
                    ) );
                    $article_count = count( $posts_for_game );

                    // Smart link: 1 article → direct, 2+ → archive, 0 → none
                    $card_href = '';
                    if ( $article_count === 1 ) {
                        $card_href = get_permalink( $posts_for_game[0] );
                    } elseif ( $article_count > 1 && ! is_wp_error( $term_link ) ) {
                        $card_href = $term_link;
                    }
                    ?>
                    <article class="cozy-collection__card"
                             data-name="<?php echo esc_attr( strtolower( $game_term->name ) ); ?>"
                             data-platforms="<?php echo esc_attr( $platforms_str ); ?>">

                        <!-- Thumbnail -->
                        <div class="cozy-collection__card-thumb">
                            <?php if ( $cover && ! empty( $cover['sizes']['medium_large'] ) ) : ?>
                                <img src="<?php echo esc_url( $cover['sizes']['medium_large'] ); ?>"
                                     alt="<?php echo esc_attr( $game_term->name ); ?>"
                                     loading="lazy">
                            <?php elseif ( $cover && ! empty( $cover['url'] ) ) : ?>
                                <img src="<?php echo esc_url( $cover['url'] ); ?>"
                                     alt="<?php echo esc_attr( $game_term->name ); ?>"
                                     loading="lazy">
                            <?php else : ?>
                                <div class="cozy-collection__card-placeholder">
                                    <i data-lucide="gamepad-2"></i>
                                </div>
                            <?php endif; ?>

                            <?php if ( $article_count > 0 ) : ?>
                                <span class="cozy-collection__card-articles">
                                    <i data-lucide="file-text"></i>
                                    <?php echo esc_html( $article_count ); ?> article<?php echo $article_count > 1 ? 's' : ''; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Body -->
                        <div class="cozy-collection__card-body">
                            <h3 class="cozy-collection__card-title">
                                <?php if ( $card_href ) : ?>
                                    <a href="<?php echo esc_url( $card_href ); ?>">
                                        <?php echo esc_html( $game_term->name ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo esc_html( $game_term->name ); ?>
                                <?php endif; ?>
                            </h3>

                            <?php if ( ! empty( $game_data['platforms'] ) && is_array( $game_data['platforms'] ) ) : ?>
                                <div class="cozy-collection__card-platforms">
                                    <?php
                                    foreach ( $game_data['platforms'] as $p ) :
                                        $plabel = isset( $all_platforms[ $p ] ) ? $all_platforms[ $p ] : $p;
                                    ?>
                                        <span class="cozy-collection__platform-tag"><?php echo esc_html( $plabel ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="cozy-collection__card-meta">
                                <?php if ( ! empty( $game_data['developer'] ) ) : ?>
                                    <span class="cozy-collection__meta-item">
                                        <i data-lucide="code-2"></i>
                                        <?php echo esc_html( $game_data['developer'] ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( ! empty( $game_data['genre'] ) ) : ?>
                                    <span class="cozy-collection__meta-item">
                                        <i data-lucide="tag"></i>
                                        <?php echo esc_html( $game_data['genre'] ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( ! empty( $game_data['release_year'] ) ) : ?>
                                    <span class="cozy-collection__meta-item">
                                        <i data-lucide="calendar"></i>
                                        <?php echo esc_html( $game_data['release_year'] ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="cozy-collection__card-footer">
                            <?php if ( $card_href && $article_count > 1 ) : ?>
                                <a href="<?php echo esc_url( $card_href ); ?>" class="cozy-collection__card-link">
                                    Voir les <?php echo esc_html( $article_count ); ?> articles
                                    <i data-lucide="arrow-right"></i>
                                </a>
                            <?php elseif ( $card_href && $article_count === 1 ) : ?>
                                <a href="<?php echo esc_url( $card_href ); ?>" class="cozy-collection__card-link">
                                    Lire l'article
                                    <i data-lucide="arrow-right"></i>
                                </a>
                            <?php else : ?>
                                <span class="cozy-collection__card-link cozy-collection__card-link--soon">
                                    <i data-lucide="clock"></i>
                                    Bientôt
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php
                endforeach;
            else :
            ?>
                <div class="cozy-collection__empty">
                    <i data-lucide="search-x"></i>
                    <p>Aucun jeu trouvé dans la collection.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Pagination ── -->
        <?php if ( $max_pages > 1 ) : ?>
            <div class="cozy-collection__pagination cozy-pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $max_pages,
                    'current'   => $paged,
                    'prev_text' => '<i data-lucide="chevron-left"></i>',
                    'next_text' => '<i data-lucide="chevron-right"></i>',
                ) );
                ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- ── Client-side filtering ── -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var filtersEl = document.getElementById('cozy-collection-filters');
        var searchEl  = document.getElementById('cozy-collection-search');
        var cards     = document.querySelectorAll('.cozy-collection__card');
        var countEl   = document.querySelector('.cozy-collection__count');

        if (!cards.length) return;

        var activeFilters = { platform: 'all' };
        var searchQuery   = '';

        if (filtersEl) {
            filtersEl.addEventListener('click', function(e) {
                var pill = e.target.closest('.cozy-collection__pill');
                if (!pill) return;

                var filterKey = pill.dataset.filter;

                pill.closest('.cozy-collection__filter-pills')
                    .querySelectorAll('.cozy-collection__pill')
                    .forEach(function(p) { p.classList.remove('is-active'); });
                pill.classList.add('is-active');

                activeFilters[filterKey] = pill.dataset.value;
                applyFilters();
            });
        }

        if (searchEl) {
            searchEl.addEventListener('input', function() {
                searchQuery = this.value.toLowerCase().trim();
                applyFilters();
            });
        }

        function applyFilters() {
            var visibleCount = 0;

            cards.forEach(function(card) {
                var cardPlatforms = card.dataset.platforms || '';
                var cardName      = card.dataset.name || '';

                var matchPlatform = activeFilters.platform === 'all' || cardPlatforms.indexOf(activeFilters.platform) !== -1;
                var matchSearch   = !searchQuery || cardName.indexOf(searchQuery) !== -1;

                if (matchPlatform && matchSearch) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (countEl) {
                countEl.textContent = visibleCount + ' jeu' + (visibleCount > 1 ? 'x' : '');
            }

            var emptyEl = document.querySelector('.cozy-collection__empty--filtered');
            var gridEl  = document.querySelector('.cozy-collection__grid');

            if (visibleCount === 0 && !emptyEl) {
                var empty = document.createElement('div');
                empty.className = 'cozy-collection__empty cozy-collection__empty--filtered';
                empty.innerHTML = '<p>Aucun jeu ne correspond à ces filtres.</p>';
                gridEl.appendChild(empty);
            } else if (emptyEl && visibleCount > 0) {
                emptyEl.remove();
            }
        }

        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode( 'cozy_game_collection', 'cozy_game_collection_shortcode' );


/* -----------------------------------------------
 * 2. ARCHIVE QUERY — Ne montrer que les articles (pas les événements)
 * ----------------------------------------------- */

function cozy_game_archive_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( $query->is_tax( 'cozy_game' ) ) {
        $query->set( 'post_type', 'post' );
    }
}
add_action( 'pre_get_posts', 'cozy_game_archive_query' );


/* -----------------------------------------------
 * 3. ENQUEUE DU CSS
 * ----------------------------------------------- */

function cozy_game_collection_enqueue() {
    // Shortcode page
    global $post;
    $has_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cozy_game_collection' );

    // Taxonomy archive page (taxonomy-cozy_game.php)
    $is_game_archive = is_tax( 'cozy_game' );

    if ( $has_shortcode || $is_game_archive ) {
        wp_enqueue_style(
            'cozy-game-collection',
            get_template_directory_uri() . '/assets/css/cozy-game-collection.css',
            array( 'cozy-main' ),
            '3.2.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'cozy_game_collection_enqueue' );


/* -----------------------------------------------
 * 4. FLUSH REWRITE RULES (une seule fois)
 * ----------------------------------------------- */

function cozy_game_collection_flush_rewrite() {
    if ( get_option( 'cozy_game_rewrite_flushed' ) !== '3.2.0' ) {
        flush_rewrite_rules();
        update_option( 'cozy_game_rewrite_flushed', '3.2.0' );
    }
}
add_action( 'init', 'cozy_game_collection_flush_rewrite', 99 );
