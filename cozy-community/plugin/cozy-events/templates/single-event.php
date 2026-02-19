<?php
/**
 * Template : Événement single.
 *
 * Affiche le détail d'un événement avec inscription,
 * charte de bienveillance et design Cozy Grove.
 *
 * @package CozyEvents
 * @since 1.1.0
 */

get_header();

while ( have_posts() ) : the_post();
    $event_id    = get_the_ID();
    $date        = get_post_meta( $event_id, '_cozy_event_date', true );
    $time        = get_post_meta( $event_id, '_cozy_event_time', true );
    $link        = get_post_meta( $event_id, '_cozy_event_link', true );
    $is_troc     = get_post_meta( $event_id, '_cozy_event_is_troc', true );
    $places      = (int) get_post_meta( $event_id, '_cozy_event_places', true );
    $places_left = cozy_get_places_left( $event_id );
    $count       = count( cozy_get_registrants( $event_id ) );
    $user_id     = get_current_user_id();
    $is_reg      = is_user_logged_in() && cozy_is_registered( $event_id, $user_id );
    $games       = get_the_terms( $event_id, 'cozy_game' );
    $types       = get_the_terms( $event_id, 'cozy_event_type' );
?>

<main id="main-content" class="cozy-main">
    <div class="cozy-container cozy-container--narrow">

        <article class="cozy-single-event" data-event-id="<?php echo esc_attr( $event_id ); ?>">

            <!-- ── En-tête ── -->
            <header class="cozy-single-event__header">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="cozy-single-event__cover">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="cozy-single-event__tags">
                    <?php if ( ! is_wp_error( $types ) && ! empty( $types ) ) : ?>
                        <?php foreach ( $types as $t ) : ?>
                            <span class="cozy-badge cozy-badge--type">
                                <i data-lucide="tag" class="lucide"></i>
                                <?php echo esc_html( $t->name ); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ( $is_troc ) : ?>
                        <span class="cozy-badge cozy-badge--troc">
                            <i data-lucide="repeat" class="lucide"></i>
                            Troc / Échange
                        </span>
                    <?php endif; ?>
                    <?php if ( ! is_wp_error( $games ) && ! empty( $games ) ) : ?>
                        <?php foreach ( $games as $g ) : ?>
                            <span class="cozy-badge cozy-badge--game">
                                <i data-lucide="gamepad-2" class="lucide"></i>
                                <?php echo esc_html( $g->name ); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h1 class="cozy-single-event__title"><?php the_title(); ?></h1>

                <ul class="cozy-single-event__infos">
                    <?php if ( $date ) : ?>
                        <li class="cozy-single-event__info">
                            <i data-lucide="calendar" class="lucide"></i>
                            <span>
                                <?php echo esc_html( date_i18n( 'l j F Y', strtotime( $date ) ) ); ?>
                                <?php if ( $time ) : ?>
                                    à <?php echo esc_html( $time ); ?>
                                <?php endif; ?>
                            </span>
                        </li>
                    <?php endif; ?>
                    <?php if ( $link ) : ?>
                        <li class="cozy-single-event__info">
                            <i data-lucide="link" class="lucide"></i>
                            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
                                <?php echo esc_html( $link ); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="cozy-single-event__info">
                        <i data-lucide="users" class="lucide"></i>
                        <span>
                            <?php if ( $places_left === -1 ) : ?>
                                <?php echo esc_html( $count ); ?> inscrit(s) · Places illimitées
                            <?php elseif ( $places_left === 0 ) : ?>
                                <strong class="cozy-text--error">Complet</strong>
                                (<?php echo esc_html( $places ); ?> places · <?php echo esc_html( $count ); ?> inscrits)
                            <?php else : ?>
                                <?php echo esc_html( $places_left ); ?> place(s) restante(s) sur <?php echo esc_html( $places ); ?>
                            <?php endif; ?>
                        </span>
                    </li>
                </ul>
            </header>

            <!-- ── Contenu ── -->
            <div class="cozy-single-event__content entry-content">
                <?php the_content(); ?>
            </div>

            <!-- ── Zone d'inscription ── -->
            <div class="cozy-registration-box" id="cozy-registration-box">

                <?php if ( ! is_user_logged_in() ) : ?>
                    <!-- Non connecté -->
                    <div class="cozy-registration-box__login">
                        <i data-lucide="log-in" class="lucide"></i>
                        <p>
                            <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">Connecte-toi</a>
                            ou
                            <a href="<?php echo esc_url( wp_registration_url() ); ?>">crée un compte</a>
                            pour t'inscrire à cet événement.
                        </p>
                    </div>

                <?php elseif ( $is_reg ) : ?>
                    <!-- Déjà inscrit -->
                    <div class="cozy-registration-box__confirmed">
                        <div class="cozy-registration-box__status">
                            <i data-lucide="circle-check" class="lucide"></i>
                            <span>Place réservée ! Prépare ton plaid et ta boisson chaude. 🌿</span>
                        </div>
                        <button class="cozy-btn cozy-btn--outline cozy-btn--sm" id="cozy-unregister-btn" data-event-id="<?php echo esc_attr( $event_id ); ?>">
                            <i data-lucide="x"></i>
                            Me désinscrire
                        </button>
                    </div>

                <?php elseif ( $places_left === 0 ) : ?>
                    <!-- Complet -->
                    <div class="cozy-registration-box__full">
                        <i data-lucide="alert-circle" class="lucide"></i>
                        <span>Cet événement est complet. Reviens vite pour les prochaines sessions !</span>
                    </div>

                <?php elseif ( ! cozy_has_accepted_charter( $user_id ) ) : ?>
                    <!-- Charte non acceptée -->
                    <?php echo cozy_render_charter_block( $event_id ); ?>

                <?php else : ?>
                    <!-- Formulaire d'inscription -->
                    <div class="cozy-registration-box__form">
                        <h3 class="cozy-registration-box__title">
                            <i data-lucide="pen-line" class="lucide"></i>
                            S'inscrire à cet événement
                        </h3>

                        <?php if ( $is_troc ) : ?>
                            <div class="cozy-registration-box__troc">
                                <label for="cozy-troc-note">
                                    <i data-lucide="repeat" class="lucide"></i>
                                    Ce que j'apporte / ce que je cherche (facultatif)
                                </label>
                                <textarea id="cozy-troc-note" rows="3" placeholder="Ex : J'apporte des tulipes roses AC, je cherche des étoiles de mer..."></textarea>
                            </div>
                        <?php endif; ?>

                        <button class="cozy-btn cozy-btn--primary" id="cozy-register-btn" data-event-id="<?php echo esc_attr( $event_id ); ?>">
                            <i data-lucide="sparkles"></i>
                            M'inscrire
                        </button>
                    </div>
                <?php endif; ?>

                <div id="cozy-registration-message" class="cozy-registration-box__message" style="display:none;"></div>
            </div>

        </article>

    </div>
</main>

<?php endwhile; get_footer(); ?>
