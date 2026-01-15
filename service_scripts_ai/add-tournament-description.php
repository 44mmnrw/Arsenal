<?php
/**
 * Скрипт: Добавление поля description в таблицу wp_arsenal_tournaments
 * 
 * Выполнение: php service_scripts_ai/add-tournament-description.php
 */

// Загружаем WordPress
require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;
$table_name = $wpdb->prefix . 'arsenal_tournaments';

// Проверяем текущую структуру
$columns = $wpdb->get_results("DESCRIBE $table_name");
$has_description = false;

echo "=== Текущая структура таблицы $table_name ===\n";
foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
    if ($col->Field === 'description') {
        $has_description = true;
    }
}

// Если поле уже есть, выходим
if ($has_description) {
    echo "\n✓ Поле 'description' уже существует.\n";
    exit;
}

// Добавляем поле description
echo "\n⏳ Добавляем поле 'description'...\n";
$result = $wpdb->query("
    ALTER TABLE $table_name 
    ADD COLUMN description LONGTEXT NULL DEFAULT NULL
");

if ($result !== false) {
    echo "✓ Поле успешно добавлено!\n\n";
    
    // Проверяем результат
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    echo "=== Новая структура таблицы ===\n";
    foreach ($columns as $col) {
        echo "- {$col->Field} ({$col->Type})\n";
    }
} else {
    echo "✗ Ошибка при добавлении поля: " . $wpdb->last_error . "\n";
}
