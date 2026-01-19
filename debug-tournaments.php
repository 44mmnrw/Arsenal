<?php
// Debug tournaments and Arsenal matches

require_once 'wp-load.php';

global $wpdb;

echo "=== TOURNAMENTS ===\n";
$tournaments = $wpdb->get_results( "SELECT * FROM wp_arsenal_tournaments" );
foreach ( $tournaments as $t ) {
    echo "ID: {$t->tournament_id}, Name: {$t->name}\n";
}

echo "\n=== ARSENAL TEAM ID ===\n";
$arsenal_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
echo "Arsenal ID: {$arsenal_id}\n";

if ( ! $arsenal_id ) {
    $arsenal_id = '915703';
    echo "Fallback to: {$arsenal_id}\n";
}

echo "\n=== ACTIVE SEASON YEAR ===\n";
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );
echo "Year: {$active_season_year}\n";

echo "\n=== MATCHES BY TOURNAMENT ===\n";
$matches_by_tournament = $wpdb->get_results( $wpdb->prepare(
    "SELECT t.tournament_id, t.name, COUNT(m.id) as count
     FROM wp_arsenal_tournaments t
     LEFT JOIN wp_arsenal_matches m ON m.tournament_id = t.tournament_id
     WHERE (m.home_team_id = %s OR m.away_team_id = %s OR m.match_id IS NULL)
     AND (YEAR(m.match_date) = %d OR m.match_id IS NULL)
     GROUP BY t.tournament_id, t.name",
    $arsenal_id, $arsenal_id, $active_season_year
) );

foreach ( $matches_by_tournament as $row ) {
    echo "Tournament: {$row->name} ({$row->tournament_id}) - Matches: {$row->count}\n";
}

echo "\n=== ALL MATCHES FOR ARSENAL IN {$active_season_year} ===\n";
$all_matches = $wpdb->get_results( $wpdb->prepare(
    "SELECT m.match_id, m.tournament_id, m.match_date, m.tour, 
            ht.name as home, at.name as away, m.home_score, m.away_score,
            t.name as tournament_name
     FROM wp_arsenal_matches m
     LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.team_id
     LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.team_id
     LEFT JOIN wp_arsenal_tournaments t ON m.tournament_id = t.tournament_id
     WHERE (m.home_team_id = %s OR m.away_team_id = %s)
     AND YEAR(m.match_date) = %d
     ORDER BY m.match_date ASC
     LIMIT 20",
    $arsenal_id, $arsenal_id, $active_season_year
) );

foreach ( $all_matches as $match ) {
    echo "{$match->match_date} | {$match->tournament_name} (Тур {$match->tour}) | {$match->home} vs {$match->away} ({$match->home_score}:{$match->away_score})\n";
}

echo "\nTotal matches found: " . count( $all_matches ) . "\n";
