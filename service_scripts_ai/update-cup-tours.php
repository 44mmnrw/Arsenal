<?php
/**
 * Скрипт для заполнения поля tour в матчах Кубка Беларуси
 * 
 * Структура турнира (24 матча):
 * - tour 1: 1/16 финала (14 матчей, июнь-июль)
 * - tour 2: 1/8 финала (6 матчей, июль-август)
 * - tour 3: 1/4 финала (4 матча, ноябрь-декабрь)
 * - tour 4: 1/2 финала (0 матчей, еще не сыграны)
 * - tour 5: Финал (0 матчей, еще не сыгран)
 */

require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;

$tournament_id = 'E4DE8DC0'; // Кубок Беларуси
$season_id = '75453CE4';     // Сезон 2025

echo "=== Обновление поля tour для Кубка Беларуси 2025 ===\n\n";

// Получаем все матчи турнира, отсортированные по дате
$matches = $wpdb->get_results($wpdb->prepare(
    "SELECT id, match_date, tour, 
            (SELECT name FROM wp_arsenal_teams WHERE id = home_team_id) as home_team,
            (SELECT name FROM wp_arsenal_teams WHERE id = away_team_id) as away_team,
            home_score, away_score
     FROM wp_arsenal_matches 
     WHERE tournament_id = %s 
     AND season_id = %s
     ORDER BY match_date ASC",
    $tournament_id,
    $season_id
));

if (empty($matches)) {
    echo "Матчи не найдены.\n";
    exit;
}

echo "Найдено матчей: " . count($matches) . "\n\n";

// Показываем текущее состояние
echo "=== Текущие значения tour ===\n";
$tour_counts = array();
foreach ($matches as $match) {
    $tour = $match->tour ?? 'NULL';
    if (!isset($tour_counts[$tour])) {
        $tour_counts[$tour] = 0;
    }
    $tour_counts[$tour]++;
}
foreach ($tour_counts as $tour => $count) {
    echo "tour $tour: $count матчей\n";
}

echo "\n=== Предлагаемое распределение ===\n";
echo "Матчи 1-14 (17.06 - 14.07) → tour = 1 (1/16 финала)\n";
echo "Матчи 15-20 (25.07 - 19.08) → tour = 2 (1/8 финала)\n";
echo "Матчи 21-24 (12.11 - 04.12) → tour = 3 (1/4 финала)\n";
echo "\n";

// Спрашиваем подтверждение
echo "Обновить значения tour? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if ($line !== 'yes') {
    echo "Отменено.\n";
    exit;
}

// Обновляем tour
$updated = 0;
foreach ($matches as $index => $match) {
    $new_tour = null;
    
    // Определяем tour на основе порядкового номера (после сортировки по дате)
    if ($index < 14) {
        $new_tour = 1; // 1/16 финала
    } elseif ($index < 20) {
        $new_tour = 2; // 1/8 финала
    } elseif ($index < 24) {
        $new_tour = 3; // 1/4 финала
    }
    
    if ($new_tour !== null) {
        $result = $wpdb->update(
            'wp_arsenal_matches',
            array('tour' => $new_tour),
            array('id' => $match->id),
            array('%d'),
            array('%d')
        );
        
        if ($result !== false) {
            $updated++;
            echo sprintf(
                "✓ Матч #%d (%s): %s %d:%d %s → tour = %d\n",
                $index + 1,
                date('d.m', strtotime($match->match_date)),
                $match->home_team ?? 'TBD',
                $match->home_score ?? 0,
                $match->away_score ?? 0,
                $match->away_team ?? 'TBD',
                $new_tour
            );
        } else {
            echo "✗ Ошибка обновления матча ID {$match->id}\n";
        }
    }
}

echo "\n=== Результат ===\n";
echo "Обновлено матчей: $updated из " . count($matches) . "\n";

// Показываем финальное распределение
echo "\n=== Финальное распределение ===\n";
$final_counts = $wpdb->get_results($wpdb->prepare(
    "SELECT tour, COUNT(*) as count 
     FROM wp_arsenal_matches 
     WHERE tournament_id = %s AND season_id = %s 
     GROUP BY tour 
     ORDER BY tour",
    $tournament_id,
    $season_id
));

foreach ($final_counts as $row) {
    $tour_name = '';
    switch ($row->tour) {
        case 1: $tour_name = '1/16 финала'; break;
        case 2: $tour_name = '1/8 финала'; break;
        case 3: $tour_name = '1/4 финала'; break;
        case 4: $tour_name = '1/2 финала'; break;
        case 5: $tour_name = 'Финал'; break;
        default: $tour_name = 'Неизвестно';
    }
    echo "tour {$row->tour} ($tour_name): {$row->count} матчей\n";
}

echo "\nГотово! ✓\n";
