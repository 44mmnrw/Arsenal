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
wp_enqueue_style( 'arsenal-page-match', get_template_directory_uri() . '/assets/css/pages/page-match.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

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
				<?php 
				global $wpdb;
				$tournament_name = 'Турнир';
				$tournament_id = '';
				if ( ! empty( $match->match_id ) ) {
					// Получить tournament_id из wp_arsenal_matches
					$tournament_id = $wpdb->get_var( $wpdb->prepare(
						"SELECT tournament_id FROM {$wpdb->prefix}arsenal_matches WHERE match_id = %s",
						$match->match_id
					) );
					
					if ( ! empty( $tournament_id ) ) {
						// Получить name из wp_arsenal_tournaments по tournament_id
						$tournament_name = $wpdb->get_var( $wpdb->prepare(
							"SELECT name FROM {$wpdb->prefix}arsenal_tournaments WHERE tournament_id = %s",
							$tournament_id
						) );
						if ( ! $tournament_name ) {
							$tournament_name = 'Турнир';
						}
					}
				}
				
				// Определить текст тура (для Кубка - этапы, для других - номер тура)
				$tour_text = 'Тур ' . intval( $match->tour );
				if ( $tournament_id === 'E4DE8DC0' ) { // Кубок Беларуси
					$tour_map = array(
						1 => '1/16 Финала',
						2 => '1/8 Финала',
						3 => '1/4 Финала',
						4 => '1/2 Финала',
						5 => 'Финал'
					);
					$tour_text = $tour_map[ intval( $match->tour ) ] ?? 'Тур ' . intval( $match->tour );
				}
				?>
				<span class="league-badge"><?php echo esc_html( $tournament_name ); ?></span>
				<span class="tour-info"><?php echo esc_html( $tour_text ); ?></span>
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
					<svg width="24" height="24" class="detail-icon">
						<use xlink:href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/sprite.svg?v=' . wp_get_theme()->get( 'Version' ) ); ?>#icon-place"></use>
					</svg>
					<?php 
					if ( $stadium ) {
						echo esc_html( $stadium->name ) . ', ' . esc_html( $stadium->city );
					} else {
						echo esc_html( $match->stadium_name ?? 'Стадион "Строитель"' ) . ', Дзержинск';
					}
					?>
				</div>
				<div class="detail-item">
					<lottie-player 
						src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/clock.json' ); ?>" 
						background="transparent" 
						speed="1" 
					style="width: 1.25rem; height: 1.25rem; display: inline-block; vertical-align: -0.15em; margin-right: 4px;" 

					autoplay>
					</lottie-player>
					<?php echo esc_html( date_i18n( 'H:i', strtotime( $match->match_time ?? '19:00' ) ) ); ?>
				</div>
				<div class="detail-item">
					<?php arsenal_icon( 'icon-people', 16, 16, 'detail-icon' ); ?>
					<?php echo esc_html( arsenal_pluralize_spectators( intval( $match->attendance ?? 3500 ) ) ); ?>
				</div>
			</div>

		<!-- Информация о судьях -->
			<div class="referee-info">
				<?php if ( ! empty( $match->main_referee ) ) : ?>
					<div>Главный судья: <?php echo esc_html( $match->main_referee ); ?></div>
				<?php endif; ?>
				<div>
					<?php 
					$assistants = array();
					if ( ! empty( $match->assistant_referees_1 ) ) {
						$assistants[] = $match->assistant_referees_1;
					}
					if ( ! empty( $match->assistant_referees_2 ) ) {
						$assistants[] = $match->assistant_referees_2;
					}
					if ( ! empty( $assistants ) ) : 
					?>
						Помощники судьи: <?php echo esc_html( implode( ', ', $assistants ) ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $match->fourth_referee ) ) : ?>
						<?php echo ! empty( $assistants ) ? ' • ' : ''; ?>Четвёртый судья: <?php echo esc_html( $match->fourth_referee ); ?>
					<?php endif; ?>
				</div>
				<div>
					<?php if ( ! empty( $match->referee_inspector ) ) : ?>
						Инспектор: <?php echo esc_html( $match->referee_inspector ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $match->delegate ) ) : ?>
						<?php echo ! empty( $match->referee_inspector ) ? ' • ' : ''; ?>Делегат: <?php echo esc_html( $match->delegate ); ?>
					<?php endif; ?>
				</div>
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
						toggleBtn.innerHTML = '<span class="toggle-text">Показать всех</span><svg class="toggle-icon" width="48" height="48"><use xlink:href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/sprite.svg?v=' . wp_get_theme()->get( 'Version' ) ); ?>#icon-arrow-down"></use></svg>';
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
						<!-- ВЕРТИКАЛЬНОЕ ПОЛЕ (для мобильных 480px) -->
						<svg class="field-svg field-svg-vertical" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 667 1000.52" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003">
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

						<!-- ГОРИЗОНТАЛЬНОЕ ПОЛЕ (для планшетов 1024px+) -->
						<?php
						// Функция для получения координат горизонтального поля
						function arsenal_get_player_coords_horizontal( $player_count, $x_position, $field_height = 198.81, $side_padding = 20 ) {
							$coords = array();
							$playable_height = $field_height - ( 2 * $side_padding );
							$center = $field_height / 2;
							
							if ( $player_count === 1 ) {
								$coords[] = array( $x_position, $center );
							} elseif ( $player_count === 2 ) {
								$coords[] = array( $x_position, $center - 40 );
								$coords[] = array( $x_position, $center + 40 );
							} elseif ( $player_count === 3 ) {
								$coords[] = array( $x_position, $center - 60 );
								$coords[] = array( $x_position, $center );
								$coords[] = array( $x_position, $center + 60 );
							} elseif ( $player_count === 4 ) {
								$coords[] = array( $x_position, $center - 70 );
								$coords[] = array( $x_position, $center - 25 );
								$coords[] = array( $x_position, $center + 25 );
								$coords[] = array( $x_position, $center + 70 );
							} elseif ( $player_count === 5 ) {
								$coords[] = array( $x_position, $center - 80 );
								$coords[] = array( $x_position, $center - 40 );
								$coords[] = array( $x_position, $center );
								$coords[] = array( $x_position, $center + 40 );
								$coords[] = array( $x_position, $center + 80 );
							} elseif ( $player_count === 6 ) {
								$coords[] = array( $x_position, $center - 68 );
								$coords[] = array( $x_position, $center - 44 );
								$coords[] = array( $x_position, $center - 20 );
								$coords[] = array( $x_position, $center + 20 );
								$coords[] = array( $x_position, $center + 44 );
								$coords[] = array( $x_position, $center + 68 );
							} elseif ( $player_count === 7 ) {
								$coords[] = array( $x_position, $center - 72 );
								$coords[] = array( $x_position, $center - 48 );
								$coords[] = array( $x_position, $center - 24 );
								$coords[] = array( $x_position, $center );
								$coords[] = array( $x_position, $center + 24 );
								$coords[] = array( $x_position, $center + 48 );
								$coords[] = array( $x_position, $center + 72 );
							} elseif ( $player_count === 8 ) {
								$coords[] = array( $x_position, $center - 60 );
								$coords[] = array( $x_position, $center - 40 );
								$coords[] = array( $x_position, $center - 20 );
								$coords[] = array( $x_position, $center );
								$coords[] = array( $x_position, $center + 20 );
								$coords[] = array( $x_position, $center + 40 );
								$coords[] = array( $x_position, $center + 60 );
								$coords[] = array( $x_position, $center + 80 );
							} else {
								// Для 9+ игроков распределить равномерно
								$step = $playable_height / ( $player_count + 1 );
								for ( $i = 1; $i <= $player_count; $i++ ) {
									$coords[] = array( $x_position, $side_padding + ( $step * $i ) );
								}
							}
							
							return $coords;
						}
						
						// Карты позиций для горизонтального поля
						// Масштаб: 298.22 × 198.81 px
						$home_positions_map_horizontal = array(
							'Вратарь' => arsenal_get_player_coords_horizontal( isset( $home_position_counts['Вратарь'] ) ? $home_position_counts['Вратарь'] : 0, 25 ),
							'Защитник' => arsenal_get_player_coords_horizontal( isset( $home_position_counts['Защитник'] ) ? $home_position_counts['Защитник'] : 0, 65 ),
							'Полузащитник' => arsenal_get_player_coords_horizontal( isset( $home_position_counts['Полузащитник'] ) ? $home_position_counts['Полузащитник'] : 0, 115 ),
							'Нападающий' => arsenal_get_player_coords_horizontal( isset( $home_position_counts['Нападающий'] ) ? $home_position_counts['Нападающий'] : 0, 210 )
						);
						
						$away_positions_map_horizontal = array(
							'Вратарь' => arsenal_get_player_coords_horizontal( isset( $away_position_counts['Вратарь'] ) ? $away_position_counts['Вратарь'] : 0, 273 ),
							'Защитник' => arsenal_get_player_coords_horizontal( isset( $away_position_counts['Защитник'] ) ? $away_position_counts['Защитник'] : 0, 233 ),
							'Полузащитник' => arsenal_get_player_coords_horizontal( isset( $away_position_counts['Полузащитник'] ) ? $away_position_counts['Полузащитник'] : 0, 183 ),
							'Нападающий' => arsenal_get_player_coords_horizontal( isset( $away_position_counts['Нападающий'] ) ? $away_position_counts['Нападающий'] : 0, 88 )
						);
						?>
						<svg class="field-svg field-svg-horizontal" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 298.22 198.81" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003">
							<defs>
								<linearGradient id="id2" gradientUnits="userSpaceOnUse" x1="149.11" y1="0" x2="149.11" y2="198.81">
									<stop offset="0" stop-opacity="1" stop-color="#00A63E"/>
									<stop offset="1" stop-opacity="1" stop-color="#008236"/>
								</linearGradient>
							</defs>
							<g>
								<rect width="298.22" height="198.81" fill="url(#id2)"/>
								<!-- Границы поля -->
								<rect x="7" y="7" width="284.22" height="184.81" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Линия середины -->
								<line x1="149.11" y1="7" x2="149.11" y2="191.81" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Центральный круг -->
								<circle cx="149.11" cy="99.405" r="30" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Центральная точка -->
								<circle cx="149.11" cy="99.405" r="2" fill="white" fill-opacity="0.8"/>
								<!-- Штрафная домашней команды -->
								<rect x="7" y="60" width="45" height="78.81" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Вратарская область домашней команды -->
								<rect x="7" y="79" width="25" height="40.81" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Точка пенальти домашней -->
								<circle cx="32" cy="99.405" r="1.5" fill="white" fill-opacity="0.8"/>
								<!-- Штрафная гостевой команды -->
								<rect x="246.22" y="60" width="45" height="78.81" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Вратарская область гостевой команды -->
								<rect x="266.22" y="79" width="25" height="40.81" fill="none" stroke="white" stroke-width="1.5" stroke-opacity="0.8"/>
								<!-- Точка пенальти гостевой -->
								<circle cx="266.22" cy="99.405" r="1.5" fill="white" fill-opacity="0.8"/>
							</g>
							<!-- Игроки домашней команды (красные) на горизонтальном поле -->
							<?php
							$home_player_idx_h = array(
								'Вратарь' => 0,
								'Защитник' => 0,
								'Полузащитник' => 0,
								'Нападающий' => 0
							);
							
							if ( ! empty( $home_players ) ) {
								foreach ( $home_players as $player ) {
									$pos = $player['position'];
									if ( isset( $home_positions_map_horizontal[ $pos ] ) && isset( $home_player_idx_h[ $pos ] ) ) {
										$idx = $home_player_idx_h[ $pos ];
										if ( $idx < count( $home_positions_map_horizontal[ $pos ] ) ) {
											$coords = $home_positions_map_horizontal[ $pos ][ $idx ];
											$home_player_idx_h[ $pos ]++;
											?>
											<circle cx="<?php echo esc_attr( $coords[0] ); ?>" cy="<?php echo esc_attr( $coords[1] ); ?>" r="6" fill="#dc2626" stroke="white" stroke-width="1"/>
											<text x="<?php echo esc_attr( $coords[0] ); ?>" y="<?php echo esc_attr( $coords[1] + 2 ); ?>" text-anchor="middle" fill="white" font-size="5" font-weight="bold" font-family="Arial"><?php echo intval( $player['shirt'] ); ?></text>
											<?php
										}
									}
								}
							}
							?>
							
							<!-- Игроки гостевой команды (синие) на горизонтальном поле -->
							<?php
							$away_player_idx_h = array(
								'Вратарь' => 0,
								'Защитник' => 0,
								'Полузащитник' => 0,
								'Нападающий' => 0
							);
							
							if ( ! empty( $away_players ) ) {
								foreach ( $away_players as $player ) {
									$pos = $player['position'];
									if ( isset( $away_positions_map_horizontal[ $pos ] ) && isset( $away_player_idx_h[ $pos ] ) ) {
										$idx = $away_player_idx_h[ $pos ];
										if ( $idx < count( $away_positions_map_horizontal[ $pos ] ) ) {
											$coords = $away_positions_map_horizontal[ $pos ][ $idx ];
											$away_player_idx_h[ $pos ]++;
											?>
											<circle cx="<?php echo esc_attr( $coords[0] ); ?>" cy="<?php echo esc_attr( $coords[1] ); ?>" r="6" fill="#1a56db" stroke="white" stroke-width="1"/>
											<text x="<?php echo esc_attr( $coords[0] ); ?>" y="<?php echo esc_attr( $coords[1] + 2 ); ?>" text-anchor="middle" fill="white" font-size="5" font-weight="bold" font-family="Arial"><?php echo intval( $player['shirt'] ); ?></text>
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
				setTimeout(function() {
					const toggleBtn = document.querySelector('[data-toggle-events]');
					const eventCard = document.querySelector('.events-section .section-card');
					
					if ( ! toggleBtn || ! eventCard ) {
						console.warn('Events toggle elements not found', { toggleBtn, eventCard });
						return;
					}
					
					// Инициализируем: закрытое состояние
					eventCard.classList.add('collapsed');
					
					toggleBtn.addEventListener('click', function(e) {
						e.preventDefault();
						eventCard.classList.toggle('collapsed');
						toggleBtn.classList.toggle('expanded');
						
						const isExpanded = !eventCard.classList.contains('collapsed');
						const toggleText = toggleBtn.querySelector('.toggle-text');
						
						if (toggleText) {
							toggleText.textContent = isExpanded 
								? 'Скрыть события'
								: 'Показать все события';
						}
					});
				}, 100);
				});
				</script>

				<div class="section-header">
			<svg class="section-icon" width="20" height="20" viewBox="0 0 22 22" fill="none">
				<use xlink:href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/sprite.svg?v=' . wp_get_theme()->get( 'Version' ) ); ?>#icon-event"></use>
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
					<div class="toggle-content"><span class="toggle-text">Показать все события</span><svg class="toggle-icon" width="24" height="24"><use xlink:href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/sprite.svg?v=' . wp_get_theme()->get( 'Version' ) ); ?>#icon-arrow-down"></use></svg></div>
				</button>
			</div>
		</section>

		<!-- РАЗДЕЛ 3: Отчет о матче -->
		<section class="report-section">
			<div class="section-card">
				<div class="section-header">
					<svg class="section-icon" width="20" height="20" viewBox="0 0 22 22" fill="none">
					<use xlink:href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/sprite.svg?v=' . wp_get_theme()->get( 'Version' ) ); ?>#icon-report"></use>
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
