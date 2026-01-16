<?php
/**
 * Тест применения коррекций к текущему сезону
 */

require_once('wp-load.php');

// Включаем функцию для тестирования
require_once( get_template_directory() . '/inc/functions/player-functions.php' );

echo "=== ТЕСТ ПРИМЕНЕНИЯ КОРРЕКЦИЙ К ТЕКУЩЕМУ СЕЗОНУ ===\n\n";

// Тестовые данные
$player_id = '1A6AC182';
$tournament_id = '71CFDAA6';
$year = 2024;

echo "Игрок ID: $player_id\n";
echo "Турнир ID: $tournament_id\n";
echo "Год: $year\n\n";

// ШАГ 1: Получаем статистику БЕЗ коррекций
echo "ШАГ 1: Получаем статистику без коррекций\n";
$stats_raw = arsenal_get_player_stats( $player_id, $tournament_id, $year );

if ( ! $stats_raw ) {
	echo "❌ Статистика не найдена!\n";
	exit;
}

echo "Статистика за $year:\n";
echo "  - goals: " . $stats_raw->goals . "\n";
echo "  - assists: " . $stats_raw->assists . "\n";
echo "  - matches_played: " . $stats_raw->matches_played . "\n";
echo "  - minutes_played: " . $stats_raw->minutes_played . "\n\n";

// ШАГ 2: Применяем коррекции
echo "ШАГ 2: Применяем коррекции через arsenal_apply_player_corrections()\n";
$stats_corrected = arsenal_apply_player_corrections( $stats_raw, $player_id, $tournament_id );

echo "Статистика после коррекций:\n";
echo "  - goals: " . $stats_corrected->goals . "\n";
echo "  - assists: " . $stats_corrected->assists . "\n";
echo "  - matches_played: " . $stats_corrected->matches_played . "\n";
echo "  - minutes_played: " . $stats_corrected->minutes_played . "\n\n";

// ШАГ 3: Сравниваем
if ( $stats_raw->goals === $stats_corrected->goals &&
     $stats_raw->assists === $stats_corrected->assists &&
     $stats_raw->matches_played === $stats_corrected->matches_played ) {
	echo "ℹ️  Коррекции НЕ применились (статистика не изменилась)\n";
	echo "   Это может быть потому что коррекции без season_id НЕ применяются к текущему сезону\n";
} else {
	echo "✅ КОРРЕКЦИИ ПРИМЕНИЛИСЬ УСПЕШНО!\n";
	echo "   goals: " . $stats_raw->goals . " → " . $stats_corrected->goals . " (+" . ( $stats_corrected->goals - $stats_raw->goals ) . ")\n";
	echo "   assists: " . $stats_raw->assists . " → " . $stats_corrected->assists . " (+" . ( $stats_corrected->assists - $stats_raw->assists ) . ")\n";
}

echo "\n=== КОНЕЦ ТЕСТА ===\n";
