<?php
/**
 * Полный тест функции arsenal_apply_player_corrections()
 */

require_once('wp-load.php');
require_once( get_template_directory() . '/inc/functions/player-functions.php' );

echo "=== ПОЛНЫЙ ТЕСТ ФУНКЦИИ arsenal_apply_player_corrections() ===\n\n";

$player_id = '1A6AC182';
$tournament_id = '71CFDAA6';
$year = 2024;

// ШАГ 1: Получаем статистику БЕЗ коррекций
echo "ШАГ 1: Получаем статистику БЕЗ коррекций\n";
$stats_raw = arsenal_get_player_stats( $player_id, $tournament_id, $year );

echo "Статистика ДО применения коррекций:\n";
if ( $stats_raw ) {
	echo "  - minutes_played: " . $stats_raw->minutes_played . "\n";
	echo "  - matches_played: " . $stats_raw->matches_played . "\n";
	echo "  - goals: " . $stats_raw->goals . "\n";
	echo "  - assists: " . $stats_raw->assists . "\n";
	echo "  - yellow_cards: " . $stats_raw->yellow_cards . "\n";
	echo "  - red_cards: " . $stats_raw->red_cards . "\n";
} else {
	echo "  ❌ Статистика не найдена!\n";
	exit;
}
echo "\n";

// ШАГ 2: Применяем коррекции
echo "ШАГ 2: Применяем коррекции через arsenal_apply_player_corrections()\n";
$stats_corrected = arsenal_apply_player_corrections( $stats_raw, $player_id, $tournament_id, $year );

echo "Статистика ПОСЛЕ применения коррекций:\n";
echo "  - minutes_played: " . $stats_corrected->minutes_played . "\n";
echo "  - matches_played: " . $stats_corrected->matches_played . "\n";
echo "  - goals: " . $stats_corrected->goals . "\n";
echo "  - assists: " . $stats_corrected->assists . "\n";
echo "  - yellow_cards: " . $stats_corrected->yellow_cards . "\n";
echo "  - red_cards: " . $stats_corrected->red_cards . "\n";
echo "\n";

// ШАГ 3: Проверяем изменения
echo "ШАГ 3: Проверяем, какие поля изменились\n";
$changes = array();
if ( $stats_raw->minutes_played !== $stats_corrected->minutes_played ) {
	$changes[] = "minutes_played: " . $stats_raw->minutes_played . " → " . $stats_corrected->minutes_played;
}
if ( $stats_raw->matches_played !== $stats_corrected->matches_played ) {
	$changes[] = "matches_played: " . $stats_raw->matches_played . " → " . $stats_corrected->matches_played;
}
if ( $stats_raw->goals !== $stats_corrected->goals ) {
	$changes[] = "goals: " . $stats_raw->goals . " → " . $stats_corrected->goals;
}
if ( $stats_raw->assists !== $stats_corrected->assists ) {
	$changes[] = "assists: " . $stats_raw->assists . " → " . $stats_corrected->assists;
}
if ( $stats_raw->yellow_cards !== $stats_corrected->yellow_cards ) {
	$changes[] = "yellow_cards: " . $stats_raw->yellow_cards . " → " . $stats_corrected->yellow_cards;
}
if ( $stats_raw->red_cards !== $stats_corrected->red_cards ) {
	$changes[] = "red_cards: " . $stats_raw->red_cards . " → " . $stats_corrected->red_cards;
}

if ( empty( $changes ) ) {
	echo "❌ БЕЗ ИЗМЕНЕНИЙ! Коррекции не применились!\n";
} else {
	echo "✅ Применились следующие изменения:\n";
	foreach ( $changes as $change ) {
		echo "   - $change\n";
	}
}

echo "\n=== КОНЕЦ ТЕСТА ===\n";
