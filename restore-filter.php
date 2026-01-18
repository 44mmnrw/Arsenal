<?php
/**
 * Восстановление фильтра отдела на странице персонала
 */

require_once 'wp-load.php';

echo "=== ВОССТАНОВЛЕНИЕ ФИЛЬТРА ОТДЕЛА ===\n\n";

$page_id = 224; // ID страницы персонала

// Восстанавливаем значение department_id = 4
update_post_meta($page_id, '_arsenal_staff_department_filter', '4');

echo "✅ Фильтр отдела восстановлен для страницы ID: {$page_id}\n";
echo "   department_id = 4 (Медицинский департамент)\n";

// Проверяем что восстановилось
$dept_filter = get_post_meta($page_id, '_arsenal_staff_department_filter', true);
$club_type_filter = get_post_meta($page_id, '_arsenal_staff_club_type_filter', true);

echo "\nТекущие фильтры:\n";
echo "  Department filter: '{$dept_filter}'\n";
echo "  Club type filter: '" . ($club_type_filter ?: '(пусто)') . "'\n";
