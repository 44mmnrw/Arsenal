<?php
// Загрузить WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Проверить что WordPress загружен
if ( function_exists( 'get_option' ) ) {
    echo "WordPress loaded successfully\n";
}

// Попытаться загрузить админ класс
require_once( dirname( __FILE__ ) . '/wp-content/plugins/arsenal-team-manager/admin/class-arsenal-staff-admin.php' );

if ( class_exists( 'Arsenal_Staff_Admin' ) ) {
    echo "Arsenal_Staff_Admin loaded successfully\n";
    
    // Инициализировать класс
    $staff_admin = Arsenal_Staff_Admin::get_instance();
    echo "Arsenal_Staff_Admin initialized\n";
} else {
    echo "ERROR: Arsenal_Staff_Admin not found\n";
}

// Попытаться загрузить представление
if ( file_exists( dirname( __FILE__ ) . '/wp-content/plugins/arsenal-team-manager/admin/views/staff-list.php' ) ) {
    echo "staff-list.php file exists\n";
    
    // Проверить что можно подключить файл
    global $wpdb;
    
    // Симуляция того что есть персонал для отображения
    $staff_members = $wpdb->get_results( 
        "SELECT * FROM {$wpdb->prefix}arsenal_staff ORDER BY id" 
    );
    
    echo "Found " . count( $staff_members ) . " staff members\n";
    
    if ( $staff_members ) {
        foreach ( $staff_members as $staff ) {
            echo "  - " . $staff->name . " (ID: " . $staff->id . ", squad_id: " . ( $staff->squad_id ?? 'NULL' ) . ")\n";
        }
    }
} else {
    echo "ERROR: staff-list.php file not found\n";
}

echo "\nTest completed successfully!\n";
