<?php
/**
 * Добавление тестового сотрудника для проверки фильтров
 */

require_once 'wp-load.php';

global $wpdb;

echo "=== ДОБАВЛЕНИЕ ТЕСТОВОГО СОТРУДНИКА ===\n\n";

// Добавляем сотрудника в Медицинский департамент с club_type = "Основной клуб"
$staff_data = array(
    'first_name'      => 'Анатолий',
    'second_name'     => 'Петровский',
    'department_id'   => 4,  // Медицинский департамент
    'job_title_id'    => 2,  // Какая-нибудь должность
    'club_type'       => 'Основной клуб',
    'birth_date'      => '1980-05-15',
    'phone'           => '+375 29 123-45-67',
    'email'           => 'petrovskiy@arsenal.by',
    'photo_url'       => '',
    'created_at'      => current_time('mysql'),
    'updated_at'      => current_time('mysql'),
);

$inserted = $wpdb->insert(
    $wpdb->prefix . 'arsenal_staff',
    $staff_data
);

if ($inserted) {
    $new_id = $wpdb->insert_id;
    echo "✅ Сотрудник добавлен успешно!\n\n";
    echo "ID: {$new_id}\n";
    echo "Имя: {$staff_data['first_name']} {$staff_data['second_name']}\n";
    echo "Отдел: Медицинский департамент (ID: 4)\n";
    echo "club_type: {$staff_data['club_type']}\n";
} else {
    echo "❌ Ошибка при добавлении!\n";
    echo "Ошибка БД: " . $wpdb->last_error . "\n";
}

// Проверяем текущие записи в отделе 4
echo "\n" . str_repeat("=", 60) . "\n";
echo "Текущие сотрудники в Медицинском департаменте (ID: 4):\n";
echo str_repeat("=", 60) . "\n";

$staff = $wpdb->get_results("SELECT id, first_name, second_name, club_type FROM {$wpdb->prefix}arsenal_staff WHERE department_id = 4");
foreach ($staff as $row) {
    echo "{$row->id}. {$row->first_name} {$row->second_name} - {$row->club_type}\n";
}
