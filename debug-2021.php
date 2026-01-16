<?php
/**
 * Отладка 2021 года
 */

require 'wp-load.php';

global $wpdb;

// Получаем все доступные сезоны с датами
$all_seasons = $wpdb->get_results(
    "SELECT season_id, season_name, start_date, end_date FROM {$wpdb->prefix}arsenal_seasons ORDER BY start_date DESC"
);

echo "Все сезоны в таблице wp_arsenal_seasons:\n";
foreach ($all_seasons as $season) {
    echo "  - {$season->season_name} (ID: {$season->season_id}, Start: {$season->start_date}, End: {$season->end_date})\n";
}

echo "\n\n";

// Теперь проверим по году 2021
$seasons_2021 = $wpdb->get_results(
    "SELECT s.season_id, s.season_name, s.start_date, COUNT(m.match_id) as match_count
     FROM {$wpdb->prefix}arsenal_seasons s
     LEFT JOIN {$wpdb->prefix}arsenal_matches m ON m.season_id = s.season_id AND m.tournament_id = '71CFDAA6'
     WHERE YEAR(s.start_date) = 2021 OR YEAR(s.end_date) = 2021
     GROUP BY s.season_id, s.season_name, s.start_date"
);

echo "Сезоны за 2021 год:\n";
foreach ($seasons_2021 as $season) {
    echo "  - {$season->season_name} (ID: {$season->season_id})\n";
    echo "    Start Date: {$season->start_date}\n";
    echo "    Matches (tournament 71CFDAA6): {$season->match_count}\n";
}

// Проверим матчи за season_id 82537CE7 (2021)
echo "\n\nМатчи для season_id 82537CE7 (2021):\n";
$matches_2021 = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT m.season_id, m.tournament_id, COUNT(*) as count, COUNT(DISTINCT m.home_team_id) as home_teams, COUNT(DISTINCT m.away_team_id) as away_teams
         FROM {$wpdb->prefix}arsenal_matches m
         WHERE m.season_id = %s
         GROUP BY m.season_id, m.tournament_id",
        '82537CE7'
    )
);

foreach ($matches_2021 as $row) {
    echo "  Season: {$row->season_id}, Tournament: {$row->tournament_id}, Total: {$row->count}, Home Teams: {$row->home_teams}, Away Teams: {$row->away_teams}\n";
}

// Проверим команды
echo "\n\nКоманды для season_id 82537CE7 и tournament 71CFDAA6:\n";
$teams_2021 = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DISTINCT t.id, t.name, t.team_id, COUNT(m.match_id) as match_count
         FROM {$wpdb->prefix}arsenal_teams t
         LEFT JOIN {$wpdb->prefix}arsenal_matches m ON (m.home_team_id = t.team_id OR m.away_team_id = t.team_id)
         WHERE m.season_id = %s AND m.tournament_id = %s
         GROUP BY t.id, t.name, t.team_id",
        '82537CE7',
        '71CFDAA6'
    )
);

echo "Найдено команд: " . count($teams_2021) . "\n";
foreach ($teams_2021 as $team) {
    echo "  - {$team->name} (team_id: {$team->team_id}, matches: {$team->match_count})\n";
}
