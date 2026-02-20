<?php
/**
 * ============================================================================
 * MODULE : Widgets Dashboard personnalisés par rôle
 * ============================================================================
 *
 * Personnalise le tableau de bord WordPress selon le rôle :
 *   - Administrateur : stats globales, modération, santé du site
 *   - Éditeur         : contenus à relire, événements à venir, articles récents
 *   - Auteur          : ses articles, ses stats, guide rédaction
 *   - Animateur Cozy  : événements, inscriptions, setups à modérer
 *   - Abonné (membre) : réservations, profil gaming, prochains events
 *
 * @package CozyGaming
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ================================================================
   1. SUPPRIMER LES WIDGETS PAR DÉFAUT INUTILES
   ================================================================ */

function cozy_remove_default_dashboard_widgets() {
    $user = wp_get_current_user();

    // Widgets supprimés pour TOUS les rôles
    remove_meta_box( 'dashboard_primary',        'dashboard', 'side' );   // Actualités WordPress
    remove_meta_box( 'dashboard_secondary',      'dashboard', 'side' );
    remove_meta_box( 'dashboard_quick_press',    'dashboard', 'side' );   // Brouillon rapide
    remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );

    // Pour les non-admins : retirer les widgets techniques
    if ( ! in_array( 'administrator', $user->roles, true ) ) {
        remove_meta_box( 'dashboard_site_health',    'dashboard', 'normal' );
        remove_meta_box( 'dashboard_right_now',      'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity',       'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins',        'dashboard', 'normal' );
    }

    // Pour les abonnés et animateurs : retirer les widgets éditoriaux natifs
    if ( in_array( 'subscriber', $user->roles, true ) || in_array( 'animateur_cozy', $user->roles, true ) ) {
        remove_meta_box( 'dashboard_recent_drafts',  'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    }
}
add_action( 'wp_dashboard_setup', 'cozy_remove_default_dashboard_widgets', 999 );


/* ================================================================
   2. ENREGISTRER LES WIDGETS PERSONNALISÉS
   ================================================================ */

function cozy_register_dashboard_widgets() {
    $user  = wp_get_current_user();
    $roles = $user->roles;

    // ─── Administrateur ───────────────────────────────────────
    if ( in_array( 'administrator', $roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_admin_community_overview',
            '⚔️ Vue d\'ensemble — Cozy Grove',
            'cozy_widget_admin_community_overview'
        );
        wp_add_dashboard_widget(
            'cozy_admin_moderation',
            '🛡️ File de modération',
            'cozy_widget_admin_moderation'
        );
        wp_add_dashboard_widget(
            'cozy_admin_upcoming_events',
            '📅 Prochains événements',
            'cozy_widget_upcoming_events_admin'
        );
        wp_add_dashboard_widget(
            'cozy_admin_recent_registrations',
            '👥 Dernières inscriptions membres',
            'cozy_widget_admin_recent_registrations'
        );
    }

    // ─── Éditeur ──────────────────────────────────────────────
    if ( in_array( 'editor', $roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_editor_pending_content',
            '📝 Contenus en attente de relecture',
            'cozy_widget_editor_pending_content'
        );
        wp_add_dashboard_widget(
            'cozy_editor_recent_articles',
            '📰 Derniers articles publiés',
            'cozy_widget_editor_recent_articles'
        );
        wp_add_dashboard_widget(
            'cozy_editor_events_overview',
            '📅 Événements à venir',
            'cozy_widget_upcoming_events_admin'
        );
        wp_add_dashboard_widget(
            'cozy_editor_community_stats',
            '📊 Statistiques rapides',
            'cozy_widget_editor_community_stats'
        );
    }

    // ─── Auteur ───────────────────────────────────────────────
    if ( in_array( 'author', $roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_author_my_articles',
            '✍️ Mes articles',
            'cozy_widget_author_my_articles'
        );
        wp_add_dashboard_widget(
            'cozy_author_writing_guide',
            '📖 Guide de rédaction Cozy',
            'cozy_widget_author_writing_guide'
        );
        wp_add_dashboard_widget(
            'cozy_author_next_events',
            '📅 Prochains événements',
            'cozy_widget_member_upcoming_events'
        );
    }

    // ─── Animateur Cozy ───────────────────────────────────────
    if ( in_array( 'animateur_cozy', $roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_animateur_events',
            '📅 Mes événements à animer',
            'cozy_widget_animateur_events'
        );
        wp_add_dashboard_widget(
            'cozy_animateur_registrations',
            '👥 Dernières inscriptions aux événements',
            'cozy_widget_animateur_registrations'
        );
        wp_add_dashboard_widget(
            'cozy_animateur_setups_moderation',
            '🖥️ Setups à valider',
            'cozy_widget_animateur_setups_moderation'
        );
        wp_add_dashboard_widget(
            'cozy_animateur_charter_stats',
            '📜 Charte de bienveillance',
            'cozy_widget_animateur_charter_stats'
        );
    }

    // ─── Abonné (Membre) ─────────────────────────────────────
    if ( in_array( 'subscriber', $roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_member_welcome',
            '⚔️ Bienvenue à Cozy Grove !',
            'cozy_widget_member_welcome'
        );
        wp_add_dashboard_widget(
            'cozy_member_reservations',
            '🎟️ Mes réservations',
            'cozy_widget_member_reservations'
        );
        wp_add_dashboard_widget(
            'cozy_member_upcoming',
            '📅 Prochains événements',
            'cozy_widget_member_upcoming_events'
        );
        wp_add_dashboard_widget(
            'cozy_member_profile_completeness',
            '👤 Mon profil gaming',
            'cozy_widget_member_profile_completeness'
        );
    }
}
add_action( 'wp_dashboard_setup', 'cozy_register_dashboard_widgets' );


/* ================================================================
   3. WIDGETS ADMINISTRATEUR
   ================================================================ */

/**
 * Vue d'ensemble communautaire (admin)
 */
function cozy_widget_admin_community_overview() {
    $users       = count_users();
    $total_users = $users['total_users'];
    $role_counts = $users['avail_roles'] ?? [];

    // Événements à venir
    $event_count = 0;
    if ( post_type_exists( 'cozy_event' ) ) {
        $upcoming = new WP_Query( [
            'post_type'      => 'cozy_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [ [
                'key'     => '_cozy_event_date',
                'value'   => date( 'Y-m-d' ),
                'compare' => '>=',
                'type'    => 'DATE',
            ] ],
            'no_found_rows' => true,
        ] );
        $event_count = $upcoming->post_count;
        wp_reset_postdata();
    }

    // Articles publiés
    $posts_count = wp_count_posts( 'post' );
    $articles    = $posts_count->publish ?? 0;

    // Setups
    $setups_count = wp_count_posts( 'cozy_setup' );
    $setups_pub   = $setups_count->publish ?? 0;
    $setups_pend  = $setups_count->pending ?? 0;

    // Total inscriptions événements
    $total_registrations = 0;
    if ( post_type_exists( 'cozy_event' ) ) {
        $all_events = get_posts( [
            'post_type'      => 'cozy_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );
        foreach ( $all_events as $eid ) {
            $regs = get_post_meta( $eid, '_cozy_event_registrants', true );
            if ( is_array( $regs ) ) {
                $total_registrations += count( $regs );
            }
        }
    }

    ?>
    <div class="cozy-dash-stats">
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number"><?php echo esc_html( $total_users ); ?></span>
            <span class="cozy-dash-stat__label">Membres</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number"><?php echo esc_html( $event_count ); ?></span>
            <span class="cozy-dash-stat__label">Événements à venir</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number"><?php echo esc_html( $total_registrations ); ?></span>
            <span class="cozy-dash-stat__label">Inscriptions totales</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number"><?php echo esc_html( $articles ); ?></span>
            <span class="cozy-dash-stat__label">Articles</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number"><?php echo esc_html( $setups_pub ); ?></span>
            <span class="cozy-dash-stat__label">Setups publiés</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number" style="color: <?php echo $setups_pend > 0 ? '#C8813A' : '#4A6649'; ?>;"><?php echo esc_html( $setups_pend ); ?></span>
            <span class="cozy-dash-stat__label">Setups en attente</span>
        </div>
    </div>

    <h4>Répartition des rôles</h4>
    <ul class="cozy-dash-roles">
        <?php
        $role_labels = [
            'administrator'       => '🔑 Administrateurs',
            'editor'              => '📝 Éditeurs',
            'author'              => '✍️ Auteurs',
            'animateur_cozy'      => '🎯 Animateurs Cozy',
            'gestionnaire_setups' => '🖥️ Gestionnaires Setups',
            'subscriber'          => '👤 Membres (abonnés)',
            'contributor'         => '📄 Contributeurs',
        ];
        foreach ( $role_labels as $role_slug => $label ) {
            $count = $role_counts[ $role_slug ] ?? 0;
            if ( $count > 0 ) {
                echo '<li>' . esc_html( $label ) . ' : <strong>' . esc_html( $count ) . '</strong></li>';
            }
        }
        ?>
    </ul>
    <?php
}


/**
 * File de modération (admin) — Setups + articles pending
 */
function cozy_widget_admin_moderation() {
    // Setups en attente
    $pending_setups = new WP_Query( [
        'post_type'      => 'cozy_setup',
        'post_status'    => 'pending',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    // Articles en attente
    $pending_posts = new WP_Query( [
        'post_type'      => 'post',
        'post_status'    => 'pending',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    // Commentaires en attente
    $pending_comments = wp_count_comments();
    $comments_pending = $pending_comments->moderated ?? 0;

    ?>
    <?php if ( $comments_pending > 0 ) : ?>
        <div class="cozy-dash-moderation-section">
            <p>💬 <a href="<?php echo esc_url( admin_url( 'edit-comments.php?comment_status=moderated' ) ); ?>">
                <strong><?php echo esc_html( $comments_pending ); ?></strong> commentaire(s) en attente
            </a></p>
        </div>
    <?php endif; ?>

    <div class="cozy-dash-moderation-section">
        <h4 class="cozy-dash-moderation-section__title">🖥️ Setups en attente (<?php echo $pending_setups->found_posts; ?>)</h4>
        <?php if ( $pending_setups->have_posts() ) : ?>
            <?php while ( $pending_setups->have_posts() ) : $pending_setups->the_post(); ?>
                <div class="cozy-dash-mod-item">
                    <div>
                        <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php the_title(); ?></a>
                        <span class="cozy-dash-mod-item__author">par <?php the_author(); ?> — <?php echo get_the_date( 'j M Y' ); ?></span>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
            <?php if ( $pending_setups->found_posts > 5 ) : ?>
                <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cozy_setup&post_status=pending' ) ); ?>">Voir tous →</a></p>
            <?php endif; ?>
        <?php else : ?>
            <p class="cozy-dash-empty">✅ Aucun setup en attente.</p>
        <?php endif; ?>
    </div>

    <div class="cozy-dash-moderation-section">
        <h4 class="cozy-dash-moderation-section__title">📝 Articles en attente (<?php echo $pending_posts->found_posts; ?>)</h4>
        <?php if ( $pending_posts->have_posts() ) : ?>
            <?php while ( $pending_posts->have_posts() ) : $pending_posts->the_post(); ?>
                <div class="cozy-dash-mod-item">
                    <div>
                        <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php the_title(); ?></a>
                        <span class="cozy-dash-mod-item__author">par <?php the_author(); ?> — <?php echo get_the_date( 'j M Y' ); ?></span>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <p class="cozy-dash-empty">✅ Aucun article en attente.</p>
        <?php endif; ?>
    </div>
    <?php
}


/**
 * Prochains événements — version admin / éditeur
 */
function cozy_widget_upcoming_events_admin() {
    if ( ! post_type_exists( 'cozy_event' ) ) {
        echo '<p class="cozy-dash-empty">Le plugin Cozy Events n\'est pas actif.</p>';
        return;
    }

    $events = get_posts( [
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'meta_value',
        'meta_key'       => '_cozy_event_date',
        'order'          => 'ASC',
        'meta_query'     => [ [
            'key'     => '_cozy_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ] ],
    ] );

    if ( empty( $events ) ) {
        echo '<p class="cozy-dash-empty">Aucun événement à venir.</p>';
        return;
    }

    foreach ( $events as $event ) {
        $date       = get_post_meta( $event->ID, '_cozy_event_date', true );
        $time       = get_post_meta( $event->ID, '_cozy_event_time', true );
        $places     = (int) get_post_meta( $event->ID, '_cozy_event_places', true );
        $regs       = get_post_meta( $event->ID, '_cozy_event_registrants', true );
        $reg_count  = is_array( $regs ) ? count( $regs ) : 0;
        $games      = get_the_terms( $event->ID, 'cozy_game' );
        $game_names = ( ! is_wp_error( $games ) && ! empty( $games ) ) ? implode( ', ', wp_list_pluck( $games, 'name' ) ) : '';
        ?>
        <div class="cozy-dash-event">
            <div class="cozy-dash-event__info">
                <a href="<?php echo esc_url( get_edit_post_link( $event->ID ) ); ?>"><?php echo esc_html( get_the_title( $event ) ); ?></a>
                <div class="cozy-dash-event__meta">
                    📅 <?php echo $date ? esc_html( date_i18n( 'j M Y', strtotime( $date ) ) ) : 'Date non définie'; ?>
                    <?php if ( $time ) echo '· ' . esc_html( $time ); ?>
                    <?php if ( $game_names ) echo '· 🎮 ' . esc_html( $game_names ); ?>
                </div>
            </div>
            <div class="cozy-dash-event__registrations">
                <strong><?php echo esc_html( $reg_count ); ?></strong>
                <?php if ( $places > 0 ) : ?>
                    /<?php echo esc_html( $places ); ?>
                <?php endif; ?>
                <br><small>inscrit(s)</small>
            </div>
        </div>
        <?php
    }

    echo '<p style="margin-top:12px;"><a href="' . esc_url( admin_url( 'edit.php?post_type=cozy_event' ) ) . '">Gérer les événements →</a></p>';
}


/**
 * Dernières inscriptions membres (admin)
 */
function cozy_widget_admin_recent_registrations() {
    $recent_users = get_users( [
        'number'  => 8,
        'orderby' => 'registered',
        'order'   => 'DESC',
    ] );

    if ( empty( $recent_users ) ) {
        echo '<p class="cozy-dash-empty">Aucun membre inscrit récemment.</p>';
        return;
    }

    foreach ( $recent_users as $u ) {
        $role_display = implode( ', ', $u->roles );
        ?>
        <div class="cozy-dash-user">
            <?php echo get_avatar( $u->ID, 32 ); ?>
            <div class="cozy-dash-user__info">
                <span class="cozy-dash-user__name"><?php echo esc_html( $u->display_name ); ?></span>
                <span class="cozy-dash-user__date">Inscrit le <?php echo esc_html( date_i18n( 'j M Y', strtotime( $u->user_registered ) ) ); ?></span>
            </div>
            <span class="cozy-dash-user__role"><?php echo esc_html( $role_display ); ?></span>
        </div>
        <?php
    }

    echo '<p style="margin-top:10px;"><a href="' . esc_url( admin_url( 'users.php' ) ) . '">Voir tous les membres →</a></p>';
}


/* ================================================================
   4. WIDGETS ÉDITEUR
   ================================================================ */

/**
 * Contenus en attente de relecture (éditeur)
 */
function cozy_widget_editor_pending_content() {
    $pending = new WP_Query( [
        'post_type'      => [ 'post', 'cozy_event', 'cozy_setup' ],
        'post_status'    => [ 'pending', 'draft' ],
        'posts_per_page' => 10,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ] );

    if ( ! $pending->have_posts() ) {
        echo '<p class="cozy-dash-empty">✅ Aucun contenu en attente. Tout est à jour !</p>';
        return;
    }

    $type_labels = [
        'post'        => '📰 Article',
        'cozy_event'  => '📅 Événement',
        'cozy_setup'  => '🖥️ Setup',
    ];

    while ( $pending->have_posts() ) : $pending->the_post();
        $pt = get_post_type();
        ?>
        <div class="cozy-dash-pending">
            <span class="cozy-dash-pending__type"><?php echo $type_labels[ $pt ] ?? $pt; ?></span>
            <span class="cozy-dash-badge"><?php echo esc_html( get_post_status() === 'pending' ? 'En relecture' : 'Brouillon' ); ?></span>
            <br>
            <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php the_title(); ?></a>
            <div class="cozy-dash-pending__meta">par <?php the_author(); ?> — modifié le <?php echo get_the_modified_date( 'j M Y' ); ?></div>
        </div>
    <?php endwhile;
    wp_reset_postdata();
}


/**
 * Derniers articles publiés (éditeur)
 */
function cozy_widget_editor_recent_articles() {
    $posts = get_posts( [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    if ( empty( $posts ) ) {
        echo '<p class="cozy-dash-empty">Aucun article publié.</p>';
        return;
    }

    foreach ( $posts as $p ) {
        $comments = wp_count_comments( $p->ID );
        ?>
        <div class="cozy-dash-mod-item">
            <div>
                <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
                <span class="cozy-dash-mod-item__author">
                    <?php echo esc_html( get_the_date( 'j M Y', $p ) ); ?> — 
                    💬 <?php echo esc_html( $comments->approved ?? 0 ); ?> commentaire(s)
                </span>
            </div>
        </div>
        <?php
    }
}


/**
 * Statistiques rapides (éditeur)
 */
function cozy_widget_editor_community_stats() {
    $users  = count_users();
    $posts  = wp_count_posts( 'post' );
    $setups = wp_count_posts( 'cozy_setup' );

    echo '<div class="cozy-dash-stats">';
    echo '<div class="cozy-dash-stat"><span class="cozy-dash-stat__number">' . esc_html( $users['total_users'] ) . '</span><span class="cozy-dash-stat__label">Membres</span></div>';
    echo '<div class="cozy-dash-stat"><span class="cozy-dash-stat__number">' . esc_html( $posts->publish ?? 0 ) . '</span><span class="cozy-dash-stat__label">Articles publiés</span></div>';
    echo '<div class="cozy-dash-stat"><span class="cozy-dash-stat__number">' . esc_html( $setups->publish ?? 0 ) . '</span><span class="cozy-dash-stat__label">Setups</span></div>';
    echo '<div class="cozy-dash-stat"><span class="cozy-dash-stat__number">' . esc_html( $setups->pending ?? 0 ) . '</span><span class="cozy-dash-stat__label">Setups à valider</span></div>';
    echo '</div>';
}


/* ================================================================
   5. WIDGETS AUTEUR
   ================================================================ */

/**
 * Mes articles (auteur)
 */
function cozy_widget_author_my_articles() {
    $user_id = get_current_user_id();

    $my_posts = new WP_Query( [
        'post_type'      => 'post',
        'author'         => $user_id,
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => [ 'publish', 'pending', 'draft' ],
    ] );

    // Stats
    $total_published = count( get_posts( [
        'post_type' => 'post', 'author' => $user_id,
        'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids',
    ] ) );
    $total_pending = count( get_posts( [
        'post_type' => 'post', 'author' => $user_id,
        'post_status' => 'pending', 'posts_per_page' => -1, 'fields' => 'ids',
    ] ) );
    $total_draft = count( get_posts( [
        'post_type' => 'post', 'author' => $user_id,
        'post_status' => 'draft', 'posts_per_page' => -1, 'fields' => 'ids',
    ] ) );

    ?>
    <div class="cozy-dash-stats" style="margin-bottom: 16px;">
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number"><?php echo esc_html( $total_published ); ?></span>
            <span class="cozy-dash-stat__label">Publiés</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number" style="color: #C8813A;"><?php echo esc_html( $total_pending ); ?></span>
            <span class="cozy-dash-stat__label">En relecture</span>
        </div>
        <div class="cozy-dash-stat">
            <span class="cozy-dash-stat__number" style="color: #6b7280;"><?php echo esc_html( $total_draft ); ?></span>
            <span class="cozy-dash-stat__label">Brouillons</span>
        </div>
    </div>

    <?php if ( $my_posts->have_posts() ) : ?>
        <h4 class="cozy-dash-moderation-section__title">Derniers articles</h4>
        <?php while ( $my_posts->have_posts() ) : $my_posts->the_post();
            $status_label = [
                'publish' => '✅ Publié',
                'pending' => '⏳ En relecture',
                'draft'   => '📝 Brouillon',
            ];
            ?>
            <div class="cozy-dash-mod-item">
                <div>
                    <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php the_title(); ?></a>
                    <span class="cozy-dash-mod-item__author"><?php echo $status_label[ get_post_status() ] ?? get_post_status(); ?> — <?php echo get_the_date( 'j M Y' ); ?></span>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    <?php else : ?>
        <p class="cozy-dash-empty">Tu n'as pas encore écrit d'article.</p>
    <?php endif; ?>

    <p style="margin-top: 12px;">
        <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="button button-primary">✍️ Écrire un nouvel article</a>
    </p>
    <?php
}


/**
 * Guide de rédaction (auteur)
 */
function cozy_widget_author_writing_guide() {
    ?>
    <div class="cozy-dash-guide">
        <h4>📝 Rappels pour la rédaction</h4>
        <ul>
            <li>🎮 <strong>Fiche jeu :</strong> Remplis les champs ACF (nom, plateformes, etc.) sous ton article</li>
            <li>⭐ <strong>Notes :</strong> Évalue chaque critère de 1 à 5 (gameplay, DA, bande-son…)</li>
            <li>📝 <strong>Verdict :</strong> Résume ton avis + points forts &amp; faibles</li>
            <li>🏷️ <strong>Type d'article :</strong> Sélectionne le bon type (Test, Guide, Coup de cœur…)</li>
            <li>🖼️ <strong>Image à la une :</strong> Ajoute toujours une image pour la carte</li>
            <li>☕ <strong>Ton :</strong> Bienveillant et accessible, c'est l'esprit Cozy !</li>
        </ul>
    </div>
    <?php
}


/* ================================================================
   6. WIDGETS ANIMATEUR COZY
   ================================================================ */

/**
 * Événements à animer (animateur)
 */
function cozy_widget_animateur_events() {
    if ( ! post_type_exists( 'cozy_event' ) ) {
        echo '<p class="cozy-dash-empty">Le plugin Cozy Events n\'est pas actif.</p>';
        return;
    }

    $events = get_posts( [
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'orderby'        => 'meta_value',
        'meta_key'       => '_cozy_event_date',
        'order'          => 'ASC',
        'meta_query'     => [ [
            'key'     => '_cozy_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ] ],
    ] );

    if ( empty( $events ) ) {
        echo '<p class="cozy-dash-empty">Aucun événement prévu prochainement.</p>';
        return;
    }

    foreach ( $events as $event ) {
        $date       = get_post_meta( $event->ID, '_cozy_event_date', true );
        $time       = get_post_meta( $event->ID, '_cozy_event_time', true );
        $places     = (int) get_post_meta( $event->ID, '_cozy_event_places', true );
        $is_troc    = get_post_meta( $event->ID, '_cozy_event_is_troc', true );
        $regs       = get_post_meta( $event->ID, '_cozy_event_registrants', true );
        $reg_count  = is_array( $regs ) ? count( $regs ) : 0;
        $games      = get_the_terms( $event->ID, 'cozy_game' );
        $places_left = function_exists( 'cozy_get_places_left' ) ? cozy_get_places_left( $event->ID ) : -1;
        ?>
        <div class="cozy-dash-anim-event">
            <div class="cozy-dash-anim-event__title"><?php echo esc_html( get_the_title( $event ) ); ?></div>
            <div class="cozy-dash-anim-event__details">
                <span>📅 <?php echo $date ? esc_html( date_i18n( 'l j F Y', strtotime( $date ) ) ) : 'Date à définir'; ?></span>
                <?php if ( $time ) : ?><span>🕐 <?php echo esc_html( $time ); ?></span><?php endif; ?>
                <?php if ( ! is_wp_error( $games ) && ! empty( $games ) ) : ?>
                    <span>🎮 <?php echo esc_html( implode( ', ', wp_list_pluck( $games, 'name' ) ) ); ?></span>
                <?php endif; ?>
            </div>
            <div class="cozy-dash-anim-event__badges">
                <span>👥 <?php echo esc_html( $reg_count ); ?> inscrit(s)
                    <?php if ( $places > 0 ) echo '/ ' . esc_html( $places ) . ' places'; ?>
                </span>
                <?php if ( $places_left === 0 ) : ?>
                    <span class="cozy-dash-badge cozy-dash-badge--full">Complet</span>
                <?php else : ?>
                    <span class="cozy-dash-badge cozy-dash-badge--open">Ouvert</span>
                <?php endif; ?>
                <?php if ( $is_troc ) : ?>
                    <span class="cozy-dash-badge cozy-dash-badge--troc">🔄 Troc</span>
                <?php endif; ?>
            </div>

            <?php
            // Liste des inscrits (résumé dépliable)
            if ( is_array( $regs ) && ! empty( $regs ) ) {
                echo '<details class="cozy-dash-anim-event__details-toggle"><summary>Voir les inscrits (' . count( $regs ) . ')</summary><ul class="cozy-dash-anim-event__registrants">';
                foreach ( $regs as $reg ) {
                    $u = get_user_by( 'id', $reg['user_id'] );
                    if ( $u ) {
                        echo '<li>' . esc_html( $u->display_name );
                        if ( ! empty( $reg['troc_note'] ) ) {
                            echo ' — <em>' . esc_html( $reg['troc_note'] ) . '</em>';
                        }
                        echo '</li>';
                    }
                }
                echo '</ul></details>';
            }
            ?>
        </div>
        <?php
    }
}


/**
 * Dernières inscriptions aux événements (animateur)
 */
function cozy_widget_animateur_registrations() {
    if ( ! post_type_exists( 'cozy_event' ) ) {
        echo '<p class="cozy-dash-empty">Le plugin Cozy Events n\'est pas actif.</p>';
        return;
    }

    // Récupérer les événements à venir avec des inscrits
    $events = get_posts( [
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [ [
            'key'     => '_cozy_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ] ],
    ] );

    // Collecter les dernières inscriptions avec timestamp
    $recent_registrations = [];
    foreach ( $events as $event ) {
        $regs = get_post_meta( $event->ID, '_cozy_event_registrants', true );
        if ( ! is_array( $regs ) ) continue;
        foreach ( $regs as $reg ) {
            $recent_registrations[] = [
                'user_id'    => $reg['user_id'],
                'event_id'   => $event->ID,
                'event_name' => get_the_title( $event ),
                'date'       => $reg['date'] ?? '',
                'troc_note'  => $reg['troc_note'] ?? '',
            ];
        }
    }

    // Trier par date DESC
    usort( $recent_registrations, function( $a, $b ) {
        return strcmp( $b['date'], $a['date'] );
    } );

    // N'afficher que les 10 dernières
    $recent_registrations = array_slice( $recent_registrations, 0, 10 );

    if ( empty( $recent_registrations ) ) {
        echo '<p class="cozy-dash-empty">Aucune inscription récente.</p>';
        return;
    }

    foreach ( $recent_registrations as $reg ) {
        $u = get_user_by( 'id', $reg['user_id'] );
        if ( ! $u ) continue;
        ?>
        <div class="cozy-dash-user">
            <?php echo get_avatar( $u->ID, 28 ); ?>
            <div class="cozy-dash-user__info">
                <span class="cozy-dash-user__name"><?php echo esc_html( $u->display_name ); ?></span>
                <span class="cozy-dash-user__date">
                    → <?php echo esc_html( $reg['event_name'] ); ?>
                    <?php if ( $reg['date'] ) echo ' · ' . esc_html( date_i18n( 'j M Y', strtotime( $reg['date'] ) ) ); ?>
                </span>
            </div>
        </div>
        <?php
    }
}


/**
 * Setups à valider (animateur)
 */
function cozy_widget_animateur_setups_moderation() {
    $pending = new WP_Query( [
        'post_type'      => 'cozy_setup',
        'post_status'    => 'pending',
        'posts_per_page' => 10,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    $count = $pending->found_posts;

    if ( ! $pending->have_posts() ) {
        echo '<p class="cozy-dash-empty">✅ Aucun setup à valider. La guilde est au top !</p>';
        return;
    }

    echo '<p style="margin-bottom: 12px;"><strong style="color: #C8813A;">' . esc_html( $count ) . '</strong> setup(s) en attente de validation :</p>';

    while ( $pending->have_posts() ) : $pending->the_post();
        $thumb = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
        ?>
        <div class="cozy-dash-user" style="padding: 8px 0;">
            <?php if ( $thumb ) : ?>
                <img src="<?php echo esc_url( $thumb ); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
            <?php endif; ?>
            <div class="cozy-dash-user__info">
                <span class="cozy-dash-user__name"><?php the_title(); ?></span>
                <span class="cozy-dash-user__date">par <?php the_author(); ?> — <?php echo get_the_date( 'j M Y' ); ?></span>
            </div>
            <a href="<?php echo esc_url( get_edit_post_link() ); ?>" class="button button-small">Vérifier</a>
        </div>
    <?php endwhile;
    wp_reset_postdata();

    echo '<p style="margin-top: 10px;"><a href="' . esc_url( admin_url( 'edit.php?post_type=cozy_setup&post_status=pending' ) ) . '">Voir tous les setups en attente →</a></p>';
}


/**
 * Statistiques de la charte (animateur)
 */
function cozy_widget_animateur_charter_stats() {
    // Compter les utilisateurs ayant accepté la charte
    $users_with_charter = get_users( [
        'meta_key'     => 'cozy_charter_accepted',
        'meta_value'   => '1',
        'meta_compare' => '=',
        'count_total'  => true,
        'fields'       => 'ID',
    ] );
    $charter_count = count( $users_with_charter );

    $total_users = count_users();
    $total       = $total_users['total_users'];
    $percentage  = $total > 0 ? round( ( $charter_count / $total ) * 100 ) : 0;

    ?>
    <div class="cozy-dash-charter">
        <div class="cozy-dash-charter__number"><?php echo esc_html( $charter_count ); ?> / <?php echo esc_html( $total ); ?></div>
        <p>membres ont accepté la charte de bienveillance</p>
        <div class="cozy-dash-charter__bar">
            <div class="cozy-dash-charter__fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
        </div>
        <p style="font-size: 13px; color: #6b7280;"><strong><?php echo esc_html( $percentage ); ?>%</strong> de la guilde</p>
    </div>
    <?php
}


/* ================================================================
   7. WIDGETS ABONNÉ (MEMBRE)
   ================================================================ */

/**
 * Message de bienvenue (membre)
 */
function cozy_widget_member_welcome() {
    $user = wp_get_current_user();
    $charter_accepted = function_exists( 'cozy_has_accepted_charter' ) && cozy_has_accepted_charter( $user->ID );

    ?>
    <div class="cozy-dash-welcome">
        <h3>👋 Salut <?php echo esc_html( $user->display_name ); ?> !</h3>
        <p>Bienvenue dans l'espace membre de <strong>Cozy Grove</strong>.</p>
        <p>Explore les quêtes, partage ton setup et rejoins la guilde !</p>

        <?php if ( $charter_accepted ) : ?>
            <div class="cozy-dash-welcome__charter cozy-dash-welcome__charter--ok">
                ✅ Charte de bienveillance acceptée — tu peux t'inscrire aux événements
            </div>
        <?php else : ?>
            <div class="cozy-dash-welcome__charter cozy-dash-welcome__charter--pending">
                📜 Tu devras accepter la charte de bienveillance pour t'inscrire à ton premier événement
            </div>
        <?php endif; ?>

        <div class="cozy-dash-welcome__links">
            <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>">📅 Événements</a>
            <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">👤 Mon profil</a>
            <a href="<?php echo esc_url( home_url() ); ?>">🏠 Voir le site</a>
        </div>
    </div>
    <?php
}


/**
 * Mes réservations (membre)
 */
function cozy_widget_member_reservations() {
    $user_id = get_current_user_id();

    if ( ! post_type_exists( 'cozy_event' ) ) {
        echo '<p class="cozy-dash-empty">Le module événements n\'est pas disponible.</p>';
        return;
    }

    $today      = date( 'Y-m-d' );
    $all_events = get_posts( [
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [ [ 'key' => '_cozy_event_registrants', 'compare' => 'EXISTS' ] ],
    ] );

    $my_upcoming = [];
    foreach ( $all_events as $event ) {
        if ( ! function_exists( 'cozy_is_registered' ) || ! cozy_is_registered( $event->ID, $user_id ) ) {
            continue;
        }
        $date = get_post_meta( $event->ID, '_cozy_event_date', true );
        if ( empty( $date ) || $date >= $today ) {
            $my_upcoming[] = $event;
        }
    }

    // Trier par date
    usort( $my_upcoming, function( $a, $b ) {
        $da = get_post_meta( $a->ID, '_cozy_event_date', true ) ?: '9999-12-31';
        $db = get_post_meta( $b->ID, '_cozy_event_date', true ) ?: '9999-12-31';
        return strcmp( $da, $db );
    } );

    if ( empty( $my_upcoming ) ) {
        echo '<div style="text-align: center; padding: 16px;">';
        echo '<p style="color: #9ca3af;">Tu n\'es inscrit·e à aucun événement pour le moment.</p>';
        echo '<a href="' . esc_url( home_url( '/events/' ) ) . '" class="button button-primary">Découvrir les quêtes</a>';
        echo '</div>';
        return;
    }

    echo '<p style="margin-bottom: 10px;">Tu as <strong>' . count( $my_upcoming ) . '</strong> réservation(s) à venir :</p>';

    foreach ( array_slice( $my_upcoming, 0, 5 ) as $event ) {
        $date = get_post_meta( $event->ID, '_cozy_event_date', true );
        $time = get_post_meta( $event->ID, '_cozy_event_time', true );
        $link = get_post_meta( $event->ID, '_cozy_event_link', true );
        ?>
        <div class="cozy-dash-anim-event" style="margin-bottom: 8px;">
            <div class="cozy-dash-anim-event__title"><?php echo esc_html( get_the_title( $event ) ); ?></div>
            <div class="cozy-dash-anim-event__details">
                📅 <?php echo $date ? esc_html( date_i18n( 'l j F Y', strtotime( $date ) ) ) : ''; ?>
                <?php if ( $time ) echo ' · 🕐 ' . esc_html( $time ); ?>
                <?php if ( $link ) : ?>
                    · <a href="<?php echo esc_url( $link ); ?>" target="_blank" style="color: #C8813A;">🔗 Rejoindre</a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}


/**
 * Prochaines quêtes de la guilde (membre + auteur)
 */
function cozy_widget_member_upcoming_events() {
    if ( ! post_type_exists( 'cozy_event' ) ) {
        echo '<p class="cozy-dash-empty">Le module événements n\'est pas disponible.</p>';
        return;
    }

    $events = get_posts( [
        'post_type'      => 'cozy_event',
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'orderby'        => 'meta_value',
        'meta_key'       => '_cozy_event_date',
        'order'          => 'ASC',
        'meta_query'     => [ [
            'key'     => '_cozy_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ] ],
    ] );

    if ( empty( $events ) ) {
        echo '<p class="cozy-dash-empty">Aucun événement prévu prochainement. Reviens vite ! 🌱</p>';
        return;
    }

    $user_id = get_current_user_id();

    foreach ( $events as $event ) {
        $date       = get_post_meta( $event->ID, '_cozy_event_date', true );
        $time       = get_post_meta( $event->ID, '_cozy_event_time', true );
        $is_reg     = function_exists( 'cozy_is_registered' ) && cozy_is_registered( $event->ID, $user_id );
        $places_left = function_exists( 'cozy_get_places_left' ) ? cozy_get_places_left( $event->ID ) : -1;
        ?>
        <div class="cozy-dash-event">
            <div class="cozy-dash-event__info">
                <a href="<?php echo esc_url( get_permalink( $event ) ); ?>"><?php echo esc_html( get_the_title( $event ) ); ?></a>
                <div class="cozy-dash-event__meta">
                    📅 <?php echo $date ? esc_html( date_i18n( 'j M Y', strtotime( $date ) ) ) : ''; ?>
                    <?php if ( $time ) echo '· ' . esc_html( $time ); ?>
                </div>
            </div>
            <div style="text-align: right; font-size: 12px;">
                <?php if ( $is_reg ) : ?>
                    <span class="cozy-dash-badge cozy-dash-badge--open">✅ Inscrit·e</span>
                <?php elseif ( $places_left === 0 ) : ?>
                    <span class="cozy-dash-badge cozy-dash-badge--full">Complet</span>
                <?php else : ?>
                    <a href="<?php echo esc_url( get_permalink( $event ) ); ?>" style="color: #C8813A; font-weight: 500;">S'inscrire →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    echo '<p style="margin-top:10px;"><a href="' . esc_url( home_url( '/events/' ) ) . '">Voir toutes les quêtes →</a></p>';
}


/**
 * Complétude du profil gaming (membre)
 */
function cozy_widget_member_profile_completeness() {
    $user_id = get_current_user_id();

    // Vérifier la complétude
    $checks = [];

    // Discord
    $discord = get_user_meta( $user_id, 'cozy_discord', true );
    $checks['discord'] = [
        'label' => '💬 Discord',
        'done'  => ! empty( $discord ),
        'value' => $discord,
    ];

    // Twitch
    $twitch = get_user_meta( $user_id, 'cozy_twitch', true );
    $checks['twitch'] = [
        'label' => '📺 Twitch',
        'done'  => ! empty( $twitch ),
        'value' => $twitch,
    ];

    // Codes ami
    $codes = function_exists( 'cozy_get_friend_codes' ) ? cozy_get_friend_codes( $user_id ) : [];
    $codes_count = count( array_filter( $codes ) );
    $checks['friend_codes'] = [
        'label' => '🎮 Codes ami',
        'done'  => $codes_count > 0,
        'value' => $codes_count > 0 ? $codes_count . ' plateforme(s)' : '',
    ];

    // Avatar
    $has_avatar = get_user_meta( $user_id, 'wp_user_avatars', true ) || get_user_meta( $user_id, 'simple_local_avatar', true );
    $checks['avatar'] = [
        'label' => '📷 Photo de profil',
        'done'  => ! empty( $has_avatar ),
        'value' => '',
    ];

    // Charte
    $charter = function_exists( 'cozy_has_accepted_charter' ) && cozy_has_accepted_charter( $user_id );
    $checks['charter'] = [
        'label' => '📜 Charte de bienveillance',
        'done'  => $charter,
        'value' => '',
    ];

    // Setup partagé
    $user_setups = new WP_Query( [
        'post_type' => 'cozy_setup', 'author' => $user_id,
        'post_status' => [ 'publish', 'pending' ],
        'posts_per_page' => 1, 'fields' => 'ids',
    ] );
    $checks['setup'] = [
        'label' => '🖥️ Setup partagé',
        'done'  => $user_setups->found_posts > 0,
        'value' => $user_setups->found_posts > 0 ? $user_setups->found_posts . ' setup(s)' : '',
    ];

    $done_count = count( array_filter( $checks, fn( $c ) => $c['done'] ) );
    $total      = count( $checks );
    $percentage = round( ( $done_count / $total ) * 100 );

    ?>
    <p style="text-align: center; margin-bottom: 4px;">
        <strong><?php echo esc_html( $percentage ); ?>%</strong> complété
    </p>
    <div class="cozy-dash-profile-bar">
        <div class="cozy-dash-profile-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
    </div>

    <?php foreach ( $checks as $check ) : ?>
        <div class="cozy-dash-profile-check cozy-dash-profile-check--<?php echo $check['done'] ? 'done' : 'todo'; ?>">
            <span class="cozy-dash-profile-check__icon"><?php echo $check['done'] ? '✅' : '⬜'; ?></span>
            <span><?php echo esc_html( $check['label'] ); ?></span>
            <?php if ( $check['value'] ) : ?>
                <span class="cozy-dash-profile-check__value"><?php echo esc_html( $check['value'] ); ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <p style="margin-top: 12px; text-align: center;">
        <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="button">Compléter mon profil</a>
    </p>
    <?php
}


/* ================================================================
   8. STYLES GLOBAUX DU DASHBOARD
   ================================================================ */

function cozy_dashboard_styles() {
    $screen = get_current_screen();
    if ( ! $screen || 'dashboard' !== $screen->id ) {
        return;
    }

    ?>
    <style>
        /* ── Style global des widgets Cozy Dashboard — Cozy Grove ── */
        [id^="cozy_"] .inside { padding: 12px !important; }
        [id^="cozy_"] .hndle { font-weight: 700 !important; }

        /* ── Stats grid ── */
        .cozy-dash-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
        .cozy-dash-stat { background: #FDF8F0; border: 1px solid #E8DCC8; border-radius: 8px; padding: 14px 10px; text-align: center; }
        .cozy-dash-stat__number { display: block; font-size: 1.6rem; font-weight: 800; color: #C8813A; line-height: 1.2; }
        .cozy-dash-stat__label { display: block; font-size: 0.72rem; color: #6b7280; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Rôles list ── */
        .cozy-dash-roles { margin-top: 12px; }
        .cozy-dash-roles li { padding: 4px 0; font-size: 13px; color: #374151; }
        .cozy-dash-roles strong { color: #C8813A; }

        /* ── Modération section ── */
        .cozy-dash-moderation-section { margin-bottom: 16px; }
        .cozy-dash-moderation-section__title { margin: 0 0 8px; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Mod items ── */
        .cozy-dash-mod-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .cozy-dash-mod-item:last-child { border-bottom: 0; }
        .cozy-dash-mod-item a { color: #C8813A; text-decoration: none; font-weight: 500; }
        .cozy-dash-mod-item a:hover { text-decoration: underline; }
        .cozy-dash-mod-item__author { display: block; font-size: 12px; color: #9ca3af; }

        /* ── Badges ── */
        .cozy-dash-badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px; background: #fef3c7; color: #92400e; }
        .cozy-dash-badge--full { background: #fce7e7; color: #9B3A1E; }
        .cozy-dash-badge--open { background: #d1fae5; color: #2d5a2d; }
        .cozy-dash-badge--troc { background: #FDF8F0; color: #C8813A; }

        /* ── Empty state ── */
        .cozy-dash-empty { color: #9ca3af; font-style: italic; font-size: 13px; text-align: center; padding: 12px 0; }

        /* ── Events (admin / éditeur) ── */
        .cozy-dash-event { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .cozy-dash-event:last-child { border-bottom: 0; }
        .cozy-dash-event__info a { color: #1A160F; font-weight: 600; text-decoration: none; }
        .cozy-dash-event__info a:hover { color: #C8813A; }
        .cozy-dash-event__meta { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .cozy-dash-event__registrations { text-align: right; font-size: 12px; }
        .cozy-dash-event__registrations strong { font-size: 16px; color: #C8813A; }

        /* ── Users list ── */
        .cozy-dash-user { display: flex; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
        .cozy-dash-user:last-child { border-bottom: 0; }
        .cozy-dash-user img { border-radius: 50%; }
        .cozy-dash-user__info { flex: 1; }
        .cozy-dash-user__name { display: block; font-weight: 600; color: #1A160F; font-size: 13px; }
        .cozy-dash-user__date { display: block; font-size: 11px; color: #9ca3af; }
        .cozy-dash-user__role { font-size: 11px; background: #f3f4f6; padding: 2px 6px; border-radius: 8px; color: #6b7280; }

        /* ── Pending items ── */
        .cozy-dash-pending { padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
        .cozy-dash-pending:last-child { border-bottom: 0; }
        .cozy-dash-pending__type { font-size: 11px; color: #6b7280; }
        .cozy-dash-pending a { font-weight: 600; color: #1A160F; text-decoration: none; }
        .cozy-dash-pending a:hover { color: #C8813A; }
        .cozy-dash-pending__meta { font-size: 11px; color: #9ca3af; }

        /* ── Guide rédaction ── */
        .cozy-dash-guide { background: #FDF8F0; border: 1px solid #E8DCC8; border-radius: 8px; padding: 16px; }
        .cozy-dash-guide h4 { margin: 0 0 8px; color: #C8813A; }
        .cozy-dash-guide ul { margin: 0; padding-left: 20px; }
        .cozy-dash-guide li { padding: 3px 0; font-size: 13px; color: #374151; }

        /* ── Animateur events ── */
        .cozy-dash-anim-event { background: #FDF8F0; border: 1px solid #E8DCC8; border-radius: 8px; padding: 12px; margin-bottom: 8px; }
        .cozy-dash-anim-event__title { font-weight: 700; color: #1A160F; margin-bottom: 4px; }
        .cozy-dash-anim-event__details { font-size: 12px; color: #6b7280; }
        .cozy-dash-anim-event__details span { margin-right: 12px; }
        .cozy-dash-anim-event__badges { margin-top: 6px; }
        .cozy-dash-anim-event__details-toggle { margin-top: 8px; font-size: 12px; }
        .cozy-dash-anim-event__details-toggle summary { cursor: pointer; color: #C8813A; font-weight: 500; }
        .cozy-dash-anim-event__registrants { margin: 6px 0 0 16px; }

        /* ── Charte progress ── */
        .cozy-dash-charter { text-align: center; padding: 16px; }
        .cozy-dash-charter__number { font-size: 2rem; font-weight: 800; color: #C8813A; }
        .cozy-dash-charter__bar { background: #e5e7eb; border-radius: 10px; height: 20px; overflow: hidden; margin: 12px 0; }
        .cozy-dash-charter__fill { height: 100%; background: linear-gradient(90deg, #C8813A, #D4AF37); border-radius: 10px; transition: width 0.5s; }

        /* ── Welcome member ── */
        .cozy-dash-welcome { background: linear-gradient(135deg, #FDF8F0, #F5EDD8); border-radius: 10px; padding: 20px; text-align: center; }
        .cozy-dash-welcome h3 { margin: 0 0 8px; color: #1A160F; font-size: 1.2rem; }
        .cozy-dash-welcome p { color: #6b7280; font-size: 13px; margin: 4px 0; }
        .cozy-dash-welcome__charter { margin-top: 12px; padding: 8px 12px; border-radius: 8px; font-size: 12px; }
        .cozy-dash-welcome__charter--ok { background: #d1fae5; color: #2d5a2d; }
        .cozy-dash-welcome__charter--pending { background: #fef3c7; color: #92400e; }
        .cozy-dash-welcome__links { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 16px; }
        .cozy-dash-welcome__links a { display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #fff; border: 1px solid #E8DCC8; border-radius: 8px; font-size: 13px; font-weight: 500; color: #C8813A; text-decoration: none; transition: all 0.2s; }
        .cozy-dash-welcome__links a:hover { background: #C8813A; color: #fff; }

        /* ── Profile completeness ── */
        .cozy-dash-profile-bar { background: #e5e7eb; border-radius: 10px; height: 12px; overflow: hidden; margin: 8px 0 16px; }
        .cozy-dash-profile-fill { height: 100%; background: linear-gradient(90deg, #4A6649, #6B8E6B); border-radius: 10px; transition: width 0.5s; }
        .cozy-dash-profile-check { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f3f4f6; }
        .cozy-dash-profile-check:last-child { border-bottom: 0; }
        .cozy-dash-profile-check--done { color: #374151; }
        .cozy-dash-profile-check--todo { color: #9ca3af; }
        .cozy-dash-profile-check__icon { font-size: 16px; width: 22px; text-align: center; }
        .cozy-dash-profile-check__value { margin-left: auto; font-size: 11px; color: #C8813A; font-weight: 500; }
    </style>
    <?php
}
add_action( 'admin_head', 'cozy_dashboard_styles' );


/* ================================================================
   9. RÉORDONNER LES WIDGETS PAR RÔLE
   ================================================================ */

function cozy_dashboard_widget_order() {
    $user = wp_get_current_user();

    // Ne forcer l'ordre qu'au premier chargement
    if ( get_user_meta( $user->ID, 'cozy_dashboard_initialized', true ) ) {
        return;
    }

    $order = [];

    if ( in_array( 'administrator', $user->roles, true ) ) {
        $order = [
            'normal'  => 'cozy_admin_community_overview,cozy_admin_moderation,cozy_admin_upcoming_events,dashboard_right_now',
            'side'    => 'cozy_admin_recent_registrations,dashboard_site_health',
        ];
    } elseif ( in_array( 'editor', $user->roles, true ) ) {
        $order = [
            'normal' => 'cozy_editor_pending_content,cozy_editor_events_overview,cozy_editor_recent_articles',
            'side'   => 'cozy_editor_community_stats',
        ];
    } elseif ( in_array( 'author', $user->roles, true ) ) {
        $order = [
            'normal' => 'cozy_author_my_articles,cozy_author_next_events',
            'side'   => 'cozy_author_writing_guide',
        ];
    } elseif ( in_array( 'animateur_cozy', $user->roles, true ) ) {
        $order = [
            'normal' => 'cozy_animateur_events,cozy_animateur_registrations',
            'side'   => 'cozy_animateur_setups_moderation,cozy_animateur_charter_stats',
        ];
    } elseif ( in_array( 'subscriber', $user->roles, true ) ) {
        $order = [
            'normal' => 'cozy_member_welcome,cozy_member_reservations,cozy_member_upcoming',
            'side'   => 'cozy_member_profile_completeness',
        ];
    }

    if ( ! empty( $order ) ) {
        update_user_meta( $user->ID, 'meta-box-order_dashboard', $order );
        update_user_meta( $user->ID, 'cozy_dashboard_initialized', true );
    }
}
add_action( 'wp_dashboard_setup', 'cozy_dashboard_widget_order', 1000 );
