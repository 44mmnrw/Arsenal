<?php
/**
 * Template Name: Спонсоры и партнеры
 * Template Post Type: page
 *
 * Страница со спонсорами и партнерами клуба
 * Использует динамические данные из таблицы wp_arsenal_sponsors
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Закешируем URL шаблона для многократного использования
$template_uri = get_template_directory_uri();

// Подключаем класс управления спонсорами
require_once get_template_directory() . '/inc/classes/class-arsenal-sponsors.php';

// Получаем спонсоров из БД
$general_sponsor = null;
$partners = array();
if ( class_exists( 'Arsenal_Sponsors' ) ) {
	$general_sponsor = Arsenal_Sponsors::get_general_sponsor();
	$partners = Arsenal_Sponsors::get_partners();
}

get_header(); 
?>

<main id="primary" class="site-main sponsors-page">
	<div class="sponsors-container">
		<!-- Page Header -->
		<div class="sponsors-header">
			<h1 class="sponsors-title"><?php the_title(); ?></h1>
		</div>

		<!-- Main Content -->
		<div class="sponsors-content">
			<!-- General Sponsor Section -->
			<section class="sponsors-section">
				<div class="sponsors-section-header">
					<svg class="sponsors-icon" width="24" height="24">
						<use xlink:href="<?php echo esc_url( $template_uri ); ?>/assets/images/sprite.svg?v=1.1#icon-medal"></use>
					</svg>
					<h2 class="sponsors-section-title">Генеральный спонсор</h2>
				</div>

				<?php if ( $general_sponsor ) : ?>
					<div class="sponsors-general-card">
						<div class="sponsors-general-logo">
							<div class="sponsors-logo-wrapper">
								<?php if ( $general_sponsor->logo_url ) : ?>
									<img src="<?php echo esc_url( $general_sponsor->logo_url ); ?>" alt="<?php echo esc_attr( $general_sponsor->name ); ?>" class="sponsors-logo-image">
								<?php else : ?>
									<?php if ( function_exists( 'arsenal_render_camera_placeholder' ) ) : ?>
										<?php arsenal_render_camera_placeholder(); ?>
									<?php else : ?>
										<div style="background: #f0f0f0; height: 300px; display: flex; align-items: center; justify-content: center; color: #999;">Нет фото</div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

						<div class="sponsors-general-content">
							<div class="sponsors-general-header">
								<h3 class="sponsors-general-name"><?php echo esc_html( $general_sponsor->name ); ?></h3>
								<?php if ( $general_sponsor->industry ) : ?>
									<span class="sponsors-badge"><?php echo esc_html( $general_sponsor->industry ); ?></span>
								<?php endif; ?>
							</div>

							<?php if ( $general_sponsor->description ) : ?>
								<p class="sponsors-general-description">
									<?php echo wp_kses_post( $general_sponsor->description ); ?>
								</p>
							<?php endif; ?>
						</div>
					</div>
				<?php else : ?>
					<p style="text-align: center; padding: 20px; color: #666;">Генеральный спонсор не добавлен</p>
				<?php endif; ?>
			</section>

			<!-- Partners Section -->
			<section class="sponsors-section">
				<div class="sponsors-section-header">
					<svg class="sponsors-icon" width="24" height="24">
						<use xlink:href="<?php echo esc_url( $template_uri ); ?>/assets/images/sprite.svg?v=1.1#icon-partner"></use>
					</svg>
					<h2 class="sponsors-section-title">Партнеры клуба</h2>
				</div>

				<?php if ( ! empty( $partners ) ) : ?>
					<div class="sponsors-grid">
						<?php foreach ( $partners as $partner ) : ?>
							<div class="sponsors-partner-card">
								<div class="sponsors-partner-logo">
									<?php if ( $partner->logo_url ) : ?>
										<img src="<?php echo esc_url( $partner->logo_url ); ?>" alt="<?php echo esc_attr( $partner->name ); ?>" class="sponsors-partner-logo-image">
									<?php else : ?>
										<?php if ( function_exists( 'arsenal_render_camera_placeholder' ) ) : ?>
											<?php arsenal_render_camera_placeholder(); ?>
										<?php else : ?>
											<div style="background: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center; color: #999;">Нет фото</div>
										<?php endif; ?>
									<?php endif; ?>
								</div>

								<div class="sponsors-partner-info">
									<h3 class="sponsors-partner-name"><?php echo esc_html( $partner->name ); ?></h3>
									<?php if ( $partner->industry ) : ?>
										<span class="sponsors-partner-category"><?php echo esc_html( $partner->industry ); ?></span>
									<?php endif; ?>

									<?php if ( $partner->description ) : ?>
										<p class="sponsors-partner-description">
											<?php echo wp_kses_post( $partner->description ); ?>
										</p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p style="text-align: center; padding: 20px; color: #666;">Партнеры не добавлены</p>
				<?php endif; ?>
			</section>
		</div>
	</div>
</main>

<?php
get_footer();
