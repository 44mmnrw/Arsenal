<?php
require_once 'wp-load.php';
global $wpdb;

echo "=== Данные в wp_arsenal_staff ===\n";
$staff = $wpdb->get_results('SELECT id, first_name, second_name, job_title_id, squad_id FROM ' . $wpdb->prefix . 'arsenal_staff');
foreach ($staff as $person) {
    echo 'ID: ' . $person->id . ' | Name: ' . $person->first_name . ' ' . $person->second_name . ' | Job ID: ' . ($person->job_title_id ?? 'NULL') . ' | Squad ID: ' . ($person->squad_id ?? 'NULL') . "\n";
}

echo "\n=== Данные в wp_arsenal_staff_job_titles ===\n";
$jobs = $wpdb->get_results('SELECT id, job_title_name FROM ' . $wpdb->prefix . 'arsenal_staff_job_titles');
foreach ($jobs as $job) {
    echo 'ID: ' . $job->id . ' | Name: ' . $job->job_title_name . "\n";
}

echo "\n=== JOIN проверка ===\n";
$result = $wpdb->get_results(
    'SELECT s.id, s.first_name, s.second_name, s.job_title_id, j.job_title_name 
     FROM ' . $wpdb->prefix . 'arsenal_staff s
     LEFT JOIN ' . $wpdb->prefix . 'arsenal_staff_job_titles j ON s.job_title_id = j.id'
);
foreach ($result as $row) {
    echo 'Staff: ' . $row->first_name . ' ' . $row->second_name . ' | Job ID: ' . ($row->job_title_id ?? 'NULL') . ' | Job Name: ' . ($row->job_title_name ?? 'NULL') . "\n";
}
