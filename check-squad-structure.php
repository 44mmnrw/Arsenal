<?php
require_once 'wp-load.php';
global $wpdb;

echo "=== Структура wp_arsenal_squad ===\n";
$columns = $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . 'arsenal_squad');
foreach ($columns as $col) {
    echo $col->Field . ' (' . $col->Type . ') - ' . ($col->Null === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}

echo "\n=== Данные в wp_arsenal_squad ===\n";
$squads = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'arsenal_squad');
foreach ($squads as $squad) {
    echo 'ID: ' . $squad->id . ' | squad_id: ' . $squad->squad_id . ' | squad_name: ' . $squad->squad_name . "\n";
}
