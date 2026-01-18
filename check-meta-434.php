<?php
require_once 'wp-load.php';

global $wpdb;

echo "=== ПРОВЕРКА ЧТО СОХРАНЕНО В БД ===\n\n";

$page_id = 434;

// Прямой запрос к postmeta
$result = $wpdb->get_results($wpdb->prepare(
	"SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key LIKE '%arsenal_staff%'",
	$page_id
));

echo "В postmeta для страницы 434:\n";
foreach ($result as $row) {
	echo "  {$row->meta_key} = '{$row->meta_value}'\n";
}

echo "\n";

// Через get_post_meta
$dept = get_post_meta(434, '_arsenal_staff_department_filter', true);
$club = get_post_meta(434, '_arsenal_staff_club_type_filter', true);

echo "Через get_post_meta:\n";
echo "  _arsenal_staff_department_filter = '{$dept}'\n";
echo "  _arsenal_staff_club_type_filter = '{$club}'\n";
