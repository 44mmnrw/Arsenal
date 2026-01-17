<?php
/**
 * Функции для работы с временной шкалой (Timeline)
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Получить данные временной шкалы из истории
 *
 * @return array Массив с годами и описаниями.
 */
function arsenal_get_timeline() {
	if ( ! class_exists( 'Arsenal_History_Manager' ) ) {
		return array();
	}

	$history = Arsenal_History_Manager::get_history();
	
	if ( empty( $history['scale'] ) ) {
		return array();
	}

	$scale = $history['scale'];
	
	// Если это строка JSON, парсим
	if ( is_string( $scale ) ) {
		$scale = json_decode( $scale, true );
	}

	return is_array( $scale ) ? $scale : array();
}

/**
 * Вывести временную шкалу
 *
 * @return void
 */
function arsenal_display_timeline() {
	$timeline = arsenal_get_timeline();

	if ( empty( $timeline ) ) {
		return;
	}

	// Сортировать по году (на случай если не отсортирована)
	usort( $timeline, function ( $a, $b ) {
		$year_a = isset( $a['year'] ) ? (int) $a['year'] : 0;
		$year_b = isset( $b['year'] ) ? (int) $b['year'] : 0;
		return $year_a - $year_b;
	} );

	// Найти минимальный и максимальный годы
	$min_year = isset( $timeline[0]['year'] ) ? (int) $timeline[0]['year'] : (int) date( 'Y' );
	$max_year = (int) end( $timeline )['year'];
	if ( ! $max_year ) {
		$max_year = (int) date( 'Y' );
	}
	$range = $max_year - $min_year;
	if ( $range === 0 ) {
		$range = 1; // Избегаем деления на ноль в viewBox
	}

	// Определить цвета по чередованию
	$colors = array( '#DC3545', '#FFA500', '#DC3545', '#FFA500' );

	?>
	<section class="timeline-section">
		<div class="timeline-container">
			<div class="timeline-line">
				<svg class="timeline-svg" viewBox="0 0 <?php echo esc_attr( $range + 1 ); ?> 100" preserveAspectRatio="none">
					<!-- Основная линия -->
					<line x1="0" y1="50" x2="<?php echo esc_attr( $range ); ?>" y2="50" class="timeline-base-line" />
					
					<?php foreach ( $timeline as $index => $entry ) : ?>
						<?php
						$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
						$text = isset( $entry['text'] ) ? $entry['text'] : '';
						$x_pos = $year - $min_year;
						$color = $colors[ $index % count( $colors ) ];
						?>
						<!-- Точка года -->
						<circle 
							cx="<?php echo esc_attr( $x_pos ); ?>" 
							cy="50" 
							r="4" 
							class="timeline-dot"
							style="fill: <?php echo esc_attr( $color ); ?>"
						/>
					<?php endforeach; ?>
				</svg>
			</div>

			<!-- Годы и подсказки -->
			<div class="timeline-events">
				<?php foreach ( $timeline as $index => $entry ) : ?>
					<?php
					$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
					$text = isset( $entry['text'] ) ? $entry['text'] : '';
					$x_percent = ( ( $year - $min_year ) / $range ) * 100;
					$color = $colors[ $index % count( $colors ) ];
					$is_even = $index % 2 === 0;
					?>
					<div 
						class="timeline-event <?php echo $is_even ? 'timeline-event--top' : 'timeline-event--bottom'; ?>"
						style="left: <?php echo esc_attr( $x_percent ); ?>%"
					>
						<div class="timeline-event__year" style="border-color: <?php echo esc_attr( $color ); ?>">
							<?php echo esc_html( $year ); ?>
						</div>
						<div class="timeline-event__tooltip">
							<?php echo esc_html( $text ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}
