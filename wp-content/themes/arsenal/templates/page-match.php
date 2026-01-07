<?php
/**
 * Имя шаблона: Страница деталей матча
 * Описание: Отображает подробную информацию о матче с использованием дизайна Figma
 *
 * Паттерн URL: /match/{team_id}/{YYYY-MM-DD}/
 * Получает данные матча через arsenal_get_match_by_date_and_team()
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Загрузить стили страницы матча
wp_enqueue_style( 'arsenal-page-match', get_template_directory_uri() . '/assets/css/page-match.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

// Получить параметры URL
$match_date = get_query_var( 'match_date' );
$team_id    = get_query_var( 'team_id' );

// Проверить параметры
if ( ! $team_id || ! $match_date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $match_date ) ) {
	echo '<div class="container" style="padding: 60px 0; text-align: center;">';
	echo '<h1>Матч не найден</h1>';
	echo '<p>Некорректные параметры. Используйте формат: /match/{team_id}/{YYYY-MM-DD}/</p>';
	echo '</div>';
	get_footer();
	return;
}

// Получить данные матча
$match = arsenal_get_match_by_date_and_team( $match_date, $team_id );

if ( ! $match ) {
	echo '<div class="container" style="padding: 60px 0; text-align: center;">';
	echo '<h1>Матч не найден</h1>';
	echo '<p>На ' . esc_html( date_i18n( 'd.m.Y', strtotime( $match_date ) ) ) . ' для команды с ID <code>' . esc_html( $team_id ) . '</code> нет завершённых матчей.</p>';
	echo '<p><a href="' . esc_url( home_url( '/' ) ) . '">← Вернуться на главную</a></p>';
	echo '</div>';
	get_footer();
	return;
}

// Получить события матча и составы
$events    = arsenal_get_match_events( $match->match_id );
$lineups   = arsenal_get_match_lineups( $match->match_id );
$organized = arsenal_organize_lineups( $lineups, $match->home_team_id );

// Получить информацию о стадионе для фона
$stadium = arsenal_get_stadium_by_id( $match->stadium_id );
$stadium_photo_url = '';
if ( $stadium && ! empty( $stadium->photo_url ) ) {
    // Преобразовать относительный путь в полный URL при необходимости
    if ( ! str_starts_with( $stadium->photo_url, 'http://' ) && ! str_starts_with( $stadium->photo_url, 'https://' ) ) {
        $stadium_photo_url = home_url( $stadium->photo_url );
    } else {
        $stadium_photo_url = $stadium->photo_url;
    }
}

?>

<main class="match-detail-page">
	<!-- Секция hero с заголовком -->
	<header class="match-hero-header" style="<?php echo $stadium_photo_url ? 'background-image: url(' . esc_url( $stadium_photo_url ) . ');' : ''; ?>">
		<div class="hero-background"></div>
		<div class="hero-overlay"></div>
		<div class="hero-content">
			<!-- Мета-информация матча -->
			<div class="match-meta">
				<span class="league-badge">Высшая лига</span>
				<span class="tour-info">Тур <?php echo intval( $match->tour ); ?></span>
				<span class="meta-sep">•</span>
				<span class="match-date"><?php echo esc_html( date_i18n( 'j F Y', strtotime( $match->match_date ) ) ); ?></span>
			</div>

			<!-- Раздел счета -->
			<div class="match-score-section">
				<!-- Домашняя команда -->
				<div class="team-block">
				<div class="team-icon-circle">
					<?php if ( ! empty( $match->home_logo ) ) : ?>
						<img src="<?php echo esc_url( $match->home_logo ); ?>" alt="<?php echo esc_attr( $match->home_team_name ); ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
					<?php else : ?>
						⚽
					<?php endif; ?>
				</div>
				<h2 class="team-title"><?php echo esc_html( $match->home_team_name ); ?></h2>
			</div>

			<!-- Большой дисплей счета -->
			<div class="score-display">
				<span class="score-num"><?php echo intval( $match->home_score ); ?></span>
				<span class="score-colon">:</span>
				<span class="score-num"><?php echo intval( $match->away_score ); ?></span>
			</div>

			<!-- Гостевая команда -->
			<div class="team-block">
				<div class="team-icon-circle">
					<?php if ( ! empty( $match->away_logo ) ) : ?>
						<img src="<?php echo esc_url( $match->away_logo ); ?>" alt="<?php echo esc_attr( $match->away_team_name ); ?>" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
					<?php else : ?>
						⚽
					<?php endif; ?>
				</div>
				<h2 class="team-title"><?php echo esc_html( $match->away_team_name ); ?></h2>
			</div>
		</div>

		<!-- Детали матча -->
			<div class="match-detail-items">
				<div class="detail-item">
					<svg viewBox="0 0 16 16" class="detail-icon">
						<path d="M8 0C3.58 0 0 3.58 0 8s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8z" fill="#d1d5dc"/>
					</svg>
					<?php echo esc_html( $match->stadium_name ?? 'Стадион "Строитель"' ); ?>, Дзержинск
				</div>
				<div class="detail-item">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="detail-icon">
					<g clip-path="url(#clip0_139_466)">
						<path d="M8 4V8L10.6667 9.33333" stroke="#D1D5DC" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M7.99998 14.6667C11.6819 14.6667 14.6666 11.6819 14.6666 8C14.6666 4.3181 11.6819 1.33333 7.99998 1.33333C4.31808 1.33333 1.33331 4.3181 1.33331 8C1.33331 11.6819 4.31808 14.6667 7.99998 14.6667Z" stroke="#D1D5DC" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
					</g>
					<defs>
						<clipPath id="clip0_139_466">
							<rect width="16" height="16" fill="white"/>
						</clipPath>
					</defs>
					</svg>
					<?php echo esc_html( date_i18n( 'H:i', strtotime( $match->match_time ?? '19:00' ) ) ); ?>
				</div>
				<div class="detail-item">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="detail-icon">
					<g clip-path="url(#clip0_139_472)">
						<path d="M10.6666 14V12.6667C10.6666 11.9594 10.3857 11.2811 9.8856 10.781C9.3855 10.281 8.70722 10 7.99998 10H3.99998C3.29274 10 2.61446 10.281 2.11436 10.781C1.61426 11.2811 1.33331 11.9594 1.33331 12.6667V14" stroke="#D1D5DC" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M10.6667 2.08533C11.2385 2.23358 11.7449 2.56751 12.1065 3.03471C12.468 3.50191 12.6642 4.07593 12.6642 4.66667C12.6642 5.25741 12.468 5.83143 12.1065 6.29862C11.7449 6.76582 11.2385 7.09975 10.6667 7.248" stroke="#D1D5DC" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M14.6667 14V12.6667C14.6662 12.0758 14.4696 11.5018 14.1076 11.0349C13.7456 10.5679 13.2388 10.2344 12.6667 10.0867" stroke="#D1D5DC" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M5.99998 7.33333C7.47274 7.33333 8.66665 6.13943 8.66665 4.66667C8.66665 3.19391 7.47274 2 5.99998 2C4.52722 2 3.33331 3.19391 3.33331 4.66667C3.33331 6.13943 4.52722 7.33333 5.99998 7.33333Z" stroke="#D1D5DC" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
					</g>
					<defs>
						<clipPath id="clip0_139_472">
							<rect width="16" height="16" fill="white"/>
						</clipPath>
					</defs>
					</svg>
					<?php echo esc_html( arsenal_pluralize_spectators( intval( $match->attendance ?? 3500 ) ) ); ?>
				</div>
			</div>

		<!-- Информация о судье -->
			<div class="referee-info">
				Судья: <?php echo esc_html( $match->main_referee ?? 'Алексей Кулешов' ); ?>
			</div>
		</div>
	</header>

	<!-- Контейнер контента -->
	<div class="match-container">

		<!-- РАЗДЕЛ 1: Составы и поле -->
		<section class="lineups-section">
			<!-- JavaScript для раскрытия составов на мобилах -->
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				const lineupPanels = document.querySelectorAll('.lineup-panel');
				
				lineupPanels.forEach(function(panel) {
					const body = panel.querySelector('.lineup-body');
					if (!body) return;
					
					// Находим существующую кнопку или создаём новую
					let toggleBtn = panel.querySelector('.toggle-lineup-btn');
					if (!toggleBtn) {
						toggleBtn = document.createElement('button');
						toggleBtn.className = 'toggle-lineup-btn';
						toggleBtn.innerHTML = '<span class="toggle-text">Показать всех</span><svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>';
						body.parentNode.appendChild(toggleBtn);
					}
					
					// Инициализируем: закрытое состояние на мобилах
					if (window.innerWidth <= 480) {
						panel.classList.add('collapsed');
					}
					
					toggleBtn.addEventListener('click', function(e) {
						e.preventDefault();
						panel.classList.toggle('collapsed');
						toggleBtn.classList.toggle('expanded');
						
						const isExpanded = !panel.classList.contains('collapsed');
						toggleBtn.querySelector('.toggle-text').textContent = isExpanded 
							? 'Скрыть'
							: 'Показать всех';
					});
				});
			});
			</script>

			<div class="lineups-grid">
				<!-- Панель домашней команды -->
				<div class="lineup-panel home-panel">
					<div class="lineup-header home-header">
						<h3 class="team-name"><?php echo esc_html( $match->home_team_name ); ?></h3>
						<span class="formation-badge">4-3-3</span>
					</div>
					<div class="lineup-body">
						<?php if ( ! empty( $organized['home_starting_by_position'] ) ) { ?>
						<div class="players-group">
							<h4 class="group-label">Основной состав</h4>
							<?php foreach ( $organized['home_starting_by_position'] as $position => $players ) { ?>
							<div class="position-section">
								<div class="position-name"><?php echo esc_html( $position ); ?></div>
								<div class="players-list">
									<?php foreach ( $players as $player ) { 
										$player_url = arsenal_get_player_url_if_has_contract( $player->player_id );
									?>
									<div class="player-entry">
										<div class="player-shirt"><?php echo intval( $player->shirt_number ); ?></div>
										<div class="player-info">
											<?php if ( $player_url ) : ?>
												<a href="<?php echo esc_url( $player_url ); ?>" class="player-full-name"><?php echo esc_html( $player->full_name ); ?></a>
											<?php else : ?>
												<div class="player-full-name"><?php echo esc_html( $player->full_name ); ?></div>
											<?php endif; ?>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
							<?php } ?>
						</div>
						<?php } ?>

						<?php if ( ! empty( $organized['home_subs'] ) ) { ?>
						<div class="players-group subs-group">
							<h4 class="group-label">Запасные</h4>
							<div class="players-list">
								<?php foreach ( $organized['home_subs'] as $player ) { 
									$player_url = arsenal_get_player_url_if_has_contract( $player->player_id );
								?>
								<div class="player-entry">
									<div class="player-shirt"><?php echo intval( $player->shirt_number ); ?></div>
									<div class="player-info">
										<?php if ( $player_url ) : ?>
											<a href="<?php echo esc_url( $player_url ); ?>" class="player-full-name"><?php echo esc_html( $player->full_name ); ?></a>
										<?php else : ?>
											<div class="player-full-name"><?php echo esc_html( $player->full_name ); ?></div>
										<?php endif; ?>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>

				<!-- Визуализация поля -->
				<div class="pitch-wrapper">
					<div class="pitch-title">Поле</div>
					<div class="pitch-field">
						<?php
						// Функция для генерации координат на основе количества игроков
						function arsenal_get_player_coords( $player_count, $y_position, $field_width = 667, $side_padding = 50 ) {
							$coords = array();
							$playable_width = $field_width - ( 2 * $side_padding );
							$center = $field_width / 2;
							
							if ( $player_count === 1 ) {
								$coords[] = array( $center, $y_position );
							} elseif ( $player_count === 2 ) {
								$coords[] = array( $center - 80, $y_position );
								$coords[] = array( $center + 80, $y_position );
							} elseif ( $player_count === 3 ) {
								$coords[] = array( $center - 120, $y_position );
								$coords[] = array( $center, $y_position );
								$coords[] = array( $center + 120, $y_position );
							} elseif ( $player_count === 4 ) {
								$coords[] = array( $center - 150, $y_position );
								$coords[] = array( $center - 50, $y_position );
								$coords[] = array( $center + 50, $y_position );
								$coords[] = array( $center + 150, $y_position );
							} elseif ( $player_count === 5 ) {
								$coords[] = array( $center - 160, $y_position );
								$coords[] = array( $center - 80, $y_position );
								$coords[] = array( $center, $y_position );
								$coords[] = array( $center + 80, $y_position );
								$coords[] = array( $center + 160, $y_position );
							} else {
							// Для 6+ игроков распределить равномерно
								$step = $playable_width / ( $player_count + 1 );
								for ( $i = 1; $i <= $player_count; $i++ ) {
									$coords[] = array( $side_padding + ( $step * $i ), $y_position );
								}
							}
							
							return $coords;
						}
						
						// Подготовить позиции игроков для визуализации
						$home_players = array();
						$away_players = array();
						
						if ( ! empty( $organized['home_starting_by_position'] ) ) {
							foreach ( $organized['home_starting_by_position'] as $position => $players ) {
								foreach ( $players as $player ) {
									$home_players[] = array(
										'shirt' => $player->shirt_number,
										'name' => $player->full_name,
										'position' => $position
									);
								}
							}
						}
						
						if ( ! empty( $organized['away_starting_by_position'] ) ) {
							foreach ( $organized['away_starting_by_position'] as $position => $players ) {
								foreach ( $players as $player ) {
									$away_players[] = array(
										'shirt' => $player->shirt_number,
										'name' => $player->full_name,
										'position' => $position
									);
								}
							}
						}
						
						// Подсчитать игроков по позиции для домашней команды
						$home_position_counts = array();
						foreach ( $home_players as $player ) {
							$pos = $player['position'];
							$home_position_counts[ $pos ] = isset( $home_position_counts[ $pos ] ) ? $home_position_counts[ $pos ] + 1 : 1;
						}
						
						// Подсчитать игроков по позиции для гостевой команды
						$away_position_counts = array();
						foreach ( $away_players as $player ) {
							$pos = $player['position'];
							$away_position_counts[ $pos ] = isset( $away_position_counts[ $pos ] ) ? $away_position_counts[ $pos ] + 1 : 1;
						}
						
						// Построить динамические карты позиций для домашней команды
						$home_positions_map = array(
							'Вратарь' => arsenal_get_player_coords( isset( $home_position_counts['Вратарь'] ) ? $home_position_counts['Вратарь'] : 0, 900 ),
							'Защитник' => arsenal_get_player_coords( isset( $home_position_counts['Защитник'] ) ? $home_position_counts['Защитник'] : 0, 750 ),
							'Полузащитник' => arsenal_get_player_coords( isset( $home_position_counts['Полузащитник'] ) ? $home_position_counts['Полузащитник'] : 0, 570 ),
							'Нападающий' => arsenal_get_player_coords( isset( $home_position_counts['Нападающий'] ) ? $home_position_counts['Нападающий'] : 0, 320 )
						);
						
						// Построить динамические карты позиций для гостевой команды
						$away_positions_map = array(
							'Вратарь' => arsenal_get_player_coords( isset( $away_position_counts['Вратарь'] ) ? $away_position_counts['Вратарь'] : 0, 100 ),
							'Защитник' => arsenal_get_player_coords( isset( $away_position_counts['Защитник'] ) ? $away_position_counts['Защитник'] : 0, 250 ),
							'Полузащитник' => arsenal_get_player_coords( isset( $away_position_counts['Полузащитник'] ) ? $away_position_counts['Полузащитник'] : 0, 430 ),
							'Нападающий' => arsenal_get_player_coords( isset( $away_position_counts['Нападающий'] ) ? $away_position_counts['Нападающий'] : 0, 680 )
						);
						?>
						<svg class="field-svg" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 667 1000.52" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003">
							<defs>
								<clipPath id="id0">
									<path d="M635.73 0c17.27,0 31.27,14 31.27,31.27l0 937.99c0,17.26 -14,31.26 -31.27,31.26l-604.46 0c-17.27,0 -31.27,-14 -31.27,-31.26l0 -937.99c0,-17.27 14,-31.27 31.27,-31.27l604.46 0z"/>
								</clipPath>
								<linearGradient id="id1" gradientUnits="userSpaceOnUse" x1="335.57" y1="0" x2="335.57" y2="1000.52">
									<stop offset="0" stop-opacity="1" stop-color="#00A63E"/>
									<stop offset="1" stop-opacity="1" stop-color="#008236"/>
								</linearGradient>
							</defs>
							<g id="Слой_x0020_1">
								<metadata id="CorelCorpID_0Corel-Layer"/>
								<g></g>
								<g clip-path="url(#id0)">
									<g id="_2179027382304">
										<path fill="url(#id1)" fill-rule="nonzero" d="M635.73 0c17.27,0 31.27,14 31.27,31.27l0 937.99c0,17.26 -14,31.26 -31.27,31.26l-604.46 0c-17.27,0 -31.27,-14 -31.27,-31.26l0 -937.99c0,-17.27 14,-31.27 31.27,-31.27l604.46 0z"/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="633.65,967.16 633.65,33.36 33.35,33.36 33.35,967.16 "/>
										<line fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" x1="633.65" y1="500.26" x2="33.35" y2="500.26" />
										<path fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" d="M253.46 500.26c0,44.21 35.83,80.04 80.04,80.04 44.2,0 80.04,-35.83 80.04,-80.04 0,-44.2 -35.84,-80.04 -80.04,-80.04 -44.21,0 -80.04,35.84 -80.04,80.04z"/>
										<path fill="white" fill-rule="nonzero" fill-opacity="0.800000" d="M328.16 500.26c0,2.95 2.39,5.34 5.34,5.34 2.95,0 5.34,-2.39 5.34,-5.34 0,-2.95 -2.39,-5.33 -5.34,-5.33 -2.95,0 -5.34,2.38 -5.34,5.33z"/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="533.6,967.16 533.6,833.76 133.4,833.76 133.4,967.16 "/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="433.55,967.16 433.55,913.8 233.45,913.8 233.45,967.16 "/>
										<path fill="white" fill-rule="nonzero" fill-opacity="0.800000" d="M330.16 887.12c0,1.84 1.5,3.34 3.34,3.34 1.84,0 3.33,-1.5 3.33,-3.34 0,-1.84 -1.49,-3.33 -3.33,-3.33 -1.84,0 -3.34,1.49 -3.34,3.33z"/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="386.86,987.17 386.86,967.16 280.14,967.16 280.14,987.17 "/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="533.6,166.76 533.6,33.36 133.4,33.36 133.4,166.76 "/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="433.55,86.72 433.55,33.36 233.45,33.36 233.45,86.72 "/>
										<path fill="white" fill-rule="nonzero" fill-opacity="0.800000" d="M330.16 113.4c0,1.84 1.5,3.34 3.34,3.34 1.84,0 3.33,-1.5 3.33,-3.34 0,-1.84 -1.49,-3.33 -3.33,-3.33 -1.84,0 -3.34,1.49 -3.34,3.33z"/>
										<polygon fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" points="386.86,33.36 386.86,13.35 280.14,13.35 280.14,33.36 "/>
										<path fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" d="M613.63 33.38c0.01,13.34 6.69,20 20.03,19.98"/>
										<path fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" d="M613.64 967.16c0,-13.34 6.67,-20.01 20.01,-20.01"/>
										<path fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" d="M53.4 33.4c-0.05,13.34 -6.74,19.99 -20.09,19.94"/>
										<path fill="none" fill-rule="nonzero" fill-opacity="0.800000" stroke="white" stroke-width="3.33" stroke-miterlimit="22.9256" stroke-opacity="0.800000" d="M53.22 967.3c0.19,-13.34 -6.39,-20.1 -19.73,-20.29"/>
									</g>
								</g>
								<path fill="none" d="M635.73 0c17.27,0 31.27,14 31.27,31.27l0 937.99c0,17.26 -14,31.26 -31.27,31.26l-604.46 0c-17.27,0 -31.27,-14 -31.27,-31.26l0 -937.99c0,-17.27 14,-31.27 31.27,-31.27l604.46 0z"/>
							</g>
							<!-- Игроки домашней команды (красные) -->
							<?php
							$home_player_idx = array(
								'Вратарь' => 0,
								'Защитник' => 0,
								'Полузащитник' => 0,
								'Нападающий' => 0
							);
							
							if ( ! empty( $home_players ) ) {
								foreach ( $home_players as $player ) {
									$pos = $player['position'];
									if ( isset( $home_positions_map[ $pos ] ) && isset( $home_player_idx[ $pos ] ) ) {
										$idx = $home_player_idx[ $pos ];
										if ( $idx < count( $home_positions_map[ $pos ] ) ) {
											$coords = $home_positions_map[ $pos ][ $idx ];
											$home_player_idx[ $pos ]++;
											?>
											<circle cx="<?php echo esc_attr( $coords[0] ); ?>" cy="<?php echo esc_attr( $coords[1] ); ?>" r="22" fill="#dc2626" stroke="white" stroke-width="2"/>
											<text x="<?php echo esc_attr( $coords[0] ); ?>" y="<?php echo esc_attr( $coords[1] + 7 ); ?>" text-anchor="middle" fill="white" font-size="14" font-weight="bold" font-family="Arial"><?php echo intval( $player['shirt'] ); ?></text>
											<?php
										}
									}
								}
							}
							?>
							
							<!-- Игроки гостевой команды (синие) -->
							<?php
							$away_player_idx = array(
								'Вратарь' => 0,
								'Защитник' => 0,
								'Полузащитник' => 0,
								'Нападающий' => 0
							);
							
							if ( ! empty( $away_players ) ) {
								foreach ( $away_players as $player ) {
									$pos = $player['position'];
									if ( isset( $away_positions_map[ $pos ] ) && isset( $away_player_idx[ $pos ] ) ) {
										$idx = $away_player_idx[ $pos ];
										if ( $idx < count( $away_positions_map[ $pos ] ) ) {
											$coords = $away_positions_map[ $pos ][ $idx ];
											$away_player_idx[ $pos ]++;
											?>
											<circle cx="<?php echo esc_attr( $coords[0] ); ?>" cy="<?php echo esc_attr( $coords[1] ); ?>" r="22" fill="#1a56db" stroke="white" stroke-width="2"/>
											<text x="<?php echo esc_attr( $coords[0] ); ?>" y="<?php echo esc_attr( $coords[1] + 7 ); ?>" text-anchor="middle" fill="white" font-size="14" font-weight="bold" font-family="Arial"><?php echo intval( $player['shirt'] ); ?></text>
											<?php
										}
									}
								}
							}
							?>
						</svg>
					</div>
				</div>
				<!-- Панель гостевой команды -->
				<div class="lineup-panel away-panel">
					<div class="lineup-header away-header">
						<h3 class="team-name"><?php echo esc_html( $match->away_team_name ); ?></h3>
						<span class="formation-badge">4-4-2</span>
					</div>
					<div class="lineup-body">
						<?php if ( ! empty( $organized['away_starting_by_position'] ) ) { ?>
						<div class="players-group">
							<h4 class="group-label">Основной состав</h4>
							<?php foreach ( $organized['away_starting_by_position'] as $position => $players ) { ?>
							<div class="position-section">
								<div class="position-name"><?php echo esc_html( $position ); ?></div>
								<div class="players-list">
									<?php foreach ( $players as $player ) { 
										$player_url = arsenal_get_player_url_if_has_contract( $player->player_id );
									?>
									<div class="player-entry">
										<div class="player-shirt"><?php echo intval( $player->shirt_number ); ?></div>
										<div class="player-info">
											<?php if ( $player_url ) : ?>
												<a href="<?php echo esc_url( $player_url ); ?>" class="player-full-name"><?php echo esc_html( $player->full_name ); ?></a>
											<?php else : ?>
												<div class="player-full-name"><?php echo esc_html( $player->full_name ); ?></div>
											<?php endif; ?>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
							<?php } ?>
						</div>
						<?php } ?>

						<?php if ( ! empty( $organized['away_subs'] ) ) { ?>
						<div class="players-group subs-group">
							<h4 class="group-label">Запасные</h4>
							<div class="players-list">
								<?php foreach ( $organized['away_subs'] as $player ) { 
									$player_url = arsenal_get_player_url_if_has_contract( $player->player_id );
								?>
								<div class="player-entry">
									<div class="player-shirt"><?php echo intval( $player->shirt_number ); ?></div>
									<div class="player-info">
										<?php if ( $player_url ) : ?>
											<a href="<?php echo esc_url( $player_url ); ?>" class="player-full-name"><?php echo esc_html( $player->full_name ); ?></a>
										<?php else : ?>
											<div class="player-full-name"><?php echo esc_html( $player->full_name ); ?></div>
										<?php endif; ?>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</section>
        <!-- РАЗДЕЛ 2: События матча -->
		<section class="events-section">
			<div class="section-card">
				<!-- JavaScript для раскрывания событий -->
				<script>
				document.addEventListener('DOMContentLoaded', function() {
					const toggleBtn = document.querySelector('[data-toggle-events]');
					const eventCard = document.querySelector('.events-section .section-card');
					const eventsList = document.querySelector('.events-list');
					
					if ( ! toggleBtn || ! eventCard || ! eventsList ) {
						console.warn('Events toggle elements not found');
						return;
					}
					
					// Инициализируем: закрытое состояние
					eventCard.classList.add('collapsed');
					
					toggleBtn.addEventListener('click', function(e) {
						e.preventDefault();
						eventCard.classList.toggle('collapsed');
						toggleBtn.classList.toggle('expanded');
						
						const isExpanded = !eventCard.classList.contains('collapsed');
						toggleBtn.querySelector('.toggle-text').textContent = isExpanded 
							? 'Скрыть события'
							: 'Показать все события';
					});
				});
				</script>

				<div class="section-header">
					<svg class="section-icon" viewBox="0 0 22 22" fill="currentColor">
						<circle cx="11" cy="11" r="10" fill="none" stroke="currentColor" stroke-width="1"/>
					</svg>
					<h3>События матча</h3>
				</div>

				<div class="events-list">
					<?php
					if ( $events ) {
						$processed_indices = array();
						
						foreach ( $events as $index => $event ) {
							// Пропускаем если это событие уже обработано как часть замены
							if ( isset( $processed_indices[ $index ] ) ) {
								continue;
							}
							
							$event_icon = '⚽';
							$event_bg = 'event-goal';

							// Определить тип события по event_name (а не по event_type, который является ID)
							$event_name = ! empty( $event->event_name ) ? strtolower( $event->event_name ) : '';
							
							if ( stripos( $event_name, 'goal' ) !== false || stripos( $event_name, 'own_goal' ) !== false ) {
								$event_icon = '⚽';
								$event_bg = 'event-goal';
							} elseif ( stripos( $event_name, 'assist' ) !== false ) {
								$event_icon = '👟';
								$event_bg = 'event-assist';
							} elseif ( stripos( $event_name, 'warning' ) !== false || stripos( $event_name, 'yellow' ) !== false ) {
								$event_icon = '🟨';
								$event_bg = 'event-yellow';
							} elseif ( stripos( $event_name, 'red' ) !== false ) {
								$event_icon = '🟥';
								$event_bg = 'event-red';
							} elseif ( stripos( $event_name, 'substitution' ) !== false || stripos( $event_name, 'sub' ) !== false ) {
								$event_icon = '🔄';
								$event_bg = 'event-sub';
							}

							// Формат минуты
							$minute = isset( $event->minute ) ? (int) $event->minute : 0;
							if ( isset( $event->extra_time ) && $event->extra_time > 0 ) {
								$minute .= '+' . (int) $event->extra_time;
							}

							// Получить команду из match_lineups (хранится в поле event_team_id через LEFT JOIN)
							$is_home = ! empty( $event->event_team_id ) && $event->event_team_id === $match->home_team_id;
							$team_name = $is_home ? $match->home_team_name : $match->away_team_name;
							$team_color = $is_home ? 'team-home' : 'team-away';

							// Построить текст события
							$event_text = '';
							$event_comment = '';
							
							// Проверяем если это замена (out), ищем соответствующее событие замены (in)
							if ( stripos( $event_name, 'substitution_out' ) !== false ) {
								$player_out = $event->player_name ?? 'Игрок';
								$player_in = '';
								
								// Ищем событие substitution_in для той же команды в близкое время
								foreach ( $events as $check_index => $check_event ) {
									if ( ! isset( $processed_indices[ $check_index ] ) && $check_index > $index ) {
										$check_name = ! empty( $check_event->event_name ) ? strtolower( $check_event->event_name ) : '';
										
										if ( stripos( $check_name, 'substitution_in' ) !== false && 
											 ! empty( $check_event->event_team_id ) && 
											 $check_event->event_team_id === $event->event_team_id &&
											 $check_event->minute == $event->minute ) {
											$player_in = $check_event->player_name ?? '';
											$processed_indices[ $check_index ] = true;
											break;
										}
									}
								}
								
								if ( ! empty( $player_in ) ) {
									$event_text = $player_out . ' на ' . $player_in;
									$event_comment = 'замена';
								} else {
									$event_text = $player_out;
									$event_comment = 'уходит с поля';
								}
							} elseif ( stripos( $event_name, 'substitution_in' ) !== false ) {
								// Это событие должно было быть обработано выше, пропускаем
								continue;
							} elseif ( stripos( $event_name, 'goal' ) !== false || stripos( $event_name, 'own_goal' ) !== false ) {
							$player_scored = $event->player_name ?? 'Игрок';
							$player_assist = '';
							
							// Ищем событие assist для той же команды в близкое время
							foreach ( $events as $check_index => $check_event ) {
								if ( ! isset( $processed_indices[ $check_index ] ) && $check_index > $index ) {
									$check_name = ! empty( $check_event->event_name ) ? strtolower( $check_event->event_name ) : '';
									
									if ( stripos( $check_name, 'assist' ) !== false && 
										 ! empty( $check_event->event_team_id ) && 
										 $check_event->event_team_id === $event->event_team_id &&
										 $check_event->minute == $event->minute ) {
										$player_assist = $check_event->player_name ?? '';
										$processed_indices[ $check_index ] = true;
										break;
									}
								}
							}
							
							if ( ! empty( $player_assist ) ) {
								$event_text = $player_scored . ' (ассистенты: ' . $player_assist . ')';
								$event_comment = 'гол';
							} else {
								$event_text = $player_scored;
								$event_comment = 'гол';
								if ( ! empty( $event->assist_name ) ) {
									$event_comment .= ' (пас: ' . $event->assist_name . ')';
								}
							}
						} elseif ( stripos( $event_name, 'assist' ) !== false ) {
							// Это событие должно было быть обработано выше, пропускаем
							continue;
							} elseif ( stripos( $event_name, 'warning' ) !== false || stripos( $event_name, 'yellow' ) !== false ) {
								$event_text = $event->player_name ?? 'Игрок';
								$event_comment = 'жёлтая карточка';
							} elseif ( stripos( $event_name, 'red' ) !== false ) {
								$event_text = $event->player_name ?? 'Игрок';
								$event_comment = 'красная карточка';
							} else {
								$event_text = $event->player_name ?? 'Игрок';
								if ( ! empty( $event->assist_name ) ) {
									$event_comment = 'пас: ' . $event->assist_name;
								}
							}
							?>
							<div class="event-item <?php echo esc_attr( $event_bg ); ?>">
								<div class="event-minute"><?php echo esc_html( $minute . "'" ); ?></div>
								<div class="event-icon"><?php echo $event_icon; ?></div>
								<div class="event-content">
									<div class="event-player"><?php echo esc_html( $event_text ); ?></div>
									<?php if ( ! empty( $event_comment ) ) : ?>
										<div class="event-comment" style="font-size: 12px; color: #6b7280; margin-top: 2px;">
											<?php echo esc_html( $event_comment ); ?>
										</div>
									<?php endif; ?>
								</div>
								<div class="event-team-badge <?php echo esc_attr( $team_color ); ?>">
									<?php echo esc_html( $team_name ); ?>
								</div>
							</div>
							<?php
						}
					}
					?>
				</div>
				<button class="toggle-events-btn" data-toggle-events>
					<span class="toggle-text">Показать все события</span>
					<svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
						<path d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
					</svg>
				</button>
			</div>
		</section>

		<!-- РАЗДЕЛ 3: Отчет о матче -->
		<section class="report-section">
			<div class="section-card">
				<div class="section-header">
					<svg class="section-icon" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.75 1.83337H5.5C5.01377 1.83337 4.54745 2.02653 4.20363 2.37034C3.85982 2.71416 3.66666 3.18048 3.66666 3.66671V18.3334C3.66666 18.8196 3.85982 19.2859 4.20363 19.6297C4.54745 19.9736 5.01377 20.1667 5.5 20.1667H16.5C16.9862 20.1667 17.4525 19.9736 17.7964 19.6297C18.1402 19.2859 18.3333 18.8196 18.3333 18.3334V6.41671L13.75 1.83337Z" stroke="currentColor" stroke-width="1.83333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M12.8333 1.83337V5.50004C12.8333 5.98627 13.0265 6.45259 13.3703 6.7964C13.7141 7.14022 14.1804 7.33337 14.6667 7.33337H18.3333" stroke="currentColor" stroke-width="1.83333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M9.16667 8.25H7.33334" stroke="currentColor" stroke-width="1.83333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M14.6667 11.9166H7.33334" stroke="currentColor" stroke-width="1.83333" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M14.6667 15.5834H7.33334" stroke="currentColor" stroke-width="1.83333" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<h3>Отчет о матче</h3>
				</div>
				<div class="report-body">
					<?php
					if ( ! empty( $match->match_report ) ) {
						// Применяем wpautop для форматирования параграфов
						echo wp_kses_post( wpautop( $match->match_report ) );
					} else {
						echo '<p style="color: #9ca3af; font-style: italic;">Отчет о матче пока не добавлен.</p>';
					}
					?>
				</div>
			</div>
		</section>

	</div>

</main>

<?php
get_footer();
