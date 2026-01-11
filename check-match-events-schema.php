<?php
/**
 * Проверка структуры таблицы wp_arsenal_match_events
 */

require 'wp-load.php';

global $wpdb;

echo "=== Структура таблицы wp_arsenal_match_events ===\n\n";

$columns = $wpdb->get_results('DESCRIBE wp_arsenal_match_events');

foreach ($columns as $col) {
    echo "Поле: " . $col->Field . "\n";
    echo "  Тип: " . $col->Type . "\n";
    echo "  Null: " . $col->Null . "\n";
    echo "  Ключ: " . ($col->Key ?: 'нет') . "\n";
    echo "  Default: " . ($col->Default ?: 'NULL') . "\n\n";
}

echo "\n=== Пример записи ===\n";
$sample = $wpdb->get_row('SELECT * FROM wp_arsenal_match_events LIMIT 1');
if ($sample) {
    echo json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo "Таблица пуста";
}
?>
