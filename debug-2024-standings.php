<?php
/**
 * Отладка страницы турнирной таблицы за 2024 год
 */

require 'wp-load.php';

global $wpdb;

$year = 2024;
$tournament_id = '71CFDAA6';

// Получаем season_id
$season_id = $wpdb->get_var( $wpdb->prepare(
    "SELECT season_id FROM {$wpdb->prefix}arsenal_matches WHERE YEAR(match_date) = %d AND tournament_id = %s LIMIT 1",
    $year,
    $tournament_id
) );

echo "Год: $year\n";
echo "Tournament ID: $tournament_id\n";
echo "Season ID: $season_id\n\n";

// Получаем команды
$teams = $wpdb->get_results( $wpdb->prepare(
    "SELECT DISTINCT t.id, t.name, t.logo_url, t.team_id
     FROM {$wpdb->prefix}arsenal_teams t
     INNER JOIN {$wpdb->prefix}arsenal_matches m ON (m.home_team_id = t.team_id OR m.away_team_id = t.team_id)
     WHERE m.season_id = %s AND m.tournament_id = %s
     ORDER BY t.name",
    $season_id,
    $tournament_id
) );

echo "Найдено команд: " . count($teams) . "\n";
foreach ($teams as $team) {
    echo "  - {$team->name} (team_id: {$team->team_id})\n";
}

// Получаем матчи
$matches = $wpdb->get_results( $wpdb->prepare(
    "SELECT m.home_team_id, m.away_team_id, m.home_score, m.away_score
     FROM {$wpdb->prefix}arsenal_matches m
     WHERE m.season_id = %s 
     AND m.tournament_id = %s
     AND m.status = '0083CE05' 
     AND m.home_score IS NOT NULL 
     AND m.away_score IS NOT NULL
     ORDER BY m.match_date ASC",
    $season_id,
    $tournament_id
) );

echo "\nНайдено матчей: " . count($matches) . "\n";
if (count($matches) > 0) {
    echo "Первые 5 матчей:\n";
    for ($i = 0; $i < min(5, count($matches)); $i++) {
        echo "  Match $i: {$matches[$i]->home_team_id} {$matches[$i]->home_score}:{$matches[$i]->away_score} {$matches[$i]->away_team_id}\n";
    }
}
