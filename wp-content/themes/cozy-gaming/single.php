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
                        $article_type_terms = wp_get_post_terms( get_the_ID(), 'cozy_article_type', array( 'fields' => 'all' ) );
                        if ( ! is_wp_error( $article_type_terms ) && ! empty( $article_type_terms ) ) :
                            $type_term = $article_type_terms[0];
                            $type_link = get_term_link( $type_term );
                        ?>
                            <a href="<?php echo esc_url( $type_link ); ?>" class="cozy-badge cozy-badge--category">
                                <?php echo esc_html( $type_term->name ); ?>
                            </a>
                        <?php endif; ?>
                        <span class="cozy-card__meta-separator"></span>
                        <i data-lucide="calendar" class="lucide"></i>
                        <time datetime="<?php echo get_the_date( 'c' ); ?>" class="cozy-single__date">
                            <?php echo get_the_date(); ?>
                        </time>
                    </div>

                    <h1 class="cozy-single__title"><?php the_title(); ?></h1>

                    <?php if ( has_excerpt() ) : ?>
                        <p class="cozy-single__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                    <?php endif; ?>

                    <div class="cozy-single__author">
                        <?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
                        <div>
                            <span class="cozy-single__author-name"><?php the_author(); ?></span>
                            <span class="cozy-single__reading-time">
                                <i data-lucide="clock" class="lucide"></i>
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

                <!-- Jeu associé -->
                <?php
                $game_terms = wp_get_post_terms( get_the_ID(), 'cozy_game', array( 'fields' => 'all' ) );
                if ( ! is_wp_error( $game_terms ) && ! empty( $game_terms ) ) : ?>
                    <footer class="cozy-single__tags">
                        <span class="cozy-single__tags-label">
                            <i data-lucide="gamepad-2" class="lucide"></i> Jeu
                        </span>
                        <?php foreach ( $game_terms as $game ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $game ) ); ?>" class="cozy-tag">
                                <?php echo esc_html( $game->name ); ?>
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
                        <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="cozy-single__nav-link cozy-single__nav-link--prev">
                            <i data-lucide="chevron-left" class="lucide"></i>
                            <div>
                                <span class="cozy-single__nav-label"><?php esc_html_e( 'Précédent', 'cozy-gaming' ); ?></span>
                                <span class="cozy-single__nav-title"><?php echo esc_html( $prev->post_title ); ?></span>
                            </div>
                        </a>
                    <?php endif; ?>
                    <?php if ( $next ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="cozy-single__nav-link cozy-single__nav-link--next">
                            <div>
                                <span class="cozy-single__nav-label"><?php esc_html_e( 'Suivant', 'cozy-gaming' ); ?></span>
                                <span class="cozy-single__nav-title"><?php echo esc_html( $next->post_title ); ?></span>
                            </div>
                            <i data-lucide="chevron-right" class="lucide"></i>
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
