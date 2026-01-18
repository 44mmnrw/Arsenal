<?php
/**
 * Диагностический скрипт для проверки фильтра club_type
 */

require_once 'wp-load.php';

global $wpdb;

echo "=== ДИАГНОСТИКА ФИЛЬТРА club_type ===\n\n";

// 1. Проверяем содержимое таблицы wp_arsenal_staff
echo "1. Данные в wp_arsenal_staff:\n";
echo str_repeat("-", 80) . "\n";
$staff = $wpdb->get_results("SELECT id, first_name, second_name, club_type, department_id FROM {$wpdb->prefix}arsenal_staff");
if ($staff) {
    foreach ($staff as $row) {
        echo "ID: {$row->id}, Имя: {$row->first_name} {$row->second_name}\n";
        echo "  club_type: '{$row->club_type}' (строка, длина: " . strlen($row->club_type) . " символов)\n";
        echo "  department_id: {$row->department_id}\n\n";
    }
} else {
    echo "Нет данных в таблице!\n\n";
}

// 2. Проверяем фильтры на странице персонала
echo "2. Фильтры на странице персонала (ID 224):\n";
echo str_repeat("-", 80) . "\n";
$page_id = 224;
$dept_filter = get_post_meta($page_id, '_arsenal_staff_department_filter', true);
$club_type_filter = get_post_meta($page_id, '_arsenal_staff_club_type_filter', true);

echo "Department filter meta: '{$dept_filter}' (тип: " . gettype($dept_filter) . ", пусто: " . (empty($dept_filter) ? 'да' : 'нет') . ")\n";
echo "Club type filter meta: '{$club_type_filter}' (тип: " . gettype($club_type_filter) . ", пусто: " . (empty($club_type_filter) ? 'да' : 'нет') . ")\n\n";

// 3. Проверяем SQL запросы
echo "3. SQL запросы:\n";
echo str_repeat("-", 80) . "\n";

// Без фильтров
echo "A) БЕЗ ФИЛЬТРОВ:\n";
$sql_no_filter = "SELECT s.*, jt.job_title_name as job_title, CONCAT(s.first_name, ' ', s.second_name) as full_name
    FROM {$wpdb->prefix}arsenal_staff s
    LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
    ORDER BY jt.job_title_name ASC, s.second_name ASC, s.first_name ASC";
echo "SQL: " . $sql_no_filter . "\n";
$result_no_filter = $wpdb->get_results($sql_no_filter);
echo "Результат: " . count($result_no_filter) . " сотрудников\n";
foreach ($result_no_filter as $row) {
    echo "  - {$row->full_name} (club_type: '{$row->club_type}')\n";
}

// С фильтром по club_type
echo "\nB) С ФИЛЬТРОМ club_type = 'Основной клуб':\n";
$sql_with_club = $wpdb->prepare(
    "SELECT s.*, jt.job_title_name as job_title, CONCAT(s.first_name, ' ', s.second_name) as full_name
    FROM {$wpdb->prefix}arsenal_staff s
    LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
    WHERE s.club_type = %s
    ORDER BY jt.job_title_name ASC, s.second_name ASC, s.first_name ASC",
    'Основной клуб'
);
echo "SQL: " . $sql_with_club . "\n";
$result_with_club = $wpdb->get_results($sql_with_club);
echo "Результат: " . count($result_with_club) . " сотрудников\n";
foreach ($result_with_club as $row) {
    echo "  - {$row->full_name} (club_type: '{$row->club_type}')\n";
}

// С фильтром по club_type = СДЮШ
echo "\nC) С ФИЛЬТРОМ club_type = 'СДЮШ':\n";
$sql_with_sdush = $wpdb->prepare(
    "SELECT s.*, jt.job_title_name as job_title, CONCAT(s.first_name, ' ', s.second_name) as full_name
    FROM {$wpdb->prefix}arsenal_staff s
    LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
    WHERE s.club_type = %s
    ORDER BY jt.job_title_name ASC, s.second_name ASC, s.first_name ASC",
    'СДЮШ'
);
echo "SQL: " . $sql_with_sdush . "\n";
$result_with_sdush = $wpdb->get_results($sql_with_sdush);
echo "Результат: " . count($result_with_sdush) . " сотрудников\n";
foreach ($result_with_sdush as $row) {
    echo "  - {$row->full_name} (club_type: '{$row->club_type}')\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
