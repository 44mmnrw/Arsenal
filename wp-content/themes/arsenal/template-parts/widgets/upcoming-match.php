<?php
/**
 * Template Part: Upcoming Match (Dynamic from Database)
 *
 * Ближайший матч Арсенала из базы данных
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// ID команды Арсенал
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = 'EB8AA245'; // Fallback ID Арсенала
}

// Получаем ближайший матч Арсенала (где ещё нет счёта)
$upcoming_match = $wpdb->get_row( $wpdb->prepare( "
	SELECT 
		m.*,
		ht.name as home_team,
		at.name as away_team,
		m.home_score as home_score,
		m.away_score as away_score,
		s.name as stadium_name,
		s.city as stadium_city
	FROM {$wpdb->prefix}arsenal_matches m
	LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
	WHERE (m.home_team_id = %s OR m.away_team_id = %s)
		AND m.match_date >= NOW()
		AND (m.home_score IS NULL OR m.away_score IS NULL)
	ORDER BY m.match_date ASC
	LIMIT 1
", $arsenal_team_id, $arsenal_team_id ) );

// Если нет предстоящих, берём последний сыгранный
if ( ! $upcoming_match ) {
	$upcoming_match = $wpdb->get_row( $wpdb->prepare( "
		SELECT 
			m.*,
			ht.name as home_team,
			at.name as away_team,
			m.home_score as home_score,
			m.away_score as away_score,
			s.name as stadium_name,
			s.city as stadium_city
		FROM {$wpdb->prefix}arsenal_matches m
		LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
		LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
		LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
		WHERE (m.home_team_id = %s OR m.away_team_id = %s)
			AND m.home_score IS NOT NULL
			AND m.away_score IS NOT NULL
		ORDER BY m.match_date DESC
		LIMIT 1
	", $arsenal_team_id, $arsenal_team_id ) );
}

if ( $upcoming_match ) :
	// Проверяем, играет ли Арсенал дома
	$is_home = ( $upcoming_match->home_team_id == $arsenal_team_id );
	$opponent = $is_home ? $upcoming_match->away_team : $upcoming_match->home_team;
	// Матч завершён если статус "Завершено" (ID: 0083CE05)
	$is_completed = ( $upcoming_match->status === '0083CE05' );
	
	// Форматирование даты
	$formatted_date = '';
	$formatted_time = '';
	
	if ( function_exists( 'wp_date' ) ) {
		$formatted_date = wp_date( 'j F Y', strtotime( $upcoming_match->match_date ) );
		$formatted_time = wp_date( 'H:i', strtotime( $upcoming_match->match_date ) );
	} else {
		$formatted_date = date_i18n( 'j F Y', strtotime( $upcoming_match->match_date ) );
		$formatted_time = date_i18n( 'H:i', strtotime( $upcoming_match->match_date ) );
	}
?>

<section class="upcoming-match-section">
	<div class="upcoming-match-container">
		<div class="section-header">
			<h2 class="section-title">
				<?php echo $is_completed ? 'ПОСЛЕДНИЙ МАТЧ' : 'БЛИЖАЙШИЙ МАТЧ'; ?>
			</h2>
			<p class="section-subtitle">
				<?php echo esc_html( $is_home ? 'Домашний матч' : 'Выездной матч' ); ?>
			</p>
		</div>
		
		<div class="match-card">
			<div class="match-card-inner">
				<!-- Команда хозяев -->
				<div class="match-team">
					<div class="team-logo <?php echo $is_home ? 'home-team' : 'away-team'; ?>">
						<img 
							src="<?php echo esc_url( arsenal_get_team_logo_url( $upcoming_match->home_team ) ); ?>" 
							alt="<?php echo esc_attr( $upcoming_match->home_team ); ?>"
						>
					</div>
					<h3 class="team-name"><?php echo esc_html( $upcoming_match->home_team ); ?></h3>
				</div>
				
				<!-- VS / Счёт -->
				<div class="match-vs">
					<?php if ( $is_completed ) : ?>
						<div class="match-score-large">
							<span class="score-home"><?php echo esc_html( $upcoming_match->home_score ); ?></span>
							<span class="score-separator">:</span>
							<span class="score-away"><?php echo esc_html( $upcoming_match->away_score ); ?></span>
						</div>
					<?php else : ?>
						<p class="match-vs-text">VS</p>
					<?php endif; ?>
					
					<div class="match-date-time">
						<span class="match-date"><?php echo esc_html( $formatted_date ); ?></span>
						<?php if ( ! $is_completed ) : ?>
							<span class="match-time-wrapper"><?php arsenal_icon( 'icon-clock', 16, 16 ); ?><span class="match-time"><?php echo esc_html( $formatted_time ); ?></span></span>
						<?php endif; ?>
						<?php if ( $upcoming_match->stadium_name ) : ?>
							<div class="match-venue">
								<?php arsenal_icon( 'icon-place', 16, 16 ); ?>
								<span>
									<?php echo esc_html( $upcoming_match->stadium_name ); ?><?php if ( $upcoming_match->stadium_city ) : ?>,
										 <?php echo esc_html( $upcoming_match->stadium_city ); ?>
									<?php endif; ?>
								</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
				
				<!-- Команда гостей -->
				<div class="match-team">
					<div class="team-logo <?php echo !$is_home ? 'home-team' : 'away-team'; ?>">
						<img 
							src="<?php echo esc_url( arsenal_get_team_logo_url( $upcoming_match->away_team ) ); ?>" 
							alt="<?php echo esc_attr( $upcoming_match->away_team ); ?>"
						>
					</div>
					<h3 class="team-name"><?php echo esc_html( $upcoming_match->away_team ); ?></h3>
				</div>
			</div>
		</div>
	</div>
</section>

<?php else : ?>
	<!-- Fallback если нет данных -->
	<section class="upcoming-match-section">
		<div class="upcoming-match-container">
			<div class="section-header">
				<h2 class="section-title">БЛИЖАЙШИЙ МАТЧ</h2>
			</div>
			<div class="match-card">
				<p style="text-align: center; padding: 2rem;">
					Информация о ближайшем матче появится в ближайшее время
				</p>
			</div>
		</div>
	</section>
<?php endif; ?>
