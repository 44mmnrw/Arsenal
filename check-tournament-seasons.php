<?php
// Проверка связи турниров и сезонов в БД

require_once 'wp-load.php';

global $wpdb;

echo "=== TOURNAMENTS ===\n";
$tournaments = $wpdb->get_results( "SELECT tournament_id, name FROM wp_arsenal_tournaments" );
foreach ( $tournaments as $row ) {
    echo "ID: {$row->tournament_id}, Name: {$row->name}\n";
}

echo "\n=== SEASONS ===\n";
$seasons = $wpdb->get_results( "SELECT season_id, year FROM wp_arsenal_seasons LIMIT 10" );
foreach ( $seasons as $row ) {
    echo "ID: {$row->season_id}, Year: {$row->year}\n";
}

echo "\n=== MATCHES with Tournament & Season (first 5) ===\n";
$matches = $wpdb->get_results( "SELECT tournament_id, season_id, match_date FROM wp_arsenal_matches LIMIT 5" );
foreach ( $matches as $row ) {
    echo "Tournament: {$row->tournament_id}, Season: {$row->season_id}, Date: {$row->match_date}\n";
}

echo "\n=== COUNT: Matches per Tournament & Season ===\n";
$counts = $wpdb->get_results( "
    SELECT tournament_id, season_id, COUNT(*) as count 
    FROM wp_arsenal_matches 
    GROUP BY tournament_id, season_id 
    ORDER BY tournament_id, season_id
" );
foreach ( $counts as $row ) {
    echo "Tournament: {$row->tournament_id}, Season: {$row->season_id}, Matches: {$row->count}\n";
}
