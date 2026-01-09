<?php
/**
 * Template part: Sponsors Section
 * 
 * Секция партнёров и спонсоров на главной странице
 *
 * @package Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<section class="sponsors-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php esc_html_e( 'Наши спонсоры', 'arsenal' ); ?></h2>
        </div>

        <div class="sponsors-grid">
            <a href="#" class="sponsor-card">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/sponsors/sponsor-1.png" alt="Спонсор 1" class="sponsor-logo">
            </a>
            <a href="#" class="sponsor-card">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/sponsors/sponsor-2.png" alt="Спонсор 2" class="sponsor-logo">
            </a>
            <a href="#" class="sponsor-card">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/sponsors/sponsor-3.png" alt="Спонсор 3" class="sponsor-logo">
            </a>
            <a href="#" class="sponsor-card">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/sponsors/sponsor-4.png" alt="Спонсор 4" class="sponsor-logo">
            </a>
            <a href="#" class="sponsor-card">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/sponsors/sponsor-5.png" alt="Спонсор 5" class="sponsor-logo">
            </a>
            <a href="#" class="sponsor-card">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/sponsors/sponsor-6.png" alt="Спонсор 6" class="sponsor-logo">
            </a>
        </div>
    </div>
</section>
