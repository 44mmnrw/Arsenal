<?php
require 'wp-load.php';
global $wpdb;

echo "\n=== wp_arsenal_staff_job_titles ===\n";
$result = $wpdb->get_results("DESCRIBE {$wpdb->prefix}arsenal_staff_job_titles");
foreach ($result as $col) {
    $nullable = ($col->Null === 'YES') ? 'NULL' : 'NOT NULL';
    echo "  {$col->Field}: {$col->Type} ({$nullable})";
    if (!empty($col->Key)) {
        echo " [{$col->Key}]";
    }
    echo "\n";
}

echo "\n=== wp_arsenal_staff ===\n";
$result = $wpdb->get_results("DESCRIBE {$wpdb->prefix}arsenal_staff");
foreach ($result as $col) {
    $nullable = ($col->Null === 'YES') ? 'NULL' : 'NOT NULL';
    echo "  {$col->Field}: {$col->Type} ({$nullable})";
    if (!empty($col->Key)) {
        echo " [{$col->Key}]";
    }
    echo "\n";
}

echo "\n=== Foreign Keys ===\n";
$fks = $wpdb->get_results(
    "SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
     FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE() 
     AND (TABLE_NAME = '{$wpdb->prefix}arsenal_staff' OR TABLE_NAME = '{$wpdb->prefix}arsenal_staff_job_titles')
     AND REFERENCED_TABLE_NAME IS NOT NULL"
);

if (empty($fks)) {
    echo "  No foreign keys found\n";
} else {
    foreach ($fks as $fk) {
        echo "  {$fk->CONSTRAINT_NAME}: {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    }
}

echo "\n";
