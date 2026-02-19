<?php
/**
 * ============================================================================
 * MODULE : Galerie Setups Gaming (style Pinterest)
 * ============================================================================
 *
 * Permet aux membres de publier une photo de leur setup gaming
 * avec un titre et une description. Les setups sont affichés dans
 * une grille masonry (Pinterest-like) via un shortcode.
 *
 * - CPT « cozy_setup » pour stocker les soumissions
 * - Formulaire d'upload front-end (AJAX)
 * - Grille masonry CSS-only (pas de librairie externe)
 * - Lightbox native pour agrandir les photos
 * - Modération : les setups sont en « pending » par défaut
 *
 * Shortcode : [cozy_setups]
 *
 * @package CozyGaming
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * -----------------------------------------------
 * 1. CUSTOM POST TYPE
 * -----------------------------------------------
 */

/**
 * Enregistre le CPT « cozy_setup » pour les photos de setups
 */
function cozy_register_setup_cpt() {
    $labels = array(
        'name'               => 'Setups Gaming',
        'singular_name'      => 'Setup',
        'menu_name'          => 'Setups',
        'add_new'            => 'Ajouter un setup',
        'add_new_item'       => 'Ajouter un setup',
        'edit_item'          => 'Modifier le setup',
        'view_item'          => 'Voir le setup',
        'all_items'          => 'Tous les setups',
        'search_items'       => 'Rechercher un setup',
        'not_found'          => 'Aucun setup trouvé',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-desktop',
        'supports'            => array( 'title', 'editor', 'thumbnail', 'author' ),
        'has_archive'         => false,
        'exclude_from_search' => true,
        'show_in_rest'        => true,
        'capability_type'     => 'post',
    );

    register_post_type( 'cozy_setup', $args );
}
add_action( 'init', 'cozy_register_setup_cpt' );


/**
 * -----------------------------------------------
 * 2. SHORTCODE PRINCIPAL : GALERIE + FORMULAIRE
 * -----------------------------------------------
 */

/**
 * Affiche la galerie masonry des setups + le formulaire d'upload
 *
 * @param array $atts Attributs du shortcode
 * @return string HTML
 */
function cozy_shortcode_setups( $atts ) {
    $atts = shortcode_atts( array(
        'per_page' => 20,
    ), $atts, 'cozy_setups' );

    ob_start();
    ?>
    <div class="cozy-setups" id="cozy-setups">

        <?php // --- Formulaire d'upload (membres connectés uniquement) --- ?>
        <?php if ( is_user_logged_in() ) : ?>
            <?php echo cozy_render_setup_form(); ?>
        <?php else : ?>
            <div class="cozy-setups__login-prompt">
                <p><i data-lucide="lock"></i> <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">Connecte-toi</a> pour partager ton setup !</p>
            </div>
        <?php endif; ?>

        <?php // --- Galerie masonry --- ?>
        <?php echo cozy_render_setup_gallery( (int) $atts['per_page'] ); ?>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cozy_setups', 'cozy_shortcode_setups' );



/**
 * -----------------------------------------------
 * 3. FORMULAIRE D'UPLOAD — classes alignées sur le CSS
 * -----------------------------------------------
 */
function cozy_render_setup_form() {
    ob_start();
    ?>
    <div class="cozy-setups__form-wrapper" id="cozy-setup-form-wrapper">

        <button class="cozy-setups__add-btn" id="cozy-setup-toggle-form" type="button">
            <i data-lucide="camera"></i> Partager mon setup
        </button>

        <form class="cozy-setups__form" id="cozy-setup-form" style="display:none;" enctype="multipart/form-data" novalidate>

            <!-- En-tête -->
            <div class="cozy-setups__form-header">
                <i data-lucide="camera"></i>
                <h3 class="cozy-setups__form-title">Partage ton setup gaming</h3>
            </div>

            <!-- Layout deux colonnes : champs | upload -->
            <div class="cozy-setups__form-body">

                <!-- Colonne gauche : champs texte -->
                <div class="cozy-setups__form-fields">

                    <div class="cozy-setups__form-group">
                        <label class="cozy-setups__form-label" for="cozy-setup-title">Titre</label>
                        <input
                            class="cozy-setups__form-input"
                            type="text"
                            id="cozy-setup-title"
                            name="setup_title"
                            placeholder="Ex : Mon coin cozy gaming"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="cozy-setups__form-group">
                        <label class="cozy-setups__form-label" for="cozy-setup-description">
                            Description <small style="text-transform:none;font-weight:400;color:var(--cozy-muted)">(optionnel)</small>
                        </label>
                        <textarea
                            class="cozy-setups__form-textarea"
                            id="cozy-setup-description"
                            name="setup_description"
                            placeholder="Décris ton setup, ton matériel préféré, ton ambiance…"
                            maxlength="500"
                        ></textarea>
                    </div>

                </div>

                <!-- Colonne droite : upload -->
                <div class="cozy-setups__form-upload-col">
                    <span class="cozy-setups__form-upload-label">Photo de ton setup</span>

                    <!-- Input natif masqué — déclenché via JS -->
                    <input
                        class="cozy-setups__upload-input"
                        type="file"
                        id="cozy-setup-photo"
                        name="setup_photo"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <!-- Zone de drop visible -->
                    <div class="cozy-setups__upload-zone" id="cozy-setup-dropzone">
                        <span class="cozy-setups__upload-icon"><i data-lucide="image"></i></span>
                        <span class="cozy-setups__upload-text">Clique ou glisse ta photo ici</span>
                        <small class="cozy-setups__upload-hint">JPG, PNG ou WebP — 5 Mo max</small>
                    </div>

                    <!-- Prévisualisation après sélection -->
                    <img class="cozy-setups__upload-preview" id="cozy-setup-preview" src="" alt="Aperçu">
                </div>

            </div><!-- /.cozy-setups__form-body -->

            <!-- Actions -->
            <div class="cozy-setups__form-actions">
                <button type="submit" class="cozy-setups__submit-btn" id="cozy-setup-submit">
                    <i data-lucide="upload"></i> Publier mon setup
                </button>
                <button type="button" class="cozy-setups__cancel-btn" id="cozy-setup-cancel">
                    Annuler
                </button>
            </div>

            <!-- Message de retour AJAX -->
            <div class="cozy-setups__form-message" id="cozy-setup-message" style="display:none;"></div>

        </form>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 4. GALERIE MASONRY
 * -----------------------------------------------
 */

/**
 * Génère le HTML de la galerie masonry des setups
 *
 * @param int $per_page Nombre de setups à afficher
 * @return string HTML de la galerie
 */
function cozy_render_setup_gallery( $per_page = 20 ) {
    $paged = max( 1, get_query_var( 'paged', 1 ) );

    $setups = new WP_Query( array(
        'post_type'      => 'cozy_setup',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );

    ob_start();

    if ( ! $setups->have_posts() ) {
        ?>
        <div class="cozy-setups__empty">
            <p>Aucun setup partagé pour le moment.</p>
            <p>Sois le premier à montrer ton coin gaming !</p>
        </div>
        <?php
        return ob_get_clean();
    }

    ?>
    <div class="cozy-setups__gallery" id="cozy-setups-gallery">
        <?php while ( $setups->have_posts() ) : $setups->the_post();
            $setup_id    = get_the_ID();
            $author_id   = get_the_author_meta( 'ID' );
            $author_name = get_the_author_meta( 'display_name' );
            $thumbnail   = get_the_post_thumbnail_url( $setup_id, 'large' );
            $full_image  = get_the_post_thumbnail_url( $setup_id, 'full' );
            $description = get_the_content();
            $date        = get_the_date( 'j M Y' );

            if ( empty( $thumbnail ) ) {
                continue;
            }
        ?>
            <div class="cozy-setups__card" data-setup-id="<?php echo esc_attr( $setup_id ); ?>"<?php if ( is_user_logged_in() && (int) $author_id === get_current_user_id() ) echo ' data-own="true"'; ?>>
                <?php if ( is_user_logged_in() && ( (int) $author_id === get_current_user_id() || current_user_can( 'delete_others_posts' ) ) ) : ?>
                    <button class="cozy-setups__card-delete" title="Supprimer"><i data-lucide="trash-2"></i></button>
                <?php endif; ?>
                <div class="cozy-setups__card-image" data-full="<?php echo esc_url( $full_image ); ?>">
                    <img
                        src="<?php echo esc_url( $thumbnail ); ?>"
                        alt="<?php echo esc_attr( get_the_title() ); ?>"
                        loading="lazy"
                    >
                </div>

                <div class="cozy-setups__card-body">
                    <h4 class="cozy-setups__card-title"><?php echo esc_html( get_the_title() ); ?></h4>

                    <?php if ( ! empty( trim( $description ) ) ) : ?>
                        <p class="cozy-setups__card-desc"><?php echo esc_html( wp_trim_words( $description, 20 ) ); ?></p>
                    <?php endif; ?>

                    <div class="cozy-setups__card-footer">
                        <div class="cozy-setups__card-author">
                            <?php echo get_avatar( $author_id, 28 ); ?>
                            <span><?php echo esc_html( $author_name ); ?></span>
                        </div>
                        <time class="cozy-setups__card-date"><?php echo esc_html( $date ); ?></time>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php
    // Pagination
    if ( $setups->max_num_pages > 1 ) {
        echo '<div class="cozy-setups__pagination">';
        echo paginate_links( array(
            'total'   => $setups->max_num_pages,
            'current' => $paged,
            'format'  => '?paged=%#%',
        ) );
        echo '</div>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}


/**
 * -----------------------------------------------
 * 5. TRAITEMENT AJAX DE L'UPLOAD
 * -----------------------------------------------
 */

/**
 * Traitement AJAX : upload et création du setup
 */
function cozy_ajax_upload_setup() {
    // Vérification du nonce
    if ( ! check_ajax_referer( 'cozy_upload_setup', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Erreur de sécurité. Recharge la page et réessaie.' ) );
    }

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Tu dois être connecté·e pour partager ton setup.' ) );
    }

    $user_id = get_current_user_id();

    // Vérifier le titre
    $title = isset( $_POST['setup_title'] ) ? sanitize_text_field( $_POST['setup_title'] ) : '';
    if ( empty( $title ) ) {
        wp_send_json_error( array( 'message' => 'Le titre est obligatoire.' ) );
    }

    if ( mb_strlen( $title ) > 100 ) {
        wp_send_json_error( array( 'message' => 'Le titre ne doit pas dépasser 100 caractères.' ) );
    }

    // Description
    $description = isset( $_POST['setup_description'] ) ? sanitize_textarea_field( $_POST['setup_description'] ) : '';
    if ( mb_strlen( $description ) > 500 ) {
        $description = mb_substr( $description, 0, 500 );
    }

    // Vérifier la photo
    if ( empty( $_FILES['setup_photo'] ) || $_FILES['setup_photo']['error'] !== UPLOAD_ERR_OK ) {
        wp_send_json_error( array( 'message' => 'La photo est obligatoire.' ) );
    }

    $file = $_FILES['setup_photo'];

    // Vérifier le type MIME
    $allowed_types = array( 'image/jpeg', 'image/png', 'image/webp' );
    $finfo = finfo_open( FILEINFO_MIME_TYPE );
    $mime  = finfo_file( $finfo, $file['tmp_name'] );
    finfo_close( $finfo );

    if ( ! in_array( $mime, $allowed_types, true ) ) {
        wp_send_json_error( array( 'message' => 'Format non supporté. Utilise JPG, PNG ou WebP.' ) );
    }

    // Vérifier la taille (5 Mo max)
    if ( $file['size'] > 5 * 1024 * 1024 ) {
        wp_send_json_error( array( 'message' => 'La photo ne doit pas dépasser 5 Mo.' ) );
    }

    // Limiter le nombre de setups par utilisateur (max 5)
    $user_setups = new WP_Query( array(
        'post_type'      => 'cozy_setup',
        'post_status'    => array( 'publish', 'pending' ),
        'author'         => $user_id,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );

    if ( $user_setups->found_posts >= 5 ) {
        wp_send_json_error( array( 'message' => 'Tu as déjà partagé 5 setups. Supprime-en un pour en ajouter un nouveau.' ) );
    }

    // Créer le post (en pending pour modération)
    $post_id = wp_insert_post( array(
        'post_type'    => 'cozy_setup',
        'post_title'   => $title,
        'post_content' => $description,
        'post_status'  => 'pending',
        'post_author'  => $user_id,
    ) );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la création. Réessaie.' ) );
    }

    // Upload de la photo via WordPress
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload( 'setup_photo', $post_id );

    if ( is_wp_error( $attachment_id ) ) {
        wp_delete_post( $post_id, true );
        wp_send_json_error( array( 'message' => 'Erreur lors de l\'upload de la photo. Réessaie.' ) );
    }

    // Définir comme image mise en avant
    set_post_thumbnail( $post_id, $attachment_id );

    wp_send_json_success( array(
        'message' => 'Setup partagé avec succès ! Il sera visible après validation par un animateur.',
    ) );
}
add_action( 'wp_ajax_cozy_upload_setup', 'cozy_ajax_upload_setup' );


/**
 * -----------------------------------------------
 * 6. SUPPRESSION D'UN SETUP (par son auteur)
 * -----------------------------------------------
 */

/**
 * Traitement AJAX : suppression d'un setup par son auteur
 */
function cozy_ajax_delete_setup() {
    if ( ! check_ajax_referer( 'cozy_upload_setup', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Erreur de sécurité.' ) );
    }

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Tu dois être connecté·e.' ) );
    }

    $setup_id = isset( $_POST['setup_id'] ) ? absint( $_POST['setup_id'] ) : 0;
    if ( ! $setup_id ) {
        wp_send_json_error( array( 'message' => 'Setup introuvable.' ) );
    }

    $post = get_post( $setup_id );
    if ( ! $post || 'cozy_setup' !== $post->post_type ) {
        wp_send_json_error( array( 'message' => 'Setup introuvable.' ) );
    }

    // Seul l'auteur ou un admin peut supprimer
    if ( (int) $post->post_author !== get_current_user_id() && ! current_user_can( 'delete_others_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Tu ne peux supprimer que tes propres setups.' ) );
    }

    // Supprimer l'image attachée
    $thumbnail_id = get_post_thumbnail_id( $setup_id );
    if ( $thumbnail_id ) {
        wp_delete_attachment( $thumbnail_id, true );
    }

    wp_delete_post( $setup_id, true );

    wp_send_json_success( array( 'message' => 'Setup supprimé.' ) );
}
add_action( 'wp_ajax_cozy_delete_setup', 'cozy_ajax_delete_setup' );


/**
 * -----------------------------------------------
 * 7. LIGHTBOX HTML (AJOUTÉE AU FOOTER)
 * -----------------------------------------------
 */

/**
 * Ajoute le markup de la lightbox au footer
 */
function cozy_setup_lightbox_markup() {
    // N'ajouter que si le shortcode est potentiellement affiché
    ?>
    <div class="cozy-lightbox" id="cozy-lightbox" role="dialog" aria-modal="true">
        <button class="cozy-lightbox__close" id="cozy-lightbox-close" aria-label="Fermer"><i data-lucide="x"></i></button>
        <div class="cozy-lightbox__content">
            <img src="" alt="" id="cozy-lightbox-img">
            <div class="cozy-lightbox__info" id="cozy-lightbox-info">
                <h4 id="cozy-lightbox-title"></h4>
                <p id="cozy-lightbox-desc"></p>
                <div class="cozy-lightbox__author" id="cozy-lightbox-author"></div>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'cozy_setup_lightbox_markup' );



/**
 * -----------------------------------------------
 * 8. ENQUEUE — corrigé pour thème enfant
 * -----------------------------------------------
 */
function cozy_setups_enqueue_assets() {
    /*
     * get_stylesheet_directory_uri() pointe vers le thème ACTIF (enfant).
     * get_template_directory_uri()   pointe vers le thème PARENT.
     * Si tes assets sont dans le thème enfant, utilise get_stylesheet_directory_uri().
     */
    wp_enqueue_style(
        'cozy-setups',
        get_stylesheet_directory_uri() . '/assets/css/cozy-setups.css',
        array(),
        '3.1.0'
    );

    wp_enqueue_script(
        'cozy-setups',
        get_stylesheet_directory_uri() . '/assets/js/cozy-setups.js',
        array(),
        '3.1.0',
        true
    );

    wp_localize_script( 'cozy-setups', 'cozySetups', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cozy_upload_setup' ),
        'userId'  => get_current_user_id(),
    ) );
}
add_action( 'wp_enqueue_scripts', 'cozy_setups_enqueue_assets' );