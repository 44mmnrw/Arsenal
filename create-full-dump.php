<?php
/**
 * Полный дамп всех Arsenal таблиц из локальной БД
 * Для полной переустановки на сервере
 */

require_once 'wp-load.php';
global $wpdb;

echo "📦 Создаю полный дамп локальной БД Arsenal...\n";

$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);

$sql = '';
$sql .= "-- Arsenal Database Complete Dump\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

$count = 0;
foreach ($tables as $table_row) {
    $table = $table_row[0];
    
    if (strpos($table, 'wp_arsenal_') === 0) {
        $count++;
        echo "✓ Таблица: $table\n";
        
        // DROP TABLE
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // CREATE TABLE
        $create = $wpdb->get_results("SHOW CREATE TABLE $table", ARRAY_N);
        if ($create) {
            $sql .= $create[0][1] . ";\n\n";
        }
        
        // INSERT DATA
        $rows = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
        foreach ($rows as $row) {
            $values = [];
            foreach ($row as $v) {
                if ($v === null) {
                    $values[] = 'NULL';
                } else {
                    $v = $wpdb->_real_escape($v);
                    $values[] = "'$v'";
                }
            }
            $cols = implode(',', array_keys($row));
            $vals = implode(',', $values);
            $sql .= "INSERT INTO $table ($cols) VALUES ($vals);\n";
        }
        $sql .= "\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents('database/arsenal_full_correct.sql', $sql);

echo "\n✅ Дамп создан: database/arsenal_full_correct.sql\n";
echo "📊 Таблиц: $count\n";
echo "📏 Размер: " . round(strlen($sql) / 1024 / 1024, 2) . " MB\n";
