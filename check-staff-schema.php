<?php
require_once 'wp-load.php';

global $wpdb;

echo "=== Колонки в wp_arsenal_staff ===\n";
$columns = $wpdb->get_results( "DESCRIBE wp_arsenal_staff" );
if ( $columns ) {
	foreach ( $columns as $col ) {
		echo $col->Field . " (" . $col->Type . ") " . ( $col->Null === 'NO' ? 'NOT NULL' : 'NULL' ) . "\n";
	}
} else {
	echo "Таблица не существует или ошибка: " . $wpdb->last_error . "\n";
}

echo "\n=== Колонки в wp_arsenal_staff_job_titles ===\n";
$columns = $wpdb->get_results( "DESCRIBE wp_arsenal_staff_job_titles" );
if ( $columns ) {
	foreach ( $columns as $col ) {
		echo $col->Field . " (" . $col->Type . ") " . ( $col->Null === 'NO' ? 'NOT NULL' : 'NULL' ) . "\n";
	}
} else {
	echo "Таблица не существует или ошибка: " . $wpdb->last_error . "\n";
}
?>
