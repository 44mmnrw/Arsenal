<?php
/**
 * Прямой тест функции arsenal_apply_player_corrections()
 * 
 * Скопируйте строку ниже в браузер:
 * http://arsenal.test/debug-apply-corrections-direct.php?player_id=1A6AC182&tournament_id=71CFDAA6&year=2025
 */

require_once 'wp-load.php';

$player_id = isset( $_GET['player_id'] ) ? sanitize_text_field( $_GET['player_id'] ) : null;
$tournament_id = isset( $_GET['tournament_id'] ) ? sanitize_text_field( $_GET['tournament_id'] ) : null;
$year = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : intval( date( 'Y' ) );

if ( ! $player_id || ! $tournament_id ) {
	die( 'Укажите player_id и tournament_id в параметрах URL' );
}

echo "<h2>🔍 Прямой тест arsenal_apply_player_corrections()</h2>";
echo "<p><strong>Player:</strong> $player_id</p>";
echo "<p><strong>Tournament:</strong> $tournament_id</p>";
echo "<p><strong>Year:</strong> $year</p>";

// Получаем статистику БЕЗ коррекций
require_once get_template_directory() . '/inc/functions/player-functions.php';

$stats_without_corrections = arsenal_get_player_stats( $player_id, $tournament_id, $year );

echo "<h3>BEFORE (without corrections):</h3>";
echo "<pre>";
echo "minutes_played: " . intval( $stats_without_corrections->minutes_played ) . "\n";
echo "matches_played: " . intval( $stats_without_corrections->matches_played ) . "\n";
echo "goals: " . intval( $stats_without_corrections->goals ) . "\n";
echo "assists: " . intval( $stats_without_corrections->assists ) . "\n";
echo "yellow_cards: " . intval( $stats_without_corrections->yellow_cards ) . "\n";
echo "red_cards: " . intval( $stats_without_corrections->red_cards ) . "\n";
echo "</pre>";

// Клонируем объект
$stats_to_correct = clone $stats_without_corrections;

// Вызываем функцию коррекции
echo "<h3>Calling arsenal_apply_player_corrections()...</h3>";
$corrected_stats = arsenal_apply_player_corrections( $stats_to_correct, $player_id, $tournament_id, $year );

echo "<h3>AFTER (with corrections):</h3>";
echo "<pre>";
echo "minutes_played: " . intval( $corrected_stats->minutes_played ) . "\n";
echo "matches_played: " . intval( $corrected_stats->matches_played ) . "\n";
echo "goals: " . intval( $corrected_stats->goals ) . "\n";
echo "assists: " . intval( $corrected_stats->assists ) . "\n";
echo "yellow_cards: " . intval( $corrected_stats->yellow_cards ) . "\n";
echo "red_cards: " . intval( $corrected_stats->red_cards ) . "\n";
echo "</pre>";

// Проверим коррекции в БД
global $wpdb;
echo "<h3>Corrections in DB for this player + tournament:</h3>";
$corrections = $wpdb->get_results( $wpdb->prepare(
	"SELECT c.*, YEAR(COALESCE(s.start_date, '2000-01-01')) as correction_year
	 FROM {$wpdb->prefix}arsenal_player_stats_corrections c
	 LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON c.season_id = s.season_id
	 WHERE c.player_id = %s AND c.tournament_id = %s AND c.is_applied = 1",
	$player_id, $tournament_id
) );

if ( ! empty( $corrections ) ) {
	echo "<pre>";
	foreach ( $corrections as $corr ) {
		$year_match = ( intval( $corr->correction_year ) === $year ) ? '✓ MATCH' : '✗ NO MATCH';
		echo "Year: " . intval( $corr->correction_year ) . " (vs $year) - $year_match\n";
		echo "  minutes_delta: " . intval( $corr->minutes_played_delta ) . "\n";
		echo "  goals_delta: " . intval( $corr->goals_delta ) . "\n";
		echo "  assists_delta: " . intval( $corr->assists_delta ) . "\n";
	}
	echo "</pre>";
} else {
	echo "<p style='color: red;'><strong>NO CORRECTIONS FOUND!</strong></p>";
}

echo "<hr>";
if ( $corrected_stats->minutes_played === $stats_without_corrections->minutes_played ) {
	echo "<p style='color: red;'><strong>❌ КОРРЕКЦИИ НЕ ПРИМЕНИЛИСЬ!</strong></p>";
} else {
	echo "<p style='color: green;'><strong>✅ КОРРЕКЦИИ ПРИМЕНИЛИСЬ!</strong></p>";
}
