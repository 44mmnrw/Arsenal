<?php
/**
 * Скрипт для исправления связи department_id в wp_arsenal_staff
 * 
 * Проблема: department_id не может быть связан с wp_arsenal_staff_department из-за NOT NULL
 * Решение: сделать nullable, очистить пустые, добавить FK
 */

define( 'WP_USE_THEMES', false );
require( __DIR__ . '/wp-load.php' );

global $wpdb;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║        Исправление Foreign Key для wp_arsenal_staff            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Шаг 1: Проверить текущее состояние колонки
echo "📋 Шаг 1: Проверка текущего состояния колонки department_id...\n";
$column_info = $wpdb->get_results(
    "SHOW FULL COLUMNS FROM {$wpdb->prefix}arsenal_staff LIKE 'department_id'"
);

if ( ! empty( $column_info ) ) {
    $col = $column_info[0];
    echo "  Тип: {$col->Type}\n";
    echo "  Nullable: {$col->Null}\n";
    echo "  Default: {$col->Default}\n";
    echo "  Extra: {$col->Extra}\n";
} else {
    echo "  ❌ Колонка department_id не найдена!\n";
    exit;
}

// Шаг 2: Проверить наличие данных
echo "\n📊 Шаг 2: Проверка данных в колонке...\n";
$data_check = $wpdb->get_results(
    "SELECT id, first_name, second_name, department_id FROM {$wpdb->prefix}arsenal_staff"
);
echo "  Всего записей: " . count( $data_check ) . "\n";
foreach ( $data_check as $row ) {
    echo "  - ID {$row->id}: {$row->first_name} {$row->second_name} → department_id: [{$row->department_id}]\n";
}

// Шаг 3: Сделать колонку nullable (как VARCHAR для начала)
echo "\n🔧 Шаг 3: Изменение колонки на VARCHAR(100) NULL...\n";
$result = $wpdb->query(
    "ALTER TABLE {$wpdb->prefix}arsenal_staff 
     MODIFY COLUMN `department_id` VARCHAR(100) NULL DEFAULT NULL"
);

if ( $result !== false ) {
    echo "  ✅ Успешно!\n";
} else {
    echo "  ❌ Ошибка: " . $wpdb->last_error . "\n";
    exit;
}

// Шаг 4: Очистить пустые значения
echo "\n🧹 Шаг 4: Очистка пустых значений (замена '' на NULL)...\n";
$updated = $wpdb->query(
    "UPDATE {$wpdb->prefix}arsenal_staff 
     SET `department_id` = NULL 
     WHERE `department_id` = '' OR `department_id` IS NULL"
);
echo "  Обновлено записей: {$updated}\n";

// Шаг 5: Изменить тип на INT NULL
echo "\n🔄 Шаг 5: Преобразование колонки в INT NULL...\n";
$result = $wpdb->query(
    "ALTER TABLE {$wpdb->prefix}arsenal_staff 
     MODIFY COLUMN `department_id` INT NULL DEFAULT NULL"
);

if ( $result !== false ) {
    echo "  ✅ Успешно!\n";
} else {
    echo "  ❌ Ошибка: " . $wpdb->last_error . "\n";
    exit;
}

// Шаг 6: Удалить старый FK если существует
echo "\n🗑️  Шаг 6: Удаление старого FK (если существует)...\n";
$constraints = $wpdb->get_results(
    "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = '{$wpdb->prefix}arsenal_staff' 
     AND COLUMN_NAME = 'department_id' 
     AND CONSTRAINT_NAME != 'PRIMARY'"
);

if ( ! empty( $constraints ) ) {
    foreach ( $constraints as $constraint ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}arsenal_staff 
             DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`"
        );
        echo "  ✅ Удален FK: {$constraint->CONSTRAINT_NAME}\n";
    }
} else {
    echo "  ℹ️  Старых FK не найдено\n";
}

// Шаг 7: Добавить новый FK
echo "\n🔗 Шаг 7: Добавление Foreign Key...\n";
$result = $wpdb->query(
    "ALTER TABLE {$wpdb->prefix}arsenal_staff 
     ADD CONSTRAINT `fk_staff_department_id` 
     FOREIGN KEY (`department_id`) 
     REFERENCES {$wpdb->prefix}arsenal_staff_department(`id`) 
     ON DELETE SET NULL 
     ON UPDATE CASCADE"
);

if ( $result !== false ) {
    echo "  ✅ Foreign Key успешно создан!\n";
} else {
    echo "  ❌ Ошибка при создании FK: " . $wpdb->last_error . "\n";
    echo "\n  🔍 Диагностика:\n";
    
    // Проверить, что таблица wp_arsenal_staff_department существует
    $check_table = $wpdb->get_results(
        "SHOW TABLES LIKE '{$wpdb->prefix}arsenal_staff_department'"
    );
    if ( empty( $check_table ) ) {
        echo "     ⚠️  Таблица wp_arsenal_staff_department не существует!\n";
    } else {
        echo "     ✓ Таблица wp_arsenal_staff_department существует\n";
        
        // Проверить колонку id в целевой таблице
        $id_col = $wpdb->get_results(
            "SHOW COLUMNS FROM {$wpdb->prefix}arsenal_staff_department WHERE Field = 'id'"
        );
        if ( ! empty( $id_col ) ) {
            echo "     ✓ Колонка id существует в wp_arsenal_staff_department\n";
        } else {
            echo "     ❌ Колонка id не найдена в wp_arsenal_staff_department!\n";
        }
    }
    exit;
}

// Финальная проверка
echo "\n✨ Шаг 8: Финальная проверка...\n";
$final_info = $wpdb->get_results(
    "SHOW FULL COLUMNS FROM {$wpdb->prefix}arsenal_staff LIKE 'department_id'"
);

if ( ! empty( $final_info ) ) {
    $col = $final_info[0];
    echo "  Тип: {$col->Type}\n";
    echo "  Nullable: {$col->Null}\n";
    echo "  Default: {$col->Default}\n";
}

// Проверить FK
$fks = $wpdb->get_results(
    "SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
     FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_NAME = '{$wpdb->prefix}arsenal_staff' 
     AND COLUMN_NAME = 'department_id'"
);

if ( ! empty( $fks ) ) {
    foreach ( $fks as $fk ) {
        if ( $fk->REFERENCED_TABLE_NAME ) {
            echo "\n  🔗 Foreign Key найден:\n";
            echo "     {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    }
}

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      ✅ ГОТОВО!                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
