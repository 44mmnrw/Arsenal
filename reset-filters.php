<?php
/**
 * Сброс фильтра отдела на странице персонала
 */

require_once 'wp-load.php';

echo "=== СБРОС ФИЛЬТРА ОТДЕЛА ===\n\n";

$page_id = 224; // ID страницы персонала

// Удаляем метаокс фильтра отдела
delete_post_meta($page_id, '_arsenal_staff_department_filter');

echo "✅ Фильтр отдела сброшен для страницы ID: {$page_id}\n";

// Проверяем что осталось
$dept_filter = get_post_meta($page_id, '_arsenal_staff_department_filter', true);
$club_type_filter = get_post_meta($page_id, '_arsenal_staff_club_type_filter', true);

echo "\nТекущие фильтры:\n";
echo "  Department filter: '" . ($dept_filter ?: '(пусто)') . "'\n";
echo "  Club type filter: '" . ($club_type_filter ?: '(пусто)') . "'\n";
