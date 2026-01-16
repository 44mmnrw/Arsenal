<?php
/**
 * Проверка tournament_id для основной лиги в БД
 */

require 'wp-load.php';

global $wpdb;

// Получаем все уникальные турниры
$tournaments = $wpdb->get_results(
    "SELECT DISTINCT tournament_id, COUNT(*) as match_count
     FROM {$wpdb->prefix}arsenal_matches
     GROUP BY tournament_id
     ORDER BY match_count DESC"
);

echo "Турниры в базе данных (по количеству матчей):\n";
foreach ($tournaments as $t) {
    echo "  - {$t->tournament_id}: {$t->match_count} матчей\n";
}

// Попробуем получить информацию о турнирах
echo "\n\nИнформация о турнирах:\n";
$tournament_info = $wpdb->get_results(
    "SELECT tournament_id, tournament_name FROM {$wpdb->prefix}arsenal_tournaments"
);

if (!empty($tournament_info)) {
    foreach ($tournament_info as $t) {
        echo "  - {$t->tournament_id}: {$t->tournament_name}\n";
    }
} else {
    echo "Таблица tournaments пуста или не существует\n";
}

// Проверим table_exists
echo "\n\nПроверка существования таблиц:\n";
$tables = $wpdb->get_results(
    "SHOW TABLES FROM " . DB_NAME . " LIKE '{$wpdb->prefix}arsenal_%'"
);

foreach ($tables as $row) {
    $table_name = array_values((array)$row)[0];
    echo "  - $table_name\n";
}
