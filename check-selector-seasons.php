<?php
/**
 * Проверка отображения селектора сезонов на странице standings
 */

require 'wp-load.php';

global $wpdb;

// Получаем список доступных сезонов из таблицы wp_arsenal_seasons для нужного турнира
$available_seasons = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DISTINCT s.season_id, s.season_name
         FROM {$wpdb->prefix}arsenal_seasons s
         INNER JOIN {$wpdb->prefix}arsenal_matches m ON m.season_id = s.season_id
         WHERE m.tournament_id = %s
         GROUP BY s.season_id, s.season_name
         ORDER BY s.start_date DESC",
        '71CFDAA6'
    )
);

echo "Доступные сезоны для селектора:\n";
foreach ($available_seasons as $season) {
    echo "  <option value=\"{$season->season_id}\">{$season->season_name}</option>\n";
}

echo "\nТотально сезонов: " . count($available_seasons) . "\n";
