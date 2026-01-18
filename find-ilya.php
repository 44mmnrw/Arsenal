<?php
require_once 'wp-load.php';

global $wpdb;

echo "=== ИНФОРМАЦИЯ О ВСЕХ СОТРУДНИКАХ ===\n\n";

$staff = $wpdb->get_results(
	"SELECT s.id, s.first_name, s.second_name, s.department_id, s.club_type, d.department_name
	FROM {$wpdb->prefix}arsenal_staff s
	LEFT JOIN {$wpdb->prefix}arsenal_staff_department d ON s.department_id = d.id
	ORDER BY s.id"
);

foreach ($staff as $row) {
	echo "ID {$row->id}: {$row->first_name} {$row->second_name}\n";
	echo "  Department: ID {$row->department_id} ({$row->department_name})\n";
	echo "  club_type: '" . ($row->club_type ?: '(пусто)') . "'\n\n";
}
