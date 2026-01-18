<?php
/**
 * Быстрая проверка отделов
 */

require_once 'wp-load.php';

global $wpdb;

echo "=== СОТРУДНИКИ ПО ОТДЕЛАМ ===\n\n";

$staff = $wpdb->get_results("SELECT s.id, s.first_name, s.second_name, s.department_id, s.club_type, d.department_name 
FROM {$wpdb->prefix}arsenal_staff s
LEFT JOIN {$wpdb->prefix}arsenal_staff_department d ON s.department_id = d.id
ORDER BY s.department_id, s.second_name");

if ($staff) {
    foreach ($staff as $row) {
        $dept_name = $row->department_name ?: 'НЕ УКАЗАН';
        $club = $row->club_type ?: '(пусто)';
        echo "ID: {$row->id}, {$row->first_name} {$row->second_name}\n";
        echo "  Отдел ID: {$row->department_id} ({$dept_name})\n";
        echo "  club_type: {$club}\n\n";
    }
}
