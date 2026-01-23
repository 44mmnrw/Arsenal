<?php
/**
 * Шаблон страницы турнирного дерева
 *
 * Template Name: Турнирное дерево
 * Template Post Type: page
 * 
 * Страница с турнирным деревом (скобками) на полную ширину
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();

// Enqueue tournament styles
wp_enqueue_style( 'arsenal-tournament', get_template_directory_uri() . '/assets/css/pages/page-tournament.css', array( 'arsenal-footer' ), ARSENAL_VERSION );

/**
 * Получить инициалы команды (2 буквы)
 */
$get_team_initials = function( $team_name ) {
	if ( empty( $team_name ) ) {
		return 'ПМ';
	}
	
	// Пытаемся найти паттерн "ФК Название"
	if ( preg_match( '/ФК\s+([А-Яа-яЁё]+)/u', $team_name, $matches ) ) {
		return mb_strtoupper( mb_substr( $matches[1], 0, 2, 'UTF-8' ), 'UTF-8' );
	}
	
	// Если не нашли, берём первые 2 буквы названия
	return mb_strtoupper( mb_substr( $team_name, 0, 2, 'UTF-8' ), 'UTF-8' );
};

/**
 * Форматировать дату на русском языке
 */
$format_russian_date = function( $date_string ) {
	$months = array(
		1  => 'января',
		2  => 'февраля',
		3  => 'марта',
		4  => 'апреля',
		5  => 'мая',
		6  => 'июня',
		7  => 'июля',
		8  => 'августа',
		9  => 'сентября',
		10 => 'октября',
		11 => 'ноября',
		12 => 'декабря'
	);
	
	$timestamp = strtotime( $date_string );
	$day       = date( 'j', $timestamp );
	$month     = $months[ (int) date( 'n', $timestamp ) ];
	
	return $day . ' ' . $month;
};

/**
 * Рендер карточки матча для турнирной сетки
 */
$render_bracket_match_card = function( $match ) use ( $get_team_initials, $format_russian_date ) {
	if ( ! $match ) {
		return;
	}
	
	$has_result    = ! is_null( $match->home_score ) && ! is_null( $match->away_score );
	$formatted_date = $format_russian_date( $match->match_date );
	$formatted_time = ( isset( $match->match_time ) ? substr( $match->match_time, 0, 5 ) : '00:00' );
	?>
	<article class="bracket-match-card">
		<div class="match-datetime">
			<?php echo esc_html( $formatted_date ); ?> • <?php echo esc_html( $formatted_time ); ?>
		</div>
		<div class="match-team match-team-home <?php echo ( $has_result && $match->home_score > $match->away_score ) ? 'winner' : ''; ?>">
			<div class="team-info">
				<div class="team-logo">
					<?php if ( ! empty( $match->home_team_logo ) ) : ?>
						<img src="<?php echo esc_url( $match->home_team_logo ); ?>" alt="<?php echo esc_attr( $match->home_team_name ); ?>" class="team-logo-img">
					<?php else : ?>
						<span class="team-initials"><?php echo esc_html( $get_team_initials( $match->home_team_name ) ); ?></span>
					<?php endif; ?>
				</div>
				<span class="team-name"><?php echo esc_html( $match->home_team_name ?? 'Предстоящий матч' ); ?></span>
			</div>
			<div class="team-score"><?php echo $has_result ? esc_html( $match->home_score ) : '—'; ?></div>
		</div>
		<div class="match-team match-team-away <?php echo ( $has_result && $match->away_score > $match->home_score ) ? 'winner' : ''; ?>">
			<div class="team-info">
				<div class="team-logo">
					<?php if ( ! empty( $match->away_team_logo ) ) : ?>
						<img src="<?php echo esc_url( $match->away_team_logo ); ?>" alt="<?php echo esc_attr( $match->away_team_name ); ?>" class="team-logo-img">
					<?php else : ?>
						<span class="team-initials"><?php echo esc_html( $get_team_initials( $match->away_team_name ) ); ?></span>
					<?php endif; ?>
				</div>
				<span class="team-name"><?php echo esc_html( $match->away_team_name ?? 'Предстоящий матч' ); ?></span>
			</div>
			<div class="team-score"><?php echo $has_result ? esc_html( $match->away_score ) : '—'; ?></div>
		</div>
	</article>
	<?php
};

global $wpdb;

// ID турнира Кубка Беларуси
$tournament_id = 'E4DE8DC0';

// Получаем последний доступный сезон для этого турнира
$season_id = isset( $_GET['season_id'] ) ? sanitize_text_field( $_GET['season_id'] ) : '';

// Если сезон не указан, берём самый свежий из доступных для турнира
if ( ! $season_id ) {
	$latest_season = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT DISTINCT season_id FROM {$wpdb->prefix}arsenal_matches 
             WHERE tournament_id = %s 
             ORDER BY match_date DESC 
             LIMIT 1",
			$tournament_id
		)
	);
	
	$season_id = $latest_season ? $latest_season->season_id : null;
}

// Получаем год сезона из первого матча этого сезона (для возможной информации)
$season_year = intval( date( 'Y' ) );
$season_name = '';

if ( $season_id ) {
	$match_date = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT YEAR(match_date) as season_year FROM {$wpdb->prefix}arsenal_matches 
             WHERE tournament_id = %s AND season_id = %s 
             LIMIT 1",
			$tournament_id,
			$season_id
		)
	);
	if ( $match_date ) {
		$season_year = intval( $match_date );
	}
	
	// Получаем название сезона из таблицы seasons
	$season = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT season_name FROM {$wpdb->prefix}arsenal_seasons WHERE season_id = %s LIMIT 1",
			$season_id
		)
	);
	
	if ( $season && isset( $season->season_name ) ) {
		$season_name = $season->season_name;
	} else {
		// Fallback - используем год если season_name недоступен
		$season_name = $season_year;
	}
}
?>

<main id="main" class="site-main tournament-page">
	
	<div class="tournament-container">
		
		<!-- Заголовок турнира -->
		<div class="tournament-header">
			<h1 class="tournament-title">Кубок Беларуси • <?php echo esc_html( $season_name ); ?> • Турнирная сетка</h1>
		</div>
		
		<!-- Турнирное дерево -->
		<?php
		// Получаем все матчи турнира, сортируем по tour (раунду)
		$matches = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.*, 
                ht.name as home_team_name, 
                ht.logo_url as home_team_logo,
                at.name as away_team_name,
                at.logo_url as away_team_logo,
                s.name as stadium_name
         FROM {$wpdb->prefix}arsenal_matches m
         LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
         LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
         LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
         WHERE m.tournament_id = %s AND m.season_id = %s
         ORDER BY m.tour ASC, m.match_date ASC, m.id ASC",
				$tournament_id,
				$season_id
			)
		);

		if ( empty( $matches ) ) {
			echo '<p class="no-matches">Матчи не найдены</p>';
		} else {
			// Распределяем матчи по турам используя поле tour
			$rounds = array(
				'1/16'  => array( 'title' => '1/16 финала', 'total' => 16, 'matches' => array() ),
				'1/8'   => array( 'title' => '1/8 финала', 'total' => 8, 'matches' => array() ),
				'1/4'   => array( 'title' => '1/4 финала', 'total' => 4, 'matches' => array() ),
				'1/2'   => array( 'title' => '1/2 финала', 'total' => 2, 'matches' => array() ),
				'final' => array( 'title' => '🏆 Финал', 'total' => 1, 'matches' => array() ),
			);

			// Распределяем матчи по раундам на основе поля tour
			// tour 1 = 1/16, tour 2 = 1/8, tour 3 = 1/4, tour 4 = 1/2, tour 5 = Финал
			foreach ( $matches as $match ) {
				$tour = (int) ( $match->tour ?? 0 );
				
				switch ( $tour ) {
					case 1:
						$rounds['1/16']['matches'][] = $match;
						break;
					case 2:
						$rounds['1/8']['matches'][] = $match;
						break;
					case 3:
						$rounds['1/4']['matches'][] = $match;
						break;
					case 4:
						$rounds['1/2']['matches'][] = $match;
						break;
					case 5:
						$rounds['final']['matches'][] = $match;
						break;
				}
			}
			?>
			<div class="bracket-grid-container">
				
				<!-- Финал (1 карточка) -->
				<div class="bracket-item bracket-final">
					<?php 
					$match = $rounds['final']['matches'][0] ?? null;
					if ( $match ) :
						$render_bracket_match_card( $match );
					else : ?>
						<div class="empty-card">
							<span class="tbd-text">Предстоящий матч</span>
							<span class="tbd-label">Финал</span>
						</div>
					<?php endif; ?>
				</div>
				
				<!-- 1/2 финала -->
				<div class="bracket-item connector-line">
					<h3 class="round-title">1/2 финала</h3>
				</div>
				
				<?php for ( $i = 0; $i < 2; $i++ ) :
					$match = $rounds['1/2']['matches'][ $i ] ?? null;
				?>
					<div class="bracket-item bracket-half-<?php echo $i + 1; ?>">
						<?php if ( $match ) :
						$render_bracket_match_card( $match );
						else : ?>
							<div class="empty-card">
								<span class="tbd-text">Предстоящий матч</span>
								<span class="tbd-label">1/2 финала</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
				
				<!-- 1/4 финала -->
				<div class="bracket-item connector-line">
					<h3 class="round-title">1/4 финала</h3>
				</div>
				
				<?php for ( $i = 0; $i < 4; $i++ ) :
					$match = $rounds['1/4']['matches'][ $i ] ?? null;
				?>
					<div class="bracket-item bracket-quarter-<?php echo $i + 1; ?>">
						<?php if ( $match ) :
						$render_bracket_match_card( $match );
						else : ?>
							<div class="empty-card">
								<span class="tbd-text">Предстоящий матч</span>
								<span class="tbd-label">1/4 финала</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
				
				<!-- 1/8 финала -->
				<div class="bracket-item connector-line">
					<h3 class="round-title">1/8 финала</h3>
				</div>
				
				<?php for ( $i = 0; $i < 8; $i++ ) :
					$match = $rounds['1/8']['matches'][ $i ] ?? null;
				?>
					<div class="bracket-item bracket-eighth-<?php echo $i + 1; ?>">
						<?php if ( $match ) :
						$render_bracket_match_card( $match );
						else : ?>
							<div class="empty-card">
								<span class="tbd-text">Предстоящий матч</span>
								<span class="tbd-label">1/8 финала</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
				
				<!-- 1/16 финала -->
				<div class="bracket-item connector-line">
					<h3 class="round-title">1/16 финала</h3>
				</div>
				
				<?php for ( $i = 0; $i < 16; $i++ ) :
					$match = $rounds['1/16']['matches'][ $i ] ?? null;
				?>
					<div class="bracket-item bracket-sixteenth-<?php echo $i + 1; ?>">
						<?php if ( $match ) :
						$render_bracket_match_card( $match );
						else : ?>
							<div class="empty-card">
								<span class="tbd-text">Предстоящий матч</span>
								<span class="tbd-label">1/16 финала</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
			</div>
		<?php } ?>
		
	</div><!-- .container -->
	
</main><!-- #main -->

<?php
get_footer();
?>
