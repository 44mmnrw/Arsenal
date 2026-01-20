<?php
/**
 * Template Name: Весь календарь
 * Description: Displays full calendar with all rounds and matches for the season
 * 
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wpdb;

$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );

$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = '915703';
}

// Получить выбранные параметры из URL
$selected_tournament = isset( $_GET['tournament'] ) ? sanitize_text_field( $_GET['tournament'] ) : '';
$selected_season = isset( $_GET['season'] ) ? sanitize_text_field( $_GET['season'] ) : '';
$selected_month = isset( $_GET['month'] ) ? absint( $_GET['month'] ) : intval( date( 'n' ) ); // 'n' = месяц без ведущего нуля (1-12)

// Значения по умолчанию
// Если сезон не выбран И турнир тоже не выбран - использовать последний доступный сезон
// Если турнир выбран, то пользователь может выбрать "Все сезоны" (empty value)
if ( empty( $selected_season ) && empty( $selected_tournament ) ) {
	// Получить последний доступный сезон
	$last_season = $wpdb->get_row( "SELECT season_id FROM wp_arsenal_seasons ORDER BY start_date DESC LIMIT 1" );
	$selected_season = $last_season ? $last_season->season_id : '';
}

// Получить все турниры из таблицы
$tournaments = $wpdb->get_results(
	"SELECT tournament_id, name
	 FROM wp_arsenal_tournaments
	 ORDER BY name ASC"
);

// Получить доступные сезоны
// Если выбран турнир - берём сезоны только для этого турнира
// Если турнир не выбран - берём все сезоны
if ( ! empty( $selected_tournament ) ) {
	$seasons = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DISTINCT s.season_id, s.season_name
			 FROM wp_arsenal_seasons s
			 INNER JOIN wp_arsenal_matches m ON s.season_id = m.season_id
			 WHERE m.tournament_id = %s
			 ORDER BY s.start_date DESC",
			$selected_tournament
		)
	);
} else {
	$seasons = $wpdb->get_results(
		"SELECT DISTINCT s.season_id, s.season_name
		 FROM wp_arsenal_seasons s
		 INNER JOIN wp_arsenal_matches m ON s.season_id = m.season_id
		 ORDER BY s.start_date DESC"
	);
}

// Построить запрос
$prepare_values = array( $arsenal_team_id, $arsenal_team_id );
$query = "
	SELECT 
		m.*,
		ht.name as home_team,
		ht.logo_url as home_logo,
		at.name as away_team,
		at.logo_url as away_logo,
		st.name as venue,
		st.city as stadium_city,
		t.name as tournament_name,
		ms.match_status as status_name
	FROM wp_arsenal_matches m
	LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN wp_arsenal_stadiums st ON m.stadium_id = st.stadium_id
	LEFT JOIN wp_arsenal_tournaments t ON m.tournament_id = t.tournament_id
	LEFT JOIN wp_arsenal_match_statuses ms ON m.status = ms.status_id
	WHERE (m.home_team_id = %s OR m.away_team_id = %s)";

// Добавить фильтр по сезону если выбран
if ( ! empty( $selected_season ) ) {
	$query .= " AND m.season_id = %s";
	$prepare_values[] = $selected_season;
}

// Добавить фильтр по турниру если выбран
if ( ! empty( $selected_tournament ) ) {
	$query .= " AND m.tournament_id = %s";
	$prepare_values[] = $selected_tournament;
}

// Добавить фильтр по месяцу если выбран
if ( ! empty( $selected_month ) && $selected_month > 0 && $selected_month <= 12 ) {
	$query .= " AND MONTH(m.match_date) = %d";
	$prepare_values[] = $selected_month;
}

$query .= " ORDER BY m.match_date ASC";
$matches = $wpdb->get_results( $wpdb->prepare( $query, ...$prepare_values ) );

function arsenal_match_result_calendar( $match, $arsenal_team_id ) {
	if ( $match->home_team_id == $arsenal_team_id ) {
		if ( $match->home_score > $match->away_score ) return 'win';
		if ( $match->home_score < $match->away_score ) return 'loss';
		return 'draw';
	} else {
		if ( $match->away_score > $match->home_score ) return 'win';
		if ( $match->away_score < $match->home_score ) return 'loss';
		return 'draw';
	}
}

function arsenal_get_logo_url_calendar( $url ) {
	if ( ! empty( $url ) ) {
		return $url;
	}
	return get_template_directory_uri() . '/assets/images/placeholder-logo.png';
}

$has_matches = ! empty( $matches );
?>

<main class="calendar-page">
	<section class="calendar-section">
		<div class="container">
			<div class="calendar-header">
				<h1 class="calendar-title">
					<?php 
					if ( ! empty( $selected_season ) ) {
						// Получить название сезона
						$season_name = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT season_name FROM wp_arsenal_seasons WHERE season_id = %s",
								$selected_season
							)
						);
						echo esc_html( 'Календарь матчей ' . ( $season_name ? $season_name : 'сезона' ) );
					} else {
						echo 'Календарь матчей';
					}
					?>
				</h1>

				<?php if ( ! empty( $tournaments ) || ! empty( $seasons ) ) : ?>
					<form method="get" class="calendar-filters">
						<div class="filters-left">
							<!-- Фильтр по турниру -->
							<?php if ( ! empty( $tournaments ) ) : ?>
								<div class="filter-group">
									<select name="tournament" class="filter-select" onchange="this.form.submit()">
										<option value="">Все турниры</option>
										<?php foreach ( $tournaments as $tournament ) : ?>
											<option value="<?php echo esc_attr( $tournament->tournament_id ); ?>" <?php selected( $selected_tournament, $tournament->tournament_id ); ?>>
												<?php echo esc_html( ! empty( $tournament->name ) ? $tournament->name : 'Турнир' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>

							<!-- Фильтр по сезону -->
							<?php if ( ! empty( $seasons ) ) : ?>
								<div class="filter-group">
									<select name="season" class="filter-select" onchange="this.form.submit()">
										<option value="">Все сезоны</option>
										<?php foreach ( $seasons as $season ) : ?>
											<option value="<?php echo esc_attr( $season->season_id ); ?>" <?php selected( $selected_season, $season->season_id ); ?>>
												<?php echo esc_html( $season->season_name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							<?php endif; ?>
						</div>

						<!-- Месячная навигация справа -->
						<div class="filters-right">
							<div class="month-navigation">
								<?php
								$months = array(
									'', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
									'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
								);

								$prev_month = $selected_month - 1;
								if ( $prev_month < 1 ) {
									$prev_month = 0;
								}

								$next_month = $selected_month + 1;
								if ( $next_month > 12 ) {
									$next_month = 0;
								}

								$prev_url = add_query_arg( array(
									'tournament' => $selected_tournament,
									'season'     => $selected_season,
									'month'      => $prev_month
								) );

								$next_url = add_query_arg( array(
									'tournament' => $selected_tournament,
									'season'     => $selected_season,
									'month'      => $next_month
								) );
								?>

							<a href="<?php echo esc_url( $prev_url ); ?>" <?php echo $selected_month <= 1 ? 'disabled' : ''; ?>>
								<?php arsenal_icon( 'icon-arrow-left', 20, 20, 'month-nav-icon' ); ?>
							</a>
							
							<span class="month-name">
								<?php echo $selected_month > 0 ? esc_html( $months[ $selected_month ] ) : 'Все месяцы'; ?>
							</span>

							<a href="<?php echo esc_url( $next_url ); ?>" <?php echo $selected_month >= 12 ? 'disabled' : ''; ?>>
								<?php arsenal_icon( 'icon-arrow-right', 20, 20, 'month-nav-icon' ); ?>
							</a>
							</div>
						</div>
					</form>
				<?php endif; ?>

				<?php if ( $has_matches ) : ?>
					<div class="calendar-container">
						<?php foreach ( $matches as $match ) :
							$has_result = ! is_null( $match->home_score ) && ! is_null( $match->away_score );
							
							// Build match URL
							$match_url = home_url( '/match/' . $arsenal_team_id . '/' . $match->match_date . '/' );
						?>
							<a href="<?php echo esc_url( $match_url ); ?>" class="calendar-match-link">
								<article class="calendar-match-card<?php echo $has_result ? '' : ' calendar-match-upcoming'; ?>">
									<!-- Заголовок: Название турнира + Тур -->
									<div class="calendar-match-header">
										<div class="calendar-tournament-name"><?php echo esc_html( ! empty( $match->tournament_name ) ? $match->tournament_name : 'Турнир' ); ?></div>
										<div class="calendar-tour-info">
											<?php 
											$tour_text = 'Тур ' . intval( $match->tour );
											if ( ! empty( $match->tournament_id ) && $match->tournament_id === 'E4DE8DC0' ) { // Кубок Беларуси
												$tour_map = array(
													1 => '1/16 Финала',
													2 => '1/8 Финала',
													3 => '1/4 Финала',
													4 => '1/2 Финала',
													5 => 'Финал'
												);
												$tour_text = $tour_map[ intval( $match->tour ) ] ?? 'Тур ' . intval( $match->tour );
											}
											echo esc_html( $tour_text );
											?>
										</div>
									</div>

									<!-- Основной контент: Левая панель + Команды + Правая панель -->
									<div class="calendar-match-content">
										<!-- Левая панель: Дата, время, стадион -->
										<div class="calendar-left-panel">
											<div class="calendar-info-row">
												<?php arsenal_icon( 'icon-calendar', 16, 16, 'calendar-icon' ); ?>
												<span class="calendar-date"><?php echo esc_html( wp_date( 'j.m.Y', strtotime( $match->match_date ) ) ); ?></span>
											</div>
											<div class="calendar-info-row">
												<?php arsenal_icon( 'icon-clock', 16, 16, 'calendar-icon' ); ?>
												<span class="calendar-time"><?php echo esc_html( ! empty( $match->match_time ) ? substr( $match->match_time, 0, 5 ) : '—' ); ?></span>
											</div>
											<?php if ( ! empty( $match->venue ) ) : ?>
											<div class="calendar-info-row">
												<?php arsenal_icon( 'icon-place', 16, 16, 'calendar-icon' ); ?>
											<span class="calendar-stadium"><?php echo esc_html( $match->venue . ( ! empty( $match->stadium_city ) ? ', ' . $match->stadium_city : '' ) ); ?></span>
											</div>
											<?php endif; ?>
										</div>

										<!-- Центральная панель: Две команды -->
										<div class="calendar-teams-panel">
											<!-- Домашняя команда -->
											<div class="calendar-team-block">
												<div class="calendar-team-avatar">
													<div class="calendar-team-avatar-content">
														<?php if ( ! empty( $match->home_logo ) ) : ?>
															<img src="<?php echo esc_url( arsenal_get_logo_url_calendar( $match->home_logo ) ); ?>" alt="<?php echo esc_attr( $match->home_team ); ?>" loading="lazy">
														<?php else : ?>
															<span class="calendar-team-initials"><?php echo esc_html( substr( $match->home_team, 0, 2 ) ); ?></span>
														<?php endif; ?>
													</div>
												</div>
												<span class="calendar-team-name"><?php echo esc_html( $match->home_team ); ?></span>
											</div>

											<!-- Гостевая команда -->
											<div class="calendar-team-block">
												<div class="calendar-team-avatar">
													<div class="calendar-team-avatar-content">
														<?php if ( ! empty( $match->away_logo ) ) : ?>
															<img src="<?php echo esc_url( arsenal_get_logo_url_calendar( $match->away_logo ) ); ?>" alt="<?php echo esc_attr( $match->away_team ); ?>" loading="lazy">
														<?php else : ?>
															<span class="calendar-team-initials"><?php echo esc_html( substr( $match->away_team, 0, 2 ) ); ?></span>
														<?php endif; ?>
													</div>
												</div>
												<span class="calendar-team-name"><?php echo esc_html( $match->away_team ); ?></span>
											</div>
										</div>

										<!-- Правая панель: Счёт + Статус -->
										<div class="calendar-right-panel">
											<div class="calendar-score-block">
												<?php if ( $has_result ) : ?>
													<span class="calendar-score"><?php echo esc_html( $match->home_score ); ?></span>
													<span class="calendar-score-colon">:</span>
													<span class="calendar-score"><?php echo esc_html( $match->away_score ); ?></span>
												<?php else : ?>
													<span class="calendar-score-dash">—</span>
												<?php endif; ?>
											</div>
											<div class="calendar-status-block">
												<?php 
													// Определяем статус на основе статуса матча
													$status_display = 'Предстоит';
													if ( ! empty( $match->status_name ) ) {
														$status_display = $match->status_name;
													} elseif ( $has_result ) {
														$status_display = 'Завершен';
													}
												?>
												<span class="calendar-status"><?php echo esc_html( $status_display ); ?></span>
												<?php if ( $has_result ) : ?>
													<span class="calendar-link-text">Матч-центр <?php arsenal_icon( 'icon-arrow-right', 16, 16, 'calendar-link-icon' ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</article>
							</a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="calendar-empty">
						<p>Матчи на сезон <?php echo esc_html( $active_season_year ); ?> не найдены</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
