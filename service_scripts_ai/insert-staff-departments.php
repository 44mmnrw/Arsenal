<?php
/**
 * Скрипт для добавления отделов в таблицу wp_arsenal_staff_department
 * 
 * @since 1.0.0
 */

// Подключаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

global $wpdb;

// Стандартные отделы футбольного клуба
$departments = array(
	array( 'department_name' => 'Тренерский штаб' ),
	array( 'department_name' => 'Медицинский департамент' ),
	array( 'department_name' => 'Администрация' ),
	array( 'department_name' => 'Аналитика' ),
	array( 'department_name' => 'Детский спорт' ),
);

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║        Добавление отделов в wp_arsenal_staff_department           ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

$table_name = $wpdb->prefix . 'arsenal_staff_department';

// Проверяем, пуста ли таблица
$existing_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

if ( $existing_count > 0 ) {
	echo "⚠️  Таблица уже содержит {$existing_count} записей.\n";
	echo "Вставляю дополнительные отделы...\n\n";
}

$inserted = 0;
foreach ( $departments as $dept ) {
	$result = $wpdb->insert( $table_name, $dept );
	if ( $result ) {
		echo "✅ Добавлен: {$dept['department_name']}\n";
		$inserted++;
	} else {
		echo "❌ Ошибка при добавлении: {$dept['department_name']}\n";
		echo "   {$wpdb->last_error}\n";
	}
}

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║ Итого добавлено: {$inserted} отделов\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

// Показываем все отделы
echo "Текущие отделы в БД:\n";
$all_departments = $wpdb->get_results( "SELECT * FROM {$table_name}" );
foreach ( $all_departments as $dept ) {
	echo "  [{$dept->id}] {$dept->department_name}\n";
}
