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
 *    php export_db_simple.php
 * 
 * 4. Бекап БД создастся в этой же папке (database/) с именем:
 *    arsenal_backup_arsenal_YYYY-MM-DD_HH-mm-ss.sql
 * 
 * РЕЗУЛЬТАТ:
 * - Только таблицы Arsenal (wp_arsenal_*)
 * - Все данные игроков, матчей, лиг, турниров
 * - Размер: ~8.8 МБ
 * 
 * ВОССТАНОВЛЕНИЕ:
 * - Откройте phpMyAdmin → выберите БД arsenal
 * - SQL → Загрузить файл → выберите .sql файл → Выполнить
 */
require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;

$output_file = dirname(__DIR__) . '/database/arsenal_backup_arsenal_' . date('Y-m-d_H-i-s') . '.sql';
$fp = fopen($output_file, 'w');

if (!$fp) {
    die("Ошибка: не удалось открыть файл для записи\n");
}

// Получаем все таблицы arsenal_*
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}arsenal_%'", ARRAY_N);

foreach ($tables as $table) {
    $table_name = $table[0];
    
    // Получаем CREATE TABLE
    $create = $wpdb->get_results("SHOW CREATE TABLE $table_name", ARRAY_A);
    if ($create) {
        fwrite($fp, "DROP TABLE IF EXISTS `$table_name`;\n");
        fwrite($fp, $create[0]['Create Table'] . ";\n\n");
    }
    
    // Получаем данные
    $rows = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    if ($rows && count($rows) > 0) {
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

echo "✅ Дамп успешно создан: $output_file\n";
echo "Размер: " . filesize($output_file) . " байт\n";
?>
