<?php
/**
 * Отладка: почему коррекции применились только к минутам
 */

require_once('wp-load.php');
global $wpdb;

$player_id = '1A6AC182';
$tournament_id = '71CFDAA6';
$year = 2024;

echo "=== ОТЛАДКА КОРРЕКЦИЙ - ПОЧЕМУ ТОЛЬКО МИНУТЫ ===\n\n";

// Получаем SQL результат как в функции
$corrections_with_years = $wpdb->get_results( $wpdb->prepare(
	"SELECT 
		c.*,
		YEAR(COALESCE(s.start_date, '2000-01-01')) as correction_year
	FROM {$wpdb->prefix}arsenal_player_stats_corrections c
	LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON c.season_id = s.season_id
	WHERE c.player_id = %s 
	AND c.tournament_id = %s 
	AND c.is_applied = 1
	ORDER BY c.created_at DESC",
	$player_id,
	$tournament_id
) );

echo "Коррекции из БД для игрока $player_id и турнира $tournament_id:\n";
echo "Всего коррекций: " . count( $corrections_with_years ) . "\n\n";

foreach ( $corrections_with_years as $i => $correction ) {
	echo "Коррекция #" . ($i+1) . ":\n";
	echo "  - ID: " . $correction->correction_id . "\n";
	echo "  - season_id: " . ( $correction->season_id ?? 'NULL' ) . "\n";
	echo "  - correction_year: " . $correction->correction_year . "\n";
	echo "  - should apply for year $year: " . ( ( !empty( $correction->season_id ) && intval( $correction->correction_year ) === $year ) || empty( $correction->season_id ) ? 'YES' : 'NO' ) . "\n";
	echo "  - Дельты:\n";
	echo "    - minutes_played_delta: " . ( $correction->minutes_played_delta ?? 0 ) . "\n";
	echo "    - matches_played_delta: " . ( $correction->matches_played_delta ?? 0 ) . "\n";
	echo "    - goals_delta: " . ( $correction->goals_delta ?? 0 ) . "\n";
	echo "    - assists_delta: " . ( $correction->assists_delta ?? 0 ) . "\n";
	echo "    - yellow_cards_delta: " . ( $correction->yellow_cards_delta ?? 0 ) . "\n";
	echo "    - red_cards_delta: " . ( $correction->red_cards_delta ?? 0 ) . "\n";
	echo "    - goals_conceded_delta: " . ( $correction->goals_conceded_delta ?? 0 ) . "\n\n";
}

// Теперь давайте мимикируем логику применения как в функции
echo "=== СИМУЛЯЦИЯ ЛОГИКИ ПРИМЕНЕНИЯ ===\n\n";

$corrections_for_year = array(
	'minutes_played_delta' => 0,
	'matches_played_delta' => 0,
	'goals_delta' => 0,
	'goals_conceded_delta' => 0,
	'assists_delta' => 0,
	'yellow_cards_delta' => 0,
	'red_cards_delta' => 0,
);

foreach ( $corrections_with_years as $correction ) {
	$correction_year = intval( $correction->correction_year );
	
	$should_apply = false;
	
	if ( ! empty( $correction->season_id ) ) {
		$should_apply = ( $correction_year === $year );
	} else {
		$should_apply = true;
	}
	
	echo "Коррекция " . $correction->correction_id . ":\n";
	echo "  - correction_year: $correction_year, should_apply: " . ( $should_apply ? 'YES' : 'NO' ) . "\n";
	
	if ( $should_apply ) {
		echo "  - Применяю дельты:\n";
		if ( isset( $correction->minutes_played_delta ) ) {
			$val = intval( $correction->minutes_played_delta );
			echo "    - minutes_played_delta: $val\n";
			$corrections_for_year['minutes_played_delta'] += $val;
		}
		if ( isset( $correction->matches_played_delta ) ) {
			$val = intval( $correction->matches_played_delta );
			echo "    - matches_played_delta: $val\n";
			$corrections_for_year['matches_played_delta'] += $val;
		}
		if ( isset( $correction->goals_delta ) ) {
			$val = intval( $correction->goals_delta );
			echo "    - goals_delta: $val\n";
			$corrections_for_year['goals_delta'] += $val;
		}
		if ( isset( $correction->goals_conceded_delta ) ) {
			$val = intval( $correction->goals_conceded_delta );
			echo "    - goals_conceded_delta: $val\n";
			$corrections_for_year['goals_conceded_delta'] += $val;
		}
		if ( isset( $correction->assists_delta ) ) {
			$val = intval( $correction->assists_delta );
			echo "    - assists_delta: $val\n";
			$corrections_for_year['assists_delta'] += $val;
		}
		if ( isset( $correction->yellow_cards_delta ) ) {
			$val = intval( $correction->yellow_cards_delta );
			echo "    - yellow_cards_delta: $val\n";
			$corrections_for_year['yellow_cards_delta'] += $val;
		}
		if ( isset( $correction->red_cards_delta ) ) {
			$val = intval( $correction->red_cards_delta );
			echo "    - red_cards_delta: $val\n";
			$corrections_for_year['red_cards_delta'] += $val;
		}
	}
	echo "\n";
}

echo "=== ИТОГОВЫЕ ДЕЛЬТЫ ДЛЯ ГОДА $year ===\n";
print_r( $corrections_for_year );
echo "\n";

echo "=== КОНЕЦ ОТЛАДКИ ===\n";
