<?php
/**
 * Template pour la liste des événements (archive).
 *
 * @package CozyEvents
 * @since 1.1.0
 */

get_header();
?>
<main id="main-content" class="cozy-main">
    <div class="cozy-container">
        <div class="cozy-events-archive">
            <h1>Événements à venir</h1>
            <?php echo do_shortcode( '[cozy_events limit="12"]' ); ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
