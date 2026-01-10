<?php
/**
 * Template Name: Статистика игрока
 * 
 * Страница отображения полной статистики игрока ФК Арсенал Дзержинск
 */

// Получаем ID игрока из параметра URL (поддерживаем и rewrite rule, и GET параметр)
// player_id может быть как строкой (player_id из БД) так и числом (id из БД)
$player_id = get_query_var( 'player_id' );
if ( ! $player_id ) {
	$player_id = isset( $_GET['player_id'] ) ? sanitize_text_field( $_GET['player_id'] ) : '';
}

// Если ID не передан или игрок не найден, используем дефолтный title
$custom_title = '';
if ( $player_id ) {
	$player = arsenal_get_player_data( $player_id );
	if ( $player ) {
		$display_name = $player->full_name ?: trim($player->last_name . ' ' . $player->first_name);
		$position_name = $player->position_name ?? 'игрок';
		$custom_title = $display_name . ' - игрок команды Арсенал, позиция - ' . $position_name;
		
		// Устанавливаем кастомный title
		add_filter( 'pre_get_document_title', function() use ( $custom_title ) {
			return $custom_title;
		}, 999 );
	}
}

get_header();

// Если ID не передан, показываем ошибку
if ( ! $player_id ) {
	?>
	<main class="player-page">
		<div class="player-hero">
			<div class="player-container">
				<div class="player-not-found">
					<h1>Игрок не указан</h1>
					<p>Не указан ID игрока. Пожалуйста, используйте корректную ссылку на страницу игрока.</p>
				</div>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
}

// Получаем данные игрока
// Функция: arsenal_get_player_data() - см. inc/player-functions.php
$player = arsenal_get_player_data( $player_id );

if ( ! $player ) {
	?>
	<main class="player-page">
		<div class="player-hero">
			<div class="player-container">
				<div class="player-not-found">
					<h1>Игрок не найден</h1>
					<p>Игрок с ID <?php echo esc_html( $player_id ); ?> не найден в базе данных.</p>
				</div>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
}

// Сохраняем оригинальный ID для URL
$url_player_id = $player_id;

// ВАЖНО: Используем реальный player_id из БД для запросов к БД
$player_id = $player->player_id;

// Получаем position_code для определения позиции на поле
$position_code = arsenal_get_player_position( $player->position_id );

// Название позиции берем прямо из БД
$position_name = $player->position_name ?? 'Не указана';

// Display name: используем full_name или собираем из first_name + last_name
$display_name = $player->full_name ?: trim($player->last_name . ' ' . $player->first_name);

// Вычисляем возраст
$age = null;
if ( $player->birth_date ) {
	$birth_date = new DateTime( $player->birth_date );
	$today = new DateTime();
	$age = $today->diff( $birth_date )->y;
}

// Гражданство
$citizenship = $player->citizenship ?? 'Беларусь';

// Команда (основа/резерв)
global $wpdb;
$squad_type = arsenal_get_player_squad_type( $player_id );

// Определяем выбранный турнир (из GET или по умолчанию 71CFDAA6)
$selected_tournament_id = isset( $_GET['tournament'] ) ? sanitize_text_field( $_GET['tournament'] ) : '71CFDAA6';

// Получаем ВСЕ данные за один вызов (оптимизация: вместо 5 отдельных запросов)
$player_data = arsenal_get_player_full_data( $player_id, $selected_tournament_id );

$available_seasons = $player_data['seasons'] ?? array();
$selected_season_stats = $player_data['stats'];
$player_events = $player_data['events'] ?? array();
$available_years = $player_data['years'] ?? array();
$years_stats = $player_data['yearly_stats'] ?? array(); // Гарантируем что это всегда массив

// Определяем название выбранного турнира
$selected_tournament_name = '';
if ( ! empty( $available_seasons ) ) {
	foreach ( $available_seasons as $season ) {
		if ( $season->tournament_id == $selected_tournament_id ) {
			$selected_tournament_name = $season->tournament_name;
			break;
		}
	}
	
	// Если турнир не найден, берем первый из доступных
	if ( empty( $selected_tournament_name ) && ! empty( $available_seasons ) ) {
		$selected_tournament_id = $available_seasons[0]->tournament_id;
		$selected_tournament_name = $available_seasons[0]->tournament_name;
		// Переполучаем данные с правильным турниром
		$player_data = arsenal_get_player_full_data( $player_id, $selected_tournament_id );
		$selected_season_stats = $player_data['stats'];
		$player_events = $player_data['events'];
		$years_stats = $player_data['yearly_stats'];
	}
}

?>


<main class="player-page">
	<div class="player-hero">
		<div class="player-container">
			<!-- Профиль игрока -->
			<div class="player-profile-card">
				<!-- Фото -->
				<div class="player-photo-wrapper">
					<?php if ( ! empty( $player->photo_url ) ) : ?>
						<img src="<?php echo esc_url( home_url( $player->photo_url ) ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" loading="lazy">
					<?php else : ?>
						<span class="no-photo"><?php echo esc_html( mb_substr( $display_name, 0, 1 ) ); ?></span>
					<?php endif; ?>
				</div>
				
				<!-- Информация игрока -->
				<div class="player-info-section">
					<!-- Заголовок с номером и именем -->
					<div class="player-header">
							<?php if ( ! empty( $player->shirt_number ) ) : ?>
						<?php endif; ?>
						
						<div class="player-name-block">
							<h1 class="player-name"><?php echo esc_html( $display_name ); ?></h1>
								<?php if ( ! empty( $position_name ) ) : ?>
								<span class="player-position-tag"><?php echo esc_html( $position_name ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					
					<!-- Статистика характеристик -->
					<div class="player-stats-grid">
						<?php if ( $age !== null ) : ?>
							<div class="stat-box">
								<div class="stat-box-label">Возраст</div>
								<div class="stat-box-value"><?php echo esc_html( arsenal_pluralize_years( $age ) ); ?></div>
							</div>
						<?php endif; ?>
						
						<?php if ( ! empty( $player->height_cm ) ) : ?>
							<div class="stat-box">
								<div class="stat-box-label">Рост</div>
								<div class="stat-box-value"><?php echo esc_html( $player->height_cm ); ?><span class="unit">см</span></div>
							</div>
						<?php endif; ?>
						
						<?php if ( ! empty( $player->weight_kg ) ) : ?>
							<div class="stat-box">
								<div class="stat-box-label">Вес</div>
								<div class="stat-box-value"><?php echo esc_html( $player->weight_kg ); ?><span class="unit">кг</span></div>
							</div>
						<?php endif; ?>
						
						<div class="stat-box">
							<div class="stat-box-label">Команда</div>
							<div class="stat-box-value"><?php echo esc_html( $squad_type ); ?></div>
						</div>
						
						<div class="stat-box full-width">
							<div class="stat-box-label">Гражданство</div>
							<div class="stat-box-value"><?php echo esc_html( $citizenship ); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- ПОЛОСА СТАТИСТИКИ ИГРОКА -->
	<section class="player-stats-bar" aria-label="<?php esc_attr_e( 'Статистика игрока', 'arsenal' ); ?>">
		<div class="player-stats-bar-container">
			<!-- Селектор турнира -->
			<?php if ( ! empty( $available_seasons ) && count( $available_seasons ) > 1 ) : ?>
		<div class="player-stats-bar-item player-stats-bar-item--tournament">
				<div class="custom-season-select">
					<span class="custom-season-select-trigger player-stats-bar-number">
						<?php echo esc_html( $selected_tournament_name ); ?>
					</span>
					<div class="custom-season-select-dropdown">
						<?php foreach ( $available_seasons as $season ) : ?>
							<a href="?page_id=<?php echo get_the_ID(); ?>&player_id=<?php echo esc_attr( $url_player_id ); ?>&tournament=<?php echo esc_attr( $season->tournament_id ); ?>" 
							   class="<?php echo ( $selected_tournament_id === $season->tournament_id ) ? 'selected' : ''; ?>">
								<?php echo esc_html( $season->tournament_name ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
				<span class="player-stats-bar-label">Турнир</span>
			</div>
			<script>
			(function() {
				const select = document.querySelector('.custom-season-select');
				const trigger = select.querySelector('.custom-season-select-trigger');
				
				trigger.addEventListener('click', function(e) {
					e.stopPropagation();
					select.classList.toggle('active');
				});
				
				// Закрытие при клике вне селектора
				document.addEventListener('click', function(e) {
					if (!select.contains(e.target)) {
						select.classList.remove('active');
					}
				});
			})();
			</script>
			<?php else : ?>
		<div class="player-stats-bar-item">
				<span class="player-stats-bar-number"><?php echo esc_html( $selected_tournament_name ); ?></span>
				<span class="player-stats-bar-label">Турнир</span>
			</div>
			<?php endif; ?>
			
			
			<?php if ( $selected_season_stats ) : ?>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number"><?php echo esc_html( number_format( $selected_season_stats->minutes_played, 0, '', ' ' ) ); ?></span>
					<span class="player-stats-bar-label">Минут за сезон</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number"><?php echo esc_html( $selected_season_stats->matches_played ); ?></span>
					<span class="player-stats-bar-label">Матчей сыграно</span>
				</div>
				<div class="player-stats-bar-item">
					<?php if ($position_code === 'A98B3A74') : ?>
						<span class="player-stats-bar-number"><?php echo esc_html( $selected_season_stats->goals_conceded ?? '0' ); ?></span>
						<span class="player-stats-bar-label">Голов пропущено</span>
					<?php else : ?>
						<span class="player-stats-bar-number"><?php echo esc_html( $selected_season_stats->goals ); ?></span>
						<span class="player-stats-bar-label">Голов забито</span>
					<?php endif; ?>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number"><?php echo esc_html( $selected_season_stats->assists ); ?></span>
					<span class="player-stats-bar-label">Ассистов</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number"><?php echo esc_html( $selected_season_stats->yellow_cards ); ?></span>
					<span class="player-stats-bar-label">Жёлтых карточек</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number"><?php echo esc_html( $selected_season_stats->red_cards ); ?></span>
					<span class="player-stats-bar-label">Красных карточек</span>
				</div>
			<?php else : ?>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number">—</span>
					<span class="player-stats-bar-label">Минут за сезон</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number">—</span>
					<span class="player-stats-bar-label">Матчей сыграно</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number">—</span>
					<span class="player-stats-bar-label"><?php echo $position_code === 'A98B3A74' ? 'Голов пропущено' : 'Голов забито'; ?></span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number">—</span>
					<span class="player-stats-bar-label">Ассистов</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number">—</span>
					<span class="player-stats-bar-label">Жёлтых карточек</span>
				</div>
				<div class="player-stats-bar-item">
					<span class="player-stats-bar-number">—</span>
					<span class="player-stats-bar-label">Красных карточек</span>
				</div>
			<?php endif; ?>
		</div>
	</section>
	
	<!-- СЕКЦИЯ СО СТАТИСТИКОЙ -->
	<div class="player-content">
		<div class="content-container">
			<!-- Сетка основных блоков: Позиция + Матчи + Биография -->
			<div class="player-sections-grid">
				<!-- Позиция на поле -->
			<div class="position-section" data-position-section>
					<h2 class="section-title">
						<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
						Позиция на поле
					</h2>
					<div class="field-container">
						<svg class="field-svg" width="320" height="214" viewBox="0 0 320 214" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_81_97)">
								<path d="M0 10C0 4.47715 4.47715 0 10 0H310C315.523 0 320 4.47715 320 10V203.328C320 208.851 315.523 213.328 310 213.328H10C4.47715 213.328 0 208.851 0 203.328V10Z" fill="url(#paint0_linear_81_97)"/>
								<path opacity="0.8" d="M309.33 10.6664H10.6704V202.662H309.33V10.6664Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M160 10.6664V202.662" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M160 132.263C174.138 132.263 185.599 120.802 185.599 106.664C185.599 92.5259 174.138 81.0646 160 81.0646C145.862 81.0646 134.401 92.5259 134.401 106.664C134.401 120.802 145.862 132.263 160 132.263Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M160 108.371C160.943 108.371 161.707 107.607 161.707 106.664C161.707 105.721 160.943 104.957 160 104.957C159.057 104.957 158.293 105.721 158.293 106.664C158.293 107.607 159.057 108.371 160 108.371Z" fill="white"/>
								<path opacity="0.8" d="M309.33 42.6656H266.664V170.662H309.33V42.6656Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M309.33 74.6648H292.263V138.663H309.33V74.6648Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M283.73 107.731C284.319 107.731 284.797 107.253 284.797 106.664C284.797 106.075 284.319 105.597 283.73 105.597C283.141 105.597 282.664 106.075 282.664 106.664C282.664 107.253 283.141 107.731 283.73 107.731Z" fill="white"/>
								<path opacity="0.8" d="M315.729 89.5978H309.33V123.73H315.729V89.5978Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M53.336 42.6656H10.6704V170.662H53.336V42.6656Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M27.7366 74.6648H10.6704V138.663H27.7366V74.6648Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M36.2698 107.731C36.8589 107.731 37.3364 107.253 37.3364 106.664C37.3364 106.075 36.8589 105.597 36.2698 105.597C35.6807 105.597 35.2031 106.075 35.2031 106.664C35.2031 107.253 35.6807 107.731 36.2698 107.731Z" fill="white"/>
								<path opacity="0.8" d="M10.6704 89.5978H4.27055V123.73H10.6704V89.5978Z" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M10.6743 17.0701C14.9409 17.0649 17.0715 14.929 17.0663 10.6625" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M309.33 17.0663C305.063 17.0662 302.93 14.9329 302.93 10.6664" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M10.6817 196.25C14.9483 196.266 17.074 198.406 17.0589 202.673" stroke="white" stroke-width="1.06664"/>
								<path opacity="0.8" d="M309.375 196.308C305.109 196.247 302.945 198.349 302.884 202.616" stroke="white" stroke-width="1.06664"/>
							</g>
							<defs>
								<linearGradient id="paint0_linear_81_97" x1="0" y1="106.664" x2="320" y2="106.664" gradientUnits="userSpaceOnUse">
									<stop stop-color="#00A63E"/>
									<stop offset="1" stop-color="#008236"/>
								</linearGradient>
								<clipPath id="clip0_81_97">
									<path d="M0 10C0 4.47715 4.47715 0 10 0H310C315.523 0 320 4.47715 320 10V203.328C320 208.851 315.523 213.328 310 213.328H10C4.47715 213.328 0 208.851 0 203.328V10Z" fill="white"/>
								</clipPath>
							</defs>
						</svg>
						<?php
						// Определяем позицию на поле в процентах от размеров SVG (320x214)
						$field_positions = arsenal_get_field_positions();
						$pos = isset($field_positions[$position_code]) ? $field_positions[$position_code] : $field_positions['M'];
						?>
						<div class="player-position-marker" style="left: <?php echo $pos['x']; ?>%; top: <?php echo $pos['y']; ?>%; transform: translate(-50%, -50%);">
						<div class="marker-content">
							<div class="marker-number"><?php echo esc_html( $player->shirt_number ? $player->shirt_number : '?' ); ?></div>
						</div>
					</div>
				</div>
				<div class="player-position-label">
					<div class="position-label-container">
						<?php if ($player->shirt_number) : ?>
							<div class="position-label-shirt">
								<div class="shirt-badge"><?php echo esc_html( $player->shirt_number ); ?></div>
							</div>
						<?php endif; ?>
						<div class="position-label-info">
							<div class="player-name-label"><?php echo esc_html( $display_name ); ?></div>
							<?php if ( $position_name ) : ?>
								<div class="position-name-label"><?php echo esc_html( $position_name ); ?></div>
							<?php endif; ?>
						</div>
						<div class="position-label-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
								<path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
							</svg>
						</div>
					</div>
				</div>
			</div>
			
			<!-- События игрока -->
			<?php
			get_template_part( 'template-parts/blocks/player-events-table', null, array(
				'player_events' => $player_events,
				'selected_tournament_name' => $selected_tournament_name,
				'position_code' => $position_code,
			) );
			?>
			<!-- Биография -->
			<div class="biography-section">
				<h2 class="section-title">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
					Биография
				</h2>
				<div class="biography-text">
					<?php 
					if ( isset($player->biography) && $player->biography ) {
						echo wp_kses_post( $player->biography );
					}
					?>
				</div>
			</div>

			<!-- Статистика по годам для выбранного турнира -->
			<?php if ( ! empty( $years_stats ) ) : ?>
				<div class="seasons-table-section">
					<h2 class="section-title">
						<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
						Статистика по годам в <?php echo esc_html( $selected_tournament_name ); ?>
					</h2>
					<table class="stats-table">
						<thead>
							<tr>
								<th>Год</th>
								<th>Клуб</th>
								<th>Матчей сыграно</th>
								<th>Минут за сезон</th>								
								<th><?php echo $position_code === 'A98B3A74' ? 'Голов пропущено' : 'Голов забито'; ?></th>
								<th>Ассистов</th>
								<th>Жёлтых карточек</th>
								<th>Красных карточек</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $years_stats as $stat ) : ?>
								<tr>
									<td data-label="Год" class="year-cell"><?php echo esc_html( $stat->year ); ?></td>
									<td data-label="Команда"><?php echo esc_html( arsenal_get_player_team_by_year( $player_id, $selected_tournament_id, $stat->year ) ); ?></td>
									<td data-label="Матчей сыграно"><?php echo esc_html( $stat->matches_played ); ?></td>
									<td data-label="Минут за сезон"><?php echo esc_html( number_format( $stat->minutes_played, 0, '', ' ' ) ); ?></td>
									<td data-label="<?php echo $position_code === 'A98B3A74' ? 'Голов пропущено' : 'Голов забито'; ?>">
										<?php 
											if ( $position_code === 'A98B3A74' ) {
												echo esc_html( $stat->goals_conceded ?? '0' );
											} else {
												echo esc_html( $stat->goals );
											}
										?>
									</td>
									<td data-label="Ассистов"><?php echo esc_html( $stat->assists ); ?></td>
									<td data-label="Жёлтых карточек"><?php echo esc_html( $stat->yellow_cards ); ?></td>
									<td data-label="Красных карточек"><?php echo esc_html( $stat->red_cards ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<script>
(function() {
	const section = document.querySelector('[data-player-events-section]');
	const wrapper = section ? section.querySelector('[data-matches-table]') : null;
	const btn = section ? section.querySelector('[data-toggle-matches]') : null;
	const positionSection = document.querySelector('[data-position-section]');
	
	if (!wrapper || !btn || !positionSection) return;
	
	// Синхронизация высот
	function syncHeights() {
		const positionHeight = positionSection.offsetHeight;
		section.style.minHeight = positionHeight + 'px';
	}
	
	// Функция для проверки, нужен ли коллапс
	function checkIfCollapseNeeded() {
		const wrapperHeight = wrapper.scrollHeight;
		
		// Если таблица длиннее 500px, применяем коллапс
		if (wrapperHeight > 500) {
			wrapper.classList.add('collapsed');
			btn.style.display = 'flex';
		}
	}
	
	// Проверяем после загрузки DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			checkIfCollapseNeeded();
			syncHeights();
		});
	} else {
		checkIfCollapseNeeded();
		syncHeights();
	}
	
	// Переключение состояния
	btn.addEventListener('click', function(e) {
		e.preventDefault();
		wrapper.classList.toggle('collapsed');
		btn.classList.toggle('expanded');
		
		if (wrapper.classList.contains('collapsed')) {
			btn.querySelector('.toggle-text').textContent = 'Показать все матчи';
		} else {
			btn.querySelector('.toggle-text').textContent = 'Свернуть матчи';
		}
	});
	
	// Переопределяем при изменении размера окна
	let resizeTimeout;
	window.addEventListener('resize', function() {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(function() {
			checkIfCollapseNeeded();
			syncHeights();
		}, 250);
	});
})();
</script>

<?php
get_footer();