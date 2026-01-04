<?php
/**
 * Экспорт таблиц Arsenal с данными и отключением FK проверок
 */
require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;

$output_file = dirname(__DIR__) . '/database/arsenal_full_dump_' . date('Y-m-d_H-i-s') . '.sql';
$fp = fopen($output_file, 'w');

if (!$fp) {
    die("Ошибка: не удалось открыть файл для записи\n");
}

// Добавляем в начало
fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
fwrite($fp, "SET UNIQUE_CHECKS=0;\n\n");

// Получаем все таблицы arsenal_*
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}arsenal_%'", ARRAY_N);

echo "Найдено таблиц: " . count($tables) . "\n";

foreach ($tables as $table) {
    $table_name = $table[0];
    echo "Экспортируем $table_name...";
    
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
        echo " " . count($rows) . " строк\n";
    } else {
        echo " 0 строк\n";
    }
}

// Заканчиваем
fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
fwrite($fp, "SET UNIQUE_CHECKS=1;\n");

fclose($fp);

echo "\n✅ Дамп создан: $output_file\n";
echo "Размер: " . (filesize($output_file) / 1024 / 1024) . " МБ\n";
?>
