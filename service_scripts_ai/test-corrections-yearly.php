<?php
/**
 * Тест применения коррекций к таблице по годам
 */

require_once('wp-load.php');

// Включаем функцию для тестирования
require_once( get_template_directory() . '/inc/functions/player-functions.php' );

echo "=== ТЕСТ ПРИМЕНЕНИЯ КОРРЕКЦИЙ К ГОДОВОЙ СТАТИСТИКЕ ===\n\n";

// Тестовые данные (нужно подобрать реального игрока с коррекциями)
$player_id = '1A6AC182';  // Игрок из тестовой коррекции
$tournament_id = '71CFDAA6';  // Турнир из тестовой коррекции

echo "Игрок ID: $player_id\n";
echo "Турнир ID: $tournament_id\n\n";

// ШАГ 1: Получаем годовую статистику БЕЗ коррекций
echo "ШАГ 1: Получаем годовую статистику без коррекций\n";
$yearly_stats_raw = arsenal_get_tournament_yearly_stats( $player_id, $tournament_id );

if ( empty( $yearly_stats_raw ) ) {
	echo "❌ Статистика по годам не найдена!\n";
	exit;
}

echo "Найдено " . count( $yearly_stats_raw ) . " лет статистики:\n";
foreach ( $yearly_stats_raw as $stat ) {
	echo "  - Год " . $stat->year . ": goals=" . $stat->goals . ", assists=" . $stat->assists . ", matches_played=" . $stat->matches_played . "\n";
}
echo "\n";

// ШАГ 2: Получаем коррекции
echo "ШАГ 2: Получаем коррекции для игрока\n";
global $wpdb;
$corrections = $wpdb->get_results( $wpdb->prepare(
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

if ( empty( $corrections ) ) {
	echo "❌ Коррекции не найдены!\n";
	exit;
}

echo "Найдено " . count( $corrections ) . " коррекций:\n";
foreach ( $corrections as $corr ) {
	echo "  - ID: " . $corr->correction_id . "\n";
	echo "    season_id: " . ( $corr->season_id ? $corr->season_id : 'NULL (для всех лет)' ) . "\n";
	echo "    correction_year (extracted): " . $corr->correction_year . "\n";
	echo "    goals_delta: " . $corr->goals_delta . "\n";
	echo "    assists_delta: " . $corr->assists_delta . "\n\n";
}

// ШАГ 3: Применяем коррекции
echo "ШАГ 3: Применяем коррекции через arsenal_apply_player_corrections_to_yearly_stats()\n";
$yearly_stats_corrected = arsenal_apply_player_corrections_to_yearly_stats( $yearly_stats_raw, $player_id, $tournament_id );

echo "Статистика после коррекций:\n";
foreach ( $yearly_stats_corrected as $stat ) {
	echo "  - Год " . $stat->year . ": goals=" . $stat->goals . ", assists=" . $stat->assists . ", matches_played=" . $stat->matches_played . "\n";
}
echo "\n";

// ШАГ 4: Сравниваем
echo "ШАГ 4: Сравнение результатов\n";
$changes_found = false;
foreach ( $yearly_stats_raw as $i => $raw_stat ) {
	$corrected_stat = $yearly_stats_corrected[ $i ];
	
	if ( $raw_stat->goals !== $corrected_stat->goals ||
	     $raw_stat->assists !== $corrected_stat->assists ||
	     $raw_stat->matches_played !== $corrected_stat->matches_played ) {
		
		$changes_found = true;
		echo "✅ КОРРЕКЦИИ ПРИМЕНИЛИСЬ ДЛЯ ГОДА " . $raw_stat->year . ":\n";
		echo "   goals: " . $raw_stat->goals . " → " . $corrected_stat->goals . " (+" . ( $corrected_stat->goals - $raw_stat->goals ) . ")\n";
		echo "   assists: " . $raw_stat->assists . " → " . $corrected_stat->assists . " (+" . ( $corrected_stat->assists - $raw_stat->assists ) . ")\n";
		if ( $raw_stat->matches_played !== $corrected_stat->matches_played ) {
			echo "   matches_played: " . $raw_stat->matches_played . " → " . $corrected_stat->matches_played . " (+" . ( $corrected_stat->matches_played - $raw_stat->matches_played ) . ")\n";
		}
	} else {
		echo "ℹ️  Год " . $raw_stat->year . ": коррекций не найдено\n";
	}
}

if ( ! $changes_found ) {
	echo "\n⚠️  ВНИМАНИЕ: Коррекции НЕ применились ни к одному году!\n";
} else {
	echo "\n✅ УСПЕШНО: Коррекции правильно применились к нужным годам!\n";
}

echo "\n=== КОНЕЦ ТЕСТА ===\n";
