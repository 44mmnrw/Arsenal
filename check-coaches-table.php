<?php
require 'wp-load.php';

global $wpdb;

echo "=== СТРУКТУРА wp_arsenal_coaches ===\n";
$schema = $wpdb->get_results('DESCRIBE wp_arsenal_coaches');
foreach ($schema as $field) {
    echo "Field: {$field->Field} | Type: {$field->Type} | Null: {$field->Null} | Key: {$field->Key} | Default: {$field->Default}\n";
}

echo "\n=== ПРИМЕР ДАННЫХ ===\n";
$coaches = $wpdb->get_results('SELECT * FROM wp_arsenal_coaches LIMIT 10');
foreach ($coaches as $coach) {
    echo json_encode($coach) . "\n";
}
