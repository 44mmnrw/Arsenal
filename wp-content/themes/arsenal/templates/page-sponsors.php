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

// Подключаем класс управления спонсорами
require_once get_template_directory() . '/inc/class-arsenal-sponsors.php';

// Получаем спонсоров из БД
$general_sponsor = Arsenal_Sponsors::get_general_sponsor();
$partners = Arsenal_Sponsors::get_partners();

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
					<svg class="sponsors-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M4 7C3.45 7 3 7.45 3 8V20C3 20.55 3.45 21 4 21H20C20.55 21 21 20.55 21 20V8C21 7.45 20.55 7 20 7H4ZM8 9H16V11H8V9ZM8 13H16V15H8V13ZM4 17H20V19H4V17Z" fill="currentColor"/>
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
									<div class="sponsors-logo-placeholder">Логотип</div>
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
					<svg class="sponsors-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 12C13.1 12 14 11.1 14 10C14 8.9 13.1 8 12 8C10.9 8 10 8.9 10 10C10 11.1 10.9 12 12 12ZM12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z" fill="currentColor"/>
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
										<div class="sponsors-logo-placeholder">Логотип</div>
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
