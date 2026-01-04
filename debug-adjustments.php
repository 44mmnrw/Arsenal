<?php
require 'wp-load.php';
global $wpdb;

$current_season_id = '5B2ABC0C';

$adjustments_data = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT team_id, adjustment_points, comment
         FROM {$wpdb->prefix}arsenal_standings_adjustments
         WHERE season_id = %s",
        $current_season_id
    )
);

echo "=== Найдено записей: " . count($adjustments_data) . " ===\n\n";

foreach ($adjustments_data as $adjustment) {
    echo "Team ID: " . $adjustment->team_id . "\n";
    echo "adjustment_points: '" . $adjustment->adjustment_points . "'\n";
    echo "comment: '" . $adjustment->comment . "'\n";
    echo "comment empty: " . (empty($adjustment->comment) ? 'YES' : 'NO') . "\n";
    echo "comment length: " . strlen($adjustment->comment) . "\n";
    echo "---\n";
}
