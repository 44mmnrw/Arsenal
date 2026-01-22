<?php
require 'wp-load.php';
global $wpdb;

echo "=== Все сотрудники ===\n";
$all = $wpdb->get_results("SELECT id, first_name, second_name, squad_id FROM {$wpdb->prefix}arsenal_staff LIMIT 5");
foreach($all as $s) {
    echo "ID: {$s->id}, Name: {$s->first_name} {$s->second_name}, Squad: {$s->squad_id}\n";
}

echo "\n=== Поиск Мелешкевича ===\n";
$melesh = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}arsenal_staff WHERE second_name LIKE '%Мелеш%' LIMIT 1");
if($melesh) {
    echo "Найден: {$melesh->first_name} {$melesh->second_name}\n";
    echo "squad_id: {$melesh->squad_id}\n";
    echo "department_id: {$melesh->department_id}\n";
} else {
    echo "Не найден!\n";
}

echo "\n=== Проверка таблицы squad ===\n";
$squads = $wpdb->get_results("SELECT id, squad_id, squad_name FROM {$wpdb->prefix}arsenal_squad");
foreach($squads as $sq) {
    echo "ID: {$sq->id}, squad_id: {$sq->squad_id}, name: {$sq->squad_name}\n";
}

echo "\n=== Тестовый запрос (без WHERE) ===\n";
$test = $wpdb->get_results("SELECT s.id, s.first_name, s.second_name, jt.job_title_name FROM {$wpdb->prefix}arsenal_staff s LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id LIMIT 3");
foreach($test as $t) {
    echo "{$t->first_name} {$t->second_name} - {$t->job_title_name}\n";
}
?>
