<?php
require_once 'wp-load.php';

$page_id = 224;

echo "=== ИСПРАВЛЕНИЕ ФИЛЬТРА ===\n\n";

// Установляем правильный отдел (1 = Тренерский штаб)
update_post_meta($page_id, '_arsenal_staff_department_filter', '1');

echo "✅ Фильтр отдела исправлен\n";
echo "   department_id = 1 (Тренерский штаб)\n\n";

// Проверяем результат
global $wpdb;
$dept = get_post_meta($page_id, '_arsenal_staff_department_filter', true);
$sql = $wpdb->prepare("SELECT s.* FROM {$wpdb->prefix}arsenal_staff s WHERE s.department_id = %d", intval($dept));
$results = $wpdb->get_results($sql);

echo "Теперь на странице будет показано " . count($results) . " сотрудников:\n";
foreach ($results as $row) {
	echo "  - {$row->first_name} {$row->second_name} (club_type: '{$row->club_type}')\n";
}
