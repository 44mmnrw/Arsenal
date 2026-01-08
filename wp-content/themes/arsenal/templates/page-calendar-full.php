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

wp_enqueue_style( 'arsenal-calendar', get_template_directory_uri() . '/assets/css/page-calendar-full.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

global $wpdb;

$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );

$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = '915703';
}

$matches = $wpdb->get_results( $wpdb->prepare( "
	SELECT 
		m.*,
		ht.name as home_team,
		ht.logo_url as home_logo,
		at.name as away_team,
		at.logo_url as away_logo,
		st.name as venue,
		st.city as stadium_city,
		t.name as tournament_name
	FROM wp_arsenal_matches m
	LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN wp_arsenal_stadiums st ON m.stadium_id = st.stadium_id
	LEFT JOIN wp_arsenal_tournaments t ON m.tournament_id = t.tournament_id
	WHERE (m.home_team_id = %s OR m.away_team_id = %s)
		AND YEAR(m.match_date) = %d
	ORDER BY m.tour ASC, m.match_date ASC
", $arsenal_team_id, $arsenal_team_id, $active_season_year ) );

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
				<h1 class="calendar-title">Календарь матчей сезона <?php echo esc_html( $active_season_year ); ?></h1>

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
												<svg class="calendar-icon" viewBox="0 0 16 16" width="16" height="16">
													<path fill="#4a5565" d="M3 2h10c.55 0 1 .45 1 1v10c0 .55-.45 1-1 1H3c-.55 0-1-.45-1-1V3c0-.55.45-1 1-1zm0 2v9h10V4H3z"/>
												</svg>
												<span class="calendar-date"><?php echo esc_html( wp_date( 'j.m.Y', strtotime( $match->match_date ) ) ); ?></span>
											</div>
											<div class="calendar-info-row">
												<svg class="calendar-icon" viewBox="0 0 16 16" width="16" height="16">
													<circle cx="8" cy="8" r="6.5" fill="none" stroke="#ff1a1a" stroke-width="1"/>
													<path d="M8 3v5h3" fill="none" stroke="#ff1a1a" stroke-width="1" stroke-linecap="round"/>
												</svg>
												<span class="calendar-time"><?php echo esc_html( ! empty( $match->match_time ) ? substr( $match->match_time, 0, 5 ) : '—' ); ?></span>
											</div>
											<?php if ( ! empty( $match->venue ) ) : ?>
											<div class="calendar-info-row">
												<svg class="calendar-icon" viewBox="0 0 16 16" width="16" height="16">
													<path fill="#6a7282" d="M8 1c-3.86 0-7 3.13-7 7 0 5.25 7 9 7 9s7-3.75 7-9c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
												</svg>
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
												<?php if ( $has_result ) : ?>
													<span class="calendar-status">Завершен</span>
													<span class="calendar-link-text">Матч-центр →</span>
												<?php else : ?>
													<span class="calendar-status">Предстоит</span>
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
