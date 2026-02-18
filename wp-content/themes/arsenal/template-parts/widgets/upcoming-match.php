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

// ID команды Арсенал — с кешированием через transient
$arsenal_team_id = get_transient( 'arsenal_team_id' );
if ( false === $arsenal_team_id ) {
	$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
	if ( ! $arsenal_team_id ) {
		$arsenal_team_id = 'EB8AA245'; // Fallback ID Арсенала
	}
	set_transient( 'arsenal_team_id', $arsenal_team_id, DAY_IN_SECONDS );
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
		s.city as stadium_city,
		t.name as tournament_name,
		CONCAT(m.match_date, ' ', COALESCE(m.match_time, '00:00:00')) as match_datetime
	FROM {$wpdb->prefix}arsenal_matches m
	LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
	LEFT JOIN {$wpdb->prefix}arsenal_tournaments t ON m.tournament_id = t.tournament_id
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
			s.city as stadium_city,
			t.name as tournament_name,
			CONCAT(m.match_date, ' ', COALESCE(m.match_time, '00:00:00')) as match_datetime
		FROM {$wpdb->prefix}arsenal_matches m
		LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
		LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
		LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
		LEFT JOIN {$wpdb->prefix}arsenal_tournaments t ON m.tournament_id = t.tournament_id
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
	
	// Форматирование даты и времени
	$formatted_date = '';
	$formatted_time = '';
	
	if ( $upcoming_match && ! empty( $upcoming_match->match_datetime ) ) {
		// Используем время прямо из БД БЕЗ конвертации таймзоны
		// (время в БД уже в нужном формате для отображения)
		$date_parts = explode( ' ', $upcoming_match->match_datetime );
		if ( count( $date_parts ) === 2 ) {
			$match_date = $date_parts[0];
			$match_time = $date_parts[1];
			
			// Форматируем дату с локализацией
			$timestamp = strtotime( $match_date );
			if ( function_exists( 'wp_date' ) ) {
				// Используем current_time чтобы избежать двойной конвертации
				$formatted_date = current_time( 'j F Y', false );
				$formatted_date = date_i18n( 'j F Y', strtotime( $match_date ) );
			} else {
				$formatted_date = date_i18n( 'j F Y', strtotime( $match_date ) );
			}
			
			// Время берём сырое из БД, без конвертации
			$formatted_time = substr( $match_time, 0, 5 ); // HH:MM
		}
	}
	
	// Debug: показать реальное время из базы
	// echo '<!-- DEBUG: match_datetime = ' . $upcoming_match->match_datetime . ' | formatted_time = ' . $formatted_time . ' -->';
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
				<!-- Название турнира (верхняя строчка) -->
				<?php if ( ! empty( $upcoming_match->tournament_name ) ) : ?>
					<div class="match-tournament-badge">
						<?php echo esc_html( $upcoming_match->tournament_name ); ?>
					</div>
				<?php endif; ?>
				
				<!-- Основное содержимое карточки -->
				<div class="match-card-content">
					<!-- Команда хозяев -->
					<div class="match-team">
					<div class="team-logo <?php echo $is_home ? 'home-team' : 'away-team'; ?>">
						<?php arsenal_render_team_logo( $upcoming_match->home_team ); ?>
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
							<div class="detail-item">
								<lottie-player 
									src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/clock.json' ); ?>" 
								background="transparent">
								</lottie-player>
								<span class="match-time"><?php echo esc_html( $formatted_time ); ?></span>
							</div>
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
						<?php arsenal_render_team_logo( $upcoming_match->away_team ); ?>
					</div>
					<h3 class="team-name"><?php echo esc_html( $upcoming_match->away_team ); ?></h3>
				</div>
				</div>
				<!-- Конец match-card-content -->
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
				<p class="match-no-data">
					Информация о ближайшем матче появится в ближайшее время
				</p>
			</div>
		</div>
	</section>
<?php endif; ?>
