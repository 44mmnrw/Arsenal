<?php
/**
 * Шаблон страницы "История ДЮСШ"
 *
 * Template Name: История ДЮСШ
 * Template Post Type: page
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();

// Подключение стилей страницы
wp_enqueue_style( 'arsenal-academy-history', get_template_directory_uri() . '/assets/css/pages/page-academy-history.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

// Получить данные страницы истории академии из Carbon Fields
$data = Arsenal_Academy_History_Carbon_Adapter::get_page_data( get_the_ID() );

?>

<main id="main" class="site-main">
	<div class="academy-history-container">
		
		<!-- Заголовок и описание -->
		<section class="academy-history-intro">
			<div class="academy-history-intro__header">
				<h1 class="academy-history-intro__title"><?php echo esc_html( $data['hero_title'] ); ?></h1>
				<p class="academy-history-intro__description"><?php echo esc_html( $data['hero_description'] ); ?></p>
			</div>

			<!-- Статистические карточки -->
			<?php if ( ! empty( $data['stat_cards'] ) ) : ?>
				<div class="academy-stats-grid">
					<?php foreach ( $data['stat_cards'] as $card ) : ?>
						<div class="academy-stat-card">
							<div class="academy-stat-card__icon">
								<?php echo arsenal_get_icon( $card['icon'] ); ?>
							</div>
							<div class="academy-stat-card__content">
								<h3 class="academy-stat-card__number"><?php echo esc_html( $card['number'] ); ?></h3>
								<p class="academy-stat-card__label"><?php echo esc_html( $card['label'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<!-- Ключевые события -->
		<?php if ( ! empty( $data['timeline_events'] ) ) : ?>
			<section class="academy-events-section">
				<h2 class="academy-events-section__title">Ключевые события</h2>
				
				<div class="academy-events-timeline">
					<?php foreach ( $data['timeline_events'] as $event ) : ?>
						<div class="academy-event-card">
							<div class="academy-event-card__icon">
								<?php echo arsenal_get_icon( $event['icon'] ); ?>
							</div>
							<div class="academy-event-card__content">
								<div class="academy-event-card__header">
									<h3 class="academy-event-card__title"><?php echo esc_html( $event['title'] ); ?></h3>
									<span class="academy-event-card__year"><?php echo esc_html( $event['year'] ); ?></span>
								</div>
								<p class="academy-event-card__description"><?php echo esc_html( $event['description'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- Тренерский штаб -->
		<?php if ( ! empty( $data['staff_members'] ) ) : ?>
			<section class="academy-staff-section">
				<h2 class="academy-staff-section__title">Тренерский штаб</h2>
				
				<div class="academy-coaches-grid">
					<?php foreach ( $data['staff_members'] as $staff ) : ?>
						<div class="academy-coach-card">
							<h3 class="academy-coach-card__name"><?php echo esc_html( $staff['name'] ); ?></h3>
							<p class="academy-coach-card__position"><?php echo esc_html( $staff['position'] ); ?></p>
							<p class="academy-coach-card__since"><?php echo esc_html( 'С ' . $staff['since'] . ' года' ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- Тренировочная база -->
		<?php if ( ! empty( $data['facilities'] ) ) : ?>
			<section class="academy-facilities-section">
				<h2 class="academy-facilities-section__title">Тренировочная база</h2>
				
				<div class="academy-facilities-grid">
					<?php foreach ( $data['facilities'] as $facility ) : ?>
						<div class="academy-facility-item">
							<div class="academy-facility-item__icon">
								<?php echo arsenal_get_icon( $facility['icon'] ); ?>
							</div>
							<div class="academy-facility-item__content">
								<h3 class="academy-facility-item__heading"><?php echo esc_html( $facility['title'] ); ?></h3>
								<ul class="academy-facility-item__list">
									<?php foreach ( $facility['items'] as $item ) : ?>
										<li>• <?php echo esc_html( $item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- Запись в академию -->
		<section class="academy-enrollment-section">
			<h2 class="academy-enrollment-section__title"><?php echo esc_html( $data['enrollment']['title'] ); ?></h2>
			<p class="academy-enrollment-section__description"><?php echo esc_html( $data['enrollment']['description'] ); ?></p>
			
			<div class="academy-enrollment-info">
				<div class="academy-enrollment-column">
					<div class="academy-enrollment-item">
				<div class="academy-enrollment-item__icon">
						<?php echo arsenal_get_icon( 'icon-place' ); ?>
					</div>
						<div class="academy-enrollment-item__content">
							<strong>Адрес:</strong> <?php echo esc_html( $data['contacts']['address'] ); ?>
						</div>
					</div>
					<div class="academy-enrollment-item">
				<div class="academy-enrollment-item__icon">
						<?php echo arsenal_get_icon( 'icon-phone' ); ?>
					</div>
						<div class="academy-enrollment-item__content">
							<strong>Телефон:</strong> <?php echo esc_html( $data['contacts']['phone'] ); ?>
						</div>
					</div>
				</div>

				<div class="academy-enrollment-column">
					<div class="academy-enrollment-item">
				<div class="academy-enrollment-item__icon">
						<?php echo arsenal_get_icon( 'icon-email' ); ?>
					</div>
						<div class="academy-enrollment-item__content">
							<strong>Email:</strong> <?php echo esc_html( $data['contacts']['email'] ); ?>
						</div>
					</div>
					<div class="academy-enrollment-item">
				<div class="academy-enrollment-item__icon">
						<?php echo arsenal_get_icon( 'icon-clock' ); ?>
					</div>
						<div class="academy-enrollment-item__content">
							<strong>Просмотры:</strong> <?php echo esc_html( $data['contacts']['schedule'] ); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
