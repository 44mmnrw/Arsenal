<?php
/**
 * ИНСТРУКЦИЯ ПО ЗАПУСКУ В WINDOWS POWERSHELL:
 * 
 * 1. Откройте PowerShell (Win + X → Windows PowerShell или PowerShell)
 * 
 * 2. Перейдите в папку database:
 *    cd C:\laragon\www\arsenal\database
 * 
 * 3. Запустите скрипт:
 *    php export_db.php
 * 
 * 4. Бекап БД создастся в этой же папке (database/) с именем:
 *    arsenal_backup_YYYY-MM-DD_HH-mm-ss.sql
 * 
 * РЕЗУЛЬТАТ:
 * - Все 32 таблицы WordPress (включая Arsenal таблицы)
 * - ~85658 строк данных
 * - Размер: ~9 МБ
 * 
 * ВОССТАНОВЛЕНИЕ:
 * - Откройте phpMyAdmin → выберите БД arsenal
 * - SQL → Загрузить файл → выберите .sql файл → Выполнить
 */
require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;

$output_file = dirname(__DIR__) . '/database/arsenal_backup_' . date('Y-m-d_H-i-s') . '.sql';
$fp = fopen($output_file, 'w');

if (!$fp) {
    die("Ошибка: не удалось открыть файл для записи\n");
}

// Получаем ВСЕ таблицы в БД
$tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

$table_count = 0;
$total_rows = 0;

foreach ($tables as $table) {
    $table_name = $table[0];
    $table_count++;
    
    // Получаем CREATE TABLE
    $create = $wpdb->get_results("SHOW CREATE TABLE $table_name", ARRAY_A);
    if ($create) {
        fwrite($fp, "DROP TABLE IF EXISTS `$table_name`;\n");
        fwrite($fp, $create[0]['Create Table'] . ";\n\n");
    }
    
    // Получаем данные
    $rows = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    if ($rows && count($rows) > 0) {
        $total_rows += count($rows);
        $columns = array_keys($rows[0]);
        $col_list = '`' . implode('`, `', $columns) . '`';
        
        fwrite($fp, "INSERT INTO `$table_name` ($col_list) VALUES\n");
        
        $values = array();
        foreach ($rows as $row) {
            $row_values = array();
            foreach ($columns as $col) {
                $val = $row[$col];
                if ($val === NULL) {
                    $row_values[] = 'NULL';
                } else {
                    $row_values[] = "'" . addslashes($val) . "'";
                }
            }
            $values[] = "(" . implode(", ", $row_values) . ")";
        }
        
        fwrite($fp, implode(",\n", $values) . ";\n\n");
    }
}

fclose($fp);

echo "✅ Дамп БД успешно создан!\n";
echo "Файл: $output_file\n";
echo "Размер: " . filesize($output_file) . " байт\n";
echo "Таблиц: $table_count\n";
echo "Строк: $total_rows\n";
?>
