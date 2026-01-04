<?php
/**
 * Скрипт восстановления БД Arsenal из бекапа
 * Использование: php restore-backup.php
 */

require_once __DIR__ . '/../wp-load.php';

global $wpdb;

// Ищем самый свежий файл бекапа
$backup_dir = __DIR__;
$backup_files = glob($backup_dir . '/arsenal_backup_*.sql');

if (empty($backup_files)) {
    die("❌ Файлы бекапа не найдены в папке: $backup_dir\n");
}

// Сортируем по времени модификации (самый свежий первым)
usort($backup_files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$backup_file = $backup_files[0];
$backup_time = date('Y-m-d H:i:s', filemtime($backup_file));

echo "🔄 Восстановление БД Arsenal из бекапа...\n";
echo "📄 Файл: " . basename($backup_file) . "\n";
echo "🕐 Время создания: $backup_time\n";

$sql_content = file_get_contents($backup_file);
if ($sql_content === false) {
    die("❌ Не удалось прочитать файл бекапа\n");
}

// Отключаем внешние ключи
$wpdb->query("SET FOREIGN_KEY_CHECKS=0");

// Разбиваем на отдельные запросы
$queries = array_filter(array_map('trim', explode(";\n", $sql_content)));

$count = 0;
$errors = 0;

foreach ($queries as $query) {
    if (empty($query)) continue;
    
    $result = $wpdb->query($query);
    if ($result === false) {
        echo "❌ Ошибка: " . $wpdb->last_error . "\n";
        echo "   Запрос: " . substr($query, 0, 100) . "...\n";
        $errors++;
    } else {
        $count++;
        if ($count % 100 === 0) {
            echo "✓ Выполнено $count запросов...\n";
        }
    }
}

// Включаем внешние ключи обратно
$wpdb->query("SET FOREIGN_KEY_CHECKS=1");

echo "\n✅ Восстановление завершено!\n";
echo "📊 Всего запросов выполнено: $count\n";
if ($errors > 0) {
    echo "❌ Ошибок: $errors\n";
}
echo "\n";
