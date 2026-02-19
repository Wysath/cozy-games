<?php
/**
 * Template : Formulaire de recherche.
 *
 * @package CozyGaming
 */
?>
<form role="search" method="get" class="cozy-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="cozy-search-form__label screen-reader-text" for="search-field">
        <?php esc_html_e( 'Rechercher :', 'cozy-gaming' ); ?>
    </label>
    <input
        type="search"
        id="search-field"
        class="cozy-search-form__input"
        placeholder="<?php esc_attr_e( 'Rechercher…', 'cozy-gaming' ); ?>"
        value="<?php echo get_search_query(); ?>"
        name="s"
    >
    <button type="submit" class="cozy-search-form__submit cozy-btn cozy-btn--primary">
        <i data-lucide="search" class="lucide"></i>
        <?php esc_html_e( 'Rechercher', 'cozy-gaming' ); ?>
    </button>
</form>
