<?php
/**
 * Test standings query
 */

require 'wp-load.php';

global $wpdb;

$tournament_id = '71CFDAA6';
$season_id = '5B2ABC0C';

echo "=== Testing Standings Query ===\n\n";

// Test 1: Get teams with new key structure
$query = $wpdb->prepare(
    "SELECT DISTINCT t.id, t.team_id, t.name
     FROM {$wpdb->prefix}arsenal_teams t
     INNER JOIN {$wpdb->prefix}arsenal_matches m ON (m.home_team_id = t.team_id OR m.away_team_id = t.team_id)
     WHERE m.season_id = %s AND m.tournament_id = %s
     ORDER BY t.name
     LIMIT 5",
    $season_id,
    $tournament_id
);

echo "Query:\n$query\n\n";

$teams = $wpdb->get_results( $query );

echo "Found teams: " . count( $teams ) . "\n";
foreach ( $teams as $team ) {
    echo "  - ID (internal): " . $team->id . ", team_id (external): " . $team->team_id . ", name: " . $team->name . "\n";
}

// Test 2: Get matches
echo "\n=== Testing Matches Query ===\n";

$matches_query = $wpdb->prepare(
    "SELECT m.home_team_id, m.away_team_id, m.home_score, m.away_score
     FROM {$wpdb->prefix}arsenal_matches m
     WHERE m.season_id = %s
     AND m.tournament_id = %s
     AND m.status = '0083CE05'
     AND m.home_score IS NOT NULL
     AND m.away_score IS NOT NULL
     LIMIT 5",
    $season_id,
    $tournament_id
);

echo "Query:\n$matches_query\n\n";

$matches = $wpdb->get_results( $matches_query );

echo "Found matches: " . count( $matches ) . "\n";
foreach ( $matches as $match ) {
    echo "  - " . $match->home_team_id . " vs " . $match->away_team_id . ": " . $match->home_score . "-" . $match->away_score . "\n";
}

// Test 3: Check if keys match
echo "\n=== Key Compatibility Check ===\n";

if ( ! empty( $teams ) && ! empty( $matches ) ) {
    $home_team_id = $matches[0]->home_team_id;
    $team_found = false;
    
    foreach ( $teams as $team ) {
        if ( $team->team_id === $home_team_id ) {
            $team_found = true;
            echo "✓ Match found: team_id '{$home_team_id}' exists in standings\n";
            break;
        }
    }
    
    if ( ! $team_found ) {
        echo "✗ ERROR: team_id '{$home_team_id}' NOT found in standings\n";
    }
} else {
    echo "⚠ No data to compare\n";
}

echo "\n=== Test Complete ===\n";
