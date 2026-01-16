<?php
require 'wp-load.php';

global $wpdb;

$results = $wpdb->get_results("SELECT * FROM wp_arsenal_tournaments");

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
