<?php
require_once 'wp-load.php';

global $wpdb;

// Проверим структуру таблицы
$columns = $wpdb->get_results( "DESCRIBE {$wpdb->prefix}arsenal_squad" );
echo "=== Структура wp_arsenal_squad ===\n\n";
foreach ( $columns as $col ) {
	echo $col->Field . " (" . $col->Type . ") " . ( $col->Null === 'NO' ? 'NOT NULL' : 'NULL' ) . "\n";
}

// Посмотрим примеры данных
echo "\n=== Примеры данных ===\n\n";
$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_squad" );
echo 'Всего записей: ' . $count . "\n\n";

if ( $count > 0 ) {
	$squad = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}arsenal_squad LIMIT 1" );
	echo json_encode( $squad, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
}
