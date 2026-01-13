<?php
/**
 * Скрипт установки таблицы спонсоров
 * 
 * Запустите: php install-sponsors.php
 */

// Подключаем WordPress
require_once 'wp-load.php';

global $wpdb;

// Читаем SQL файл
$sql = file_get_contents( 'database/create-sponsors-table.sql' );

// Разбиваем на отдельные запросы
$queries = array_filter( array_map( 'trim', explode( ';', $sql ) ) );

$success_count = 0;
$error_count = 0;

foreach ( $queries as $query ) {
	if ( ! empty( $query ) ) {
		$result = $wpdb->query( $query );
		
		if ( false === $result ) {
			echo "❌ Ошибка: " . $wpdb->last_error . "\n";
			echo "Запрос: " . substr( $query, 0, 100 ) . "...\n\n";
			$error_count++;
		} else {
			echo "✅ Успешно: " . substr( $query, 0, 80 ) . "...\n";
			$success_count++;
		}
	}
}

echo "\n" . str_repeat( '=', 50 ) . "\n";
echo "Результат: $success_count успешно, $error_count ошибок\n";
echo str_repeat( '=', 50 ) . "\n";

if ( $error_count === 0 ) {
	// Проверяем созданную таблицу
	$columns = $wpdb->get_results( "DESCRIBE {$wpdb->prefix}arsenal_sponsors" );
	echo "\n✅ Таблица создана! Поля:\n";
	foreach ( $columns as $col ) {
		echo "  - {$col->Field} ({$col->Type})\n";
	}
	
	// Проверяем тестовые данные
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_sponsors" );
	echo "\n✅ Тестовых записей: $count\n";
	
	echo "\n🎉 Таблица спонсоров успешно установлена!\n";
} else {
	echo "\n⚠️ Установка завершена с ошибками. Проверьте логи выше.\n";
}
?>
