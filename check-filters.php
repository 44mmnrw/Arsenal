<?php
require_once 'wp-load.php';

$page_id = 224;
$dept_filter = get_post_meta($page_id, '_arsenal_staff_department_filter', true);
$club_type_filter = get_post_meta($page_id, '_arsenal_staff_club_type_filter', true);

echo "Текущие фильтры на странице персонала (ID 224):\n";
echo "================================================\n";
echo "Department filter: '" . ($dept_filter ?: '(пусто)') . "'\n";
echo "Club type filter: '" . ($club_type_filter ?: '(пусто)') . "'\n";
