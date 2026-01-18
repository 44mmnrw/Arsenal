<?php
require_once 'wp-load.php';

global $wpdb;

$page_id = 224;

echo "=== ПРОВЕРКА ФИЛЬТРА club_type ===\n\n";

// Получаем текущий фильтр
$club_type_filter = get_post_meta($page_id, '_arsenal_staff_club_type_filter', true);

echo "1. Текущее значение фильтра в post_meta:\n";
echo "   _arsenal_staff_club_type_filter = '" . ($club_type_filter ?: '(пусто)') . "'\n\n";

// Тестируем SQL с этим фильтром
echo "2. Проверка SQL с текущим фильтром:\n";
if (!empty($club_type_filter)) {
	$sql = $wpdb->prepare(
		"SELECT s.first_name, s.second_name, s.club_type FROM {$wpdb->prefix}arsenal_staff s WHERE s.club_type = %s",
		$club_type_filter
	);
	echo "   SQL: " . $sql . "\n";
	$results = $wpdb->get_results($sql);
	echo "   Результат: " . count($results) . " сотрудников\n";
	foreach ($results as $row) {
		echo "   - {$row->first_name} {$row->second_name} ({$row->club_type})\n";
	}
} else {
	echo "   Фильтр пуст - показываются все\n";
}

// Тестируем с конкретными значениями
echo "\n3. Тест с конкретными значениями:\n";
$test_values = array('Основной клуб', 'СДЮШ');
foreach ($test_values as $value) {
	$sql = $wpdb->prepare(
		"SELECT COUNT(*) as cnt FROM {$wpdb->prefix}arsenal_staff s WHERE s.club_type = %s",
		$value
	);
	$count = $wpdb->get_var($sql);
	echo "   club_type = '{$value}': " . $count . " сотрудников\n";
}

echo "\n✅ ОТВЕТ: фильтр " . (!empty($club_type_filter) ? "УСТАНОВЛЕН (" . $club_type_filter . ")" : "НЕ УСТАНОВЛЕН или ПУСТ") . "\n";
