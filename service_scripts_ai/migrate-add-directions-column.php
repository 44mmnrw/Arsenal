<?php
/**
 * Миграция: Добавить колонку directions_data в таблицу wp_arsenal_academy_recruitment
 * 
 * Запуск: php service_scripts_ai/migrate-add-directions-column.php
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Загрузить WordPress
require_once 'wp-load.php';

global $wpdb;
$table = $wpdb->prefix . 'arsenal_academy_recruitment';

// Проверить, существует ли таблица
$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" );
if ( ! $table_exists ) {
	echo "❌ Таблица $table не существует\n";
	exit( 1 );
}

// Проверить, существует ли уже колонка
$column_exists = $wpdb->get_var( 
	$wpdb->prepare( 
		"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = 'directions_data'",
		$table
	)
);

if ( $column_exists ) {
	echo "ℹ️  Колонка directions_data уже существует\n";
	exit( 0 );
}

// Добавить колонку
$sql = "ALTER TABLE `$table` ADD COLUMN `directions_data` JSON DEFAULT NULL COMMENT 'Маршруты общественного транспорта' AFTER `contacts_data`";

$result = $wpdb->query( $sql );

if ( $result === false ) {
	echo "❌ Ошибка при добавлении колонки: " . $wpdb->last_error . "\n";
	exit( 1 );
}

echo "✅ Колонка directions_data успешно добавлена в таблицу $table\n";
echo "ℹ️  Таблица обновлена и готова к использованию\n";
exit( 0 );
