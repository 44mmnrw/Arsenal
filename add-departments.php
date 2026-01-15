<?php
/**
 * Скрипт для добавления отделов в wp_arsenal_staff_department
 */

define( 'WP_USE_THEMES', false );
require( __DIR__ . '/wp-load.php' );

global $wpdb;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║     Добавление отделов в wp_arsenal_staff_department           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Отделы для добавления
$departments = array(
    array(
        'department_name' => 'Тренерский штаб',
        'description' => 'Отдел тренерского персонала',
        'sort_order' => 1,
    ),
    array(
        'department_name' => 'Основной персонал',
        'description' => 'Административный и вспомогательный персонал',
        'sort_order' => 2,
    ),
);

echo "📝 Добавляем отделы...\n\n";

foreach ( $departments as $dept ) {
    // Проверить, существует ли уже
    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}arsenal_staff_department WHERE department_name = %s",
            $dept['department_name']
        )
    );

    if ( $exists ) {
        echo "  ℹ️  '{$dept['department_name']}' уже существует (ID: {$exists})\n";
        continue;
    }

    // Добавить запись
    $result = $wpdb->insert(
        "{$wpdb->prefix}arsenal_staff_department",
        array(
            'department_name' => $dept['department_name'],
            'description' => $dept['description'],
            'sort_order' => $dept['sort_order'],
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%d', '%s', '%s' )
    );

    if ( $result ) {
        $id = $wpdb->insert_id;
        echo "  ✅ Добавлен: '{$dept['department_name']}' (ID: {$id})\n";
    } else {
        echo "  ❌ Ошибка при добавлении '{$dept['department_name']}': " . $wpdb->last_error . "\n";
    }
}

// Показать все отделы
echo "\n📊 Все отделы в таблице:\n\n";
$all_departments = $wpdb->get_results(
    "SELECT id, department_name, description, sort_order, created_at FROM {$wpdb->prefix}arsenal_staff_department ORDER BY sort_order"
);

if ( ! empty( $all_departments ) ) {
    foreach ( $all_departments as $dept ) {
        echo "  #{$dept->id} | {$dept->department_name}\n";
        echo "      Описание: {$dept->description}\n";
        echo "      Порядок: {$dept->sort_order}\n";
        echo "      Создано: {$dept->created_at}\n\n";
    }
} else {
    echo "  ❌ Отделы не найдены!\n";
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      ✅ ГОТОВО!                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
