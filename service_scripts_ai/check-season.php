<?php
require_once('wp-load.php');
global $wpdb;

$season = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}arsenal_seasons WHERE season_id = '5100648E' LIMIT 1");

if ($season) {
    echo "Сезон найден:\n";
    echo "  season_id: " . $season->season_id . "\n";
    echo "  tournament_id: " . $season->tournament_id . "\n";
    echo "  season_name: " . $season->season_name . "\n";
    echo "  start_date: " . $season->start_date . "\n";
    echo "  end_date: " . $season->end_date . "\n";
} else {
    echo "Сезон не найден с ID 5100648E\n";
    echo "Проверим все сезоны:\n";
    $seasons = $wpdb->get_results("SELECT season_id, season_name, start_date FROM {$wpdb->prefix}arsenal_seasons LIMIT 10");
    foreach ($seasons as $s) {
        $year = date('Y', strtotime($s->start_date));
        echo "  - " . $s->season_id . " (name: " . $s->season_name . ", year: $year)\n";
    }
}
