<?php
/**
 * Template Name: Стадион
 * Template Post Type: page
 *
 * @package Arsenal
 */

get_header();

// Получаем ID выбранного стадиона из метаполя
$stadium_id = get_post_meta( get_the_ID(), '_arsenal_stadium_id', true );

// Получаем данные стадиона из БД
$stadium = null;
if ( $stadium_id ) {
	global $wpdb;
	$stadium = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}arsenal_stadiums WHERE id = %d",
			intval( $stadium_id )
		)
	);
}
?>

<main id="primary" class="site-main">
	<!-- Hero Banner with Stadium Image -->
	<div class="stadium-hero">
		<div class="stadium-hero__image-wrapper">
			<?php
			// Пытаемся вывести фото из БД стадиона
			if ( $stadium && ! empty( $stadium->photo_url ) ) {
				$photo_url = $stadium->photo_url;
				// Если URL относительный, делаем его абсолютным
				if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
					$photo_url = home_url( $photo_url );
				}
				?>
				<img src="<?php echo esc_url( $photo_url ); ?>" 
				     alt="<?php echo esc_attr( get_the_title() ); ?>"
				     class="stadium-hero__image">
				<?php
			} elseif ( has_post_thumbnail() ) {
				// Если нет фото в БД, используем featured image
				the_post_thumbnail( 'full', array(
					'class' => 'stadium-hero__image',
					'alt'   => get_the_title(),
				) );
			} else {
				// Если нет ни БД ни featured image - плейсхолдер
				echo '<div class="stadium-hero__image-placeholder" aria-hidden="true"></div>';
			}
			?>
			<div class="stadium-hero__overlay"></div>
		</div>

		<div class="stadium-hero__content">
			<div class="container">
				<h1 class="stadium-hero__title"><?php echo esc_html( get_the_title() ); ?></h1>
				<div class="stadium-hero__meta">
					<?php if ( $stadium && ! empty( $stadium->city ) ) : ?>
					<div class="stadium-hero__meta-item">
						<svg class="stadium-hero__meta-icon" viewBox="0 0 24 24" aria-hidden="true">
							<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#icon-place"></use>
						</svg>
						<span><?php echo esc_html( $stadium->city . ', Беларусь' ); ?></span>
					</div>
					<?php endif; ?>
					<div class="stadium-hero__meta-item">
					<svg class="stadium-hero__meta-icon" viewBox="0 0 24 24" aria-hidden="true">
						<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#icon-people"></use>
						</svg>
						<span>
							<?php 
							if ( $stadium && ! is_null( $stadium->capacity ) ) {
								echo esc_html_e( 'Вместимость:', 'arsenal' ) . ' ' . number_format( $stadium->capacity );

							}
							?>
						</span>
					</div>
					<?php if ( $stadium ) : ?>
						<div class="stadium-hero__meta-item">
						<svg class="stadium-hero__meta-icon" viewBox="0 0 24 24" aria-hidden="true">
							<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#icon-date"></use>
							</svg>
						<span><?php printf( esc_html__( 'Открыт: %s г.', 'arsenal' ), ! empty( $stadium->open_date ) ? esc_html( $stadium->open_date ) : '' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<!-- Main Content Section -->
		<div class="stadium-grid">
			<div class="stadium-main">
				<!-- Stats Cards -->
				<div class="stadium-stats">
					<?php
					if ( $stadium && ! empty( $stadium->stat_cards ) ) {
						$stat_cards = json_decode( $stadium->stat_cards, true );
						if ( is_array( $stat_cards ) ) {
							foreach ( $stat_cards as $card ) {
								if ( isset( $card['title'] ) && isset( $card['value'] ) && isset( $card['icon'] ) ) {
									$icon_id = sanitize_text_field( $card['icon'] );
									?>
									<div class="stat-card">
										<div class="stat-card__icon">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
												<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $icon_id ); ?>"></use>
											</svg>
										</div>
										<div class="stat-card__number"><?php echo esc_html( $card['title'] ); ?></div>
										<div class="stat-card__label"><?php echo esc_html( $card['value'] ); ?></div>
									</div>
									<?php
								}
							}
						}
					}
					?>
				</div>

				<!-- About Stadium -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'О стадионе', 'arsenal' ); ?></h2>
					<div class="stadium-text">
						<?php
						if ( $stadium && ! empty( $stadium->description ) ) {
							echo wp_kses_post( nl2br( $stadium->description ) );
						}
						?>
					</div>
				</section>

				<!-- Technical Characteristics -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'Технические характеристики', 'arsenal' ); ?></h2>
					<div class="stadium-specs">
						<?php
						if ( $stadium && ! empty( $stadium->tech_features ) ) {
							$tech_features = json_decode( $stadium->tech_features, true );
							if ( is_array( $tech_features ) ) {
								foreach ( $tech_features as $label => $value ) {
									?>
									<div class="spec-item">
										<div class="spec-item__label"><?php echo esc_html( $label ); ?></div>
										<div class="spec-item__value"><?php echo esc_html( $value ); ?></div>
									</div>
									<?php
								}
							}
						}
						?>
					</div>
				</section>

				<section class="stadium-section">
					<h2><?php esc_html_e( 'Секторы стадиона', 'arsenal' ); ?></h2>
					<div class="stadium-sectors">
						<?php
						// Получаем данные секторов из БД
						$sectors_json = '';
						if ( $stadium && ! empty( $stadium->sectors ) ) {
							$sectors_json = $stadium->sectors;
						}

						// Парсим JSON и выводим секторы
						if ( ! empty( $sectors_json ) ) {
							$sectors_data = json_decode( $sectors_json, true );
							
							if ( is_array( $sectors_data ) && ! empty( $sectors_data ) ) {
								foreach ( $sectors_data as $name => $capacity ) {
									if ( ! empty( $name ) ) {
										$name_display = esc_html( $name );
										$capacity_display = intval( $capacity );
										?>
										<div class="sector-item">
											<div class="sector-item__info">
												<div class="sector-item__name"><?php echo $name_display; ?></div>
											</div>
											<div class="sector-item__count"><?php echo number_format( $capacity_display, 0, '.', ' ' ); ?> <?php esc_html_e( 'мест', 'arsenal' ); ?></div>
										</div>
										<?php
									}
								}
							}
						}
						?>
					</div>
				</section>

				<!-- Stadium History -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'История стадиона', 'arsenal' ); ?></h2>
					<div class="stadium-timeline">
						<?php 
						$history = ! empty( $stadium->history ) ? json_decode( $stadium->history, true ) : null;
						
						if ( is_array( $history ) && ! empty( $history ) ) {
							foreach ( $history as $key => $event ) {
								$year = isset( $event['year'] ) ? intval( $event['year'] ) : '';
								$title = isset( $event['event'] ) ? $event['event'] : '';
								$icon_id = isset( $event['icon'] ) ? $event['icon'] : 'icon-calendar';
								$is_last = ( $key === count( $history ) - 1 );
								$last_class = $is_last ? ' timeline-item--last' : '';
								
								if ( $year || $title ) {
									?>
									<div class="timeline-item<?php echo esc_attr( $last_class ); ?>">
									<div class="timeline-item__icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
												<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $icon_id ); ?>"></use>
											</svg>
										</div>
										<div class="timeline-item__content">
											<?php if ( $year ) : ?>
												<div class="timeline-item__year"><?php echo esc_html( $year ); ?></div>
											<?php endif; ?>
											<?php if ( $title ) : ?>
												<div class="timeline-item__title"><?php echo esc_html( $title ); ?></div>
											<?php endif; ?>
										</div>
									</div>
									<?php
								}
							}
						}
						?>
					</div>
				</section>

				<!-- How to Get There -->
				<section class="stadium-section">
					<h2 class="stadium-section__heading-with-icon">
						<svg class="stadium-section__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#icon-map"></use>
						</svg>
						<?php esc_html_e( 'Как добраться', 'arsenal' ); ?>
					</h2>
					<div class="stadium-directions">
						<?php
						if ( $stadium && ! empty( $stadium->to_get ) ) {
							$directions = json_decode( $stadium->to_get, true );
							if ( is_array( $directions ) ) {
								foreach ( $directions as $direction ) {
									$icon_id = ! empty( $direction['icon'] ) ? $direction['icon'] : 'icon-car';
									?>
									<div class="direction-card">
										<div class="direction-card__header">
											<div class="direction-card__icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
													<use xlink:href="<?php echo get_template_directory_uri() . '/assets/images/sprite.svg?v=1.1#' . esc_attr( $icon_id ); ?>"></use>
												</svg>
											</div>
											<h3><?php echo ! empty( $direction['transport'] ) ? esc_html( $direction['transport'] ) : '—'; ?></h3>
										</div>
										<div class="direction-card__info">
											<?php if ( ! empty( $direction['route'] ) ) : ?>
												<div class="direction-card__item">
													<span class="direction-card__label"><?php esc_html_e( 'Маршрут:', 'arsenal' ); ?></span>
													<span class="direction-card__value"><?php echo esc_html( $direction['route'] ); ?></span>
												</div>
											<?php endif; ?>
											<?php if ( ! empty( $direction['station'] ) ) : ?>
												<div class="direction-card__item">
													<span class="direction-card__label"><?php echo ! empty( $direction['station_label'] ) ? esc_html( $direction['station_label'] ) . ':' : esc_html_e( 'Станция:', 'arsenal' ); ?></span>
													<span class="direction-card__value"><?php echo esc_html( $direction['station'] ); ?></span>
												</div>
											<?php endif; ?>
											<?php if ( ! empty( $direction['time'] ) ) : ?>
												<div class="direction-card__highlight">
													<svg class="direction-card__highlight-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
														<path d="M8 1a7 7 0 100 14A7 7 0 008 1z"/>
													</svg>
													<span><?php echo esc_html( $direction['time'] ); ?></span>
												</div>
											<?php endif; ?>
										</div>
									</div>
									<?php
								}
							}
						}
						?>
					</div>
				</section>
			</div>

			<!-- Sidebar -->
			<aside class="stadium-sidebar">
				<!-- Contacts -->
				<section class="stadium-card">
					<h3><?php esc_html_e( 'Контакты', 'arsenal' ); ?></h3>
					<div class="stadium-card__content">
						<?php
						if ( $stadium && ! empty( $stadium->contacts ) ) {
							$contacts = json_decode( $stadium->contacts, true );
							if ( is_array( $contacts ) && count( $contacts ) > 0 ) {
								foreach ( $contacts as $key => $contact ) {
									$value = isset( $contact['value'] ) ? $contact['value'] : $contact;
									$icon = isset( $contact['icon'] ) ? $contact['icon'] : 'icon-phone';
									if ( ! empty( $value ) ) {
										?>
										<div class="contact-item">
											<svg class="contact-item__icon" viewBox="0 0 24 24" aria-hidden="true">
												<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#<?php echo esc_attr( $icon ); ?>"></use>
											</svg>
											<div class="contact-item__text"><?php echo esc_html( $value ); ?></div>
										</div>
										<?php
									}
								}
							} else {
								?>
								<div class="contact-item">
									<svg class="contact-item__icon" viewBox="0 0 24 24" aria-hidden="true">
										<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#icon-phone"></use>
									</svg>
									<div class="contact-item__text"><?php esc_html_e( 'Контакты не указаны', 'arsenal' ); ?></div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</section>

				<!-- Infrastructure -->
				<section class="stadium-card">
					<h3><?php esc_html_e( 'Инфраструктура', 'arsenal' ); ?></h3>
					<div class="stadium-card__content">
						<?php
						if ( $stadium && ! empty( $stadium->infrastructure ) ) {
							$infrastructure = json_decode( $stadium->infrastructure, true );
							if ( is_array( $infrastructure ) && ! empty( $infrastructure ) ) {
								foreach ( $infrastructure as $key => $obj ) {
									// Поддержка старого формата (string) и нового (object)
									if ( is_array( $obj ) ) {
										$name = $obj['name'] ?? '';
										$icon_id = $obj['icon'] ?? 'icon-place';
									} else {
										$name = $obj;
										$icon_id = 'icon-place';
									}
									?>
									<div class="infrastructure-item">
										<div class="infrastructure-item__icon">
											<svg viewBox="0 0 24 24" aria-hidden="true">
												<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $icon_id ); ?>"></use>
											</svg>
										</div>
										<div class="infrastructure-item__text">
											<div class="infrastructure-item__name"><?php echo esc_html( $key ); ?></div>
											<div class="infrastructure-item__desc"><?php echo esc_html( $name ); ?></div>
										</div>
									</div>
									<?php
								}
							}
						}
						?>
					</div>
				</section>

				<!-- Match Day -->
				<section class="stadium-card stadium-card--match-day">
					<h3 class="stadium-card__title-with-icon">
						<svg class="stadium-card__title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg?v=1.1#icon-clock"></use>
						</svg>
						<?php esc_html_e( 'В день матча', 'arsenal' ); ?>
					</h3>
					<ul class="stadium-card__list">
					<?php
					if ( $stadium && ! empty( $stadium->on_date ) ) {
						$match_day_items = json_decode( $stadium->on_date, true );
						if ( is_array( $match_day_items ) && count( $match_day_items ) > 0 ) {
							foreach ( $match_day_items as $item ) {
								$text = isset( $item['text'] ) ? $item['text'] : $item;
								if ( ! empty( $text ) ) {
									?>
									<li><?php echo esc_html( $text ); ?></li>
									<?php
								}
							}
						}
					}
					?>
					</ul>
				</section>
			</aside>
		</div>
	</div>
</main>

<?php get_footer();
