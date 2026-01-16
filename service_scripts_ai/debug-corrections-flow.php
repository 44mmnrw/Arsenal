<?php
/**
 * Скрипт для отладки потока коррекций статистики игрока
 * 
 * Проверяет:
 * 1. Есть ли корректировки в таблице для конкретного игрока и турнира
 * 2. Какие значения у season_id и is_applied
 * 3. Как функция применяет коррекции
 */

// Инициализация WordPress
require_once( dirname( __FILE__ ) . '/../wp-load.php' );

// Получаем глобальные переменные
global $wpdb;

// Таблица коррекций
$corrections_table = $wpdb->prefix . 'arsenal_player_stats_corrections';

// Тестовый игрок и турнир (возьмем первые из таблицы)
$test_correction = $wpdb->get_row( "SELECT * FROM $corrections_table LIMIT 1" );

if ( ! $test_correction ) {
	echo "❌ НЕТ КОРРЕКЦИЙ В ТАБЛИЦЕ!\n";
	exit;
}

echo "=== ОТЛАДКА ПОТОКА КОРРЕКЦИЙ ===\n\n";

$player_id = $test_correction->player_id;
$tournament_id = $test_correction->tournament_id;
$correction_id = $test_correction->correction_id;

echo "🔍 Тестовая коррекция:\n";
echo "  - Correction ID: $correction_id\n";
echo "  - Player ID: $player_id\n";
echo "  - Tournament ID: $tournament_id\n";
echo "  - Season ID: " . ( $test_correction->season_id ?? 'NULL' ) . "\n";
echo "  - is_applied: " . $test_correction->is_applied . "\n";
echo "  - goals_delta: " . $test_correction->goals_delta . "\n\n";

// ШАГ 1: Проверяем, что таблица существует
$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$corrections_table'" );
echo "✅ Таблица существует: $table_exists\n\n";

// ШАГ 2: Проверяем класс и функцию
if ( class_exists( 'Arsenal_Player_Stats_Corrections' ) ) {
	echo "✅ Класс Arsenal_Player_Stats_Corrections загружен\n\n";
	
	$manager = Arsenal_Player_Stats_Corrections::get_instance();
	
	// Получаем коррекции используя менеджер (только примененные)
	$corrections_applied_only = $manager->get_corrections( $player_id, $tournament_id, true );
	
	// Получаем ВСЕ коррекции
	$corrections_all = $manager->get_corrections( $player_id, $tournament_id, false );
	
	echo "📊 Результаты get_corrections():\n";
	echo "  - Всего коррекций (все): " . count( $corrections_all ) . "\n";
	echo "  - Примененных (is_applied=1): " . count( $corrections_applied_only ) . "\n\n";
	
	if ( ! empty( $corrections_applied_only ) ) {
		echo "✅ КОРРЕКЦИИ НАЙДЕНЫ (is_applied=1)!\n";
		foreach ( $corrections_applied_only as $corr ) {
			echo "  - ID: " . $corr->correction_id . ", goals_delta: " . $corr->goals_delta . ", season_id: " . ( $corr->season_id ?? 'NULL' ) . "\n";
		}
	} else {
		echo "❌ НЕТ ПРИМЕНЕННЫХ КОРРЕКЦИЙ (is_applied=1)\n";
		if ( ! empty( $corrections_all ) ) {
			echo "   Но есть НЕ примененные:\n";
			foreach ( $corrections_all as $corr ) {
				echo "   - ID: " . $corr->correction_id . ", is_applied: " . $corr->is_applied . ", goals_delta: " . $corr->goals_delta . "\n";
			}
		}
	}
	echo "\n";
} else {
	echo "❌ Класс Arsenal_Player_Stats_Corrections НЕ загружен!\n";
	echo "   Проверьте, активирован ли плагин arsenal-team-manager\n\n";
}

// ШАГ 3: Проверяем функцию из player-functions.php
if ( function_exists( 'arsenal_get_player_corrections' ) ) {
	echo "✅ Функция arsenal_get_player_corrections загружена\n\n";
	
	$corrections = arsenal_get_player_corrections( $player_id, $tournament_id );
	echo "📊 Результат arsenal_get_player_corrections():\n";
	echo "  - Коррекций найдено: " . count( $corrections ) . "\n";
	
	if ( ! empty( $corrections ) ) {
		echo "  - Первая коррекция:\n";
		$first = $corrections[0];
		echo "    - correction_id: " . $first->correction_id . "\n";
		echo "    - season_id: " . ( $first->season_id ?? 'NULL' ) . "\n";
		echo "    - goals_delta: " . $first->goals_delta . "\n";
	} else {
		echo "  ❌ КОРРЕКЦИИ НЕ ВОЗВРАЩЕНЫ!\n";
	}
	echo "\n";
} else {
	echo "❌ Функция arsenal_get_player_corrections НЕ найдена!\n\n";
}

// ШАГ 4: Проверяем функцию применения коррекций
if ( function_exists( 'arsenal_get_player_stats' ) ) {
	echo "✅ Функция arsenal_get_player_stats загружена\n\n";
	
	// Получаем текущий год
	$current_year = intval( date( 'Y' ) );
	
	// Получаем статистику БЕЗ коррекций
	$stats = arsenal_get_player_stats( $player_id, $tournament_id, $current_year );
	
	echo "📊 Статистика игрока (без коррекций):\n";
	if ( $stats ) {
		echo "  - goals: " . $stats->goals . "\n";
		echo "  - assists: " . $stats->assists . "\n";
		echo "  - matches_played: " . $stats->matches_played . "\n";
	} else {
		echo "  ❌ Статистика не найдена для года $current_year\n";
	}
	echo "\n";
	
	// Применяем коррекции
	if ( function_exists( 'arsenal_apply_player_corrections' ) ) {
		$corrected_stats = arsenal_apply_player_corrections( $stats, $player_id, $tournament_id );
		
		echo "📊 Статистика игрока (с коррекциями):\n";
		echo "  - goals: " . $corrected_stats->goals . "\n";
		echo "  - assists: " . $corrected_stats->assists . "\n";
		echo "  - matches_played: " . $corrected_stats->matches_played . "\n";
		
		if ( $stats->goals === $corrected_stats->goals ) {
			echo "  ⚠️  ВНИМАНИЕ: Коррекции НЕ применились! (goals не изменился)\n";
		} else {
			echo "  ✅ Коррекции применились успешно\n";
		}
	}
	echo "\n";
}

// ШАГ 5: Прямой SQL запрос
echo "=== ПРЯМОЙ SQL ЗАПРОС К ТАБЛИЦЕ ===\n\n";

$sql = $wpdb->prepare(
	"SELECT * FROM $corrections_table WHERE player_id = %s AND tournament_id = %s AND is_applied = 1",
	$player_id,
	$tournament_id
);

echo "SQL: $sql\n\n";

$results = $wpdb->get_results( $sql );
echo "Результаты: " . count( $results ) . " коррекций найдено\n";

if ( ! empty( $results ) ) {
	foreach ( $results as $row ) {
		echo "  - " . $row->correction_id . " (season_id: " . ( $row->season_id ?? 'NULL' ) . ")\n";
	}
}

echo "\n=== КОНЕЦ ОТЛАДКИ ===\n";
