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

// ID команды Арсенал (fallback на случай проблем с запросом)
$arsenal_team_id = $wpdb->get_var( "SELECT id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = 915703; // Hardcoded fallback
}

// Получаем ближайший матч Арсенала (где ещё нет счёта)
$upcoming_match = $wpdb->get_row( $wpdb->prepare( "
	SELECT 
		m.*,
		ht.name as home_team,
		at.name as away_team,
		m.home_score_final as home_score,
		m.away_score_final as away_score,
		v.name as venue
	FROM wp_arsenal_matches m
	LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.id
	LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.id
	LEFT JOIN wp_arsenal_venues v ON m.venue_id = v.id
	WHERE (m.home_team_id = %d OR m.away_team_id = %d)
		AND m.match_date >= NOW()
		AND (m.home_score_final IS NULL OR m.away_score_final IS NULL)
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
			m.home_score_final as home_score,
			m.away_score_final as away_score,
			v.name as venue
		FROM wp_arsenal_matches m
		LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.id
		LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.id
		LEFT JOIN wp_arsenal_venues v ON m.venue_id = v.id
		WHERE (m.home_team_id = %d OR m.away_team_id = %d)
			AND m.home_score_final IS NOT NULL
			AND m.away_score_final IS NOT NULL
		ORDER BY m.match_date DESC
		LIMIT 1
	", $arsenal_team_id, $arsenal_team_id ) );
}

// Используем общую функцию из helpers.php

if ( $upcoming_match ) :
	// Проверяем, играет ли Арсенал дома (сравниваем по ID)
	$is_home = ( $upcoming_match->home_team_id == $arsenal_team_id );
	$opponent = $is_home ? $upcoming_match->away_team : $upcoming_match->home_team;
	$is_completed = ( $upcoming_match->home_score !== null && $upcoming_match->away_score !== null );
	
	// Форматирование даты
	$match_datetime = new DateTime( $upcoming_match->match_date );
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
					<?php if ( $upcoming_match->venue ) : ?>
						<div class="team-location">
							<?php arsenal_icon( 'icon-location-sm', 16, 16 ); ?>
							<span><?php echo esc_html( $upcoming_match->venue ); ?></span>
						</div>
					<?php endif; ?>
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
							<span class="match-time"><?php echo esc_html( $formatted_time ); ?></span>
						<?php endif; ?>
						<?php if ( $upcoming_match->venue ) : ?>
							<div class="match-venue">
								<?php arsenal_icon( 'icon-stadium', 16, 16 ); ?>
								<span><?php echo esc_html( $upcoming_match->venue ); ?></span>
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
			
			<?php if ( ! $is_completed ) : ?>
				<div class="match-ticket-info">
					<p class="ticket-text">
						Билеты на матч можно приобрести на 
						<a href="#" class="ticket-link">официальном сайте</a>
					</p>
				</div>
			<?php endif; ?>
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
