<?php
/**
 * Проверка наличия данных за 2024 год
 */

require 'wp-load.php';

global $wpdb;

// Проверяем, есть ли матчи за 2024 год с нужным tournament_id
$matches_2024 = $wpdb->get_results( $wpdb->prepare(
    "SELECT DISTINCT YEAR(match_date) as year, season_id, COUNT(*) as count
     FROM {$wpdb->prefix}arsenal_matches 
     WHERE tournament_id = %s
     GROUP BY YEAR(match_date), season_id
     ORDER BY year DESC",
    '71CFDAA6'
) );

echo "Матчи по годам для tournament_id 71CFDAA6:\n";
foreach ( $matches_2024 as $row ) {
    echo "  Год: {$row->year}, Season ID: {$row->season_id}, Матчей: {$row->count}\n";
}

echo "\n\nВсе годы в БД с матчами:\n";
$all_years = $wpdb->get_results(
    "SELECT DISTINCT YEAR(match_date) as year, COUNT(*) as count
     FROM {$wpdb->prefix}arsenal_matches
     WHERE tournament_id = '71CFDAA6'
     GROUP BY YEAR(match_date)
     ORDER BY year DESC"
);

foreach ( $all_years as $row ) {
    echo "  Год: {$row->year}, Матчей: {$row->count}\n";
}
