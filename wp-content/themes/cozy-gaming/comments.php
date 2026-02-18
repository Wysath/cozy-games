<?php
/**
 * Template : Commentaires.
 *
 * @package CozyGaming
 */

if ( post_password_required() ) {
    return;
}
?>

<section id="comments" class="cozy-comments">

    <?php if ( have_comments() ) : ?>

        <h2 class="cozy-comments__title">
            💬 <?php
            $count = get_comments_number();
            printf(
                _n( '%d commentaire', '%d commentaires', $count, 'cozy-gaming' ),
                $count
            );
            ?>
        </h2>

        <ol class="cozy-comments__list">
            <?php
            wp_list_comments( [
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 40,
            ] );
            ?>
        </ol>

        <?php
        the_comments_navigation( [
            'prev_text' => '← ' . __( 'Commentaires précédents', 'cozy-gaming' ),
            'next_text' => __( 'Commentaires suivants', 'cozy-gaming' ) . ' →',
        ] );
        ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="cozy-comments__closed">
            <?php esc_html_e( 'Les commentaires sont fermés.', 'cozy-gaming' ); ?>
        </p>
    <?php endif; ?>

    <?php
    comment_form( [
        'title_reply'         => __( 'Laisser un commentaire', 'cozy-gaming' ),
        'title_reply_to'      => __( 'Répondre à %s', 'cozy-gaming' ),
        'cancel_reply_link'   => __( 'Annuler', 'cozy-gaming' ),
        'label_submit'        => __( 'Publier', 'cozy-gaming' ),
        'class_container'     => 'cozy-comment-form',
        'class_form'          => 'cozy-comment-form__form',
        'class_submit'        => 'cozy-btn cozy-btn--primary',
    ] );
    ?>

</section>
