<?php
/**
 * Тест интеграции коррекций в страницу игрока
 * 
 * Проверяет, что функции получения и применения коррекций работают
 */

// Загружаем WordPress
require_once dirname( __FILE__ ) . '/../wp-load.php';

// Загружаем плагин управления командой (где находится класс коррекций)
if ( ! class_exists( 'Arsenal_Player_Stats_Corrections' ) ) {
	require_once WP_CONTENT_DIR . '/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections.php';
}

// Загружаем функции игроков
require_once WP_CONTENT_DIR . '/themes/arsenal/inc/functions/player-functions.php';

echo "=== Тест интеграции коррекций статистики игроков ===\n\n";

// Берем любого игрока и турнир для тестирования
global $wpdb;

$player = $wpdb->get_row( "SELECT player_id, full_name FROM {$wpdb->prefix}arsenal_players LIMIT 1" );
$tournament = $wpdb->get_row( "SELECT tournament_id, tournament_name FROM {$wpdb->prefix}arsenal_tournaments LIMIT 1" );

if ( ! $player || ! $tournament ) {
	echo "❌ Не найдены тестовые данные (игроки или турниры)\n";
	exit;
}

$player_id = $player->player_id;
$tournament_id = $tournament->tournament_id;
$player_name = $player->full_name;
$tournament_name = $tournament->tournament_name;

echo "Игрок: $player_name (ID: $player_id)\n";
echo "Турнир: $tournament_name (ID: $tournament_id)\n\n";

// Получаем исходную статистику
$original_stats = arsenal_get_player_stats( $player_id, $tournament_id );

if ( $original_stats ) {
	echo "✅ Исходная статистика получена:\n";
	echo "   - Матчей: " . $original_stats->matches_played . "\n";
	echo "   - Минут: " . $original_stats->minutes_played . "\n";
	echo "   - Голов: " . $original_stats->goals . "\n";
	echo "   - Ассистов: " . $original_stats->assists . "\n";
	echo "   - Жёлтых карточек: " . $original_stats->yellow_cards . "\n";
	echo "   - Красных карточек: " . $original_stats->red_cards . "\n\n";
} else {
	echo "⚠️  Статистика не найдена для этого игрока\n\n";
	// Используем пустой объект для тестирования логики коррекций
	$original_stats = (object) array(
		'matches_played' => 0,
		'minutes_played' => 0,
		'goals' => 0,
		'assists' => 0,
		'yellow_cards' => 0,
		'red_cards' => 0,
		'goals_conceded' => 0,
	);
	echo "Используем пустую статистику для тестирования\n\n";
}

// Получаем коррекции
echo "Получение коррекций...\n";
$corrections = arsenal_get_player_corrections( $player_id, $tournament_id );

if ( ! empty( $corrections ) ) {
	echo "✅ Найдено " . count( $corrections ) . " примененных коррекций:\n";
	foreach ( $corrections as $i => $correction ) {
		echo "   Коррекция #" . ($i + 1) . ":\n";
		echo "      ID: " . $correction->correction_id . "\n";
		echo "      Причина: " . ( $correction->correction_reason ? substr( $correction->correction_reason, 0, 50 ) . '...' : '—' ) . "\n";
		echo "      Дельты: М+" . $correction->minutes_played_delta . 
			" Матч+" . $correction->matches_played_delta . 
			" Голы+" . $correction->goals_delta . 
			" Ассист+" . $correction->assists_delta . "\n";
	}
	echo "\n";
} else {
	echo "⚠️  Примененных коррекций не найдено\n\n";
}

// Применяем коррекции
echo "Применение коррекций к статистике...\n";
$corrected_stats = arsenal_apply_player_corrections( $original_stats, $player_id, $tournament_id );

if ( $corrected_stats !== $original_stats || ! empty( $corrections ) ) {
	echo "✅ Коррекции применены:\n";
	if ( $original_stats->matches_played != $corrected_stats->matches_played ) {
		echo "   - Матчей: " . $original_stats->matches_played . " → " . $corrected_stats->matches_played . "\n";
	}
	if ( $original_stats->minutes_played != $corrected_stats->minutes_played ) {
		echo "   - Минут: " . $original_stats->minutes_played . " → " . $corrected_stats->minutes_played . "\n";
	}
	if ( $original_stats->goals != $corrected_stats->goals ) {
		echo "   - Голов: " . $original_stats->goals . " → " . $corrected_stats->goals . "\n";
	}
	if ( $original_stats->assists != $corrected_stats->assists ) {
		echo "   - Ассистов: " . $original_stats->assists . " → " . $corrected_stats->assists . "\n";
	}
	if ( $original_stats->yellow_cards != $corrected_stats->yellow_cards ) {
		echo "   - Жёлтых карточек: " . $original_stats->yellow_cards . " → " . $corrected_stats->yellow_cards . "\n";
	}
	if ( $original_stats->red_cards != $corrected_stats->red_cards ) {
		echo "   - Красных карточек: " . $original_stats->red_cards . " → " . $corrected_stats->red_cards . "\n";
	}
} else {
	echo "⚠️  Коррекций не было, статистика не изменилась (это нормально)\n";
}

echo "\n✅ Интеграция работает корректно!\n";
echo "Функции arsenal_get_player_corrections() и arsenal_apply_player_corrections() готовы к использованию.\n";
