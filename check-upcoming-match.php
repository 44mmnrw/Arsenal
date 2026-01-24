<?php
/**
 * Check actual match data from database
 */
require 'wp-load.php';

global $wpdb;

// ID команды Арсенал
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = 'EB8AA245';
}

echo "🔍 Ищу Арсенал: ID = " . $arsenal_team_id . "\n\n";

// Получаем ближайший матч Арсенала (где ещё нет счёта)
$upcoming_match = $wpdb->get_row( $wpdb->prepare( "
	SELECT 
		m.match_id,
		m.match_date,
		m.match_time,
		CONCAT(m.match_date, ' ', COALESCE(m.match_time, '00:00:00')) as match_datetime,
		ht.name as home_team,
		at.name as away_team,
		m.home_score,
		m.away_score,
		s.name as stadium_name,
		t.name as tournament_name
	FROM {$wpdb->prefix}arsenal_matches m
	LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
	LEFT JOIN {$wpdb->prefix}arsenal_tournaments t ON m.tournament_id = t.tournament_id
	WHERE (m.home_team_id = %s OR m.away_team_id = %s)
		AND m.match_date >= NOW()
		AND (m.home_score IS NULL OR m.away_score IS NULL)
	ORDER BY m.match_date ASC
	LIMIT 1
", $arsenal_team_id, $arsenal_team_id ) );

if ( $upcoming_match ) {
	echo "✅ Ближайший матч найден:\n";
	echo "   Match ID: " . $upcoming_match->match_id . "\n";
	echo "   Дата (match_date): " . $upcoming_match->match_date . "\n";
	echo "   Время (match_time): " . $upcoming_match->match_time . "\n";
	echo "   Объединённо (match_datetime): " . $upcoming_match->match_datetime . "\n";
	echo "   " . $upcoming_match->home_team . " vs " . $upcoming_match->away_team . "\n";
	echo "   Стадион: " . $upcoming_match->stadium_name . "\n";
	echo "   Турнир: " . $upcoming_match->tournament_name . "\n";
	echo "   Счёт: " . $upcoming_match->home_score . " : " . $upcoming_match->away_score . "\n";
} else {
	echo "❌ Предстоящих матчей не найдено, ищу последний сыгранный...\n\n";
	
	$last_match = $wpdb->get_row( $wpdb->prepare( "
		SELECT 
			m.match_id,
			m.match_date,
			m.match_time,
			CONCAT(m.match_date, ' ', COALESCE(m.match_time, '00:00:00')) as match_datetime,
			ht.name as home_team,
			at.name as away_team,
			m.home_score,
			m.away_score,
			s.name as stadium_name,
			t.name as tournament_name
		FROM {$wpdb->prefix}arsenal_matches m
		LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
		LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
		LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
		LEFT JOIN {$wpdb->prefix}arsenal_tournaments t ON m.tournament_id = t.tournament_id
		WHERE (m.home_team_id = %s OR m.away_team_id = %s)
			AND m.home_score IS NOT NULL
			AND m.away_score IS NOT NULL
		ORDER BY m.match_date DESC
		LIMIT 1
	", $arsenal_team_id, $arsenal_team_id ) );

	if ( $last_match ) {
		echo "✅ Последний матч найден:\n";
		echo "   Match ID: " . $last_match->match_id . "\n";
		echo "   Дата (match_date): " . $last_match->match_date . "\n";
		echo "   Время (match_time): " . $last_match->match_time . "\n";
		echo "   Объединённо (match_datetime): " . $last_match->match_datetime . "\n";
		echo "   " . $last_match->home_team . " vs " . $last_match->away_team . "\n";
		echo "   Стадион: " . $last_match->stadium_name . "\n";
		echo "   Турнир: " . $last_match->tournament_name . "\n";
		echo "   Счёт: " . $last_match->home_score . " : " . $last_match->away_score . "\n";
	}
}

echo "\n\n📊 Всего матчей Арсенала в базе:\n";
$all_matches = $wpdb->get_results( $wpdb->prepare( "
	SELECT COUNT(*) as total FROM {$wpdb->prefix}arsenal_matches
	WHERE home_team_id = %s OR away_team_id = %s
", $arsenal_team_id, $arsenal_team_id ) );

foreach ( $all_matches as $stat ) {
	echo "   " . $stat->total . "\n";
}
?>
