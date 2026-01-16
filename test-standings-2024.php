<?php
/**
 * Проверка загрузки шаблона standings за разные годы
 */

require 'wp-load.php';

// Имитируем REQUEST с параметром year
$_GET['year'] = 2024;

global $wpdb;

// Копируем логику из page-standings.php

// Получаем список доступных сезонов
$available_seasons = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT DISTINCT m.season_id, YEAR(m.match_date) as year
         FROM {$wpdb->prefix}arsenal_matches m
         WHERE m.tournament_id = %s
         GROUP BY m.season_id, YEAR(m.match_date)
         ORDER BY YEAR(m.match_date) DESC",
        '71CFDAA6'
    )
);

echo "Available seasons:\n";
foreach ($available_seasons as $s) {
    echo "  Year: {$s->year}, Season ID: {$s->season_id}\n";
}

$selected_year = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : intval( get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) ) );

echo "\nSelected year: $selected_year\n";

// Автоматически находим season_id по году из БД
$current_season_id = $wpdb->get_var( $wpdb->prepare(
    "SELECT season_id FROM {$wpdb->prefix}arsenal_matches WHERE YEAR(match_date) = %d AND tournament_id = %s LIMIT 1",
    $selected_year,
    '71CFDAA6'
) );

echo "Current season ID: $current_season_id\n";

if ( ! $current_season_id ) {
    $current_season_id = get_option( 'arsenal_active_season_id', '5B2ABC0C' );
    echo "Fallback season ID: $current_season_id\n";
}

$current_year = $selected_year;

// Получаем все команды
$query = "SELECT DISTINCT t.id, t.name, t.logo_url, t.team_id
         FROM {$wpdb->prefix}arsenal_teams t
         INNER JOIN {$wpdb->prefix}arsenal_matches m ON (m.home_team_id = t.team_id OR m.away_team_id = t.team_id)
         WHERE m.season_id = %s AND m.tournament_id = %s
         ORDER BY t.name";

$query = $wpdb->prepare( $query, $current_season_id, '71CFDAA6' );
$teams = $wpdb->get_results( $query );

echo "Teams count: " . count($teams) . "\n";
if (count($teams) > 0) {
    echo "Sample teams:\n";
    for ($i = 0; $i < min(3, count($teams)); $i++) {
        echo "  - {$teams[$i]->name} (team_id: {$teams[$i]->team_id})\n";
    }
}
