<?php
require_once 'wp-load.php';

global $wpdb;

$page_id = 224;

echo "=== ПОЛНАЯ ДИАГНОСТИКА ===\n\n";

// 1. Что в post_meta?
echo "1. МЕТАОКС ЗНАЧЕНИЯ НА СТРАНИЦЕ (ID 224):\n";
$dept = get_post_meta($page_id, '_arsenal_staff_department_filter', true);
$club = get_post_meta($page_id, '_arsenal_staff_club_type_filter', true);
echo "  _arsenal_staff_department_filter = '" . var_export($dept, true) . "'\n";
echo "  _arsenal_staff_club_type_filter = '" . var_export($club, true) . "'\n\n";

// 2. Какие сотрудники есть в БД?
echo "2. ВСЕ СОТРУДНИКИ В БД:\n";
$staff = $wpdb->get_results("SELECT id, first_name, second_name, department_id, club_type FROM {$wpdb->prefix}arsenal_staff");
foreach ($staff as $row) {
	echo "  ID {$row->id}: {$row->first_name} {$row->second_name} | dept={$row->department_id} | club_type='{$row->club_type}'\n";
}

// 3. SQL запрос кот будет выполнен
echo "\n3. SQL КОТОРОЙ БУДЕТ ВЫПОЛНЕН:\n";
$where_conditions = array();
if (!empty($dept)) {
	$where_conditions[] = $wpdb->prepare("s.department_id = %d", intval($dept));
}
if (!empty($club)) {
	$where_conditions[] = $wpdb->prepare("s.club_type = %s", sanitize_text_field($club));
}
$where_clause = '';
if (!empty($where_conditions)) {
	$where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
}
$sql = "SELECT s.* FROM {$wpdb->prefix}arsenal_staff s {$where_clause}";
echo "  " . $sql . "\n\n";

// 4. Результат запроса
echo "4. РЕЗУЛЬТАТЫ ЗАПРОСА:\n";
$results = $wpdb->get_results($sql);
echo "  Найдено: " . count($results) . " сотрудников\n";
foreach ($results as $row) {
	echo "  - {$row->first_name} {$row->second_name} (club_type: '{$row->club_type}')\n";
}
