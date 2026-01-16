<?php
/**
 * Проверка таблицы игроков и их доступности для корректировок
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Загружаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

global $wpdb;

echo "\n=== ПРОВЕРКА ИГРОКОВ В БД ===\n\n";

// Получаем игроков
$players = $wpdb->get_results( 
    "SELECT DISTINCT player_id, full_name FROM {$wpdb->prefix}arsenal_players ORDER BY full_name LIMIT 20" 
);

echo "Всего игроков (первые 20): " . count( $players ) . "\n\n";

if ( ! empty( $players ) ) {
    foreach ( $players as $p ) {
        echo "  - " . $p->player_id . " => " . $p->full_name . "\n";
    }
} else {
    echo "❌ Игроки не найдены!\n";
}

echo "\n";

// Получаем турниры
echo "=== ТУРНИРЫ В БД ===\n\n";
$tournaments = $wpdb->get_results( 
    "SELECT DISTINCT tournament_id, name FROM {$wpdb->prefix}arsenal_tournaments ORDER BY name LIMIT 10" 
);

echo "Всего турниров (первые 10): " . count( $tournaments ) . "\n\n";

if ( ! empty( $tournaments ) ) {
    foreach ( $tournaments as $t ) {
        echo "  - " . $t->tournament_id . " => " . $t->name . "\n";
    }
} else {
    echo "❌ Турниры не найдены!\n";
}

echo "\n=== КОНЕЦ ПРОВЕРКИ ===\n\n";
