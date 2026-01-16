<?php
/**
 * Отладочный вывод на странице игрока
 * Добавить код в page-player.php после строки 117 (после применения коррекций)
 */

// ВРЕМЕННЫЙ ОТЛАДОЧНЫЙ КОД
// Добавьте это прямо после строки 117 в page-player.php:
?>

<?php
// DEBUG: Выводим информацию о коррекциях непосредственно перед отображением
if ( isset( $_GET['debug'] ) && $_GET['debug'] === '1' ) {
	echo '<div style="background: #f0f0f0; padding: 15px; margin: 20px; border: 2px solid red; font-family: monospace; font-size: 12px;">';
	echo '<h3 style="color: red; margin-top: 0;">🔴 DEBUG INFO (удалить после проверки)</h3>';
	
	echo '<p><strong>Selected Year:</strong> ' . $selected_year . '</p>';
	echo '<p><strong>Tournament ID:</strong> ' . $selected_tournament_id . '</p>';
	
	echo '<p><strong>Stats Object before corrections:</strong></p>';
	$stats_before = arsenal_get_player_stats( $player_id, $selected_tournament_id, $selected_year );
	echo '<pre>';
	echo "  minutes_played: " . intval( $stats_before->minutes_played ) . "\n";
	echo "  matches_played: " . intval( $stats_before->matches_played ) . "\n";
	echo "  goals: " . intval( $stats_before->goals ) . "\n";
	echo "  assists: " . intval( $stats_before->assists ) . "\n";
	echo "  yellow_cards: " . intval( $stats_before->yellow_cards ) . "\n";
	echo "  red_cards: " . intval( $stats_before->red_cards ) . "\n";
	echo '</pre>';
	
	echo '<p><strong>Corrections applied (from $selected_season_stats):</strong></p>';
	echo '<pre>';
	echo "  minutes_played: " . intval( $selected_season_stats->minutes_played ) . "\n";
	echo "  matches_played: " . intval( $selected_season_stats->matches_played ) . "\n";
	echo "  goals: " . intval( $selected_season_stats->goals ) . "\n";
	echo "  assists: " . intval( $selected_season_stats->assists ) . "\n";
	echo "  yellow_cards: " . intval( $selected_season_stats->yellow_cards ) . "\n";
	echo "  red_cards: " . intval( $selected_season_stats->red_cards ) . "\n";
	echo '</pre>';
	
	// Проверим напрямую коррекции в БД
	global $wpdb;
	$corrections = $wpdb->get_results( $wpdb->prepare(
		"SELECT 
			c.*,
			YEAR(COALESCE(s.start_date, '2000-01-01')) as correction_year
		 FROM {$wpdb->prefix}arsenal_player_stats_corrections c
		 LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON c.season_id = s.season_id
		 WHERE c.player_id = %s 
		 AND c.tournament_id = %s 
		 AND c.is_applied = 1",
		$player_id,
		$selected_tournament_id
	) );
	
	echo '<p><strong>Corrections in DB for this player + tournament:</strong></p>';
	if ( ! empty( $corrections ) ) {
		foreach ( $corrections as $corr ) {
			echo "Correction: correction_year=" . $corr->correction_year . ", year=$selected_year, MATCH=" . ( $corr->correction_year == $selected_year ? 'YES' : 'NO' ) . "\n";
			echo "  minutes_played_delta: " . intval( $corr->minutes_played_delta ) . "\n";
			echo "  matches_played_delta: " . intval( $corr->matches_played_delta ) . "\n";
			echo "  goals_delta: " . intval( $corr->goals_delta ) . "\n";
			echo "  assists_delta: " . intval( $corr->assists_delta ) . "\n";
			echo "  yellow_cards_delta: " . intval( $corr->yellow_cards_delta ) . "\n";
			echo "  red_cards_delta: " . intval( $corr->red_cards_delta ) . "\n";
			echo "\n";
		}
	} else {
		echo "NO CORRECTIONS FOUND\n";
	}
	
	echo '</div>';
}
?>
