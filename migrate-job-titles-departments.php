<?php
/**
 * Миграция: Связь должностей с отделами
 * 
 * Добавляет поле department_id в таблицу wp_arsenal_staff_job_titles
 * и создаёт Foreign Key связь с wp_arsenal_staff_department
 * 
 * Запуск: php migrate-job-titles-departments.php
 */

require_once dirname( __FILE__ ) . '/wp-load.php';

global $wpdb;

echo "=== Миграция: Связь должностей с отделами ===\n\n";

// Шаг 1: Проверить текущую структуру
echo "Шаг 1: Проверка текущей структуры таблицы job_titles...\n";
$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}arsenal_staff_job_titles" );
$has_department_id = false;

foreach ( $columns as $col ) {
    if ( $col->Field === 'department_id' ) {
        $has_department_id = true;
        echo "   ✅ Поле department_id уже существует\n";
        break;
    }
}

if ( ! $has_department_id ) {
    echo "   Поле department_id отсутствует, добавляю...\n";
    $result = $wpdb->query( "ALTER TABLE {$wpdb->prefix}arsenal_staff_job_titles 
    ADD COLUMN department_id INT NULL 
    AFTER job_title_name" );
    
    if ( $result !== false ) {
        echo "   ✅ Поле department_id добавлено успешно\n";
    } else {
        echo "   ❌ Ошибка при добавлении поля: " . $wpdb->last_error . "\n";
        die();
    }
}

// Шаг 2: Проверить Foreign Key
echo "\nШаг 2: Проверка Foreign Key связи...\n";
$fk_exists = false;
$fks = $wpdb->get_results( "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = '{$wpdb->prefix}arsenal_staff_job_titles' 
AND COLUMN_NAME = 'department_id'" );

foreach ( $fks as $fk ) {
    if ( $fk->CONSTRAINT_NAME === 'fk_job_title_department_id' ) {
        $fk_exists = true;
        echo "   ✅ Foreign Key уже существует: fk_job_title_department_id\n";
        break;
    }
}

if ( ! $fk_exists ) {
    echo "   Foreign Key отсутствует, добавляю...\n";
    
    // Сначала удалить старые FK если есть
    $old_fks = $wpdb->get_results( "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = '{$wpdb->prefix}arsenal_staff_job_titles' 
    AND COLUMN_NAME = 'department_id'
    AND CONSTRAINT_NAME != 'PRIMARY'" );
    
    foreach ( $old_fks as $old_fk ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}arsenal_staff_job_titles 
        DROP FOREIGN KEY " . $old_fk->CONSTRAINT_NAME );
        echo "   Удалён старый FK: " . $old_fk->CONSTRAINT_NAME . "\n";
    }
    
    // Добавить новый FK
    $result = $wpdb->query( "ALTER TABLE {$wpdb->prefix}arsenal_staff_job_titles 
    ADD CONSTRAINT fk_job_title_department_id 
    FOREIGN KEY (department_id) 
    REFERENCES {$wpdb->prefix}arsenal_staff_department(id) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE" );
    
    if ( $result !== false ) {
        echo "   ✅ Foreign Key создан успешно: fk_job_title_department_id\n";
    } else {
        echo "   ❌ Ошибка при создании FK: " . $wpdb->last_error . "\n";
        die();
    }
}

// Шаг 3: Проверить данные
echo "\nШаг 3: Проверка данных в таблице job_titles...\n";
$job_titles = $wpdb->get_results( "SELECT id, job_title_name, department_id FROM {$wpdb->prefix}arsenal_staff_job_titles" );

if ( $job_titles ) {
    foreach ( $job_titles as $job ) {
        $dept_info = $job->department_id ? "department_id={$job->department_id}" : "не привязан";
        echo "   - ID {$job->id}: {$job->job_title_name} ({$dept_info})\n";
    }
    echo "   ✅ Всего должностей: " . count( $job_titles ) . "\n";
} else {
    echo "   ⚠️  Должностей не найдено\n";
}

// Шаг 4: Информация о доступных отделах
echo "\nШаг 4: Доступные отделы для привязки...\n";
$departments = $wpdb->get_results( "SELECT id, department_name FROM {$wpdb->prefix}arsenal_staff_department" );

if ( $departments ) {
    foreach ( $departments as $dept ) {
        echo "   - ID {$dept->id}: {$dept->department_name}\n";
    }
} else {
    echo "   ⚠️  Отделы не найдены (см. add-departments.php)\n";
}

echo "\n=== Миграция завершена успешно ===\n";
echo "\n📝 Следующие шаги:\n";
echo "   1. Откройте WordPress админку\n";
echo "   2. Перейдите в Arsenal Team Manager → Должности\n";
echo "   3. Отредактируйте каждую должность и выберите её отдел\n";
echo "   4. Сохраните изменения\n";
