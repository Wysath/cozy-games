<?php
/**
 * ============================================================================
 * MODULE : Page d'Accueil — Shortcodes & Blocs
 * ============================================================================
 *
 * Shortcodes dédiés à la page d'accueil :
 *   - [cozy_hero]             → Section héro (titre, sous-titre, CTA)
 *   - [cozy_upcoming_events]  → Prochains événements (timeline)
 *   - [cozy_latest_posts]     → Derniers articles (cartes)
 *   - [cozy_community_stats]  → Compteurs guilde
 *   - [cozy_values]           → Piliers / valeurs de la guilde
 *   - [cozy_join_cta]         → Bandeau inscription (visiteurs)
 *
 * @package CozyGaming
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * -----------------------------------------------
 * 1. HERO SECTION  (v2.0)
 * -----------------------------------------------
 * Stats dynamiques : Aventuriers, Quêtes, Grimoire, Setups.
 */

/**
 * Récupère les stats dynamiques pour la barre hero.
 */
function cozy_get_hero_stats() {
    // Aventuriers
    $users       = count_users();
    $aventuriers = $users['total_users'];

    // Quêtes à venir
    $quetes = new WP_Query( array(
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array( array(
            'key'     => '_cozy_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ) ),
    ) );
    $nb_quetes = $quetes->found_posts;
    wp_reset_postdata();

    // Grimoire (posts du blog)
    $grimoire    = wp_count_posts( 'post' );
    $nb_grimoire = (int) $grimoire->publish;

    // Setups
    $setups    = wp_count_posts( 'cozy_setup' );
    $nb_setups = (int) $setups->publish;

    return array(
        array( 'icon' => 'users',     'value' => $aventuriers, 'label' => 'Aventuriers' ),
        array( 'icon' => 'calendar',  'value' => $nb_quetes,   'label' => 'Quêtes à venir' ),
        array( 'icon' => 'book-open', 'value' => $nb_grimoire, 'label' => 'Grimoire' ),
        array( 'icon' => 'monitor',   'value' => $nb_setups,   'label' => 'Setups' ),
    );
}

/**
 * Shortcode principal [cozy_hero]
 */
function cozy_hero_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'title'    => 'Bienvenue à',
        'title_em' => 'Cozy Games',
        'subtitle' => 'La guilde des jeux cozy où chaque aventurier·ère trouve sa place.',
        'cta_text' => 'Découvrir les quêtes',
        'cta_url'  => '/evenements',
        'bg_image' => '',
    ), $atts, 'cozy_hero' );

    ob_start();
    ?>
    <section class="cozy-hero" <?php if ( $atts['bg_image'] ) : ?>style="--hero-bg: url('<?php echo esc_url( $atts['bg_image'] ); ?>')"<?php endif; ?>>

        <!-- Coins décoratifs -->
        <span class="cozy-hero__corner cozy-hero__corner--tl"></span>
        <span class="cozy-hero__corner cozy-hero__corner--tr"></span>
        <span class="cozy-hero__corner cozy-hero__corner--bl"></span>
        <span class="cozy-hero__corner cozy-hero__corner--br"></span>

        <!-- Lueurs d'ambiance -->
        <div class="cozy-hero__glow cozy-hero__glow--left"  aria-hidden="true"></div>
        <div class="cozy-hero__glow cozy-hero__glow--right" aria-hidden="true"></div>

        <div class="cozy-hero__inner">

            <!-- Badge statut -->
            <div class="cozy-hero__badge">
                <span class="cozy-hero__badge-dot"></span>
                Guilde ouverte
            </div>

            <!-- Titre -->
            <h1 class="cozy-hero__title">
                <?php echo esc_html( $atts['title'] ); ?>
                <em><?php echo esc_html( $atts['title_em'] ); ?></em>
            </h1>

            <!-- Sous-titre -->
            <p class="cozy-hero__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>

            <!-- CTAs -->
            <div class="cozy-hero__actions">
                <a href="<?php echo esc_url( $atts['cta_url'] ); ?>" class="cozy-hero__cta cozy-hero__cta--primary">
                    <i data-lucide="calendar"></i>
                    <?php echo esc_html( $atts['cta_text'] ); ?>
                </a>
                <?php if ( ! is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="cozy-hero__cta cozy-hero__cta--secondary">
                        <i data-lucide="user-plus"></i>
                        Rejoindre la guilde
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/mon-profil/' ) ); ?>" class="cozy-hero__cta cozy-hero__cta--secondary">
                        <i data-lucide="shield"></i>
                        Mon profil
                    </a>
                <?php endif; ?>
            </div>

            <!-- Ligne décorative -->
            <div class="cozy-hero__divider" aria-hidden="true">
                <span></span>
                <i data-lucide="leaf"></i>
                <span></span>
            </div>
        </div><!-- /.cozy-hero__inner -->
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_hero', 'cozy_hero_shortcode' );


/**
 * -----------------------------------------------
 * 2. ÉVÉNEMENTS À VENIR
 * -----------------------------------------------
 */
function cozy_shortcode_upcoming_events( $atts ) {
    $atts = shortcode_atts( array(
        'max'   => 4,
        'title' => 'Prochaines quêtes',
    ), $atts, 'cozy_upcoming_events' );

    $max = absint( $atts['max'] );
    if ( $max < 1 ) $max = 4;

    // Le CPT cozy_event doit être enregistré par le plugin Cozy Events
    if ( ! post_type_exists( 'cozy_event' ) ) {
        return '';
    }

    // Récupère les prochains événements via le plugin Cozy Events
    $events = get_posts( array(
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => $max,
        'orderby'        => 'meta_value',
        'meta_key'       => '_cozy_event_date',
        'order'          => 'ASC',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_cozy_event_date',
                'value'   => date( 'Y-m-d' ),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
            array(
                'key'     => '_cozy_event_date',
                'value'   => '',
                'compare' => '=',
            ),
            array(
                'key'     => '_cozy_event_date',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );

    ob_start();
    ?>
    <section class="cozy-home-section cozy-home-events" data-cozy-reveal>
        <div class="cozy-home-section__header">
            <h2 class="cozy-home-section__title">
                <i data-lucide="calendar"></i>
                <?php echo esc_html( $atts['title'] ); ?>
            </h2>
        </div>

        <?php if ( empty( $events ) ) : ?>
            <div class="cozy-home-events__empty">
                <i data-lucide="calendar-off" class="cozy-icon--xl"></i>
                <p>Aucun événement prévu pour le moment. Reviens vite !</p>
            </div>

        <?php elseif ( count( $events ) === 1 ) : ?>
            <div class="cozy-home-events__featured">
                <?php echo cozy_render_event_card( $events[0], 'featured' ); ?>
            </div>

        <?php else : ?>
            <div class="cozy-home-events__grid cozy-home-events__grid--<?php echo count( $events ); ?>">
                <?php
                foreach ( $events as $i => $event ) {
                    echo cozy_render_event_card( $event, $i === 0 ? 'next' : 'default' );
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="cozy-home-events__more">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'cozy_event' ) ); ?>" class="cozy-btn cozy-btn--outline">
                <i data-lucide="arrow-right"></i>
                Voir toutes les quêtes
            </a>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_upcoming_events', 'cozy_shortcode_upcoming_events' );


/**
 * Rend une carte événement pour la homepage.
 *
 * Utilise les metas du plugin Cozy Events (cozy_event CPT).
 *
 * @param WP_Post $event   Post de type cozy_event
 * @param string  $variant 'featured', 'next', ou 'default'
 * @return string HTML
 */
function cozy_render_event_card( $event, $variant = 'default' ) {
    $event_id   = is_object( $event ) ? $event->ID : 0;
    $title      = get_the_title( $event );
    $permalink  = get_permalink( $event );
    $thumb      = get_the_post_thumbnail_url( $event, 'medium_large' );
    $date       = get_post_meta( $event_id, '_cozy_event_date', true );
    $time       = get_post_meta( $event_id, '_cozy_event_time', true );
    $is_troc    = get_post_meta( $event_id, '_cozy_event_is_troc', true );
    $games      = get_the_terms( $event_id, 'cozy_game' );
    $types      = get_the_terms( $event_id, 'cozy_event_type' );

    // Places restantes (si la fonction du plugin existe)
    $places_left = function_exists( 'cozy_get_places_left' ) ? cozy_get_places_left( $event_id ) : -1;

    $classes = 'cozy-event-card cozy-event-card--' . esc_attr( $variant );

    ob_start();
    ?>
    <a href="<?php echo esc_url( $permalink ); ?>" class="<?php echo esc_attr( $classes ); ?>">
        <?php if ( $thumb ) : ?>
            <div class="cozy-event-card__thumb">
                <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
            </div>
        <?php endif; ?>

        <div class="cozy-event-card__body">
            <div class="cozy-event-card__meta">
                <?php if ( ! is_wp_error( $types ) && ! empty( $types ) ) : ?>
                    <span class="cozy-badge cozy-badge--type"><?php echo esc_html( $types[0]->name ); ?></span>
                <?php endif; ?>
                <?php if ( $is_troc ) : ?>
                    <span class="cozy-badge cozy-badge--troc">🔄 Troc</span>
                <?php endif; ?>
            </div>

            <h3 class="cozy-event-card__title"><?php echo esc_html( $title ); ?></h3>

            <?php if ( $date ) : ?>
                <p class="cozy-event-card__date">
                    <i data-lucide="calendar" class="lucide"></i>
                    <?php echo esc_html( date_i18n( 'j F Y', strtotime( $date ) ) ); ?>
                    <?php if ( $time ) : ?>
                        à <?php echo esc_html( $time ); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if ( ! is_wp_error( $games ) && ! empty( $games ) ) : ?>
                <p class="cozy-event-card__games">
                    <i data-lucide="gamepad-2" class="lucide"></i>
                    <?php echo esc_html( implode( ', ', wp_list_pluck( $games, 'name' ) ) ); ?>
                </p>
            <?php endif; ?>

            <?php if ( $places_left === 0 ) : ?>
                <span class="cozy-badge cozy-badge--full">Complet</span>
            <?php endif; ?>
        </div>
    </a>
    <?php
    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 3. DERNIERS ARTICLES
 * -----------------------------------------------
 */
function cozy_shortcode_latest_posts( $atts ) {
    $atts = shortcode_atts( array(
        'count' => 3,
        'title' => 'Derniers articles',
    ), $atts, 'cozy_latest_posts' );

    $count = absint( $atts['count'] );
    if ( $count < 1 ) $count = 3;

    $posts = get_posts( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    if ( empty( $posts ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="cozy-home-section cozy-home-posts" data-cozy-reveal>
        <div class="cozy-home-section__header">
            <h2 class="cozy-home-section__title">
                <i data-lucide="file-text"></i>
                <?php echo esc_html( $atts['title'] ); ?>
            </h2>
        </div>

        <div class="cozy-home-posts__grid">
            <?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
                <article class="cozy-post-card cozy-card">
                    <?php if ( has_post_thumbnail( $post ) ) : ?>
                        <a href="<?php echo get_permalink( $post ); ?>" class="cozy-card__thumbnail">
                            <?php echo get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                        </a>
                    <?php endif; ?>

                    <div class="cozy-card__body">
                        <div class="cozy-card__meta">
                            <i data-lucide="clock"></i>
                            <time datetime="<?php echo get_the_date( 'c', $post ); ?>">
                                <?php echo get_the_date( 'j M Y', $post ); ?>
                            </time>
                            <span class="cozy-card__meta-separator"></span>
                            <i data-lucide="user"></i>
                            <?php echo get_the_author_meta( 'display_name', $post->post_author ); ?>
                        </div>

                        <h3 class="cozy-card__title">
                            <a href="<?php echo get_permalink( $post ); ?>">
                                <?php echo get_the_title( $post ); ?>
                            </a>
                        </h3>

                        <?php if ( has_excerpt( $post ) || ! empty( $post->post_content ) ) : ?>
                            <p class="cozy-card__excerpt">
                                <?php echo wp_trim_words( get_the_excerpt( $post ), 18, '…' ); ?>
                            </p>
                        <?php endif; ?>

                        <div class="cozy-card__footer">
                            <div class="cozy-card__author">
                                <?php echo get_avatar( $post->post_author, 28 ); ?>
                                <?php echo get_the_author_meta( 'display_name', $post->post_author ); ?>
                            </div>
                            <span class="cozy-card__read-more">
                                Lire <i data-lucide="arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </article>
            <?php endforeach; wp_reset_postdata(); ?>
        </div>

        <div class="cozy-home-posts__more">
            <a href="<?php echo esc_url( home_url( '/tous-nos-articles/' ) ); ?>" class="cozy-btn cozy-btn--outline">
                <i data-lucide="arrow-right"></i>
                Voir tous les articles
            </a>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_latest_posts', 'cozy_shortcode_latest_posts' );


/**
 * -----------------------------------------------
 * 4. STATS COMMUNAUTÉ
 * -----------------------------------------------
 */
function cozy_shortcode_community_stats( $atts ) {
    $atts = shortcode_atts( array(
        'title' => '',
    ), $atts, 'cozy_community_stats' );

    // Nombre de membres
    $members = count_users();
    $member_count = $members['total_users'] ?? 0;

    // Événements à venir via le plugin Cozy Events
    $event_count = 0;
    if ( post_type_exists( 'cozy_event' ) ) {
        $upcoming = new WP_Query( array(
            'post_type'      => 'cozy_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => '_cozy_event_date',
                    'value'   => date( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
                array(
                    'key'     => '_cozy_event_date',
                    'value'   => '',
                    'compare' => '=',
                ),
                array(
                    'key'     => '_cozy_event_date',
                    'compare' => 'NOT EXISTS',
                ),
            ),
            'no_found_rows'  => true,
        ) );
        $event_count = $upcoming->post_count;
        wp_reset_postdata();
    }

    // Articles publiés
    $post_count = wp_count_posts( 'post' );
    $article_count = $post_count->publish ?? 0;

    // Setups partagés
    $setup_count_obj = wp_count_posts( 'cozy_setup' );
    $setup_count = $setup_count_obj->publish ?? 0;

    $stats = array(
        array(
            'icon'   => 'users',
            'number' => $member_count,
            'label'  => 'Aventuriers',
            'color'  => 'var(--cozy-amber)',
        ),
        array(
            'icon'   => 'calendar',
            'number' => $event_count,
            'label'  => 'Quêtes à venir',
            'color'  => 'var(--cozy-moss)',
        ),
        array(
            'icon'   => 'file-text',
            'number' => $article_count,
            'label'  => 'Grimoire',
            'color'  => 'var(--cozy-gold)',
        ),
        array(
            'icon'   => 'monitor',
            'number' => $setup_count,
            'label'  => 'Setups',
            'color'  => 'var(--cozy-ember)',
        ),
    );

    ob_start();
    ?>
    <section class="cozy-home-stats" data-cozy-reveal>
        <div class="cozy-home-stats__inner">
            <?php foreach ( $stats as $stat ) : ?>
                <div class="cozy-home-stats__item" style="--stat-color: <?php echo $stat['color']; ?>;">
                    <div class="cozy-home-stats__icon">
                        <i data-lucide="<?php echo esc_attr( $stat['icon'] ); ?>"></i>
                    </div>
                    <span class="cozy-home-stats__number" data-count="<?php echo esc_attr( $stat['number'] ); ?>">
                        0
                    </span>
                    <span class="cozy-home-stats__label"><?php echo esc_html( $stat['label'] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_community_stats', 'cozy_shortcode_community_stats' );


/**
 * -----------------------------------------------
 * 5. VALEURS DE LA GUILDE  [cozy_values]
 * -----------------------------------------------
 * Affiche les 4 piliers fondateurs de la guilde avec
 * icônes, titres et descriptions configurables.
 */
function cozy_shortcode_values( $atts ) {
    $atts = shortcode_atts( array(
        'label'    => 'Code de la guilde',
        'title'    => 'Une guilde fondée sur la <em>bienveillance</em>',
        'subtitle' => 'Chez Cozy Grove, chaque aventurier·ère est le bienvenu, quel que soit son niveau ou son style de jeu.',
    ), $atts, 'cozy_values' );

    $values = array(
        array(
            'icon'  => 'heart',
            'color' => 'var(--cozy-amber)',
            'title' => 'Bienveillance',
            'text'  => 'Respect, écoute et bonne humeur sont nos maîtres mots. Pas de toxicité ici.',
        ),
        array(
            'icon'  => 'users',
            'color' => 'var(--cozy-moss)',
            'title' => 'Inclusivité',
            'text'  => 'Ouvert à tous les profils de joueur·ses, du débutant au compétitif, sans jugement.',
        ),
        array(
            'icon'  => 'sparkles',
            'color' => 'var(--cozy-gold)',
            'title' => 'Fun & Cozy',
            'text'  => 'L\'objectif c\'est de passer un bon moment, en pyjama ou en LAN, toujours détendus.',
        ),
        array(
            'icon'  => 'shield-check',
            'color' => 'var(--cozy-ember)',
            'title' => 'Safe Space',
            'text'  => 'Un espace sécurisé avec une charte de bienveillance et des animateurs attentifs.',
        ),
    );

    ob_start();
    ?>
    <section class="cozy-home-values" data-cozy-reveal>
        <div class="cozy-home-values__header">
            <span class="cozy-home-values__label"><?php echo esc_html( $atts['label'] ); ?></span>
            <h2 class="cozy-home-values__title"><?php echo wp_kses_post( $atts['title'] ); ?></h2>
            <p class="cozy-home-values__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
        </div>
        <div class="cozy-home-values__grid">
            <?php foreach ( $values as $i => $val ) : ?>
                <div class="cozy-home-values__card" data-cozy-reveal data-cozy-delay="<?php echo ( $i + 1 ) * 100; ?>">
                    <div class="cozy-home-values__card-icon" style="--val-color: <?php echo esc_attr( $val['color'] ); ?>;">
                        <i data-lucide="<?php echo esc_attr( $val['icon'] ); ?>"></i>
                    </div>
                    <h3><?php echo esc_html( $val['title'] ); ?></h3>
                    <p><?php echo esc_html( $val['text'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_values', 'cozy_shortcode_values' );


/**
 * -----------------------------------------------
 * 6. BANDEAU CTA INSCRIPTION
 * -----------------------------------------------
 */
function cozy_shortcode_join_cta( $atts ) {
    // Masqué si déjà connecté
    if ( is_user_logged_in() ) {
        return '';
    }

    $atts = shortcode_atts( array(
        'title'    => 'Prêt·e à rejoindre l\'aventure ?',
        'text'     => 'Rejoins notre guilde et participe à des quêtes gaming dans une ambiance cozy.',
        'btn_text' => 'Rejoindre la guilde',
    ), $atts, 'cozy_join_cta' );

    ob_start();
    ?>
    <section class="cozy-home-cta" data-cozy-reveal>
        <div class="cozy-home-cta__inner">
            <div class="cozy-home-cta__content">
                <h2 class="cozy-home-cta__title"><?php echo esc_html( $atts['title'] ); ?></h2>
                <p class="cozy-home-cta__text"><?php echo esc_html( $atts['text'] ); ?></p>
            </div>
            <div class="cozy-home-cta__action">
                <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="cozy-btn cozy-btn--primary cozy-btn--lg">
                    <i data-lucide="sword"></i>
                    <?php echo esc_html( $atts['btn_text'] ); ?>
                </a>
            </div>
        </div>
        <div class="cozy-home-cta__bracket cozy-home-cta__bracket--tl"></div>
        <div class="cozy-home-cta__bracket cozy-home-cta__bracket--br"></div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_join_cta', 'cozy_shortcode_join_cta' );


/**
 * -----------------------------------------------
 * 7. APERÇU SETUPS — Mini-galerie homepage
 * -----------------------------------------------
 */

/**
 * Affiche un aperçu compact de la galerie de setups gaming
 * avec les 6 derniers setups publiés + lien vers la page complète.
 *
 * @return string HTML
 */
function cozy_homepage_setups_preview() {
    if ( ! post_type_exists( 'cozy_setup' ) ) {
        return '';
    }

    $setups = get_posts( array(
        'post_type'      => 'cozy_setup',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    if ( empty( $setups ) ) {
        return '';
    }

    ob_start();
    ?>
    <section class="cozy-home-section cozy-home-setups" data-cozy-reveal>
        <div class="cozy-home-section__header">
            <h2 class="cozy-home-section__title">
                <i data-lucide="monitor"></i>
                Galerie des setups
            </h2>
            <span class="cozy-home-section__label">par la guilde</span>
        </div>

        <div class="cozy-home-setups__grid">
            <?php foreach ( $setups as $setup ) :
                $thumb_url = get_the_post_thumbnail_url( $setup, 'medium_large' );
                if ( ! $thumb_url ) continue;
                $author_name = get_the_author_meta( 'display_name', $setup->post_author );
                $author_avatar = get_avatar( $setup->post_author, 28 );
            ?>
                <div class="cozy-home-setups__item" data-cozy-reveal>
                    <div class="cozy-home-setups__img-wrapper">
                        <img src="<?php echo esc_url( $thumb_url ); ?>"
                             alt="<?php echo esc_attr( get_the_title( $setup ) ); ?>"
                             loading="lazy">
                        <div class="cozy-home-setups__overlay">
                            <span class="cozy-home-setups__item-title"><?php echo esc_html( get_the_title( $setup ) ); ?></span>
                            <span class="cozy-home-setups__item-author">
                                <?php echo $author_avatar; ?>
                                <?php echo esc_html( $author_name ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        // Trouver la page qui contient le shortcode [cozy_setups]
        global $wpdb;
        $setups_page_id = $wpdb->get_var(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'page'
             AND post_status = 'publish'
             AND post_content LIKE '%[cozy_setups%'
             LIMIT 1"
        );
        ?>
        <?php if ( $setups_page_id ) : ?>
            <div class="cozy-home-setups__more">
                <a href="<?php echo esc_url( get_permalink( $setups_page_id ) ); ?>" class="cozy-btn cozy-btn--outline">
                    <i data-lucide="arrow-right"></i>
                    Voir tous les setups
                </a>
            </div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 8. ASSETS
 * -----------------------------------------------
 */
function cozy_homepage_enqueue_assets() {
    // Charger uniquement sur la page d'accueil
    if ( ! is_front_page() ) {
        return;
    }

    wp_enqueue_style(
        'cozy-hero',
        get_template_directory_uri() . '/assets/css/cozy-hero.css',
        array( 'cozy-main' ),
        '2.0.0'
    );

    wp_enqueue_style(
        'cozy-homepage',
        get_template_directory_uri() . '/assets/css/cozy-homepage.css',
        array( 'cozy-main', 'cozy-hero' ),
        '2.2.0'
    );

    wp_enqueue_script(
        'cozy-homepage',
        get_template_directory_uri() . '/assets/js/cozy-homepage.js',
        array(),
        '2.2.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_homepage_enqueue_assets' );
