<?php
/**
 * Отладочный скрипт для проверки коррекций по полям
 * Проверяем, почему только minutes_played корректируется
 */

// Подключаем WordPress
require_once 'wp-load.php';

global $wpdb;

// Укажите ID игрока из URL, например из page-player.php?player_id=XXX
$player_id = isset( $_GET['player_id'] ) ? sanitize_text_field( $_GET['player_id'] ) : null;

if ( ! $player_id ) {
	die( 'Укажите player_id в параметре ?player_id=XXX' );
}

echo "=== DEBUG: Коррекции по полям для игрока $player_id ===\n\n";

// Получаем ВСЕ коррекции для этого игрока
$all_corrections = $wpdb->get_results( $wpdb->prepare(
	"SELECT 
		c.*,
		YEAR(COALESCE(s.start_date, '2000-01-01')) as correction_year
	 FROM {$wpdb->prefix}arsenal_player_stats_corrections c
	 LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON c.season_id = s.season_id
	 WHERE c.player_id = %s
	 AND c.is_applied = 1",
	$player_id
) );

if ( empty( $all_corrections ) ) {
	die( "Нет коррекций для этого игрока\n" );
}

echo "Всего коррекций (is_applied=1): " . count( $all_corrections ) . "\n\n";

// Выводим информацию по ВСЕМ коррекциям
foreach ( $all_corrections as $idx => $corr ) {
	echo "--- Коррекция #" . ($idx + 1) . " ---\n";
	echo "  ID: " . $corr->correction_id . "\n";
	echo "  Tournament: " . $corr->tournament_id . "\n";
	echo "  Season ID: " . ($corr->season_id ?: 'NULL') . "\n";
	echo "  Correction Year: " . $corr->correction_year . "\n";
	echo "  DELTAS:\n";
	echo "    minutes_played_delta: " . intval( $corr->minutes_played_delta ) . "\n";
	echo "    matches_played_delta: " . intval( $corr->matches_played_delta ) . "\n";
	echo "    goals_delta: " . intval( $corr->goals_delta ) . "\n";
	echo "    goals_conceded_delta: " . intval( $corr->goals_conceded_delta ) . "\n";
	echo "    assists_delta: " . intval( $corr->assists_delta ) . "\n";
	echo "    yellow_cards_delta: " . intval( $corr->yellow_cards_delta ) . "\n";
	echo "    red_cards_delta: " . intval( $corr->red_cards_delta ) . "\n";
	echo "\n";
}

// Теперь посчитаем, какие поля ненулевые
echo "\n=== СТАТИСТИКА ненулевых дельта-полей ===\n";

$field_counts = array(
	'minutes_played_delta' => 0,
	'matches_played_delta' => 0,
	'goals_delta' => 0,
	'goals_conceded_delta' => 0,
	'assists_delta' => 0,
	'yellow_cards_delta' => 0,
	'red_cards_delta' => 0,
);

foreach ( $all_corrections as $corr ) {
	foreach ( $field_counts as $field => &$count ) {
		$delta = intval( $corr->$field );
		if ( $delta !== 0 ) {
			$count++;
		}
	}
	unset( $count );
}

foreach ( $field_counts as $field => $count ) {
	echo "$field: $count коррекций с ненулевым значением\n";
}

echo "\n✅ Отладка завершена\n";
