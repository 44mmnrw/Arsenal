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

// Подключение стилей страницы (используем константу версии)
$theme_version = defined( 'ARSENAL_VERSION' ) ? ARSENAL_VERSION : wp_get_theme()->get( 'Version' );
wp_enqueue_style( 'arsenal-academy-history', get_template_directory_uri() . '/assets/css/pages/page-academy-history.css', array( 'arsenal-footer' ), $theme_version );

// Получить данные страницы истории академии из Carbon Fields (с защитой)
$data = array();
if ( class_exists( 'Arsenal_Academy_History_Carbon_Adapter' ) ) {
	$page_data = Arsenal_Academy_History_Carbon_Adapter::get_page_data( get_the_ID() );
	if ( is_array( $page_data ) ) {
		$data = $page_data;
	}
}

// Установить fallback значения если данные не загружены
$data = wp_parse_args( $data, array(
	'hero_title'       => get_the_title(),
	'hero_description' => '',
	'stat_cards'       => array(),
	'timeline_title'   => 'Ключевые события',
	'timeline_events'  => array(),
	'staff_title'      => 'Тренерский штаб',
	'staff_members'    => array(),
	'facilities_title' => 'Тренировочная база',
	'facilities'       => array(),
	'enrollment'       => array(
		'title'       => 'Запись в академию',
		'description' => '',
	),
	'contacts'         => array(
		'address'  => '',
		'phone'    => '',
		'email'    => '',
		'schedule' => '',
	),
) );

?>

<main id="main" class="site-main">
	<div class="academy-history-container">
		
		<!-- Заголовок и описание -->
		<section class="academy-history-intro">
			<div class="academy-history-intro__header">
				<h1 class="academy-history-intro__title"><?php echo esc_html( $data['hero_title'] ); ?></h1>
				<p class="academy-history-intro__description"><?php echo wpautop( wp_kses_post( $data['hero_description'] ) ); ?></p>
			</div>

			<!-- Статистические карточки -->
			<?php if ( ! empty( $data['stat_cards'] ) ) : ?>
				<div class="academy-stats-grid">
					<?php foreach ( $data['stat_cards'] as $card ) : ?>
						<div class="academy-stat-card">
							<div class="academy-stat-card__icon">
								<?php 
								if ( function_exists( 'arsenal_get_icon' ) ) {
									echo arsenal_get_icon( $card['icon'] ?? 'star' );
								}
								?>
							</div>
							<div class="academy-stat-card__content">
								<h3 class="academy-stat-card__number"><?php echo esc_html( $card['number'] ?? '' ); ?></h3>
								<p class="academy-stat-card__label"><?php echo esc_html( $card['label'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<!-- Ключевые события -->
		<?php if ( ! empty( $data['timeline_events'] ) ) : ?>
			<section class="academy-events-section">
				<h2 class="academy-events-section__title"><?php echo esc_html( $data['timeline_title'] ); ?></h2>
				
				<div class="academy-events-timeline">
					<?php foreach ( $data['timeline_events'] as $event ) : ?>
						<div class="academy-event-card">
							<div class="academy-event-card__icon">
								<?php 
								if ( function_exists( 'arsenal_get_icon' ) ) {
									echo arsenal_get_icon( $event['icon'] ?? 'calendar' );
								}
								?>
							</div>
							<div class="academy-event-card__content">
								<div class="academy-event-card__header">
									<h3 class="academy-event-card__title"><?php echo esc_html( $event['title'] ?? '' ); ?></h3>
									<span class="academy-event-card__year"><?php echo esc_html( $event['year'] ?? '' ); ?></span>
								</div>
							<p class="academy-event-card__description"><?php echo wpautop( wp_kses_post( $event['description'] ?? '' ) ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- Тренерский штаб -->
		<?php if ( ! empty( $data['staff_members'] ) ) : ?>
			<section class="academy-staff-section">
				<h2 class="academy-staff-section__title"><?php echo esc_html( $data['staff_title'] ); ?></h2>
				
				<div class="academy-coaches-grid">
					<?php foreach ( $data['staff_members'] as $staff ) : ?>
						<div class="academy-coach-card">
							<h3 class="academy-coach-card__name"><?php echo esc_html( $staff['name'] ); ?></h3>
							<p class="academy-coach-card__position"><?php echo esc_html( $staff['position'] ); ?></p>
						<p class="academy-coach-card__since"><?php echo esc_html( $staff['since'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<!-- Тренировочная база -->
		<?php if ( ! empty( $data['facilities'] ) ) : ?>
			<section class="academy-facilities-section">
				<h2 class="academy-facilities-section__title"><?php echo esc_html( $data['facilities_title'] ); ?></h2>
				
				<div class="academy-facilities-grid">
					<?php foreach ( $data['facilities'] as $facility ) : ?>
						<div class="academy-facility-item">
							<div class="academy-facility-item__icon">
								<?php 
								if ( function_exists( 'arsenal_get_icon' ) ) {
									echo arsenal_get_icon( $facility['icon'] ?? 'building' );
								}
								?>
							</div>
							<div class="academy-facility-item__content">
								<h3 class="academy-facility-item__heading"><?php echo esc_html( $facility['title'] ?? '' ); ?></h3>
								<ul class="academy-facility-item__list">
									<?php foreach ( ( $facility['items'] ?? array() ) as $item ) : ?>
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
			<p class="academy-enrollment-section__description"><?php echo wpautop( wp_kses_post( $data['enrollment']['description'] ) ); ?></p>
			
			<div class="academy-enrollment-info">
				<div class="academy-enrollment-column">
					<?php if ( ! empty( $data['contacts']['address'] ) ) : ?>
					<div class="academy-enrollment-item">
						<div class="academy-enrollment-item__icon">
							<?php 
							if ( function_exists( 'arsenal_get_icon' ) ) {
								echo arsenal_get_icon( 'icon-place' );
							}
							?>
						</div>
						<div class="academy-enrollment-item__content">
							<strong>Адрес:</strong> <?php echo esc_html( $data['contacts']['address'] ); ?>
						</div>
					</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $data['contacts']['phone'] ) ) : ?>
					<div class="academy-enrollment-item">
						<div class="academy-enrollment-item__icon">
							<?php 
							if ( function_exists( 'arsenal_get_icon' ) ) {
								echo arsenal_get_icon( 'icon-phone' );
							}
							?>
						</div>
						<div class="academy-enrollment-item__content">
							<strong>Телефон:</strong> <?php echo esc_html( $data['contacts']['phone'] ); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<div class="academy-enrollment-column">
					<?php if ( ! empty( $data['contacts']['email'] ) ) : ?>
					<div class="academy-enrollment-item">
						<div class="academy-enrollment-item__icon">
							<?php 
							if ( function_exists( 'arsenal_get_icon' ) ) {
								echo arsenal_get_icon( 'icon-email' );
							}
							?>
						</div>
						<div class="academy-enrollment-item__content">
							<strong>Email:</strong> <?php echo esc_html( $data['contacts']['email'] ); ?>
						</div>
					</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $data['contacts']['schedule'] ) ) : ?>
					<div class="academy-enrollment-item">
						<div class="academy-enrollment-item__icon">
							<?php 
							if ( function_exists( 'arsenal_get_icon' ) ) {
								echo arsenal_get_icon( 'icon-clock' );
							}
							?>
						</div>
						<div class="academy-enrollment-item__content">
							<strong>Просмотры:</strong> <?php echo esc_html( $data['contacts']['schedule'] ); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
