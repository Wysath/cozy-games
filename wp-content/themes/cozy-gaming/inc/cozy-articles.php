<?php
/**
 * ============================================================================
 * MODULE : Articles Gaming — Fiche Jeu via ACF (Free)
 * ============================================================================
 *
 * Enrichit les articles WordPress (posts) avec des champs métier
 * adaptés à une guilde gaming, gérés par ACF (gratuit).
 *
 * 📋 FICHE JEU :
 *   - Nom du jeu, plateformes, nombre de joueurs
 *   - Temps de jeu moyen, développeur, éditeur
 *
 * ⭐ NOTES & ÉVALUATIONS :
 *   - Note globale (1-5)
 *   - Notes par critère : Gameplay, Direction artistique,
 *     Bande-son, Scénario, Accessibilité, Ambiance cozy
 *
 * 📝 VERDICT :
 *   - Verdict résumé
 *   - Points forts / Points faibles (textarea, un par ligne)
 *
 * Les field groups sont enregistrés via PHP (acf_add_local_field_group)
 * pour être versionnés dans Git, sans dépendre de la DB.
 *
 * Requiert : ACF Free (Advanced Custom Fields by WP Engine)
 *
 * @package CozyGaming
 * @since 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * -----------------------------------------------
 * 0. VÉRIFICATION ACF
 * -----------------------------------------------
 * Affiche un avis admin si ACF n'est pas activé.
 */
function cozy_check_acf_dependency() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Cozy Grove :</strong> Le module « Articles Gaming » nécessite le plugin
                    <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=keyword' ) ); ?>">Advanced Custom Fields</a>
                    (gratuit). Installe et active-le pour profiter des fiches jeu.
                </p>
            </div>
            <?php
        } );
    }
}
add_action( 'admin_init', 'cozy_check_acf_dependency' );


/**
 * -----------------------------------------------
 * 1. CONSTANTES & CONFIGURATION
 * -----------------------------------------------
 */

/**
 * Retourne la liste des plateformes supportées
 *
 * @return array slug => label
 */
function cozy_get_platforms() {
    return apply_filters( 'cozy_article_platforms', array(
        'pc'          => '🖥️ PC',
        'ps5'         => '🎮 PS5',
        'ps4'         => '🎮 PS4',
        'xbox_series' => '🟢 Xbox Series',
        'xbox_one'    => '🟢 Xbox One',
        'switch'      => '🔴 Nintendo Switch',
        'switch_2'    => '🔴 Nintendo Switch 2',
        'mobile'      => '📱 Mobile',
        'vr'          => '🥽 VR',
        'mac'         => '🍎 Mac',
        'linux'       => '🐧 Linux',
        'retro'       => '🕹️ Rétro',
    ) );
}

/**
 * Retourne les types d'article disponibles
 *
 * @return array slug => array( label, icon, color )
 */
function cozy_get_article_types() {
    return apply_filters( 'cozy_article_types', array(
        'test'        => array( 'label' => 'Test / Review',   'icon' => '🎯', 'color' => '#C8813A' ),
        'guide'       => array( 'label' => 'Guide',           'icon' => '📖', 'color' => '#0891b2' ),
        'coup_coeur'  => array( 'label' => 'Coup de cœur',    'icon' => '💜', 'color' => '#db2777' ),
        'actualite'   => array( 'label' => 'Actualité',        'icon' => '📰', 'color' => '#059669' ),
        'dossier'     => array( 'label' => 'Dossier',         'icon' => '📁', 'color' => '#d97706' ),
        'decouverte'  => array( 'label' => 'Découverte',       'icon' => '🔍', 'color' => '#2563eb' ),
        'top'         => array( 'label' => 'Top / Classement', 'icon' => '🏆', 'color' => '#ca8a04' ),
    ) );
}

/**
 * Retourne les critères de notation
 *
 * @return array slug => array( label, icon, description )
 */
function cozy_get_rating_criteria() {
    return apply_filters( 'cozy_rating_criteria', array(
        'gameplay'      => array(
            'label' => 'Gameplay',
            'icon'  => '🎮',
            'desc'  => 'Qualité des mécaniques de jeu',
        ),
        'direction_art' => array(
            'label' => 'Direction artistique',
            'icon'  => '🎨',
            'desc'  => 'Graphismes, style visuel',
        ),
        'bande_son'     => array(
            'label' => 'Bande-son',
            'icon'  => '🎵',
            'desc'  => 'Musique et effets sonores',
        ),
        'scenario'      => array(
            'label' => 'Scénario',
            'icon'  => '📜',
            'desc'  => 'Histoire et narration',
        ),
        'accessibilite' => array(
            'label' => 'Accessibilité',
            'icon'  => '♿',
            'desc'  => 'Options d\'accessibilité, prise en main',
        ),
        'ambiance_cozy' => array(
            'label' => 'Ambiance cozy',
            'icon'  => '☕',
            'desc'  => 'Le jeu est-il relaxant et agréable ?',
        ),
    ) );
}


/**
 * -----------------------------------------------
 * 2. TAXONOMIE TYPE D'ARTICLE
 * -----------------------------------------------
 */

/**
 * Enregistre la taxonomie « Type d'article » pour les posts
 */
function cozy_register_article_type_taxonomy() {
    register_taxonomy( 'cozy_article_type', 'post', array(
        'labels'            => array(
            'name'          => 'Types d\'article',
            'singular_name' => 'Type d\'article',
            'menu_name'     => '🎯 Type d\'article',
            'all_items'     => 'Tous les types',
            'edit_item'     => 'Modifier le type',
            'add_new_item'  => 'Ajouter un type',
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'type-article' ),
    ) );
}
add_action( 'init', 'cozy_register_article_type_taxonomy' );


/**
 * -----------------------------------------------
 * 2b. TAXONOMIE JEU (fallback si le plugin Cozy Events n'est pas actif)
 * -----------------------------------------------
 * La taxonomie cozy_game est normalement enregistrée par le plugin.
 * Ce fallback garantit qu'elle existe même sans le plugin.
 */
function cozy_register_game_taxonomy_fallback() {
    if ( taxonomy_exists( 'cozy_game' ) ) {
        // Le plugin l'a déjà enregistrée, on s'assure que les posts sont inclus
        register_taxonomy_for_object_type( 'cozy_game', 'post' );
        return;
    }

    // Enregistrement autonome si le plugin est désactivé
    register_taxonomy( 'cozy_game', 'post', array(
        'labels'            => array(
            'name'                       => 'Jeux',
            'singular_name'              => 'Jeu',
            'menu_name'                  => '🎮 Jeux',
            'all_items'                  => 'Tous les jeux',
            'edit_item'                  => 'Modifier le jeu',
            'view_item'                  => 'Voir le jeu',
            'add_new_item'               => 'Ajouter un jeu',
            'new_item_name'              => 'Nom du nouveau jeu',
            'search_items'               => 'Rechercher un jeu',
            'popular_items'              => 'Jeux populaires',
            'separate_items_with_commas' => 'Séparer les jeux par des virgules',
            'add_or_remove_items'        => 'Ajouter ou retirer des jeux',
            'choose_from_most_used'      => 'Choisir parmi les jeux les plus utilisés',
            'not_found'                  => 'Aucun jeu trouvé',
            'back_to_items'              => '← Retour aux jeux',
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'jeu' ),
    ) );
}
add_action( 'init', 'cozy_register_game_taxonomy_fallback', 25 );

/**
 * Insère les types d'article par défaut
 */
function cozy_insert_default_article_types() {
    $types = cozy_get_article_types();

    foreach ( $types as $slug => $data ) {
        if ( ! term_exists( $slug, 'cozy_article_type' ) ) {
            wp_insert_term( $data['icon'] . ' ' . $data['label'], 'cozy_article_type', array(
                'slug' => $slug,
            ) );
        }
    }
}
add_action( 'admin_init', function() {
    if ( get_option( 'cozy_article_types_initialized' ) ) {
        return;
    }
    cozy_insert_default_article_types();
    update_option( 'cozy_article_types_initialized', true );
} );


/**
 * -----------------------------------------------
 * 3. ENREGISTREMENT DES FIELD GROUPS ACF (via PHP)
 * -----------------------------------------------
 * Les champs sont déclarés en code pour être versionnés
 * dans Git. Pas besoin de les créer depuis l'interface ACF.
 */
function cozy_register_acf_fields() {

    // Vérifier qu'ACF est disponible
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $platforms = cozy_get_platforms();
    $criteria  = cozy_get_rating_criteria();

    // --- Préparer les choix de plateformes pour ACF ---
    $platform_choices = array();
    foreach ( $platforms as $slug => $label ) {
        $platform_choices[ $slug ] = $label;
    }

    // =====================================================
    // FIELD GROUP 1 : FICHE DU JEU
    // =====================================================
    acf_add_local_field_group( array(
        'key'      => 'group_cozy_game_info',
        'title'    => '🎮 Fiche du jeu',
        'fields'   => array(
            array(
                'key'           => 'field_cozy_game_info_message',
                'name'          => '',
                'label'         => '',
                'type'          => 'message',
                'message'       => '💡 <strong>Utilise la taxonomie « 🎮 Jeux » dans la barre latérale</strong> pour associer un jeu à cet article (comme les étiquettes). Les champs ci-dessous sont optionnels et ne servent que si le jeu n\'est pas encore dans la collection.',
            ),
            array(
                'key'           => 'field_cozy_game_platforms',
                'name'          => 'cozy_game_platforms',
                'label'         => '🕹️ Plateformes',
                'type'          => 'checkbox',
                'choices'       => $platform_choices,
                'layout'        => 'horizontal',
                'toggle'        => 0,
            ),
            array(
                'key'           => 'field_cozy_game_players',
                'name'          => 'cozy_game_players',
                'label'         => '👥 Nombre de joueurs',
                'type'          => 'text',
                'placeholder'   => 'Ex : 1-4 joueurs, 1-8 en ligne',
            ),
            array(
                'key'           => 'field_cozy_game_playtime',
                'name'          => 'cozy_game_playtime',
                'label'         => '⏱️ Temps de jeu',
                'type'          => 'text',
                'placeholder'   => 'Ex : ~30h (histoire), 100h+ (complétion)',
            ),
            array(
                'key'           => 'field_cozy_game_developer',
                'name'          => 'cozy_game_developer',
                'label'         => '🛠️ Développeur',
                'type'          => 'text',
                'placeholder'   => 'Ex : Nintendo EPD',
            ),
            array(
                'key'           => 'field_cozy_game_publisher',
                'name'          => 'cozy_game_publisher',
                'label'         => '📦 Éditeur',
                'type'          => 'text',
                'placeholder'   => 'Ex : Nintendo',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ),
            ),
        ),
        'position'     => 'normal',
        'style'        => 'default',
        'menu_order'   => 0,
    ) );


    // =====================================================
    // FIELD GROUP 2 : NOTES & ÉVALUATIONS
    // =====================================================

    // Construire les champs de notes dynamiquement
    $rating_fields = array(
        array(
            'key'           => 'field_cozy_rating_global',
            'name'          => 'cozy_rating_global',
            'label'         => '🌟 Note globale',
            'type'          => 'range',
            'instructions'  => 'Note de 0 à 5. Laisse à 0 si pas de note.',
            'min'           => 0,
            'max'           => 5,
            'step'          => 1,
            'default_value' => 0,
            'append'        => '/ 5',
        ),
    );

    foreach ( $criteria as $slug => $criterion ) {
        $rating_fields[] = array(
            'key'           => 'field_cozy_rating_' . $slug,
            'name'          => 'cozy_rating_' . $slug,
            'label'         => $criterion['icon'] . ' ' . $criterion['label'],
            'type'          => 'range',
            'instructions'  => $criterion['desc'],
            'min'           => 0,
            'max'           => 5,
            'step'          => 1,
            'default_value' => 0,
            'append'        => '/ 5',
        );
    }

    acf_add_local_field_group( array(
        'key'      => 'group_cozy_ratings',
        'title'    => '⭐ Notes & évaluations',
        'fields'   => $rating_fields,
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ),
            ),
        ),
        'position'     => 'normal',
        'style'        => 'default',
        'menu_order'   => 1,
    ) );


    // =====================================================
    // FIELD GROUP 3 : VERDICT
    // =====================================================
    acf_add_local_field_group( array(
        'key'      => 'group_cozy_verdict',
        'title'    => '📝 Verdict',
        'fields'   => array(
            array(
                'key'           => 'field_cozy_verdict',
                'name'          => 'cozy_verdict',
                'label'         => '💬 Verdict / Résumé',
                'type'          => 'textarea',
                'instructions'  => 'Un résumé en quelques phrases de ton avis sur le jeu.',
                'placeholder'   => 'Un jeu cozy parfait pour les soirées d\'hiver, avec un charme indéniable malgré quelques longueurs.',
                'rows'          => 3,
                'maxlength'     => 500,
            ),
            array(
                'key'           => 'field_cozy_pros',
                'name'          => 'cozy_pros',
                'label'         => '✅ Points forts',
                'type'          => 'textarea',
                'instructions'  => 'Un point fort par ligne.',
                'placeholder'   => "Direction artistique sublime\nBande-son immersive\nGameplay accessible",
                'rows'          => 5,
                'new_lines'     => '',
            ),
            array(
                'key'           => 'field_cozy_cons',
                'name'          => 'cozy_cons',
                'label'         => '❌ Points faibles',
                'type'          => 'textarea',
                'instructions'  => 'Un point faible par ligne.',
                'placeholder'   => "Quelques bugs mineurs\nContenu additionnel payant",
                'rows'          => 5,
                'new_lines'     => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'post',
                ),
            ),
        ),
        'position'     => 'normal',
        'style'        => 'default',
        'menu_order'   => 2,
    ) );


    // =====================================================
    // FIELD GROUP 4 : FICHE JEU (sur taxonomie cozy_game)
    // =====================================================
    acf_add_local_field_group( array(
        'key'      => 'group_cozy_game_term',
        'title'    => '🎮 Fiche du jeu',
        'fields'   => array(
            array(
                'key'           => 'field_cozy_game_cover',
                'name'          => 'cozy_game_cover',
                'label'         => '🖼️ Image de couverture',
                'type'          => 'image',
                'instructions'  => 'Image de couverture du jeu (ratio 16:9 recommandé, min. 600×340 px).',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'library'       => 'all',
            ),
            array(
                'key'           => 'field_cozy_game_term_platforms',
                'name'          => 'cozy_game_term_platforms',
                'label'         => '🕹️ Plateformes',
                'type'          => 'checkbox',
                'choices'       => $platform_choices,
                'layout'        => 'horizontal',
            ),
            array(
                'key'           => 'field_cozy_game_term_developer',
                'name'          => 'cozy_game_term_developer',
                'label'         => '🛠️ Développeur',
                'type'          => 'text',
                'placeholder'   => 'Ex : Nintendo EPD',
            ),
            array(
                'key'           => 'field_cozy_game_term_publisher',
                'name'          => 'cozy_game_term_publisher',
                'label'         => '📦 Éditeur',
                'type'          => 'text',
                'placeholder'   => 'Ex : Nintendo',
            ),
            array(
                'key'           => 'field_cozy_game_term_players',
                'name'          => 'cozy_game_term_players',
                'label'         => '👥 Nombre de joueurs',
                'type'          => 'text',
                'placeholder'   => 'Ex : 1-4 joueurs, 1-8 en ligne',
            ),
            array(
                'key'           => 'field_cozy_game_term_playtime',
                'name'          => 'cozy_game_term_playtime',
                'label'         => '⏱️ Temps de jeu moyen',
                'type'          => 'text',
                'placeholder'   => 'Ex : ~30h (histoire), 100h+ (complétion)',
            ),
            array(
                'key'           => 'field_cozy_game_term_release_year',
                'name'          => 'cozy_game_term_release_year',
                'label'         => '📅 Année de sortie',
                'type'          => 'text',
                'placeholder'   => 'Ex : 2020',
            ),
            array(
                'key'           => 'field_cozy_game_term_genre',
                'name'          => 'cozy_game_term_genre',
                'label'         => '🏷️ Genre',
                'type'          => 'text',
                'placeholder'   => 'Ex : Simulation de vie, Aventure',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'cozy_game',
                ),
            ),
        ),
        'position'     => 'normal',
        'style'        => 'default',
        'menu_order'   => 0,
    ) );
}
add_action( 'acf/init', 'cozy_register_acf_fields' );


/**
 * -----------------------------------------------
 * 4. HELPERS — Lecture des données ACF
 * -----------------------------------------------
 */

/**
 * Récupère les données d'un jeu depuis un terme de taxonomie cozy_game
 *
 * @param int $term_id
 * @return array
 */
function cozy_get_game_term_data( $term_id ) {
    if ( ! function_exists( 'get_field' ) ) {
        return array();
    }

    $prefix = 'cozy_game_' . $term_id;

    return array(
        'cover'        => get_field( 'cozy_game_cover', $prefix ) ?: null,
        'platforms'    => get_field( 'cozy_game_term_platforms', $prefix ) ?: array(),
        'developer'    => get_field( 'cozy_game_term_developer', $prefix ) ?: '',
        'publisher'    => get_field( 'cozy_game_term_publisher', $prefix ) ?: '',
        'players'      => get_field( 'cozy_game_term_players', $prefix ) ?: '',
        'playtime'     => get_field( 'cozy_game_term_playtime', $prefix ) ?: '',
        'release_year' => get_field( 'cozy_game_term_release_year', $prefix ) ?: '',
        'genre'        => get_field( 'cozy_game_term_genre', $prefix ) ?: '',
    );
}

/**
 * Récupère les données de la fiche jeu d'un article.
 * Priorité : taxonomie cozy_game → champs ACF sur l'article.
 *
 * @param int $post_id
 * @return array
 */
function cozy_get_game_data( $post_id ) {

    // 1. Priorité à la taxonomie cozy_game
    if ( taxonomy_exists( 'cozy_game' ) ) {
        $game_terms = wp_get_post_terms( $post_id, 'cozy_game', array( 'fields' => 'all' ) );

        if ( ! is_wp_error( $game_terms ) && ! empty( $game_terms ) ) {
            $term      = $game_terms[0];
            $term_data = cozy_get_game_term_data( $term->term_id );

            return array(
                'name'       => $term->name,
                'platforms'  => ! empty( $term_data['platforms'] ) ? $term_data['platforms'] : array(),
                'players'    => $term_data['players'],
                'playtime'   => $term_data['playtime'],
                'developer'  => $term_data['developer'],
                'publisher'  => $term_data['publisher'],
            );
        }
    }

    // 2. Fallback : champs ACF sur l'article
    if ( ! function_exists( 'get_field' ) ) {
        return array();
    }

    return array(
        'name'       => get_field( 'cozy_game_name', $post_id ) ?: '',
        'platforms'  => get_field( 'cozy_game_platforms', $post_id ) ?: array(),
        'players'    => get_field( 'cozy_game_players', $post_id ) ?: '',
        'playtime'   => get_field( 'cozy_game_playtime', $post_id ) ?: '',
        'developer'  => get_field( 'cozy_game_developer', $post_id ) ?: '',
        'publisher'  => get_field( 'cozy_game_publisher', $post_id ) ?: '',
    );
}

/**
 * Récupère toutes les notes d'un article
 *
 * @param int $post_id
 * @return array( 'global' => int, 'criteria' => array( slug => int ) )
 */
function cozy_get_ratings( $post_id ) {
    if ( ! function_exists( 'get_field' ) ) {
        return array( 'global' => 0, 'criteria' => array() );
    }

    $global   = (int) get_field( 'cozy_rating_global', $post_id );
    $criteria = cozy_get_rating_criteria();
    $scores   = array();

    foreach ( $criteria as $slug => $criterion ) {
        $value = (int) get_field( 'cozy_rating_' . $slug, $post_id );
        if ( $value > 0 ) {
            $scores[ $slug ] = $value;
        }
    }

    return array(
        'global'   => $global,
        'criteria' => $scores,
    );
}

/**
 * Récupère le verdict d'un article
 *
 * @param int $post_id
 * @return array( 'text' => string, 'pros' => array, 'cons' => array )
 */
function cozy_get_verdict( $post_id ) {
    if ( ! function_exists( 'get_field' ) ) {
        return array( 'text' => '', 'pros' => array(), 'cons' => array() );
    }

    $verdict   = get_field( 'cozy_verdict', $post_id ) ?: '';
    $pros_raw  = get_field( 'cozy_pros', $post_id ) ?: '';
    $cons_raw  = get_field( 'cozy_cons', $post_id ) ?: '';

    // Convertir les textarea (un point par ligne) en array
    $pros = array_values( array_filter( array_map( 'trim', explode( "\n", $pros_raw ) ) ) );
    $cons = array_values( array_filter( array_map( 'trim', explode( "\n", $cons_raw ) ) ) );

    return array(
        'text' => $verdict,
        'pros' => $pros,
        'cons' => $cons,
    );
}


/**
 * -----------------------------------------------
 * 5. AFFICHAGE FRONT-END — INJECTION AUTOMATIQUE
 * -----------------------------------------------
 */

/**
 * Vérifie si un article a des données gaming remplies
 *
 * @param int $post_id
 * @return bool
 */
function cozy_article_has_game_data( $post_id ) {
    // Vérifier la taxonomie cozy_game
    if ( taxonomy_exists( 'cozy_game' ) ) {
        $terms = wp_get_post_terms( $post_id, 'cozy_game' );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            return true;
        }
    }

    // Fallback ACF
    if ( ! function_exists( 'get_field' ) ) {
        return false;
    }

    $game    = cozy_get_game_data( $post_id );
    $ratings = cozy_get_ratings( $post_id );

    return ! empty( $game['name'] )
        || ! empty( $game['playtime'] )
        || $ratings['global'] > 0;
}

/**
 * Injecte la fiche jeu et le verdict dans le contenu de l'article
 */
function cozy_inject_article_game_card( $content ) {
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $post_id = get_the_ID();

    if ( ! cozy_article_has_game_data( $post_id ) ) {
        return $content;
    }

    $game_card = cozy_render_game_card( $post_id );
    $verdict   = cozy_render_verdict_card( $post_id );

    return $game_card . $content . $verdict;
}
add_filter( 'the_content', 'cozy_inject_article_game_card', 15 );


/**
 * -----------------------------------------------
 * 6. RENDU FRONT-END — FICHE JEU (en haut)
 * -----------------------------------------------
 */

/**
 * Génère le HTML de la fiche jeu
 *
 * @param int $post_id
 * @return string HTML
 */
function cozy_render_game_card( $post_id ) {
    $game    = cozy_get_game_data( $post_id );
    $ratings = cozy_get_ratings( $post_id );

    // Ne rien afficher si aucune donnée de fiche
    if ( empty( $game['name'] ) && empty( $game['platforms'] ) && empty( $game['playtime'] ) ) {
        return '';
    }

    // Type d'article (taxonomie)
    $type_data = null;
    $terms = wp_get_post_terms( $post_id, 'cozy_article_type', array( 'fields' => 'slugs' ) );
    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        $all_types = cozy_get_article_types();
        $type_data = isset( $all_types[ $terms[0] ] ) ? $all_types[ $terms[0] ] : null;
    }

    $all_platforms = cozy_get_platforms();
    $criteria      = cozy_get_rating_criteria();

    // Mapping icônes Lucide pour les critères
    $criteria_icons = array(
        'gameplay'      => 'gamepad-2',
        'direction_art' => 'palette',
        'bande_son'     => 'music',
        'scenario'      => 'scroll-text',
        'accessibilite' => 'accessibility',
        'ambiance_cozy' => 'coffee',
    );

    // Mapping icônes Lucide pour les types d'article
    $type_icons = array(
        'test'        => 'crosshair',
        'guide'       => 'book-open',
        'coup_coeur'  => 'heart',
        'actualite'   => 'newspaper',
        'dossier'     => 'folder-open',
        'decouverte'  => 'compass',
        'top'         => 'trophy',
    );

    ob_start();
    ?>
    <div class="cozy-game-card">

        <!-- Trait ambre gauche -->
        <div class="cozy-game-card__accent"></div>

        <!-- En-tête : nom du jeu + badge type + note globale -->
        <div class="cozy-game-card__header">
            <div class="cozy-game-card__header-top">
                <?php if ( $type_data ) :
                    $type_slug = ! empty( $terms[0] ) ? $terms[0] : '';
                    $type_icon = isset( $type_icons[ $type_slug ] ) ? $type_icons[ $type_slug ] : 'tag';
                ?>
                    <span class="cozy-game-card__type-badge" style="--badge-color: <?php echo esc_attr( $type_data['color'] ); ?>;">
                        <i data-lucide="<?php echo esc_attr( $type_icon ); ?>" class="lucide"></i>
                        <?php echo esc_html( $type_data['label'] ); ?>
                    </span>
                <?php endif; ?>

                <?php if ( $ratings['global'] > 0 ) : ?>
                    <div class="cozy-game-card__score">
                        <span class="cozy-game-card__score-number"><?php echo $ratings['global']; ?></span>
                        <span class="cozy-game-card__score-max">/5</span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $game['name'] ) ) : ?>
                <h2 class="cozy-game-card__game-name">
                    <i data-lucide="gamepad-2" class="lucide"></i>
                    <?php echo esc_html( $game['name'] ); ?>
                </h2>
            <?php endif; ?>

            <?php if ( $ratings['global'] > 0 ) : ?>
                <div class="cozy-game-card__stars">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <i data-lucide="star" class="lucide cozy-star <?php echo ( $i <= $ratings['global'] ) ? 'cozy-star--filled' : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Détails du jeu -->
        <div class="cozy-game-card__details">
            <?php if ( ! empty( $game['platforms'] ) ) : ?>
                <div class="cozy-game-card__detail cozy-game-card__detail--platforms">
                    <span class="cozy-game-card__detail-label">
                        <i data-lucide="monitor" class="lucide"></i> Plateformes
                    </span>
                    <div class="cozy-game-card__platforms">
                        <?php foreach ( $game['platforms'] as $slug ) :
                            $label = isset( $all_platforms[ $slug ] ) ? $all_platforms[ $slug ] : $slug;
                            // Retirer l'emoji du label pour n'avoir que le texte
                            $clean_label = preg_replace( '/^[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}]\s*/u', '', $label );
                        ?>
                            <span class="cozy-game-card__platform-tag"><?php echo esc_html( $clean_label ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $game['players'] ) ) : ?>
                <div class="cozy-game-card__detail">
                    <span class="cozy-game-card__detail-label">
                        <i data-lucide="users" class="lucide"></i> Joueurs
                    </span>
                    <span class="cozy-game-card__detail-value"><?php echo esc_html( $game['players'] ); ?></span>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $game['playtime'] ) ) : ?>
                <div class="cozy-game-card__detail">
                    <span class="cozy-game-card__detail-label">
                        <i data-lucide="clock" class="lucide"></i> Temps de jeu
                    </span>
                    <span class="cozy-game-card__detail-value"><?php echo esc_html( $game['playtime'] ); ?></span>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $game['developer'] ) ) : ?>
                <div class="cozy-game-card__detail">
                    <span class="cozy-game-card__detail-label">
                        <i data-lucide="code" class="lucide"></i> Développeur
                    </span>
                    <span class="cozy-game-card__detail-value"><?php echo esc_html( $game['developer'] ); ?></span>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $game['publisher'] ) ) : ?>
                <div class="cozy-game-card__detail">
                    <span class="cozy-game-card__detail-label">
                        <i data-lucide="building-2" class="lucide"></i> Éditeur
                    </span>
                    <span class="cozy-game-card__detail-value"><?php echo esc_html( $game['publisher'] ); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notes détaillées (barres XP) -->
        <?php if ( ! empty( $ratings['criteria'] ) ) : ?>
            <div class="cozy-game-card__ratings">
                <h3 class="cozy-game-card__ratings-title">
                    <i data-lucide="bar-chart-3" class="lucide"></i> Notes détaillées
                </h3>
                <div class="cozy-game-card__ratings-grid">
                    <?php foreach ( $ratings['criteria'] as $slug => $value ) :
                        if ( ! isset( $criteria[ $slug ] ) ) continue;
                        $criterion = $criteria[ $slug ];
                        $percent   = ( $value / 5 ) * 100;
                        $icon_name = isset( $criteria_icons[ $slug ] ) ? $criteria_icons[ $slug ] : 'circle';
                    ?>
                        <div class="cozy-game-card__rating-row">
                            <span class="cozy-game-card__rating-label">
                                <i data-lucide="<?php echo esc_attr( $icon_name ); ?>" class="lucide"></i>
                                <?php echo esc_html( $criterion['label'] ); ?>
                            </span>
                            <div class="cozy-game-card__rating-bar">
                                <div class="cozy-game-card__rating-fill" style="width: <?php echo $percent; ?>%;"></div>
                            </div>
                            <span class="cozy-game-card__rating-score"><?php echo $value; ?><small>/5</small></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 7. RENDU FRONT-END — VERDICT (en bas)
 * -----------------------------------------------
 */

/**
 * Génère le HTML du verdict
 *
 * @param int $post_id
 * @return string HTML
 */
function cozy_render_verdict_card( $post_id ) {
    $verdict = cozy_get_verdict( $post_id );
    $ratings = cozy_get_ratings( $post_id );

    // Ne rien afficher si pas de verdict
    if ( empty( $verdict['text'] ) && empty( $verdict['pros'] ) && empty( $verdict['cons'] ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="cozy-verdict">

        <!-- Trait ambre gauche -->
        <div class="cozy-verdict__accent"></div>

        <div class="cozy-verdict__header">
            <i data-lucide="scroll-text" class="lucide"></i>
            <h3>Notre verdict</h3>
        </div>

        <?php if ( ! empty( $verdict['text'] ) ) : ?>
            <blockquote class="cozy-verdict__quote">
                <i data-lucide="quote" class="lucide cozy-verdict__quote-icon"></i>
                <p><?php echo esc_html( $verdict['text'] ); ?></p>
            </blockquote>
        <?php endif; ?>

        <?php if ( ! empty( $verdict['pros'] ) || ! empty( $verdict['cons'] ) ) : ?>
            <div class="cozy-verdict__pros-cons">
                <?php if ( ! empty( $verdict['pros'] ) ) : ?>
                    <div class="cozy-verdict__column cozy-verdict__column--pros">
                        <h4 class="cozy-verdict__column-title">
                            <i data-lucide="circle-check" class="lucide"></i> Points forts
                        </h4>
                        <ul>
                            <?php foreach ( $verdict['pros'] as $pro ) : ?>
                                <li>
                                    <i data-lucide="plus" class="lucide"></i>
                                    <span><?php echo esc_html( $pro ); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $verdict['cons'] ) ) : ?>
                    <div class="cozy-verdict__column cozy-verdict__column--cons">
                        <h4 class="cozy-verdict__column-title">
                            <i data-lucide="circle-x" class="lucide"></i> Points faibles
                        </h4>
                        <ul>
                            <?php foreach ( $verdict['cons'] as $con ) : ?>
                                <li>
                                    <i data-lucide="minus" class="lucide"></i>
                                    <span><?php echo esc_html( $con ); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( $ratings['global'] > 0 ) : ?>
            <div class="cozy-verdict__final-score">
                <div class="cozy-verdict__score-badge">
                    <span class="cozy-verdict__score-number"><?php echo $ratings['global']; ?></span>
                    <span class="cozy-verdict__score-max">/5</span>
                </div>
                <div class="cozy-verdict__score-info">
                    <span class="cozy-verdict__score-label">Note finale</span>
                    <div class="cozy-verdict__score-stars">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <i data-lucide="star" class="lucide cozy-star <?php echo ( $i <= $ratings['global'] ) ? 'cozy-star--filled' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 8. COLONNES ADMIN PERSONNALISÉES
 * -----------------------------------------------
 */

/**
 * Ajoute des colonnes dans la liste des articles (admin)
 */
function cozy_article_admin_columns( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;

        if ( 'title' === $key ) {
            $new_columns['cozy_rating'] = '⭐ Note';
        }
    }

    return $new_columns;
}
add_filter( 'manage_posts_columns', 'cozy_article_admin_columns' );

/**
 * Remplit les colonnes personnalisées
 */
function cozy_article_admin_column_content( $column, $post_id ) {
    if ( ! function_exists( 'get_field' ) ) {
        return;
    }

    switch ( $column ) {
        case 'cozy_rating':
            $rating = (int) get_field( 'cozy_rating_global', $post_id );
            if ( $rating > 0 ) {
                $stars = str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating );
                echo '<span style="color:#f59e0b;font-size:1.1em;">' . $stars . '</span>';
            } else {
                echo '<span style="color:#999;">—</span>';
            }
            break;
    }
}
add_action( 'manage_posts_custom_column', 'cozy_article_admin_column_content', 10, 2 );


/**
 * -----------------------------------------------
 * 9. ENQUEUE DES ASSETS FRONT-END
 * -----------------------------------------------
 */
function cozy_articles_enqueue_assets() {
    // Charger uniquement sur les pages qui affichent des fiches jeu
    if ( ! is_singular( 'post' ) && ! is_archive() && ! is_search() ) {
        return;
    }

    wp_enqueue_style(
        'cozy-articles',
        get_template_directory_uri() . '/assets/css/cozy-articles.css',
        array( 'cozy-main' ),
        '1.7.0'
    );
}
add_action( 'wp_enqueue_scripts', 'cozy_articles_enqueue_assets' );
