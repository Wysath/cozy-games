<?php
/**
 * Template : Article individuel (single post).
 *
 * @package CozyGaming
 */

get_header(); ?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container cozy-container--narrow">

        <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'cozy-single' ); ?>>

                <!-- En-tête article -->
                <header class="cozy-single__header">
                    <div class="cozy-single__meta-top">
                        <?php
                        $categories = get_the_category();
                        if ( ! empty( $categories ) ) : ?>
                            <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>" class="cozy-badge cozy-badge--category">
                                <?php echo esc_html( $categories[0]->name ); ?>
                            </a>
                        <?php endif; ?>
                        <time datetime="<?php echo get_the_date( 'c' ); ?>" class="cozy-single__date">
                            <?php echo get_the_date(); ?>
                        </time>
                    </div>

                    <h1 class="cozy-single__title"><?php the_title(); ?></h1>

                    <?php if ( has_excerpt() ) : ?>
                        <p class="cozy-single__excerpt"><?php echo get_the_excerpt(); ?></p>
                    <?php endif; ?>

                    <div class="cozy-single__author">
                        <?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
                        <div>
                            <span class="cozy-single__author-name"><?php the_author(); ?></span>
                            <span class="cozy-single__reading-time">
                                <?php
                                $word_count = str_word_count( strip_tags( get_the_content() ) );
                                $reading_time = max( 1, ceil( $word_count / 200 ) );
                                printf( __( '%d min de lecture', 'cozy-gaming' ), $reading_time );
                                ?>
                            </span>
                        </div>
                    </div>
                </header>

                <!-- Image à la une -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="cozy-single__thumbnail">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </figure>
                <?php endif; ?>

                <!-- Contenu -->
                <div class="cozy-single__content entry-content">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php
                $tags = get_the_tags();
                if ( ! empty( $tags ) ) : ?>
                    <footer class="cozy-single__tags">
                        <?php foreach ( $tags as $tag ) : ?>
                            <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="cozy-tag">
                                #<?php echo esc_html( $tag->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </footer>
                <?php endif; ?>

                <!-- Navigation article précédent/suivant -->
                <nav class="cozy-single__nav">
                    <?php
                    $prev = get_previous_post();
                    $next = get_next_post();
                    ?>
                    <?php if ( $prev ) : ?>
                        <a href="<?php echo get_permalink( $prev ); ?>" class="cozy-single__nav-link cozy-single__nav-link--prev">
                            <span class="cozy-single__nav-label">← <?php esc_html_e( 'Article précédent', 'cozy-gaming' ); ?></span>
                            <span class="cozy-single__nav-title"><?php echo esc_html( $prev->post_title ); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $next ) : ?>
                        <a href="<?php echo get_permalink( $next ); ?>" class="cozy-single__nav-link cozy-single__nav-link--next">
                            <span class="cozy-single__nav-label"><?php esc_html_e( 'Article suivant', 'cozy-gaming' ); ?> →</span>
                            <span class="cozy-single__nav-title"><?php echo esc_html( $next->post_title ); ?></span>
                        </a>
                    <?php endif; ?>
                </nav>

            </article>

            <?php
            // Commentaires
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
            ?>

        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
