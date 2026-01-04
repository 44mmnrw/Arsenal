<?php
/**
 * Проверка таблицы wp_arsenal_standings_adjustments
 */

require 'wp-load.php';
global $wpdb;

echo "=== Проверка таблицы wp_arsenal_standings_adjustments ===\n\n";

// Проверяем все записи
$adjustments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}arsenal_standings_adjustments");

if (empty($adjustments)) {
    echo "Таблица пустая!\n";
} else {
    foreach ($adjustments as $adj) {
        echo "Team ID: " . $adj->team_id . "\n";
        echo "Season ID: " . $adj->season_id . "\n";
        echo "Points Adjustment: " . $adj->points_adjustment . "\n";
        echo "Reason: " . $adj->reason . "\n";
        echo "---\n";
    }
}

echo "\n=== Проверка команды 8A058A2D ===\n";
$team = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}arsenal_teams WHERE team_id = %s", '8A058A2D'));
if ($team) {
    echo "Команда найдена: " . $team->name . "\n";
} else {
    echo "Команда НЕ найдена!\n";
}

echo "\n=== Используемый season_id в page-standings.php ===\n";
echo "Season ID: 5B2ABC0C\n";
