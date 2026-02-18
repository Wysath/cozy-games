<?php
/**
 * ============================================================================
 * MODULE : Dashboard Personnalisé par Rôle
 * ============================================================================
 *
 * Affiche des boîtes (widgets) personnalisées sur le tableau de bord
 * wp-admin en fonction du rôle de l'utilisateur connecté.
 *
 * Rôles ciblés :
 *   - administrator  → Vue d'ensemble complète du site
 *   - editor         → Gestion éditoriale (articles, commentaires)
 *   - animateur_cozy → Gestion des événements et participants
 *   - subscriber     → Espace membre (réservations, profil social)
 *
 * @package CozyGaming
 * @since 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ============================================================================
// 1. ENREGISTREMENT DES WIDGETS + NETTOYAGE
// ============================================================================

/**
 * Enregistre les widgets personnalisés et retire les widgets par défaut
 * inutiles selon le rôle.
 */
function cozy_dashboard_setup() {
    $user = wp_get_current_user();

    // --- Widget de bienvenue commun à tous ---
    wp_add_dashboard_widget(
        'cozy_welcome_widget',
        '🎮 Bienvenue sur Cozy Gaming',
        'cozy_render_welcome_widget'
    );

    // --- Widgets par rôle ---
    if ( in_array( 'administrator', $user->roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_admin_overview',
            '🛡️ Vue d\'ensemble — Cozy Gaming',
            'cozy_render_admin_overview'
        );
        wp_add_dashboard_widget(
            'cozy_admin_recent_members',
            '👥 Derniers membres inscrits',
            'cozy_render_admin_recent_members'
        );
        wp_add_dashboard_widget(
            'cozy_admin_quick_links',
            '⚡ Accès rapides administrateur',
            'cozy_render_admin_quick_links'
        );
    }

    if ( in_array( 'editor', $user->roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_editor_stats',
            '✍️ Mon activité rédactionnelle',
            'cozy_render_editor_stats'
        );
        wp_add_dashboard_widget(
            'cozy_editor_drafts',
            '📝 Mes brouillons en cours',
            'cozy_render_editor_drafts'
        );
        wp_add_dashboard_widget(
            'cozy_editor_quick_links',
            '⚡ Accès rapides rédacteur',
            'cozy_render_editor_quick_links'
        );
    }

    if ( in_array( 'animateur_cozy', $user->roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_animateur_events',
            '🎯 Mes prochains événements',
            'cozy_render_animateur_events'
        );
        wp_add_dashboard_widget(
            'cozy_animateur_stats',
            '📊 Statistiques animateur',
            'cozy_render_animateur_stats'
        );
        wp_add_dashboard_widget(
            'cozy_animateur_quick_links',
            '⚡ Accès rapides animateur',
            'cozy_render_animateur_quick_links'
        );
    }

    if ( in_array( 'subscriber', $user->roles, true ) ) {
        wp_add_dashboard_widget(
            'cozy_subscriber_reservations',
            '🎟️ Mes prochaines sessions',
            'cozy_render_subscriber_reservations'
        );
        wp_add_dashboard_widget(
            'cozy_subscriber_profile',
            '👤 Mon profil communautaire',
            'cozy_render_subscriber_profile'
        );
        wp_add_dashboard_widget(
            'cozy_subscriber_quick_links',
            '⚡ Accès rapides membre',
            'cozy_render_subscriber_quick_links'
        );
    }

    // --- Nettoyer les widgets par défaut inutiles pour certains rôles ---
    cozy_cleanup_default_widgets( $user );
}
add_action( 'wp_dashboard_setup', 'cozy_dashboard_setup' );


/**
 * Retire les widgets par défaut peu pertinents selon le rôle.
 */
function cozy_cleanup_default_widgets( $user ) {
    // Widgets à retirer pour tout le monde (peu utiles pour un site communautaire)
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );         // Actualités WordPress
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );     // Publication rapide

    // Pour les abonnés, retirer presque tout
    if ( in_array( 'subscriber', $user->roles, true ) ) {
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
    }

    // Pour les animateurs, retirer les widgets d'édition d'articles
    if ( in_array( 'animateur_cozy', $user->roles, true ) ) {
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
    }
}


// ============================================================================
// 2. WIDGET DE BIENVENUE (TOUS LES RÔLES)
// ============================================================================

function cozy_render_welcome_widget() {
    $user         = wp_get_current_user();
    $display_name = $user->display_name ?: $user->user_login;
    $hour         = (int) current_time( 'G' );

    if ( $hour < 12 ) {
        $greeting = 'Bonjour';
        $emoji    = '☀️';
    } elseif ( $hour < 18 ) {
        $greeting = 'Bon après-midi';
        $emoji    = '🌤️';
    } else {
        $greeting = 'Bonsoir';
        $emoji    = '🌙';
    }

    // Déterminer le badge rôle
    $role_badges = [
        'administrator'  => [ 'label' => 'Administrateur', 'icon' => '🛡️', 'color' => '#e74c3c' ],
        'editor'         => [ 'label' => 'Rédacteur',      'icon' => '✍️', 'color' => '#3498db' ],
        'animateur_cozy' => [ 'label' => 'Animateur Cozy', 'icon' => '🎯', 'color' => '#9b59b6' ],
        'subscriber'     => [ 'label' => 'Membre',         'icon' => '🎮', 'color' => '#2ecc71' ],
    ];

    $role_key  = $user->roles[0] ?? 'subscriber';
    $role_info = $role_badges[ $role_key ] ?? $role_badges['subscriber'];

    ?>
    <div class="cozy-dash-welcome">
        <div class="cozy-dash-welcome__greeting">
            <span class="cozy-dash-welcome__emoji"><?php echo $emoji; ?></span>
            <div>
                <strong><?php echo esc_html( "$greeting, $display_name !" ); ?></strong>
                <span class="cozy-dash-welcome__role" style="--role-color: <?php echo esc_attr( $role_info['color'] ); ?>">
                    <?php echo $role_info['icon'] . ' ' . esc_html( $role_info['label'] ); ?>
                </span>
            </div>
        </div>
        <p class="cozy-dash-welcome__tagline">
            Bienvenue dans l'espace d'administration de <strong>Cozy Gaming</strong>. 
            Retrouve ici tout ce dont tu as besoin pour profiter de la communauté ! 🕹️
        </p>
    </div>
    <?php
}


// ============================================================================
// 3. WIDGETS ADMINISTRATEUR
// ============================================================================

/**
 * Vue d'ensemble : compteurs principaux du site.
 */
function cozy_render_admin_overview() {
    // Compteur de membres
    $users_count = count_users();
    $total_users = $users_count['total_users'];

    // Compteur d'événements à venir
    $upcoming_events = 0;
    if ( function_exists( 'tribe_get_events' ) ) {
        $events = tribe_get_events( [
            'start_date'     => 'now',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );
        $upcoming_events = count( $events );
    }

    // Compteur d'articles publiés
    $posts_count = wp_count_posts( 'post' );
    $published_posts = $posts_count->publish ?? 0;

    // Compteur de setups
    $setups_count = wp_count_posts( 'cozy_setup' );
    $published_setups = $setups_count->publish ?? 0;

    // RSVP en attente (si Event Tickets est actif)
    $pending_rsvp = 0;
    if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
        $pending_rsvp = (int) wp_count_posts( 'tribe_rsvp_attendees' )->publish ?? 0;
    }

    $stats = [
        [ 'icon' => '👥', 'value' => $total_users,     'label' => 'Membres inscrits' ],
        [ 'icon' => '📅', 'value' => $upcoming_events,  'label' => 'Événements à venir' ],
        [ 'icon' => '📰', 'value' => $published_posts,  'label' => 'Articles publiés' ],
        [ 'icon' => '🖼️', 'value' => $published_setups, 'label' => 'Setups partagés' ],
        [ 'icon' => '🎟️', 'value' => $pending_rsvp,     'label' => 'Réservations RSVP' ],
    ];

    // Rôles détaillés
    $role_details = [];
    foreach ( $users_count['avail_roles'] as $role => $count ) {
        if ( $count > 0 && $role !== 'none' ) {
            $role_details[ $role ] = $count;
        }
    }

    ?>
    <div class="cozy-dash-stats">
        <?php foreach ( $stats as $stat ) : ?>
            <div class="cozy-dash-stats__card">
                <span class="cozy-dash-stats__icon"><?php echo $stat['icon']; ?></span>
                <span class="cozy-dash-stats__value"><?php echo number_format_i18n( $stat['value'] ); ?></span>
                <span class="cozy-dash-stats__label"><?php echo esc_html( $stat['label'] ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( ! empty( $role_details ) ) : ?>
        <div class="cozy-dash-roles">
            <h4>Répartition des rôles</h4>
            <div class="cozy-dash-roles__list">
                <?php
                $role_labels = [
                    'administrator'  => '🛡️ Administrateurs',
                    'editor'         => '✍️ Rédacteurs',
                    'animateur_cozy' => '🎯 Animateurs Cozy',
                    'author'         => '📝 Auteurs',
                    'contributor'    => '🤝 Contributeurs',
                    'subscriber'     => '🎮 Membres',
                ];
                foreach ( $role_details as $role => $count ) :
                    $label = $role_labels[ $role ] ?? ucfirst( $role );
                ?>
                    <div class="cozy-dash-roles__item">
                        <span><?php echo $label; ?></span>
                        <strong><?php echo number_format_i18n( $count ); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Derniers membres inscrits.
 */
function cozy_render_admin_recent_members() {
    $recent_users = get_users( [
        'orderby' => 'registered',
        'order'   => 'DESC',
        'number'  => 8,
    ] );

    if ( empty( $recent_users ) ) {
        echo '<p class="cozy-dash-empty">Aucun membre inscrit pour le moment.</p>';
        return;
    }

    ?>
    <div class="cozy-dash-members">
        <?php foreach ( $recent_users as $user ) :
            $registered = date_i18n( 'j M Y', strtotime( $user->user_registered ) );
            $role_key   = $user->roles[0] ?? 'subscriber';
            $role_names = [
                'administrator'  => 'Admin',
                'editor'         => 'Rédacteur',
                'animateur_cozy' => 'Animateur',
                'subscriber'     => 'Membre',
                'author'         => 'Auteur',
                'contributor'    => 'Contributeur',
            ];
            $role_name = $role_names[ $role_key ] ?? ucfirst( $role_key );
        ?>
            <div class="cozy-dash-members__item">
                <div class="cozy-dash-members__avatar">
                    <?php echo get_avatar( $user->ID, 36 ); ?>
                </div>
                <div class="cozy-dash-members__info">
                    <strong><?php echo esc_html( $user->display_name ); ?></strong>
                    <span class="cozy-dash-members__meta">
                        <?php echo esc_html( $role_name ); ?> · inscrit le <?php echo esc_html( $registered ); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="cozy-dash-footer-link">
        <a href="<?php echo admin_url( 'users.php' ); ?>">Voir tous les membres →</a>
    </p>
    <?php
}

/**
 * Accès rapides administrateur.
 */
function cozy_render_admin_quick_links() {
    $links = [
        [ 'icon' => '📅', 'label' => 'Créer un événement',     'url' => admin_url( 'post-new.php?post_type=tribe_events' ) ],
        [ 'icon' => '📰', 'label' => 'Écrire un article',      'url' => admin_url( 'post-new.php' ) ],
        [ 'icon' => '👥', 'label' => 'Gérer les membres',       'url' => admin_url( 'users.php' ) ],
        [ 'icon' => '🔌', 'label' => 'Extensions',              'url' => admin_url( 'plugins.php' ) ],
        [ 'icon' => '🎨', 'label' => 'Personnaliser le thème',  'url' => admin_url( 'customize.php' ) ],
        [ 'icon' => '⚙️', 'label' => 'Réglages généraux',      'url' => admin_url( 'options-general.php' ) ],
        [ 'icon' => '🩺', 'label' => 'Santé du site',           'url' => admin_url( 'site-health.php' ) ],
        [ 'icon' => '💾', 'label' => 'Exporter les données',    'url' => admin_url( 'export.php' ) ],
    ];

    cozy_render_quick_links_grid( $links );
}


// ============================================================================
// 4. WIDGETS RÉDACTEUR (EDITOR)
// ============================================================================

/**
 * Statistiques d'activité rédactionnelle.
 */
function cozy_render_editor_stats() {
    $user_id = get_current_user_id();

    // Articles de l'éditeur
    $my_published = count_user_posts( $user_id, 'post', true );

    // Tous les articles du site
    $all_posts  = wp_count_posts( 'post' );
    $all_drafts = $all_posts->draft ?? 0;
    $all_pending = $all_posts->pending ?? 0;

    // Commentaires en attente
    $pending_comments = wp_count_comments();
    $awaiting_moderation = $pending_comments->moderated ?? 0;

    // Articles en brouillon de cet utilisateur
    $my_drafts = new WP_Query( [
        'post_type'      => 'post',
        'post_status'    => 'draft',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );
    $my_drafts_count = $my_drafts->found_posts;

    $stats = [
        [ 'icon' => '✅', 'value' => $my_published,       'label' => 'Mes articles publiés' ],
        [ 'icon' => '📝', 'value' => $my_drafts_count,    'label' => 'Mes brouillons' ],
        [ 'icon' => '⏳', 'value' => $all_pending,        'label' => 'En attente de relecture' ],
        [ 'icon' => '💬', 'value' => $awaiting_moderation, 'label' => 'Commentaires à modérer' ],
    ];

    ?>
    <div class="cozy-dash-stats cozy-dash-stats--compact">
        <?php foreach ( $stats as $stat ) : ?>
            <div class="cozy-dash-stats__card">
                <span class="cozy-dash-stats__icon"><?php echo $stat['icon']; ?></span>
                <span class="cozy-dash-stats__value"><?php echo number_format_i18n( $stat['value'] ); ?></span>
                <span class="cozy-dash-stats__label"><?php echo esc_html( $stat['label'] ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Brouillons en cours du rédacteur.
 */
function cozy_render_editor_drafts() {
    $user_id = get_current_user_id();

    $drafts = get_posts( [
        'post_type'      => 'post',
        'post_status'    => [ 'draft', 'pending' ],
        'author'         => $user_id,
        'posts_per_page' => 5,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ] );

    if ( empty( $drafts ) ) {
        echo '<div class="cozy-dash-empty">';
        echo '<p>📄 Aucun brouillon en cours.</p>';
        echo '<a href="' . admin_url( 'post-new.php' ) . '" class="button button-primary">Écrire un article</a>';
        echo '</div>';
        return;
    }

    ?>
    <div class="cozy-dash-drafts">
        <?php foreach ( $drafts as $draft ) :
            $modified = human_time_diff( strtotime( $draft->post_modified ), current_time( 'timestamp' ) );
            $status_label = $draft->post_status === 'pending' ? 'En relecture' : 'Brouillon';
            $status_class = $draft->post_status === 'pending' ? 'pending' : 'draft';
        ?>
            <div class="cozy-dash-drafts__item">
                <div class="cozy-dash-drafts__info">
                    <a href="<?php echo get_edit_post_link( $draft->ID ); ?>" class="cozy-dash-drafts__title">
                        <?php echo esc_html( $draft->post_title ?: '(Sans titre)' ); ?>
                    </a>
                    <span class="cozy-dash-drafts__meta">
                        Modifié il y a <?php echo esc_html( $modified ); ?>
                    </span>
                </div>
                <span class="cozy-dash-drafts__status cozy-dash-drafts__status--<?php echo $status_class; ?>">
                    <?php echo esc_html( $status_label ); ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="cozy-dash-footer-link">
        <a href="<?php echo admin_url( 'edit.php?post_status=draft' ); ?>">Voir tous les brouillons →</a>
    </p>
    <?php
}

/**
 * Accès rapides rédacteur.
 */
function cozy_render_editor_quick_links() {
    $links = [
        [ 'icon' => '📰', 'label' => 'Écrire un article',      'url' => admin_url( 'post-new.php' ) ],
        [ 'icon' => '📋', 'label' => 'Tous les articles',       'url' => admin_url( 'edit.php' ) ],
        [ 'icon' => '🏷️', 'label' => 'Types d\'articles',      'url' => admin_url( 'edit-tags.php?taxonomy=cozy_article_type&post_type=post' ) ],
        [ 'icon' => '📂', 'label' => 'Catégories',              'url' => admin_url( 'edit-tags.php?taxonomy=category' ) ],
        [ 'icon' => '💬', 'label' => 'Commentaires',            'url' => admin_url( 'edit-comments.php' ) ],
        [ 'icon' => '📸', 'label' => 'Médiathèque',             'url' => admin_url( 'upload.php' ) ],
    ];

    cozy_render_quick_links_grid( $links );
}


// ============================================================================
// 5. WIDGETS ANIMATEUR COZY
// ============================================================================

/**
 * Prochains événements de l'animateur.
 */
function cozy_render_animateur_events() {
    if ( ! function_exists( 'tribe_get_events' ) ) {
        echo '<p class="cozy-dash-empty">Le plugin The Events Calendar n\'est pas actif.</p>';
        return;
    }

    $events = tribe_get_events( [
        'start_date'     => 'now',
        'posts_per_page' => 5,
        'orderby'        => 'event_date',
        'order'          => 'ASC',
    ] );

    if ( empty( $events ) ) {
        echo '<div class="cozy-dash-empty">';
        echo '<p>📅 Aucun événement à venir.</p>';
        echo '<a href="' . admin_url( 'post-new.php?post_type=tribe_events' ) . '" class="button button-primary">Créer un événement</a>';
        echo '</div>';
        return;
    }

    ?>
    <div class="cozy-dash-events">
        <?php foreach ( $events as $event ) :
            $start_date = tribe_get_start_date( $event->ID, false, 'j M Y' );
            $start_time = tribe_get_start_date( $event->ID, false, 'H:i' );

            // Compter les RSVP pour cet événement
            $rsvp_count = 0;
            if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
                $attendees = tribe( 'tickets.rsvp' )->get_attendees_by_id( $event->ID );
                $rsvp_count = is_array( $attendees ) ? count( $attendees ) : 0;
            }

            // Capacité max
            $tickets = Tribe__Tickets__Tickets::get_all_event_tickets( $event->ID );
            $capacity = 0;
            foreach ( $tickets as $ticket ) {
                $capacity += $ticket->capacity();
            }
        ?>
            <div class="cozy-dash-events__item">
                <div class="cozy-dash-events__date">
                    <span class="cozy-dash-events__day"><?php echo date_i18n( 'j', strtotime( tribe_get_start_date( $event->ID, true, 'Y-m-d' ) ) ); ?></span>
                    <span class="cozy-dash-events__month"><?php echo date_i18n( 'M', strtotime( tribe_get_start_date( $event->ID, true, 'Y-m-d' ) ) ); ?></span>
                </div>
                <div class="cozy-dash-events__info">
                    <a href="<?php echo get_edit_post_link( $event->ID ); ?>" class="cozy-dash-events__title">
                        <?php echo esc_html( $event->post_title ); ?>
                    </a>
                    <span class="cozy-dash-events__meta">
                        🕐 <?php echo esc_html( $start_time ); ?>
                        · 🎟️ <?php echo $rsvp_count; ?><?php echo $capacity > 0 ? '/' . $capacity : ''; ?> inscrit(s)
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="cozy-dash-footer-link">
        <a href="<?php echo admin_url( 'edit.php?post_type=tribe_events' ); ?>">Voir tous les événements →</a>
    </p>
    <?php
}

/**
 * Statistiques animateur.
 */
function cozy_render_animateur_stats() {
    $upcoming_events = 0;
    $total_rsvps     = 0;
    $venues_count    = 0;

    if ( function_exists( 'tribe_get_events' ) ) {
        // Événements à venir
        $events = tribe_get_events( [
            'start_date'     => 'now',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );
        $upcoming_events = count( $events );

        // Total des RSVP sur les événements à venir
        if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
            foreach ( $events as $event_id ) {
                $attendees = tribe( 'tickets.rsvp' )->get_attendees_by_id( $event_id );
                $total_rsvps += is_array( $attendees ) ? count( $attendees ) : 0;
            }
        }

        // Lieux
        $venues = get_posts( [
            'post_type'      => 'tribe_venue',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );
        $venues_count = count( $venues );
    }

    // Organisateurs
    $organizers = get_posts( [
        'post_type'      => 'tribe_organizer',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );
    $organizers_count = count( $organizers );

    $stats = [
        [ 'icon' => '📅', 'value' => $upcoming_events,  'label' => 'Événements à venir' ],
        [ 'icon' => '🎟️', 'value' => $total_rsvps,      'label' => 'Inscriptions totales' ],
        [ 'icon' => '📍', 'value' => $venues_count,     'label' => 'Lieux enregistrés' ],
        [ 'icon' => '🎤', 'value' => $organizers_count, 'label' => 'Organisateurs' ],
    ];

    ?>
    <div class="cozy-dash-stats cozy-dash-stats--compact">
        <?php foreach ( $stats as $stat ) : ?>
            <div class="cozy-dash-stats__card">
                <span class="cozy-dash-stats__icon"><?php echo $stat['icon']; ?></span>
                <span class="cozy-dash-stats__value"><?php echo number_format_i18n( $stat['value'] ); ?></span>
                <span class="cozy-dash-stats__label"><?php echo esc_html( $stat['label'] ); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Accès rapides animateur.
 */
function cozy_render_animateur_quick_links() {
    $links = [
        [ 'icon' => '📅', 'label' => 'Créer un événement',     'url' => admin_url( 'post-new.php?post_type=tribe_events' ) ],
        [ 'icon' => '📋', 'label' => 'Tous les événements',    'url' => admin_url( 'edit.php?post_type=tribe_events' ) ],
        [ 'icon' => '📍', 'label' => 'Gérer les lieux',         'url' => admin_url( 'edit.php?post_type=tribe_venue' ) ],
        [ 'icon' => '🎤', 'label' => 'Gérer les organisateurs', 'url' => admin_url( 'edit.php?post_type=tribe_organizer' ) ],
        [ 'icon' => '🏷️', 'label' => 'Modes de communication', 'url' => admin_url( 'edit-tags.php?taxonomy=cozy_comm_mode&post_type=tribe_events' ) ],
        [ 'icon' => '⚠️', 'label' => 'Content Warnings',       'url' => admin_url( 'edit-tags.php?taxonomy=cozy_content_warning&post_type=tribe_events' ) ],
    ];

    cozy_render_quick_links_grid( $links );
}


// ============================================================================
// 6. WIDGETS ABONNÉ (SUBSCRIBER)
// ============================================================================

/**
 * Prochaines sessions réservées par l'abonné.
 */
function cozy_render_subscriber_reservations() {
    $user_id = get_current_user_id();

    if ( ! class_exists( 'Tribe__Tickets__RSVP' ) || ! function_exists( 'tribe_get_start_date' ) ) {
        echo '<p class="cozy-dash-empty">Le système de réservation n\'est pas disponible.</p>';
        return;
    }

    // Récupérer les attendees RSVP de l'utilisateur
    $attendees = get_posts( [
        'post_type'      => 'tribe_rsvp_attendees',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'   => '_tribe_tickets_attendee_user_id',
                'value' => $user_id,
            ],
            [
                'key'   => '_tribe_rsvp_status',
                'value' => 'yes',
            ],
        ],
        'fields' => 'ids',
    ] );

    // Filtrer les événements à venir
    $upcoming = [];
    $now = current_time( 'timestamp' );

    foreach ( $attendees as $attendee_id ) {
        $event_id = get_post_meta( $attendee_id, '_tribe_rsvp_event', true );
        if ( ! $event_id ) continue;

        $start = strtotime( tribe_get_start_date( $event_id, true, 'Y-m-d H:i:s' ) );
        if ( $start && $start >= $now ) {
            $upcoming[] = [
                'event_id'   => $event_id,
                'start'      => $start,
                'event_title' => get_the_title( $event_id ),
            ];
        }
    }

    // Trier par date
    usort( $upcoming, function( $a, $b ) { return $a['start'] - $b['start']; } );
    $upcoming = array_slice( $upcoming, 0, 5 );

    if ( empty( $upcoming ) ) {
        echo '<div class="cozy-dash-empty">';
        echo '<p>🎮 Tu n\'as pas encore de session prévue.</p>';
        echo '<a href="' . home_url( '/events/' ) . '" class="button button-primary">Découvrir les événements</a>';
        echo '</div>';
        return;
    }

    ?>
    <div class="cozy-dash-events">
        <?php foreach ( $upcoming as $res ) :
            $event_id = $res['event_id'];
            $start_time = tribe_get_start_date( $event_id, false, 'H:i' );
        ?>
            <div class="cozy-dash-events__item">
                <div class="cozy-dash-events__date">
                    <span class="cozy-dash-events__day"><?php echo date_i18n( 'j', $res['start'] ); ?></span>
                    <span class="cozy-dash-events__month"><?php echo date_i18n( 'M', $res['start'] ); ?></span>
                </div>
                <div class="cozy-dash-events__info">
                    <a href="<?php echo esc_url( get_permalink( $event_id ) ); ?>" class="cozy-dash-events__title" target="_blank">
                        <?php echo esc_html( $res['event_title'] ); ?>
                    </a>
                    <span class="cozy-dash-events__meta">
                        🕐 <?php echo esc_html( $start_time ); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="cozy-dash-footer-link">
        <a href="<?php echo home_url( '/events/' ); ?>">Voir le calendrier complet →</a>
    </p>
    <?php
}

/**
 * Profil communautaire de l'abonné.
 */
function cozy_render_subscriber_profile() {
    $user_id = get_current_user_id();

    // Vérifier les profils sociaux
    $discord = get_user_meta( $user_id, 'cozy_discord', true );
    $twitch  = get_user_meta( $user_id, 'cozy_twitch', true );

    // Vérifier les codes ami
    $friend_codes = get_user_meta( $user_id, 'cozy_friend_codes', true );
    $codes_count  = 0;
    if ( is_array( $friend_codes ) ) {
        $codes_count = count( array_filter( $friend_codes ) );
    }

    // Compteur de participations passées
    $total_events = 0;
    if ( class_exists( 'Tribe__Tickets__RSVP' ) ) {
        $all_attendees = get_posts( [
            'post_type'      => 'tribe_rsvp_attendees',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => '_tribe_tickets_attendee_user_id',
                    'value' => $user_id,
                ],
                [
                    'key'   => '_tribe_rsvp_status',
                    'value' => 'yes',
                ],
            ],
            'fields' => 'ids',
        ] );
        $total_events = count( $all_attendees );
    }

    // Setups partagés
    $my_setups = get_posts( [
        'post_type'      => 'cozy_setup',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );
    $setups_count = count( $my_setups );

    ?>
    <div class="cozy-dash-profile">
        <div class="cozy-dash-profile__checklist">
            <div class="cozy-dash-profile__item <?php echo $discord ? 'cozy-dash-profile__item--done' : ''; ?>">
                <span class="cozy-dash-profile__check"><?php echo $discord ? '✅' : '⬜'; ?></span>
                <span>Discord lié<?php echo $discord ? ' : <strong>' . esc_html( $discord ) . '</strong>' : ''; ?></span>
            </div>
            <div class="cozy-dash-profile__item <?php echo $twitch ? 'cozy-dash-profile__item--done' : ''; ?>">
                <span class="cozy-dash-profile__check"><?php echo $twitch ? '✅' : '⬜'; ?></span>
                <span>Twitch lié<?php echo $twitch ? ' : <strong>' . esc_html( $twitch ) . '</strong>' : ''; ?></span>
            </div>
            <div class="cozy-dash-profile__item <?php echo $codes_count > 0 ? 'cozy-dash-profile__item--done' : ''; ?>">
                <span class="cozy-dash-profile__check"><?php echo $codes_count > 0 ? '✅' : '⬜'; ?></span>
                <span>Codes ami renseignés : <strong><?php echo $codes_count; ?></strong> jeu(x)</span>
            </div>
        </div>

        <div class="cozy-dash-profile__stats">
            <div class="cozy-dash-profile__stat">
                <span class="cozy-dash-profile__stat-value"><?php echo $total_events; ?></span>
                <span class="cozy-dash-profile__stat-label">Participations</span>
            </div>
            <div class="cozy-dash-profile__stat">
                <span class="cozy-dash-profile__stat-value"><?php echo $setups_count; ?></span>
                <span class="cozy-dash-profile__stat-label">Setups partagés</span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Accès rapides abonné.
 */
function cozy_render_subscriber_quick_links() {
    $links = [
        [ 'icon' => '📅', 'label' => 'Calendrier des événements', 'url' => home_url( '/events/' ) ],
        [ 'icon' => '🎟️', 'label' => 'Mes réservations',          'url' => home_url( '/tickets/' ) ],
        [ 'icon' => '👤', 'label' => 'Mon profil',                 'url' => admin_url( 'profile.php' ) ],
        [ 'icon' => '🖼️', 'label' => 'Galerie des setups',        'url' => home_url( '/setups/' ) ],
    ];

    cozy_render_quick_links_grid( $links );
}


// ============================================================================
// 7. HELPERS
// ============================================================================

/**
 * Affiche une grille de liens rapides.
 */
function cozy_render_quick_links_grid( $links ) {
    ?>
    <div class="cozy-dash-links">
        <?php foreach ( $links as $link ) : ?>
            <a href="<?php echo esc_url( $link['url'] ); ?>" class="cozy-dash-links__item">
                <span class="cozy-dash-links__icon"><?php echo $link['icon']; ?></span>
                <span class="cozy-dash-links__label"><?php echo esc_html( $link['label'] ); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
}


// ============================================================================
// 8. STYLES CSS ADMIN
// ============================================================================

/**
 * Enqueue les styles du dashboard personnalisé.
 */
function cozy_dashboard_enqueue_styles( $hook ) {
    if ( 'index.php' !== $hook ) {
        return;
    }

    wp_enqueue_style(
        'cozy-dashboard',
        get_template_directory_uri() . '/assets/css/cozy-dashboard.css',
        [],
        '1.7.0'
    );
}
add_action( 'admin_enqueue_scripts', 'cozy_dashboard_enqueue_styles' );
