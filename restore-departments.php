<?php
require_once 'wp-load.php';

global $wpdb;

echo "=== ВОССТАНОВЛЕНИЕ department_id ===\n\n";

// Восстанавливаем правильные отделы
$updates = array(
	1 => array('id' => 1, 'dept' => 1, 'name' => 'Вячеслав Вашкевич'),      // Тренерский штаб
	3 => array('id' => 3, 'dept' => 1, 'name' => 'Юрий Гагарин'),           // Тренерский штаб
	4 => array('id' => 4, 'dept' => 4, 'name' => 'Илон Маск'),              // Медицинский департамент
);

foreach ($updates as $data) {
	$wpdb->update(
		$wpdb->prefix . 'arsenal_staff',
		array('department_id' => $data['dept']),
		array('id' => $data['id'])
	);
	echo "✅ {$data['name']} → department_id = {$data['dept']}\n";
}

// Проверяем результат
echo "\n" . str_repeat("=", 60) . "\n";
echo "РЕЗУЛЬТАТ ПОСЛЕ ВОССТАНОВЛЕНИЯ:\n";
$staff = $wpdb->get_results("SELECT id, first_name, second_name, department_id, club_type FROM {$wpdb->prefix}arsenal_staff");
foreach ($staff as $row) {
	echo "  ID {$row->id}: {$row->first_name} {$row->second_name} | dept={$row->department_id} | club_type='{$row->club_type}'\n";
}
