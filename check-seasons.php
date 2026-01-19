<?php
require 'wp-load.php';

global $wpdb;

echo "=== Таблица wp_arsenal_seasons ===\n\n";
$seasons = $wpdb->get_results( "SELECT * FROM wp_arsenal_seasons ORDER BY season_id DESC" );

foreach ( $seasons as $s ) {
	echo "season_id: {$s->season_id}\n";
	echo "season_name: {$s->season_name}\n";
	echo "start_date: {$s->start_date}\n";
	echo "end_date: {$s->end_date}\n";
	echo "---\n";
}

echo "\n=== Таблица wp_arsenal_matches имеет season_id? ===\n";
$match_seasons = $wpdb->get_results( "SELECT DISTINCT season_id FROM wp_arsenal_matches LIMIT 10" );
foreach ( $match_seasons as $ms ) {
	echo "season_id: {$ms->season_id}\n";
}
