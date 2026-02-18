<?php
/**
 * Template : Résultats de recherche.
 *
 * @package CozyGaming
 */

get_header(); ?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container">

        <header class="cozy-archive__header">
            <h1 class="cozy-archive__title">
                🔍 <?php printf( __( 'Résultats pour : « %s »', 'cozy-gaming' ), get_search_query() ); ?>
            </h1>
        </header>

        <?php if ( have_posts() ) : ?>

            <p class="cozy-search__count">
                <?php
                global $wp_query;
                printf(
                    _n( '%d résultat trouvé', '%d résultats trouvés', $wp_query->found_posts, 'cozy-gaming' ),
                    $wp_query->found_posts
                );
                ?>
            </p>

            <div class="cozy-posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content' ); ?>
                <?php endwhile; ?>
            </div>

            <?php cozy_pagination(); ?>

        <?php else : ?>

            <div class="cozy-empty">
                <p class="cozy-empty__icon">😿</p>
                <h2 class="cozy-empty__title"><?php esc_html_e( 'Aucun résultat', 'cozy-gaming' ); ?></h2>
                <p class="cozy-empty__text">
                    <?php esc_html_e( 'Aucun contenu ne correspond à ta recherche. Essaie avec d\'autres mots-clés !', 'cozy-gaming' ); ?>
                </p>
                <?php get_search_form(); ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
