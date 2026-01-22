<?php
require 'wp-load.php';

global $wpdb;

echo "=== wp_arsenal_staff_department ===\n";
$cols = $wpdb->get_results( "DESCRIBE {$wpdb->prefix}arsenal_staff_department" );
foreach ( $cols as $c ) {
    echo $c->Field . ' (' . $c->Type . ') ' . ($c->Key ? '[' . $c->Key . ']' : '') . "\n";
}
?>
