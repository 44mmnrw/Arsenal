<?php
require 'wp-load.php';

global $wpdb;

echo "=== КОМАНДЫ В БД ===\n";
$teams = $wpdb->get_results("SELECT id, team_id, name FROM wp_arsenal_teams");
foreach ($teams as $team) {
    echo "ID: {$team->id} | team_id: {$team->team_id} | name: {$team->name}\n";
}

echo "\n=== ТРЕНЕРЫ В БД ===\n";
$all_coaches = $wpdb->get_results("SELECT tc.team_id, c.coach_id, c.name, tc.start_date, tc.end_date FROM wp_arsenal_team_coaches tc LEFT JOIN wp_arsenal_coaches c ON tc.coach_id = c.coach_id ORDER BY tc.team_id, tc.start_date");
foreach ($all_coaches as $coach) {
    echo "Team ID: {$coach->team_id} | Coach: {$coach->name} | Start: {$coach->start_date} | End: {$coach->end_date}\n";
}

echo "\n=== ТРЕНЕРЫ БЕЗ ПЕРЕСЕЧЕНИЯ (ВСЕГО) ===\n";
$count = $wpdb->get_var("SELECT COUNT(*) FROM wp_arsenal_team_coaches");
echo "Записей о тренерах: " . $count . "\n";
