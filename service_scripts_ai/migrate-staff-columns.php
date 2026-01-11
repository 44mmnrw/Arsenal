<?php
/**
 * Миграция: добавление недостающих колонок в таблицы персонала
 */
require_once 'wp-load.php';

global $wpdb;

echo "=== Добавление недостающих колонок ===\n\n";

// 1. Добавить uid и is_active в wp_arsenal_staff
echo "1. Проверка wp_arsenal_staff...\n";
$columns = $wpdb->get_results( "DESCRIBE wp_arsenal_staff" );
$column_names = array_column( $columns, 'Field' );

if ( ! in_array( 'uid', $column_names ) ) {
	echo "  - Добавляю колонку uid...\n";
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}arsenal_staff ADD COLUMN uid CHAR(36) UNIQUE NOT NULL DEFAULT UUID() AFTER id" );
	echo "    ✓ uid добавлена\n";
} else {
	echo "  ✓ uid уже существует\n";
}

if ( ! in_array( 'is_active', $column_names ) ) {
	echo "  - Добавляю колонку is_active...\n";
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}arsenal_staff ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER bio" );
	echo "    ✓ is_active добавлена\n";
} else {
	echo "  ✓ is_active уже существует\n";
}

// 2. Проверить wp_arsenal_staff_job_titles
echo "\n2. Проверка wp_arsenal_staff_job_titles...\n";
$columns = $wpdb->get_results( "DESCRIBE wp_arsenal_staff_job_titles" );
$column_names = array_column( $columns, 'Field' );

if ( ! in_array( 'is_active', $column_names ) ) {
	echo "  - Добавляю колонку is_active...\n";
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}arsenal_staff_job_titles ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER sort_order" );
	echo "    ✓ is_active добавлена\n";
} else {
	echo "  ✓ is_active уже существует\n";
}

echo "\n✓ Миграция завершена!\n";

// Проверка результата
echo "\n=== Проверка результата ===\n";
echo "wp_arsenal_staff колонки:\n";
$columns = $wpdb->get_results( "DESCRIBE wp_arsenal_staff" );
foreach ( $columns as $col ) {
	echo "  - " . $col->Field . " (" . $col->Type . ")\n";
}

echo "\nwp_arsenal_staff_job_titles колонки:\n";
$columns = $wpdb->get_results( "DESCRIBE wp_arsenal_staff_job_titles" );
foreach ( $columns as $col ) {
	echo "  - " . $col->Field . " (" . $col->Type . ")\n";
}
?>
