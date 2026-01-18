<?php
/**
 * Скрипт добавления столбца club_type в таблицу wp_arsenal_staff
 * 
 * Использование: php add-club-type-staff.php
 * 
 * @package Arsenal_Team_Manager
 */

require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

global $wpdb;

$table_name = $wpdb->prefix . 'arsenal_staff';

// Проверяем, существует ли столбец
$column_exists = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_name}' AND COLUMN_NAME = 'club_type'" );

if ( ! empty( $column_exists ) ) {
    echo "✅ Столбец 'club_type' уже существует!\n";
    exit;
}

// Добавляем столбец после job_title_id
$sql = "ALTER TABLE {$table_name} ADD COLUMN club_type VARCHAR(50) DEFAULT 'Основной клуб' NOT NULL AFTER job_title_id";

$result = $wpdb->query( $sql );

if ( $result !== false ) {
    echo "✅ Столбец 'club_type' успешно добавлен в таблицу wp_arsenal_staff!\n";
    echo "   Местоположение: после job_title_id\n";
    echo "   Тип: VARCHAR(50)\n";
    echo "   Значение по умолчанию: 'Основной клуб'\n";
} else {
    echo "❌ Ошибка при добавлении столбца:\n";
    echo $wpdb->last_error . "\n";
}
