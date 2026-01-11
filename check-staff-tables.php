<?php
require 'wp-load.php'; 
global $wpdb; 
$tables = $wpdb->get_results('SHOW TABLES LIKE "%staff%"'); 
foreach($tables as $t) {
    echo implode(' | ', (array)$t) . "\n";
}

if (empty($tables)) {
    echo "No staff tables found\n";
    echo "\nAll arsenal tables:\n";
    $all = $wpdb->get_results('SHOW TABLES LIKE "wp_arsenal_%"');
    echo "Count: " . count($all) . "\n";
}
