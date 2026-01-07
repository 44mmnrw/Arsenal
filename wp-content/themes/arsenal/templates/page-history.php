<?php
/**
 * Шаблон страницы "История клуба"
 *
 * Template Name: История клуба
 * Template Post Type: page
 *
 * @package Arsenal
 */

get_header();
wp_enqueue_style( 'arsenal-history', get_template_directory_uri() . '/assets/css/history-page.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );
?>

<div class="club-page">
    <div class="club-page__content">
        <div class="club-page__section">
            <?php
            the_content();
            
            wp_link_pages(
                array(
                    'before' => '<div class="page-links">' . esc_html__( 'Страницы:', 'arsenal' ),
                    'after'  => '</div>',
                )
            );
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
