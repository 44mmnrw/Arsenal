<?php
/**
 * Отладка: Проверка обновления сотрудника
 */

require_once dirname( __FILE__ ) . '/wp-load.php';
require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

global $wpdb;

echo "=== Отладка обновления сотрудника ===\n\n";

// Получить первого сотрудника
$staff = Arsenal_Staff_Manager::get_staff( array( 'limit' => 1 ) );

if ( ! $staff ) {
    echo "❌ Сотрудники не найдены\n";
    die();
}

$person = $staff[0];
echo "Сотрудник: {$person->first_name} {$person->second_name} (ID {$person->id})\n\n";

// Попробовать обновить с department_id = 1
$update_data = array(
    'department_id' => 1,
);

echo "Попытка обновления:\n";
echo "  department_id = 1\n";

$result = Arsenal_Staff_Manager::update_staff( $person->id, $update_data );

echo "Результат: " . ( $result !== false ? "✅ " . $result . " строк обновлено" : "❌ false" ) . "\n";

if ( $wpdb->last_error ) {
    echo "DB Error: " . $wpdb->last_error . "\n";
}

echo "\nПроверка обновления:\n";
$updated_staff = Arsenal_Staff_Manager::get_staff_member( $person->id );
echo "  department_id в БД: " . $updated_staff->department_id . "\n";
