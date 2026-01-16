<?php
/**
 * Тест применения коррекций к полосе статистики
 * Проверяет, что коррекции применяются ТОЛЬКО для выбранного года
 */

require_once('wp-load.php');
require_once( get_template_directory() . '/inc/functions/player-functions.php' );

echo "=== ТЕСТ ПОЛОСЫ СТАТИСТИКИ С ФИЛЬТРАЦИЕЙ ПО ГОДУ ===\n\n";

$player_id = '1A6AC182';
$tournament_id = '71CFDAA6';

echo "Игрок ID: $player_id\n";
echo "Турнир ID: $tournament_id\n\n";

// Тест 1: Получить данные для года 2024
echo "--- ТЕСТ 1: Год 2024 ---\n";
$player_data_2024 = arsenal_get_player_full_data( $player_id, $tournament_id, 2024 );

$selected_year = $player_data_2024['selected_year'];
$stats_raw = $player_data_2024['stats'];

echo "Выбранный год: $selected_year\n";
echo "Статистика БЕЗ коррекций:\n";
echo "  - goals: " . $stats_raw->goals . "\n";
echo "  - assists: " . $stats_raw->assists . "\n";
echo "  - matches_played: " . $stats_raw->matches_played . "\n\n";

// Применяем коррекции
$stats_corrected = arsenal_apply_player_corrections( $stats_raw, $player_id, $tournament_id, $selected_year );

echo "Статистика ПОСЛЕ коррекций для года $selected_year:\n";
echo "  - goals: " . $stats_corrected->goals . "\n";
echo "  - assists: " . $stats_corrected->assists . "\n";
echo "  - matches_played: " . $stats_corrected->matches_played . "\n\n";

if ( $stats_raw->goals !== $stats_corrected->goals ) {
	echo "✅ КОРРЕКЦИИ ПРИМЕНИЛИСЬ ДЛЯ ГОДА 2024\n\n";
} else {
	echo "❌ КОРРЕКЦИИ НЕ ПРИМЕНИЛИСЬ ДЛЯ ГОДА 2024\n\n";
}

// Тест 2: Получить данные для года 2025 (там не должно быть коррекций)
echo "--- ТЕСТ 2: Год 2025 ---\n";
$player_data_2025 = arsenal_get_player_full_data( $player_id, $tournament_id, 2025 );

$selected_year_2025 = $player_data_2025['selected_year'];
$stats_raw_2025 = $player_data_2025['stats'];

echo "Выбранный год: $selected_year_2025\n";
echo "Статистика БЕЗ коррекций:\n";
echo "  - goals: " . $stats_raw_2025->goals . "\n";
echo "  - assists: " . $stats_raw_2025->assists . "\n";
echo "  - matches_played: " . $stats_raw_2025->matches_played . "\n\n";

// Применяем коррекции
$stats_corrected_2025 = arsenal_apply_player_corrections( $stats_raw_2025, $player_id, $tournament_id, $selected_year_2025 );

echo "Статистика ПОСЛЕ коррекций для года $selected_year_2025:\n";
echo "  - goals: " . $stats_corrected_2025->goals . "\n";
echo "  - assists: " . $stats_corrected_2025->assists . "\n";
echo "  - matches_played: " . $stats_corrected_2025->matches_played . "\n\n";

if ( $stats_raw_2025->goals === $stats_corrected_2025->goals ) {
	echo "✅ КОРРЕКЦИИ ПРАВИЛЬНО НЕ ПРИМЕНИЛИСЬ ДЛЯ ГОДА 2025 (где их нет)\n\n";
} else {
	echo "❌ ВНИМАНИЕ: Коррекции НЕПРАВИЛЬНО применились для года 2025!\n\n";
}

// Тест 3: Проверяем тот же подход для таблицы по годам
echo "--- ТЕСТ 3: Таблица по годам ---\n";
$years_stats_raw = arsenal_get_tournament_yearly_stats( $player_id, $tournament_id );
$years_stats_corrected = arsenal_apply_player_corrections_to_yearly_stats( $years_stats_raw, $player_id, $tournament_id );

echo "Проверка, что коррекции применены только к нужному году в таблице:\n";
$any_corrections = false;
foreach ( $years_stats_corrected as $i => $corrected_stat ) {
	$raw_stat = $years_stats_raw[$i];
	if ( $raw_stat->goals !== $corrected_stat->goals ) {
		echo "  ✅ Год " . $corrected_stat->year . ": коррекции применены (goals: " . $raw_stat->goals . " → " . $corrected_stat->goals . ")\n";
		$any_corrections = true;
	}
}

if ( ! $any_corrections ) {
	echo "  ℹ️  Коррекции не найдены в таблице\n";
}

echo "\n=== КОНЕЦ ТЕСТА ===\n";
