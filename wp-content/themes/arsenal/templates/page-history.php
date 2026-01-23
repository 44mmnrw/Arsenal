<?php
/**
 * Шаблон страницы "История клуба"
 *
 * Template Name: История клуба
 * Template Post Type: page
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();

// Получить историю клуба из Carbon Fields (с защитой)
$history = array();
if ( class_exists( 'Arsenal_History_Carbon_Adapter' ) ) {
	$history = Arsenal_History_Carbon_Adapter::get_page_data( get_the_ID() );
	// Убедимся что это массив
	if ( ! is_array( $history ) ) {
		$history = array();
	}
}

// Если нет данных - показываем сообщение
if ( empty( $history ) || ! isset( $history['title'] ) ) {
	$history = array(
		'title'       => get_the_title(),
		'description' => '',
		'records'     => array(),
		'achievements' => array(),
		'additional_cards' => array(),
	);
}

?>

<main id="main" class="site-main">
	<div class="club-history-container">
		<div class="entry-header">
			<h1 class="entry-title"><?php echo esc_html( $history['title'] ); ?></h1>
		</div>

		<!-- История клуба -->

		<section class="history-intro">
			<div class="history-intro__text">
				<?php echo wpautop( wp_kses_post( $history['description'] ) ); ?>
			</div>
		</section>

		<!-- Временная шкала -->
		<?php
		if ( ! empty( $history['scale'] ) && function_exists( 'arsenal_display_timeline' ) ) {
			arsenal_display_timeline();
		}
		?>

		<!-- Рекорды и достижения -->

		<section class="achievements-section">
			<h3 class="achievements-section__heading"><?php echo esc_html( $history['title_second'] ); ?></h3>
			<div class="achievements-grid">

				<!-- Рекорды из JSON -->
				<?php 
				if ( ! empty( $history['records'] ) ) {
					$records = $history['records'];
					if ( is_string( $records ) ) {
						$records = json_decode( $records, true );
					}
					
					if ( is_array( $records ) ) {
						foreach ( $records as $record ) {
							if ( is_array( $record ) && isset( $record['title'], $record['items'] ) ) {
								// Определяем класс стиля: если style == 'white', то --white, иначе --primary
								$style_class = isset( $record['style'] ) && 'white' === $record['style'] ? 'achievement-card--white' : 'achievement-card--primary';
								?>
								<div class="achievement-card <?php echo esc_attr( $style_class ); ?>">
									<div class="achievement-card__header">
									<span class="achievement-card__icon">
										<?php 
										if ( function_exists( 'arsenal_get_icon' ) ) {
											if ( isset( $record['icon'] ) && ! empty( $record['icon'] ) ) {
												echo arsenal_get_icon( $record['icon'] );
											} else {
												echo arsenal_get_icon( 'white' === $style_class ? 'chart' : 'cup' );
											}
										} else {
											echo '📊';
										}
										?>
									</span>
										<h4 class="achievement-card__title"><?php echo esc_html( $record['title'] ); ?></h4>
									</div>
									<ul class="achievement-card__list">
										<?php foreach ( $record['items'] as $item ) : ?>
											<li class="achievement-card__item">
												<?php if ( 'achievement-card--primary' === $style_class ) : ?>
											<span class="achievement-card__item-icon">
												<?php 
												if ( function_exists( 'arsenal_get_icon' ) ) {
													echo arsenal_get_icon( isset( $record['icon'] ) ? $record['icon'] : 'cup' );
												} else {
													echo '🏆';
												}
												?>
											</span>
										<?php endif; ?>
										<span class="achievement-card__item-text">
											<?php 
											if ( function_exists( 'arsenal_format_item_text' ) ) {
												echo arsenal_format_item_text( $item );
											} else {
												echo esc_html( $item );
											}
											?>
										</span>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
								<?php
							}
						}
					}
				}
				?>
				
			<!-- Достижения из JSON (achievements) -->
			<?php 
			if ( ! empty( $history['achievements'] ) ) {
				$achievements = $history['achievements'];
				if ( is_string( $achievements ) ) {
					$achievements = json_decode( $achievements, true );
				}
				
				if ( is_array( $achievements ) ) {
					foreach ( $achievements as $achievement ) {
						if ( is_array( $achievement ) && isset( $achievement['label'], $achievement['value'] ) ) {
							?>
							<div class="stat-card">
								<div class="stat-card__label">
									<?php 
									if ( function_exists( 'arsenal_format_item_text' ) ) {
										echo arsenal_format_item_text( $achievement['label'] );
									} else {
										echo esc_html( $achievement['label'] );
									}
									?>
								</div>
								<div class="stat-card__value">
									<?php 
									if ( function_exists( 'arsenal_format_item_text' ) ) {
										echo arsenal_format_item_text( $achievement['value'] );
									} else {
										echo esc_html( $achievement['value'] );
									}
									?>
								</div>
							</div>
							<?php
						}
					}
				}
			}
			?>
		</div>
	</section><!-- Дополнительная секция -->

		<section class="stadiums-section">
			<?php if ( ! empty( $history['title_third'] ) ) : ?>
				<h3 class="stadiums-section__heading"><?php echo esc_html( $history['title_third'] ); ?></h3>
			<?php endif; ?>
			<div class="stadiums-grid">
				<?php 
				if ( ! empty( $history['additional_cards'] ) ) {
					$additional = $history['additional_cards'];
					if ( is_string( $additional ) ) {
						$additional = json_decode( $additional, true );
					}
					
					if ( is_array( $additional ) ) {
						foreach ( $additional as $card ) {
							if ( is_array( $card ) && isset( $card['label'], $card['value'] ) ) {
								$icon = isset( $card['icon'] ) && ! empty( $card['icon'] ) ? $card['icon'] : 'stadium';
								?>
								<div class="stadium-item">
									<div class="stadium-item__icon">
										<?php 
										if ( function_exists( 'arsenal_get_icon' ) ) {
											echo arsenal_get_icon( $icon );
										} else {
											echo '🏟️';
										}
										?>
									</div>
									<div class="stadium-item__info">
										<h4 class="stadium-item__name"><?php echo esc_html( $card['label'] ); ?></h4>
										<p class="stadium-item__location"><?php echo wp_kses_post( $card['value'] ); ?></p>
									</div>
								</div>
								<?php
							}
						}
					}
				}
				?>
			</div>
		</section></div>
</main>

<?php
get_footer();
