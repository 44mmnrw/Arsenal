<?php
// Проверить структуру БД для турнирной сетки

$wpdb_host = 'localhost';
$wpdb_user = 'root';
$wpdb_pass = '';
$wpdb_db = 'arsenal';

$conn = new mysqli($wpdb_host, $wpdb_user, $wpdb_pass, $wpdb_db);

if ($conn->connect_error) {
    die('Ошибка подключения: ' . $conn->connect_error);
}

// Проверить поля в таблице teams
echo "=== Структура wp_arsenal_teams ===\n";
$result = $conn->query("DESCRIBE wp_arsenal_teams");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n=== Примеры команд ===\n";
$result = $conn->query("SELECT team_id, name, logo_url FROM wp_arsenal_teams LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['team_id'] . ", Name: " . $row['name'] . ", Logo: " . ($row['logo_url'] ? 'YES (' . substr($row['logo_url'], 0, 50) . '...)' : 'NULL') . "\n";
    }
}

echo "\n=== Примеры матчей турнира ===\n";
$result = $conn->query("SELECT id, home_team_id, away_team_id FROM wp_arsenal_matches WHERE tournament_id='E4DE8DC0' LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Match ID: " . $row['id'] . ", Home: " . $row['home_team_id'] . ", Away: " . $row['away_team_id'] . "\n";
    }
}

$conn->close();
