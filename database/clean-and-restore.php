<?php
/**
 * Очистка и восстановление базы данных из бекапа
 */

require_once dirname(__FILE__) . '/../wp-load.php';

global $wpdb;

$backup_file = __DIR__ . '/arsenal_backup_2025-12-27_23-09-29.sql';

if (!file_exists($backup_file)) {
    die("❌ Файл бекапа не найден: $backup_file\n");
}

echo "=" . str_repeat("=", 60) . "\n";
echo "ОЧИСТКА И ВОССТАНОВЛЕНИЕ БАЗЫ ДАННЫХ\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// ШАГ 1: Отключаем foreign keys
echo "1. Отключаю foreign key constraints...\n";
$wpdb->query("SET FOREIGN_KEY_CHECKS = 0");

// ШАГ 2: Получаем список всех таблиц
echo "2. Получаю список таблиц...\n";
$tables = $wpdb->get_col("SHOW TABLES FROM " . DB_NAME);
echo "   Найдено таблиц: " . count($tables) . "\n\n";

// ШАГ 3: Удаляем все таблицы
if (!empty($tables)) {
    echo "3. Удаляю все таблицы...\n";
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS `$table`");
        echo "   ✓ Удалена: $table\n";
    }
    echo "\n";
}

// ШАГ 4: Восстанавливаем из бекапа
echo "4. Восстанавливаю базу из бекапа...\n";
echo "   Файл: $backup_file\n";

$sql = file_get_contents($backup_file);

if (empty($sql)) {
    die("❌ Ошибка: файл бекапа пуст\n");
}

// Разбиваем на отдельные команды
$queries = array_filter(
    array_map(function($q) {
        return trim($q);
    }, explode(';', $sql))
);

$count = 0;
$errors = 0;
$error_log = array();

foreach ($queries as $query) {
    if (empty($query)) {
        continue;
    }
    
    $result = $wpdb->query($query);
    
    if ($wpdb->last_error) {
        $errors++;
        $error_log[] = $wpdb->last_error;
    } else {
        $count++;
    }
    
    if ($count % 100 == 0) {
        echo "   ✓ Выполнено: $count запросов\n";
    }
}

// ШАГ 5: Включаем foreign keys обратно
echo "\n5. Включаю foreign key constraints обратно...\n";
$wpdb->query("SET FOREIGN_KEY_CHECKS = 1");

// Итоговый отчет
echo "\n" . str_repeat("=", 60) . "\n";
echo "РЕЗУЛЬТАТЫ ВОССТАНОВЛЕНИЯ\n";
echo str_repeat("=", 60) . "\n";
echo "✓ Успешно выполнено запросов: $count\n";

if ($errors > 0) {
    echo "⚠ Ошибок: $errors\n";
    echo "\nОшибки:\n";
    foreach ($error_log as $i => $err) {
        echo "  " . ($i + 1) . ". $err\n";
    }
}

echo str_repeat("=", 60) . "\n";
echo "✅ Восстановление завершено!\n";
echo str_repeat("=", 60) . "\n";
