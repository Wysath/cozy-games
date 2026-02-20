<?php
/**
 * ============================================================================
 * MODULE : Archive des Articles — Shortcode [cozy_articles_archive]
 * ============================================================================
 *
 * Affiche tous les articles du Grimoire avec des filtres interactifs
 * par type d'article, jeu, et tri par date/note.
 *
 * Usage :
 *   [cozy_articles_archive]
 *   [cozy_articles_archive per_page="12" columns="3"]
 *
 * @package CozyGaming
 * @since   3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* -----------------------------------------------
 * 1. SHORTCODE PRINCIPAL
 * ----------------------------------------------- */

function cozy_articles_archive_shortcode( $atts ) {

    $atts = shortcode_atts( array(
        'per_page' => 12,
        'columns'  => 3,
    ), $atts, 'cozy_articles_archive' );

    $paged    = max( 1, get_query_var( 'paged', 1 ) );
    $per_page = (int) $atts['per_page'];

    // ── Récupérer les filtres depuis l'URL (server-side) ──
    $filter_type     = isset( $_GET['cozy_type'] )     ? sanitize_text_field( $_GET['cozy_type'] )     : '';
    $filter_game     = isset( $_GET['cozy_game'] )     ? sanitize_text_field( $_GET['cozy_game'] )     : '';
    $filter_sort     = isset( $_GET['cozy_sort'] )     ? sanitize_text_field( $_GET['cozy_sort'] )     : 'date-desc';
    $filter_search   = isset( $_GET['cozy_search'] )   ? sanitize_text_field( $_GET['cozy_search'] )   : '';

    // ── Build WP_Query ──
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
    );

    // Tri
    switch ( $filter_sort ) {
        case 'date-asc':
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
            break;
        case 'title-asc':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'title-desc':
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
            break;
        case 'rating':
            $args['meta_key'] = 'cozy_rating_global';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        default: // date-desc
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
    }

    // Recherche
    if ( ! empty( $filter_search ) ) {
        $args['s'] = $filter_search;
    }

    // Tax queries
    $tax_query = array();

    if ( ! empty( $filter_type ) ) {
        $tax_query[] = array(
            'taxonomy' => 'cozy_article_type',
            'field'    => 'slug',
            'terms'    => $filter_type,
        );
    }

    if ( ! empty( $filter_game ) ) {
        $tax_query[] = array(
            'taxonomy' => 'cozy_game',
            'field'    => 'slug',
            'terms'    => $filter_game,
        );
    }

    if ( ! empty( $tax_query ) ) {
        $tax_query['relation'] = 'AND';
        $args['tax_query']     = $tax_query;
    }

    $query = new WP_Query( $args );

    // ── Données pour les filtres ──
    $article_types = cozy_get_article_types();
    $all_platforms = cozy_get_platforms();

    // Jeux avec articles
    $games = get_terms( array(
        'taxonomy'   => 'cozy_game',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );

    // Total articles sans filtre pour le compteur
    $total_all = (int) wp_count_posts( 'post' )->publish;

    ob_start();
    ?>
    <div class="cozy-articles-archive" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">

        <!-- ══════════════════════════════════════
             HEADER
             ══════════════════════════════════════ -->
        <div class="cozy-articles-archive__header">
            <div class="cozy-articles-archive__title-row">
                <h2 class="cozy-articles-archive__title">
                    <i data-lucide="book-open"></i>
                    Le Grimoire
                </h2>
                <span class="cozy-articles-archive__count">
                    <?php echo esc_html( $query->found_posts ); ?> article<?php echo $query->found_posts > 1 ? 's' : ''; ?>
                    <?php if ( $query->found_posts < $total_all && ( $filter_type || $filter_game || $filter_search ) ) : ?>
                        <span class="cozy-articles-archive__count-total">/ <?php echo esc_html( $total_all ); ?></span>
                    <?php endif; ?>
                </span>
            </div>

            <!-- ── Recherche ── -->
            <div class="cozy-articles-archive__search">
                <div class="cozy-form__input-icon">
                    <i data-lucide="search" class="lucide"></i>
                    <input type="text"
                           class="cozy-form__input"
                           id="cozy-articles-search"
                           placeholder="Rechercher un article…"
                           value="<?php echo esc_attr( $filter_search ); ?>">
                </div>
            </div>

            <!-- ══════════════════════════════════════
                 FILTRES
                 ══════════════════════════════════════ -->
            <div class="cozy-articles-archive__filters" id="cozy-articles-filters">

                <!-- Type d'article -->
                <div class="cozy-articles-archive__filter-group">
                    <span class="cozy-articles-archive__filter-label">
                        <i data-lucide="tag" class="lucide"></i> Type
                    </span>
                    <div class="cozy-articles-archive__filter-pills">
                        <button class="cozy-articles-archive__pill <?php echo empty( $filter_type ) ? 'is-active' : ''; ?>"
                                data-filter="type" data-value="">
                            Tous
                        </button>
                        <?php foreach ( $article_types as $slug => $type_data ) : ?>
                            <button class="cozy-articles-archive__pill <?php echo ( $filter_type === $slug ) ? 'is-active' : ''; ?>"
                                    data-filter="type" data-value="<?php echo esc_attr( $slug ); ?>">
                                <?php echo esc_html( $type_data['icon'] . ' ' . $type_data['label'] ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Jeu -->
                <?php if ( ! empty( $games ) && ! is_wp_error( $games ) ) : ?>
                    <div class="cozy-articles-archive__filter-group">
                        <span class="cozy-articles-archive__filter-label">
                            <i data-lucide="gamepad-2" class="lucide"></i> Jeu
                        </span>
                        <div class="cozy-articles-archive__filter-pills cozy-articles-archive__filter-pills--scrollable">
                            <button class="cozy-articles-archive__pill <?php echo empty( $filter_game ) ? 'is-active' : ''; ?>"
                                    data-filter="game" data-value="">
                                Tous
                            </button>
                            <?php foreach ( $games as $game_term ) : ?>
                                <button class="cozy-articles-archive__pill <?php echo ( $filter_game === $game_term->slug ) ? 'is-active' : ''; ?>"
                                        data-filter="game" data-value="<?php echo esc_attr( $game_term->slug ); ?>">
                                    <?php echo esc_html( $game_term->name ); ?>
                                    <span class="cozy-articles-archive__pill-count"><?php echo esc_html( $game_term->count ); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tri -->
                <div class="cozy-articles-archive__filter-group">
                    <span class="cozy-articles-archive__filter-label">
                        <i data-lucide="arrow-up-down" class="lucide"></i> Tri
                    </span>
                    <div class="cozy-articles-archive__filter-pills">
                        <button class="cozy-articles-archive__pill <?php echo ( $filter_sort === 'date-desc' || empty( $filter_sort ) ) ? 'is-active' : ''; ?>"
                                data-filter="sort" data-value="date-desc">
                            <i data-lucide="clock" class="lucide"></i> Récents
                        </button>
                        <button class="cozy-articles-archive__pill <?php echo ( $filter_sort === 'date-asc' ) ? 'is-active' : ''; ?>"
                                data-filter="sort" data-value="date-asc">
                            <i data-lucide="history" class="lucide"></i> Anciens
                        </button>
                        <button class="cozy-articles-archive__pill <?php echo ( $filter_sort === 'title-asc' ) ? 'is-active' : ''; ?>"
                                data-filter="sort" data-value="title-asc">
                            <i data-lucide="arrow-down-a-z" class="lucide"></i> A → Z
                        </button>
                        <?php if ( function_exists( 'get_field' ) ) : ?>
                            <button class="cozy-articles-archive__pill <?php echo ( $filter_sort === 'rating' ) ? 'is-active' : ''; ?>"
                                    data-filter="sort" data-value="rating">
                                <i data-lucide="star" class="lucide"></i> Meilleures notes
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Filtres actifs -->
            <?php if ( $filter_type || $filter_game || $filter_search ) : ?>
                <div class="cozy-articles-archive__active-filters">
                    <span class="cozy-articles-archive__active-label">Filtres actifs :</span>

                    <?php if ( $filter_type ) :
                        $type_label = isset( $article_types[ $filter_type ] ) ? $article_types[ $filter_type ]['icon'] . ' ' . $article_types[ $filter_type ]['label'] : $filter_type;
                    ?>
                        <span class="cozy-articles-archive__active-tag" data-remove="type">
                            <?php echo esc_html( $type_label ); ?>
                            <i data-lucide="x" class="lucide"></i>
                        </span>
                    <?php endif; ?>

                    <?php if ( $filter_game ) :
                        $game_obj = get_term_by( 'slug', $filter_game, 'cozy_game' );
                        $game_label = $game_obj ? $game_obj->name : $filter_game;
                    ?>
                        <span class="cozy-articles-archive__active-tag" data-remove="game">
                            <?php echo esc_html( $game_label ); ?>
                            <i data-lucide="x" class="lucide"></i>
                        </span>
                    <?php endif; ?>

                    <?php if ( $filter_search ) : ?>
                        <span class="cozy-articles-archive__active-tag" data-remove="search">
                            « <?php echo esc_html( $filter_search ); ?> »
                            <i data-lucide="x" class="lucide"></i>
                        </span>
                    <?php endif; ?>

                    <button class="cozy-articles-archive__clear-filters" id="cozy-articles-clear">
                        <i data-lucide="filter-x" class="lucide"></i> Réinitialiser
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- ══════════════════════════════════════
             GRILLE D'ARTICLES
             ══════════════════════════════════════ -->
        <?php if ( $query->have_posts() ) : ?>
            <div class="cozy-articles-archive__grid">
                <?php while ( $query->have_posts() ) : $query->the_post();
                    $post_id = get_the_ID();

                    // Type d'article
                    $type_terms = wp_get_post_terms( $post_id, 'cozy_article_type', array( 'fields' => 'slugs' ) );
                    $post_type_slug = ( ! is_wp_error( $type_terms ) && ! empty( $type_terms ) ) ? $type_terms[0] : '';
                    $post_type_data = ( $post_type_slug && isset( $article_types[ $post_type_slug ] ) ) ? $article_types[ $post_type_slug ] : null;

                    // Jeu associé
                    $game_terms = wp_get_post_terms( $post_id, 'cozy_game', array( 'fields' => 'all' ) );
                    $game_name  = ( ! is_wp_error( $game_terms ) && ! empty( $game_terms ) ) ? $game_terms[0]->name : '';

                    // Note
                    $rating = function_exists( 'get_field' ) ? (int) get_field( 'cozy_rating_global', $post_id ) : 0;

                    // Plateformes depuis la fiche jeu
                    $platforms = array();
                    if ( ! is_wp_error( $game_terms ) && ! empty( $game_terms ) && function_exists( 'cozy_get_game_term_data' ) ) {
                        $game_data = cozy_get_game_term_data( $game_terms[0]->term_id );
                        $platforms = ! empty( $game_data['platforms'] ) ? $game_data['platforms'] : array();
                    }
                ?>
                    <article class="cozy-articles-archive__card" data-cozy-reveal>
                        <!-- Thumbnail -->
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="cozy-articles-archive__card-thumb">
                                <?php the_post_thumbnail( 'medium_large' ); ?>

                                <?php if ( $post_type_data ) : ?>
                                    <span class="cozy-articles-archive__card-type"
                                          style="background: <?php echo esc_attr( $post_type_data['color'] ); ?>;">
                                        <?php echo esc_html( $post_type_data['icon'] . ' ' . $post_type_data['label'] ); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ( $rating > 0 ) : ?>
                                    <span class="cozy-articles-archive__card-score">
                                        <?php echo esc_html( $rating ); ?><small>/5</small>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <!-- Body -->
                        <div class="cozy-articles-archive__card-body">
                            <!-- Meta -->
                            <div class="cozy-articles-archive__card-meta">
                                <?php if ( $game_name ) : ?>
                                    <span class="cozy-articles-archive__card-game">
                                        <i data-lucide="gamepad-2" class="lucide"></i>
                                        <?php echo esc_html( $game_name ); ?>
                                    </span>
                                <?php endif; ?>

                                <span class="cozy-articles-archive__card-date">
                                    <i data-lucide="calendar" class="lucide"></i>
                                    <time datetime="<?php echo get_the_date( 'c' ); ?>">
                                        <?php echo get_the_date(); ?>
                                    </time>
                                </span>
                            </div>

                            <!-- Titre -->
                            <h3 class="cozy-articles-archive__card-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>

                            <!-- Extrait -->
                            <p class="cozy-articles-archive__card-excerpt">
                                <?php cozy_excerpt( 18 ); ?>
                            </p>

                            <!-- Plateformes -->
                            <?php if ( ! empty( $platforms ) ) : ?>
                                <div class="cozy-articles-archive__card-platforms">
                                    <?php foreach ( array_slice( $platforms, 0, 3 ) as $p ) :
                                        $plabel = isset( $all_platforms[ $p ] ) ? $all_platforms[ $p ] : $p;
                                    ?>
                                        <span class="cozy-articles-archive__platform-tag"><?php echo esc_html( $plabel ); ?></span>
                                    <?php endforeach; ?>
                                    <?php if ( count( $platforms ) > 3 ) : ?>
                                        <span class="cozy-articles-archive__platform-tag cozy-articles-archive__platform-tag--more">
                                            +<?php echo count( $platforms ) - 3; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Footer -->
                            <div class="cozy-articles-archive__card-footer">
                                <div class="cozy-articles-archive__card-author">
                                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 22 ); ?>
                                    <span><?php the_author(); ?></span>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="cozy-articles-archive__card-link">
                                    Lire <i data-lucide="arrow-right" class="lucide"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- ── Pagination ── -->
            <?php if ( $query->max_num_pages > 1 ) : ?>
                <div class="cozy-articles-archive__pagination">
                    <?php
                    // Build base URL sans les params de pagination
                    $base_url = get_pagenum_link( 1 );

                    echo paginate_links( array(
                        'total'     => $query->max_num_pages,
                        'current'   => $paged,
                        'format'    => 'page/%#%/',
                        'prev_text' => '<i data-lucide="chevron-left"></i> Précédent',
                        'next_text' => 'Suivant <i data-lucide="chevron-right"></i>',
                        'add_args'  => array_filter( array(
                            'cozy_type'     => $filter_type ?: false,
                            'cozy_game'     => $filter_game ?: false,
                            'cozy_sort'     => ( $filter_sort !== 'date-desc' ) ? $filter_sort : false,
                            'cozy_search'   => $filter_search ?: false,
                        ) ),
                    ) );
                    ?>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <!-- ── Aucun résultat ── -->
            <div class="cozy-articles-archive__empty">
                <div class="cozy-articles-archive__empty-icon">
                    <i data-lucide="book-x"></i>
                </div>
                <h3>Aucun article trouvé</h3>
                <p>Aucun article ne correspond à tes filtres. Essaie de modifier ta recherche ou de réinitialiser les filtres.</p>
                <button class="cozy-btn" id="cozy-articles-reset">
                    <i data-lucide="rotate-ccw" class="lucide"></i>
                    Réinitialiser les filtres
                </button>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode( 'cozy_articles_archive', 'cozy_articles_archive_shortcode' );


/* -----------------------------------------------
 * 2. ENQUEUE DES ASSETS
 * ----------------------------------------------- */

function cozy_articles_archive_enqueue_assets() {
    global $post;

    // Ne charger que si le shortcode est présent
    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'cozy_articles_archive' ) ) {
        return;
    }

    wp_enqueue_style(
        'cozy-articles-archive',
        get_template_directory_uri() . '/assets/css/cozy-articles-archive.css',
        array( 'cozy-main', 'cozy-components' ),
        COZY_THEME_VERSION
    );

    wp_enqueue_script(
        'cozy-articles-archive',
        get_template_directory_uri() . '/assets/js/cozy-articles-archive.js',
        array(),
        COZY_THEME_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_articles_archive_enqueue_assets' );
