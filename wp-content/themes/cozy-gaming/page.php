<?php
/**
 * Template : Page statique.
 *
 * @package CozyGaming
 */

get_header(); ?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container cozy-container--narrow">

        <?php while ( have_posts() ) : the_post(); ?>

            <article id="page-<?php the_ID(); ?>" <?php post_class( 'cozy-page' ); ?>>

                <header class="cozy-page__header">
                    <h1 class="cozy-page__title"><?php the_title(); ?></h1>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="cozy-page__thumbnail">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </figure>
                <?php endif; ?>

                <div class="cozy-page__content entry-content">
                    <?php the_content(); ?>
                </div>

            </article>

            <?php
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
            ?>

        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
