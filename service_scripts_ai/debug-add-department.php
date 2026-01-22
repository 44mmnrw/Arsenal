<?php
/**
 * Скрипт отладки - добавление отдела
 */

require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress не загружен' );
}

global $wpdb;

echo "\n=== ОТЛАДКА ДОБАВЛЕНИЯ ОТДЕЛА ===\n\n";

// 1. Проверим структуру таблицы
echo "1️⃣ СТРУКТУРА ТАБЛИЦЫ wp_arsenal_staff_department:\n";
$columns = $wpdb->get_results( "DESCRIBE {$wpdb->prefix}arsenal_staff_department" );
foreach ( $columns as $col ) {
    echo "  - {$col->Field}: {$col->Type}";
    if ( $col->Null === 'NO' ) echo " (NOT NULL)";
    if ( $col->Default !== null ) echo " DEFAULT {$col->Default}";
    echo "\n";
}

echo "\n2️⃣ ПОПЫТКА ДОБАВИТЬ ОТДЕЛ:\n";

// Используем логику из add_department_ajax
require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

$department_name = 'Тестовый отдел';
$squad_id = '43915DAC';

echo "  department_name: $department_name\n";
echo "  squad_id: $squad_id\n";

// Добавляем отдел
$result = Arsenal_Staff_Manager::add_department( $department_name, '', 0 );

echo "\n  Результат add_department(): ";
if ( $result ) {
    echo "✅ ID = $result\n";
    
    // Обновляем squad_id
    $update = $wpdb->update(
        $wpdb->prefix . 'arsenal_staff_department',
        array( 'squad_id' => $squad_id ),
        array( 'id' => $result ),
        array( '%s' ),
        array( '%d' )
    );
    
    echo "\n3️⃣ ПОПЫТКА ОБНОВИТЬ squad_id:\n";
    echo "  UPDATE результат: $update\n";
    
    if ( $wpdb->last_error ) {
        echo "  ❌ MySQL ошибка: {$wpdb->last_error}\n";
    } else {
        echo "  ✅ Обновление успешно\n";
        
        // Проверим что было добавлено
        $check = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}arsenal_staff_department WHERE id = %d",
            $result
        ));
        
        echo "\n4️⃣ ПРОВЕРКА ДОБАВЛЕННОГО ОТДЕЛА:\n";
        echo "  ID: {$check->id}\n";
        echo "  Название: {$check->department_name}\n";
        echo "  Squad ID: {$check->squad_id}\n";
        echo "  Описание: {$check->description}\n";
    }
} else {
    echo "❌ ОШИБКА\n";
    if ( $wpdb->last_error ) {
        echo "  MySQL ошибка: {$wpdb->last_error}\n";
    }
}

echo "\n=== КОНЕЦ ОТЛАДКИ ===\n\n";
?>
