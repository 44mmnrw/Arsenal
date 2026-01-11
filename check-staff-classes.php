<?php
require 'wp-load.php'; 
require_once get_template_directory() . '/inc/class-arsenal-staff-manager.php';
require_once dirname( plugin_dir_path( __FILE__ ) ) . '/wp-content/plugins/arsenal-team-manager/admin/class-arsenal-staff-admin.php';

echo 'Staff Manager: ' . (class_exists('Arsenal_Staff_Manager') ? 'OK' : 'FAIL') . "\n";
echo 'Staff Admin: ' . (class_exists('Arsenal_Staff_Admin') ? 'OK' : 'FAIL') . "\n";

// Проверим таблицы
global $wpdb;
$tables = $wpdb->get_results('SHOW TABLES LIKE "wp_arsenal_staff%"');
echo 'Staff tables found: ' . count($tables) . "\n";
foreach ($tables as $t) {
    echo '  - ' . implode(' ', (array)$t) . "\n";
}
