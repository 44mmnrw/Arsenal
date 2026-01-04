<?php
require 'wp-load.php';
global $wpdb;

$result = $wpdb->update(
    $wpdb->prefix . 'arsenal_standings_adjustments',
    array(
        'comment' => 'Команда снята с соревнований за неявку на матч'
    ),
    array(
        'team_id' => '8A058A2D',
        'season_id' => '5B2ABC0C'
    ),
    array('%s'),
    array('%s', '%s')
);

echo "Обновлено записей: " . $result . "\n";

// Проверяем результат
$check = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}arsenal_standings_adjustments WHERE team_id = %s",
    '8A058A2D'
));

echo "\nПроверка:\n";
echo "Team ID: " . $check->team_id . "\n";
echo "adjustment_points: " . $check->adjustment_points . "\n";
echo "comment: " . $check->comment . "\n";
