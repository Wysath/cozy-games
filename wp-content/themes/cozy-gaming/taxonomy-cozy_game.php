<?php
/**
 * Template : Archive taxonomie « Jeux » (cozy_game).
 *
 * Affiche un en-tête riche (cover, plateformes, infos)
 * suivi de la grille des articles liés au jeu.
 *
 * @package CozyGaming
 * @since   3.2.0
 */

get_header();

$term      = get_queried_object();
$term_id   = $term->term_id ?? 0;
$game_data = function_exists( 'cozy_get_game_term_data' )
    ? cozy_get_game_term_data( $term_id )
    : array();

$cover         = ! empty( $game_data['cover'] ) ? $game_data['cover'] : null;
$platforms     = ! empty( $game_data['platforms'] ) && is_array( $game_data['platforms'] ) ? $game_data['platforms'] : array();
$all_platforms = function_exists( 'cozy_get_platforms' ) ? cozy_get_platforms() : array();

// Count only real articles (post type = post), not events
global $wp_query;
$article_count = (int) $wp_query->found_posts;
?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container">

        <!-- ── En-tête fiche jeu ── -->
        <header class="cozy-game-archive__header">

            <?php if ( $cover ) : ?>
                <div class="cozy-game-archive__cover">
                    <?php if ( ! empty( $cover['sizes']['medium_large'] ) ) : ?>
                        <img src="<?php echo esc_url( $cover['sizes']['medium_large'] ); ?>"
                             alt="<?php echo esc_attr( $term->name ); ?>">
                    <?php elseif ( ! empty( $cover['url'] ) ) : ?>
                        <img src="<?php echo esc_url( $cover['url'] ); ?>"
                             alt="<?php echo esc_attr( $term->name ); ?>">
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="cozy-game-archive__info">
                <h1 class="cozy-game-archive__title">
                    <i data-lucide="gamepad-2" class="lucide"></i>
                    <?php echo esc_html( $term->name ); ?>
                </h1>

                <?php if ( $term->description ) : ?>
                    <div class="cozy-game-archive__desc">
                        <?php echo wp_kses_post( $term->description ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $platforms ) ) : ?>
                    <div class="cozy-game-archive__platforms">
                        <?php foreach ( $platforms as $p ) :
                            $plabel = isset( $all_platforms[ $p ] ) ? $all_platforms[ $p ] : $p;
                        ?>
                            <span class="cozy-collection__platform-tag"><?php echo esc_html( $plabel ); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="cozy-game-archive__meta">
                    <?php if ( ! empty( $game_data['developer'] ) ) : ?>
                        <span class="cozy-game-archive__meta-item">
                            <i data-lucide="code-2" class="lucide"></i>
                            <?php echo esc_html( $game_data['developer'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $game_data['publisher'] ) ) : ?>
                        <span class="cozy-game-archive__meta-item">
                            <i data-lucide="building-2" class="lucide"></i>
                            <?php echo esc_html( $game_data['publisher'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $game_data['genre'] ) ) : ?>
                        <span class="cozy-game-archive__meta-item">
                            <i data-lucide="tag" class="lucide"></i>
                            <?php echo esc_html( $game_data['genre'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $game_data['release_year'] ) ) : ?>
                        <span class="cozy-game-archive__meta-item">
                            <i data-lucide="calendar" class="lucide"></i>
                            <?php echo esc_html( $game_data['release_year'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $game_data['players'] ) ) : ?>
                        <span class="cozy-game-archive__meta-item">
                            <i data-lucide="users" class="lucide"></i>
                            <?php echo esc_html( $game_data['players'] ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( ! empty( $game_data['playtime'] ) ) : ?>
                        <span class="cozy-game-archive__meta-item">
                            <i data-lucide="clock" class="lucide"></i>
                            <?php echo esc_html( $game_data['playtime'] ); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <span class="cozy-game-archive__count">
                    <i data-lucide="file-text" class="lucide"></i>
                    <?php echo esc_html( $article_count ); ?> article<?php echo $article_count > 1 ? 's' : ''; ?>
                </span>
            </div>

        </header>

        <!-- ── Grille des articles ── -->
        <?php if ( have_posts() ) : ?>

            <div class="cozy-posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content' ); ?>
                <?php endwhile; ?>
            </div>

            <?php cozy_pagination(); ?>

        <?php else : ?>
            <div class="cozy-game-archive__empty">
                <i data-lucide="search-x"></i>
                <p>Aucun article publié pour ce jeu.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
