<?php
require 'wp-load.php';
global $wpdb;

$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );
echo "Active season year from dashboard: " . $active_season_year . "\n\n";

$arsenal_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_id ) {
    $arsenal_id = '915703';
}

echo "Arsenal ID: $arsenal_id\n\n";

$matches = $wpdb->get_results( $wpdb->prepare(
    "SELECT m.match_id, m.match_date, m.tournament_id, t.name as tournament_name, ht.name as home, at.name as away
     FROM wp_arsenal_matches m
     LEFT JOIN wp_arsenal_tournaments t ON m.tournament_id = t.tournament_id
     LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.team_id
     LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.team_id
     WHERE (m.home_team_id = %s OR m.away_team_id = %s)
     AND YEAR(m.match_date) = %d
     ORDER BY m.tournament_id, m.match_date",
    $arsenal_id, $arsenal_id, $active_season_year
) );

if ( empty( $matches ) ) {
    echo "Матчей Арсенала в $active_year году не найдено!\n";
} else {
    $current_tournament = null;
    foreach ( $matches as $m ) {
        if ( $current_tournament !== $m->tournament_id ) {
            echo "\n=== " . ($m->tournament_name ?: 'Неизвестный турнир') . " ({$m->tournament_id}) ===\n";
            $current_tournament = $m->tournament_id;
        }
        echo $m->match_date . " | " . $m->home . " vs " . $m->away . "\n";
    }
}
