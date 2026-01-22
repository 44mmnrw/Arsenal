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

<section class="sponsors-section" style="background-color: #ffffff;">
	<div class="container">
		<div class="section-header">
			<h2 class="section-title"><?php esc_html_e( 'Наши партнёры', 'arsenal' ); ?></h2>
			<a href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>" class="section-link">
				<?php esc_html_e( 'Все партнёры', 'arsenal' ); ?>
				<?php arsenal_icon( 'icon-arrow-right', 14, 14 ); ?>
			</a>
		</div>

		<div class="sponsors-carousel-wrapper">
			<div class="sponsors-carousel" id="sponsors-carousel">
				<?php foreach ( $sponsors as $sponsor ) : ?>
					<div class="sponsor-slide">
						<?php if ( ! empty( $sponsor->website_url ) ) : ?>
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
								<lottie-player src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/wired-outline-61-camera-hover-flash.json' ); ?>" background="transparent"></lottie-player>
							<?php endif; ?>
						</a>
						<?php endif; ?>
						<?php if ( empty( $sponsor->website_url ) ) : ?>
						<div class="sponsor-card sponsor-card--no-link">
							<?php if ( ! empty( $sponsor->logo_url ) ) : ?>
								<img 
									src="<?php echo esc_url( $sponsor->logo_url ); ?>" 
									alt="<?php echo esc_attr( $sponsor->name ); ?>" 
									class="sponsor-logo"
									loading="lazy"
								>
							<?php else : ?>
								<lottie-player src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/wired-outline-61-camera-hover-flash.json' ); ?>" background="transparent"></lottie-player>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

