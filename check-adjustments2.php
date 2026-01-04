<?php
/**
 * Детальная проверка таблицы wp_arsenal_standings_adjustments
 */

require 'wp-load.php';
global $wpdb;

echo "=== Структура таблицы ===\n";
$columns = $wpdb->get_results("DESCRIBE {$wpdb->prefix}arsenal_standings_adjustments");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== Все данные в таблице ===\n";
$adjustments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}arsenal_standings_adjustments");
if (empty($adjustments)) {
    echo "Таблица пустая!\n";
} else {
    foreach ($adjustments as $adj) {
        echo "ID: " . $adj->id . "\n";
        echo "Team ID: " . $adj->team_id . "\n";
        echo "Season ID: " . $adj->season_id . "\n";
        echo "adjustment_points: '" . $adj->adjustment_points . "'\n";
        echo "Reason: '" . $adj->reason . "'\n";
        echo "---\n";
    }
}

echo "\n=== Тест запроса как в page-standings.php ===\n";
$current_season_id = '5B2ABC0C';
$adjustments_data = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT team_id, adjustment_points, reason
         FROM {$wpdb->prefix}arsenal_standings_adjustments
         WHERE season_id = %s",
        $current_season_id
    )
);

echo "Найдено записей: " . count($adjustments_data) . "\n";
foreach ($adjustments_data as $adj) {
    echo "Team: " . $adj->team_id . ", Points: " . $adj->adjustment_points . ", Reason: " . $adj->reason . "\n";
}
