<?php
require_once 'wp-load.php';
global $wpdb;

$tournament_id = 'E4DE8DC0'; // Кубок Беларуси

// Последний сезон
$season = $wpdb->get_row($wpdb->prepare(
    "SELECT DISTINCT season_id FROM {$wpdb->prefix}arsenal_matches 
     WHERE tournament_id = %s 
     ORDER BY match_date DESC 
     LIMIT 1",
    $tournament_id
));

if (!$season) {
    echo "Сезон не найден!\n";
    exit;
}

echo "Tournament: $tournament_id\n";
echo "Season: {$season->season_id}\n\n";

// Получаем матчи
$matches = $wpdb->get_results($wpdb->prepare(
    "SELECT m.id, m.match_date, m.home_score, m.away_score,
            ht.name as home_team, at.name as away_team
     FROM {$wpdb->prefix}arsenal_matches m
     LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
     LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
     WHERE m.tournament_id = %s AND m.season_id = %s
     ORDER BY m.match_date ASC
     LIMIT 15",
    $tournament_id,
    $season->season_id
));

// Подсчитываем все матчи
$total = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_matches 
     WHERE tournament_id = %s AND season_id = %s",
    $tournament_id,
    $season->season_id
));

echo "Total matches in DB: $total\n";
echo "Found matches (first 15): " . count($matches) . "\n\n";

foreach ($matches as $m) {
    echo date('d.m.Y', strtotime($m->match_date)) . ': ';
    echo ($m->home_team ?? 'NULL') . ' ' . ($m->home_score ?? '-') . ':' . ($m->away_score ?? '-') . ' ' . ($m->away_team ?? 'NULL') . "\n";
}

// Группируем по датам
echo "\n--- Matches by date ---\n";
$by_date = $wpdb->get_results($wpdb->prepare(
    "SELECT DATE(match_date) as date, COUNT(*) as cnt 
     FROM {$wpdb->prefix}arsenal_matches 
     WHERE tournament_id = %s AND season_id = %s
     GROUP BY DATE(match_date)
     ORDER BY match_date",
    $tournament_id,
    $season->season_id
));

foreach ($by_date as $d) {
    echo $d->date . ": " . $d->cnt . " matches\n";
}

// Попробуем определить раунды
echo "\n--- Trying to identify rounds (24 matches structure) ---\n";
echo "Possible structures:\n";
echo "A) 1/16 (14) + 1/8 (4) + 1/4 (2) + 1/2 (2) + Final (1) + 3rd place (1) = 24\n";
echo "B) 1/16 (12) + 1/8 (4) + 1/4 (2) + 1/2 (2) + Final (1) + replays (3) = 24\n";
echo "C) 1/32 preliminary (8) + 1/16 (8) + 1/8 (4) + 1/4 (2) + 1/2 (1) + Final (1) = 24\n\n";

// Получаем все матчи с сортировкой
$all = $wpdb->get_results($wpdb->prepare(
    "SELECT match_date, home_team_id, away_team_id, home_score, away_score,
            ht.name as home_team, at.name as away_team
     FROM {$wpdb->prefix}arsenal_matches m
     LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
     LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
     WHERE m.tournament_id = %s AND m.season_id = %s
     ORDER BY m.match_date ASC",
    $tournament_id,
    $season->season_id
));

echo "All 24 matches:\n";
$i = 1;
foreach ($all as $m) {
    printf("%2d. %s: %-25s %d:%d %-25s\n", 
        $i++,
        date('d.m', strtotime($m->match_date)),
        $m->home_team ?? 'NULL',
        $m->home_score ?? 0,
        $m->away_score ?? 0,
        $m->away_team ?? 'NULL'
    );
}
