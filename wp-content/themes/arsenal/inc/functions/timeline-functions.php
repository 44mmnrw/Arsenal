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

	// Определить цвета для точек по годам
	$color_map = array(
		2018 => '#900',
		2019 => '#f0b100',
		2020 => '#ff1a1a',
		2021 => '#f0b100',
		2022 => '#f33',
		2023 => '#f0b100',
		2024 => '#ff1a1a',
		2025 => '#900',
	);

	?>
	<section class="timeline-section">
		<div class="timeline-container">
			<!-- Года в бейджах сверху -->
			<div class="timeline-years-badges">
				<?php foreach ( $timeline as $index => $entry ) : ?>
					<?php
					$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
					$x_percent = 5 + ( ( $year - $min_year ) / max( 1, $range ) ) * 90;
					?>
					<div 
						class="timeline-year-badge"
						style="left: <?php echo esc_attr( $x_percent ); ?>%"
					>
						<?php echo esc_html( $year ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Линия временной шкалы -->
			<div class="timeline-line">
				<div class="timeline-background-line"></div>
				<div class="timeline-dots">
					<?php foreach ( $timeline as $index => $entry ) : ?>
						<?php
						$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
						$x_percent = 5 + ( ( $year - $min_year ) / max( 1, $range ) ) * 90;
						$color = isset( $color_map[ $year ] ) ? $color_map[ $year ] : '#900';
						?>
						<div 
							class="timeline-dot"
							style="left: <?php echo esc_attr( $x_percent ); ?>%; background-color: <?php echo esc_attr( $color ); ?>"
						></div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Подробные подсказки при наведении -->
			<?php if ( ! empty( $timeline ) ) : ?>
				<div class="timeline-events">
					<?php foreach ( $timeline as $index => $entry ) : ?>
						<?php
						$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
						$event = isset( $entry['event'] ) ? $entry['event'] : '';
						$x_percent = 5 + ( ( $year - $min_year ) / max( 1, $range ) ) * 90;
						$color = isset( $color_map[ $year ] ) ? $color_map[ $year ] : '#900';
						$is_even = $index % 2 === 0;
						?>
						<div 
							class="timeline-event <?php echo $is_even ? 'timeline-event--top' : 'timeline-event--bottom'; ?>"
							style="left: <?php echo esc_attr( $x_percent ); ?>%"
						>
							<div class="timeline-event__tooltip" style="border-top-color: <?php echo esc_attr( $color ); ?>">
								<?php echo esc_html( $event ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
