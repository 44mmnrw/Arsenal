<?php
/**
 * Template Part: Stats Bar (Dynamic from Database)
 *
 * Красная полоса со статистикой команды из базы данных.
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Получаем активный год из настроек плагина (wp_options)
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );

// Получаем ID команды Арсенал
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );

if ( ! $arsenal_team_id ) {
	$arsenal_team_id = 'EB8AA245'; // Fallback ID Арсенала
}

// Инициализация статистики
$stats = array();

if ( $arsenal_team_id ) {
	$tournament_id = '71CFDAA6'; // Беларусь Высшая лига
	$standings = arsenal_calculate_standings( $active_season_year, $tournament_id );
	
	// Находим позицию Арсенала в турнирной таблице
	$position = '—';
	$arsenal_stats = null;
	
	foreach ( $standings as $rank => $team ) {
		if ( stripos( $team['name'], 'Арсенал' ) !== false ) {
			$position = $rank + 1;
			$arsenal_stats = $team;
			break;
		}
	}
	
	// Количество игроков с активными контрактами на сегодня
	$current_date = current_time( 'Y-m-d' );
	$total_players = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(DISTINCT p.id) 
		FROM {$wpdb->prefix}arsenal_players p
		INNER JOIN {$wpdb->prefix}arsenal_team_contracts c ON p.player_id = c.player_id
		WHERE c.contract_start <= %s AND c.contract_end >= %s",
		$current_date, $current_date
	) );
	
	// Формируем массив статистики
	$total_matches = $arsenal_stats ? $arsenal_stats['played'] : 0;
	$total_goals = $arsenal_stats ? $arsenal_stats['goals_for'] : 0;
	
	$stats = array(
		array(
			'number' => $position,
			'label'  => 'Место в таблице',
		),
		array(
			'number' => $total_matches,
			'label'  => 'Матчей сыграно',
		),
		array(
			'number' => $total_goals,
			'label'  => 'Голов забито',
		),
		array(
			'number' => $total_players ?: '0',
			'label'  => 'Игроков в составе',
		),
	);
} else {
	// Fallback если команда не найдена
	$stats = array(
		array(
			'number' => '2018',
			'label'  => 'Год основания',
		),
	);
}
?>

<section class="stats-bar" aria-label="<?php esc_attr_e( 'Статистика клуба', 'arsenal' ); ?>">
	<div class="stats-bar-container">
		<?php foreach ( $stats as $stat ) : ?>
			<div class="stats-bar-item">
				<span class="stats-bar-number"><?php echo esc_html( $stat['number'] ); ?></span>
				<span class="stats-bar-label"><?php echo esc_html( $stat['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</section>
