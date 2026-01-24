<?php
/**
 * Check match_time field in database
 */
require 'wp-load.php';

global $wpdb;

echo "📊 Статистика match_time в базе:\n";

$query = "SELECT COUNT(*) as total, 
    SUM(CASE WHEN match_time IS NOT NULL THEN 1 ELSE 0 END) as with_time,
    SUM(CASE WHEN match_time IS NULL THEN 1 ELSE 0 END) as without_time
FROM {$wpdb->prefix}arsenal_matches";

$result = $wpdb->get_row( $query );
echo "   Всего матчей: " . $result->total . "\n";
echo "   С временем (match_time): " . $result->with_time . "\n";
echo "   Без времени (NULL): " . $result->without_time . "\n\n";

// Пример матча с временем
$sample = $wpdb->get_row( "SELECT match_date, match_time, home_team_id, away_team_id FROM {$wpdb->prefix}arsenal_matches WHERE match_time IS NOT NULL LIMIT 1" );
if ( $sample ) {
	echo "✅ Пример матча с временем:\n";
	echo "   match_date: " . $sample->match_date . "\n";
	echo "   match_time: " . $sample->match_time . "\n";
}

// Пример матча без времени
$no_time = $wpdb->get_row( "SELECT match_date, match_time, home_team_id, away_team_id FROM {$wpdb->prefix}arsenal_matches WHERE match_time IS NULL LIMIT 1" );
if ( $no_time ) {
	echo "\n⚠️ Пример матча БЕЗ времени:\n";
	echo "   match_date: " . $no_time->match_date . "\n";
	echo "   match_time: " . $no_time->match_time . "\n";
}
?>
