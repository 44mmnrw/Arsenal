<?php
/**
 * Template part: Last Games (Dynamic from Database)
 *
 * Последние матчи Арсенала из базы данных
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// ID команды Арсенал по team_id из новой структуры БД
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name = 'Арсенал' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = 'EB8AA245'; // Fallback ID Арсенала из БД
}

// Получаем название команды
$arsenal_name = $wpdb->get_var( $wpdb->prepare(
	"SELECT name FROM {$wpdb->prefix}arsenal_teams WHERE team_id = %s",
	$arsenal_team_id
) );

// Получаем последние 3 матча Арсенала с именами команд
// Используем home_score и away_score (не home_score_final/away_score_final)
// Фильтруем по статусу завершенных матчей
$matches = $wpdb->get_results( $wpdb->prepare( "
	SELECT 
		m.match_id,
		m.match_date,
		m.match_time,
		m.home_team_id,
		m.away_team_id,
		m.home_score,
		m.away_score,
		m.status,
		ht.name as home_team,
		at.name as away_team,
		s.name as stadium
	FROM {$wpdb->prefix}arsenal_matches m
	LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
	WHERE (m.home_team_id = %s OR m.away_team_id = %s)
		AND m.home_score IS NOT NULL
		AND m.away_score IS NOT NULL
		AND m.match_date <= NOW()
	ORDER BY m.match_date DESC
	LIMIT 3
", $arsenal_team_id, $arsenal_team_id ) );

// Функция определения результата
function arsenal_match_result( $match, $arsenal_team_id ) {
	if ( $match->home_team_id == $arsenal_team_id ) {
		// Арсенал дома
		if ( $match->home_score > $match->away_score ) return 'win';
		if ( $match->home_score < $match->away_score ) return 'loss';
		return 'draw';
	} else {
		// Арсенал в гостях
		if ( $match->away_score > $match->home_score ) return 'win';
		if ( $match->away_score < $match->home_score ) return 'loss';
		return 'draw';
	}
}

// Используем общую функцию из helpers.php
?>

<section class="last-games-section">
	<div class="container">
		<div class="section-header">
			<h2 class="section-title">РЕЗУЛЬТАТЫ ПРОШЕДШИХ ИГР</h2>
			<a href="<?php echo esc_url( home_url( '/calendar/' ) ); ?>" class="section-link">
				Весь календарь <span class="arrow">→</span>
			</a>
		</div>
		
		<div class="games-grid">
			<?php if ( ! empty( $matches ) ) : ?>
				<?php foreach ( $matches as $match ) : 
					$result = arsenal_match_result( $match, $arsenal_team_id );
					$badge_class = 'badge-' . $result;
					
					// Текст бейджа
					if ( $result === 'win' ) {
						$badge_text = 'Победа';
					} elseif ( $result === 'draw' ) {
						$badge_text = 'Ничья';
					} elseif ( $result === 'loss' ) {
						$badge_text = 'Поражение';
					} else {
						$badge_text = 'Завершен';
					}
					
					// Форматирование даты
					$match_datetime = new DateTime( $match->match_date );
					$formatted_date = '';
					if ( function_exists( 'wp_date' ) ) {
						$formatted_date = wp_date( 'j F Y, H:i', strtotime( $match->match_date ) );
					} else {
						$formatted_date = date_i18n( 'j F Y, H:i', strtotime( $match->match_date ) );
					}
				?>
					<article class="game-card">
						<div class="game-header">
							<span class="game-date"><?php echo esc_html( $formatted_date ); ?></span>
							<span class="game-badge <?php echo esc_attr( $badge_class ); ?>">
								<?php echo esc_html( $badge_text ); ?>
							</span>
						</div>
						
						<div class="game-teams">
							<!-- Команда хозяев -->
							<div class="team home-team">
								<div class="team-logo">
									<img 
										src="<?php echo esc_url( arsenal_get_team_logo_url( $match->home_team ) ); ?>" 
										alt="<?php echo esc_attr( $match->home_team ); ?>"
									>
								</div>
								<span class="team-name"><?php echo esc_html( $match->home_team ); ?></span>
							</div>
							
							<!-- Счёт -->
							<div class="game-score">
								<span class="score-home"><?php echo esc_html( $match->home_score ); ?></span>
								<span class="score-separator">:</span>
								<span class="score-away"><?php echo esc_html( $match->away_score ); ?></span>
							</div>
							
							<!-- Команда гостей -->
							<div class="team away-team">
								<div class="team-logo">
									<img 
										src="<?php echo esc_url( arsenal_get_team_logo_url( $match->away_team ) ); ?>" 
										alt="<?php echo esc_attr( $match->away_team ); ?>"
									>
								</div>
								<span class="team-name"><?php echo esc_html( $match->away_team ); ?></span>
							</div>
						</div>
						
						<div class="game-footer">
							<div class="game-location">
								<?php arsenal_icon( 'icon-location-sm', 14, 14 ); ?>
								<span><?php echo esc_html( $match->stadium ?: 'Стадион не указан' ); ?></span>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="no-matches-message">Нет данных о прошедших матчах.</p>
			<?php endif; ?>
		</div>
	</div>
</section>
