<?php
/**
 * Template : Archives (catégories, tags, dates, auteurs).
 *
 * @package CozyGaming
 */

get_header(); ?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container">

        <!-- En-tête d'archive -->
        <header class="cozy-archive__header">
            <?php the_archive_title( '<h1 class="cozy-archive__title">', '</h1>' ); ?>
            <?php the_archive_description( '<div class="cozy-archive__description">', '</div>' ); ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="cozy-posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content' ); ?>
                <?php endwhile; ?>
            </div>

            <?php cozy_pagination(); ?>

        <?php else : ?>
            <?php get_template_part( 'template-parts/content', 'none' ); ?>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
