<?php
require 'wp-load.php';
global $wpdb;

$tournaments = $wpdb->get_results('SELECT tournament_id, name FROM wp_arsenal_tournaments ORDER BY name');
foreach ($tournaments as $t) {
    echo $t->name . ' => ' . $t->tournament_id . PHP_EOL;
}
