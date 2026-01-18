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

	// Количество точек для равномерного распределения
	$total_points = count( $timeline );

	// Найти минимальный и максимальный годы (для справки)
	$min_year = isset( $timeline[0]['year'] ) ? (int) $timeline[0]['year'] : (int) date( 'Y' );
	$max_year = (int) end( $timeline )['year'];
	if ( ! $max_year ) {
		$max_year = (int) date( 'Y' );
	}

	// Определить цвета для точек по годам
	// Четные года - #f0b100, нечетные - #ff1a1a

	?>
	<section class="timeline-section">
		<div class="timeline-container" data-timeline-items="<?php echo esc_attr( $total_points ); ?>">
			<!-- Года в бейджах сверху -->
			<div class="timeline-years-badges">
				<?php foreach ( $timeline as $index => $entry ) : ?>
					<?php
					$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
					// Первая и последняя точки в 5% от края, остальные между ними
					$x_percent = 5 + ( $index / max( 1, $total_points - 1 ) ) * 90;
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
				<div class="timeline-fill-line"></div>
				<div class="timeline-dots" style="--timeline-items: <?php echo esc_attr( $total_points ); ?>;">
					<?php foreach ( $timeline as $index => $entry ) : ?>
						<?php
						$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
						// Первая и последняя точки в 5% от края, остальные между ними
						$x_percent = 5 + ( $index / max( 1, $total_points - 1 ) ) * 90;
						$color = ( $year % 2 === 0 ) ? '#f0b100' : '#ff1a1a';
						?>
						<div 
							class="timeline-dot"
							data-index="<?php echo esc_attr( $index ); ?>"
							style="--dot-position: <?php echo esc_attr( $index + 1 ); ?>; left: <?php echo esc_attr( $x_percent ); ?>%; background-color: <?php echo esc_attr( $color ); ?>"
						></div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Подробные подсказки при наведении -->
			<?php if ( ! empty( $timeline ) ) : ?>
				<div class="timeline-events" style="--timeline-items: <?php echo esc_attr( $total_points ); ?>;">
					<?php foreach ( $timeline as $index => $entry ) : ?>
						<?php
						$year = isset( $entry['year'] ) ? (int) $entry['year'] : 0;
						$event = isset( $entry['event'] ) ? $entry['event'] : '';
						// Первая и последняя точки в 5% от края, остальные между ними
						$x_percent = 5 + ( $index / max( 1, $total_points - 1 ) ) * 90;
						$color = ( $year % 2 === 0 ) ? '#f0b100' : '#ff1a1a';
						$is_even = $index % 2 === 0;
						?>
						<div 
							class="timeline-event <?php echo $is_even ? 'timeline-event--top' : 'timeline-event--bottom'; ?>"
							style="--dot-position: <?php echo esc_attr( $index + 1 ); ?>; left: <?php echo esc_attr( $x_percent ); ?>%"
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
