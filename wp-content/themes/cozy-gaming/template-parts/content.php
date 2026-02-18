<?php
/**
 * Template Part : Carte d'article (boucles / grilles).
 *
 * Utilisé dans index.php, archive.php, search.php.
 *
 * @package CozyGaming
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'cozy-card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="cozy-card__thumbnail">
            <?php the_post_thumbnail( 'cozy-card' ); ?>
        </a>
    <?php endif; ?>

    <div class="cozy-card__body">
        <div class="cozy-card__meta">
            <?php
            $categories = get_the_category();
            if ( ! empty( $categories ) ) : ?>
                <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="cozy-badge cozy-badge--sm">
                    <?php echo esc_html( $categories[0]->name ); ?>
                </a>
            <?php endif; ?>
            <time datetime="<?php echo get_the_date( 'c' ); ?>">
                <?php echo get_the_date(); ?>
            </time>
        </div>

        <h2 class="cozy-card__title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>

        <p class="cozy-card__excerpt">
            <?php cozy_excerpt( 20 ); ?>
        </p>

        <div class="cozy-card__footer">
            <div class="cozy-card__author">
                <?php echo get_avatar( get_the_author_meta( 'ID' ), 24 ); ?>
                <span><?php the_author(); ?></span>
            </div>
        </div>
    </div>
</article>
