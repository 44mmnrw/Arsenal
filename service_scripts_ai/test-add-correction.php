<?php
/**
 * Тест функции добавления корректировки
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Загружаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

// Подключаем класс корректировок
require_once ABSPATH . 'wp-content/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections.php';

$manager = Arsenal_Player_Stats_Corrections::get_instance();

echo "\n=== ТЕСТ ДОБАВЛЕНИЯ КОРРЕКТИРОВКИ ===\n\n";

// Берем первого игрока и первый турнир
$player_id = 'E7167B1F';  // Абдихоликов Бобир
$tournament_id = '71CFDAA6';  // Чемпионат Беларуси

$deltas = array(
    'minutes_played' => 10,
    'matches_played' => 1,
    'goals' => 1,
    'assists' => 0,
    'yellow_cards' => 0,
    'red_cards' => 0,
);

$reason = 'Тест корректировки - 10 минут за матч';

echo "Параметры:\n";
echo "  Player ID: " . $player_id . "\n";
echo "  Tournament ID: " . $tournament_id . "\n";
echo "  Deltas: " . json_encode( $deltas ) . "\n";
echo "  Reason: " . $reason . "\n\n";

try {
    echo "Добавляем корректировку...\n";
    $correction_id = $manager->add_correction(
        $player_id,
        $tournament_id,
        $deltas,
        $reason,
        null
    );
    
    if ( $correction_id ) {
        echo "✓ SUCCESS! Correction ID: " . $correction_id . "\n\n";
        
        // Проверяем, что она создалась в БД
        $check = $manager->get_correction( $correction_id );
        if ( $check ) {
            echo "Проверка - корректировка найдена в БД:\n";
            echo "  ID: " . $check->id . "\n";
            echo "  Correction ID: " . $check->correction_id . "\n";
            echo "  Player: " . $check->player_id . "\n";
            echo "  Tournament: " . $check->tournament_id . "\n";
            echo "  Goals Δ: " . $check->goals_delta . "\n";
            echo "  Created: " . $check->created_at . "\n";
        } else {
            echo "❌ Ошибка: корректировка не найдена в БД!\n";
        }
    } else {
        echo "❌ FAILURE! add_correction вернул false\n";
    }
} catch ( Exception $e ) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== КОНЕЦ ТЕСТА ===\n\n";
