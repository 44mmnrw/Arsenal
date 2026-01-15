<?php
// Быстрая проверка стадионов на проде
define('WP_USE_THEMES', false);
require(__DIR__ . '/wp-load.php');

global $wpdb;

$count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_stadiums");

echo "Количество стадионов: " . $count . PHP_EOL;

if ($count == 0) {
    echo "ОШИБКА: Таблица стадионов ПУСТА!" . PHP_EOL;
} else {
    echo "Первые 5 стадионов:" . PHP_EOL;
    $rows = $wpdb->get_results("SELECT stadium_id, name FROM {$wpdb->prefix}arsenal_stadiums LIMIT 5");
    foreach ($rows as $row) {
        echo " - ID: {$row->stadium_id}, Имя: {$row->name}" . PHP_EOL;
    }
}
