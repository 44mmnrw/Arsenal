<?php
require 'wp-load.php';

global $wpdb;

echo "=== Турниры и их сезоны ===\n\n";

// Получить все турниры
$tournaments = $wpdb->get_results( 'SELECT tournament_id, name FROM wp_arsenal_tournaments' );

foreach ( $tournaments as $t ) {
	echo "Турнир: {$t->name} ({$t->tournament_id})\n";
	
	// Получить сезоны для этого турнира
	$seasons = $wpdb->get_col(
		$wpdb->prepare(
			'SELECT DISTINCT YEAR(match_date) FROM wp_arsenal_matches WHERE tournament_id = %s ORDER BY YEAR(match_date) DESC',
			$t->tournament_id
		)
	);
	
	if ( empty( $seasons ) ) {
		echo "  Нет матчей!\n";
	} else {
		echo "  Сезоны: " . implode( ', ', $seasons ) . "\n";
	}
	
	// Количество матчей
	$count = $wpdb->get_var(
		$wpdb->prepare(
			'SELECT COUNT(*) FROM wp_arsenal_matches WHERE tournament_id = %s',
			$t->tournament_id
		)
	);
	echo "  Всего матчей: $count\n\n";
}
