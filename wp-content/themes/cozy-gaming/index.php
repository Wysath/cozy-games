<?php
/**
 * Template principal (fallback).
 *
 * WordPress utilise ce fichier si aucun autre template
 * plus spécifique n'est trouvé dans la hiérarchie.
 *
 * @package CozyGaming
 */

get_header(); ?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container">

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
