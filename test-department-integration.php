<?php
/**
 * Проверка интеграции department в админ-интерфейс
 */

// Инициализируем WordPress
require_once dirname( __FILE__ ) . '/wp-load.php';

// Подключим класс Arsenal_Staff_Manager
require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

echo "=== Проверка интеграции Department в админ-интерфейс ===\n\n";

// Проверка 1: Получить отделы
echo "1. Получение отделов:\n";
$departments = Arsenal_Staff_Manager::get_departments( true );
if ( $departments ) {
    foreach ( $departments as $dept ) {
        echo "   - ID: {$dept->id}, Name: {$dept->department_name}\n";
    }
    echo "   ✅ Отделы получены успешно\n\n";
} else {
    echo "   ❌ Отделы не найдены\n\n";
}

// Проверка 2: Получить сотрудников с department_id
echo "2. Получение сотрудников с department_id:\n";
$staff = Arsenal_Staff_Manager::get_staff( array( 'limit' => 10 ) );
if ( $staff ) {
    foreach ( $staff as $person ) {
        $dept = $person->department_id ? Arsenal_Staff_Manager::get_department( $person->department_id ) : null;
        echo "   - {$person->first_name} {$person->second_name}: department_id={$person->department_id}, dept_name=" . ( $dept ? $dept->department_name : '—' ) . "\n";
    }
    echo "   ✅ Сотрудники получены успешно\n\n";
} else {
    echo "   ❌ Сотрудники не найдены\n\n";
}

// Проверка 3: Методы в классе
echo "3. Проверка методов в Arsenal_Staff_Manager:\n";
$methods = array( 'get_departments', 'get_department' );
foreach ( $methods as $method ) {
    if ( method_exists( 'Arsenal_Staff_Manager', $method ) ) {
        echo "   ✅ Метод '$method' существует\n";
    } else {
        echo "   ❌ Метод '$method' НЕ найден\n";
    }
}

echo "\n=== Проверка завершена ===\n";
