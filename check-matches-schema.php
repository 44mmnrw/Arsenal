<?php
require 'wp-load.php';

global $wpdb;

// Получить структуру таблицы
echo "=== Структура таблицы wp_arsenal_matches ===\n\n";
$columns = $wpdb->get_results("DESC wp_arsenal_matches");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

// Получить несколько примеров матчей
echo "\n=== Пример данных (первые 5 матчей) ===\n\n";
$matches = $wpdb->get_results("SELECT * FROM wp_arsenal_matches LIMIT 5");
foreach ($matches as $match) {
    echo "ID: " . $match->match_id . "\n";
    echo "  match_date: " . $match->match_date . "\n";
    if (isset($match->match_time)) {
        echo "  match_time: " . $match->match_time . "\n";
    }
    // Проверим все поля
    foreach ((array)$match as $key => $val) {
        if (strpos(strtolower($key), 'time') !== false) {
            echo "  [TIME FIELD] $key: $val\n";
        }
    }
    echo "\n";
}
?>
