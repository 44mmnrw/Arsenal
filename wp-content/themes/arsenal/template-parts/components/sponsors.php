<?php
/**
 * Template part: Sponsors Section (Carousel)
 * 
 * Карусель партнёров и спонсоров на главной странице
 * Динамически загружает данные из БД
 *
 * @package Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Получаем активных спонсоров
$sponsors = Arsenal_Sponsors::get_sponsors( array( 
    'is_active' => 1,
    'orderby'   => 'order_index'
) );

// Если спонсоров нет, не показываем секцию
if ( empty( $sponsors ) ) {
    return;
}
?>

<section class="sponsors-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php esc_html_e( 'Наши спонсоры', 'arsenal' ); ?></h2>
            <a href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>" class="section-link">
                <?php esc_html_e( 'Все спонсоры', 'arsenal' ); ?> <?php arsenal_icon( 'icon-arrow-right' ); ?>
            </a>
        </div>

        <div class="sponsors-carousel-wrapper">
            <div class="sponsors-carousel" id="sponsors-carousel">
                <?php foreach ( $sponsors as $sponsor ) : ?>
                    <div class="sponsor-slide">
                        <a 
                            href="<?php echo esc_url( $sponsor->website_url ); ?>" 
                            class="sponsor-card" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            title="<?php echo esc_attr( $sponsor->name ); ?>"
                        >
                            <?php if ( ! empty( $sponsor->logo_url ) ) : ?>
                                <img 
                                    src="<?php echo esc_url( $sponsor->logo_url ); ?>" 
                                    alt="<?php echo esc_attr( $sponsor->name ); ?>" 
                                    class="sponsor-logo"
                                    loading="lazy"
                                >
                            <?php else : ?>
                                <div class="sponsor-placeholder">
                                    <span><?php echo esc_html( $sponsor->name ); ?></span>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

