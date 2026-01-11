<?php
/**
 * Arsenal Staff Management - Service Script
 * 
 * Управление таблицами сотрудников:
 * - wp_arsenal_staff (сотрудники)
 * - wp_arsenal_staff_job_titles (должности)
 * 
 * Использование:
 *   php service_scripts_ai/staff-manager.php
 *
 * @package Arsenal
 */

// Загружаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

// Проверка прав доступа (только из командной строки)
if ( php_sapi_name() !== 'cli' ) {
	wp_die( 'Этот скрипт можно запускать только из командной строки' );
}

global $wpdb;

/**
 * Главное меню
 */
function staff_menu() {
	echo "\n";
	echo "===========================================\n";
	echo "  Arsenal FC - Управление сотрудниками\n";
	echo "===========================================\n";
	echo "1. Проверить структуру таблиц\n";
	echo "2. Добавить пример должности\n";
	echo "3. Добавить пример сотрудника\n";
	echo "4. Показать все должности\n";
	echo "5. Показать всех сотрудников\n";
	echo "6. Очистить таблицы сотрудников\n";
	echo "0. Выход\n";
	echo "===========================================\n";
	echo "Выберите опцию (0-6): ";
	
	$choice = trim( fgets( STDIN ) );
	
	switch ( $choice ) {
		case '1':
			check_tables();
			break;
		case '2':
			add_sample_job_title();
			break;
		case '3':
			add_sample_staff();
			break;
		case '4':
			show_job_titles();
			break;
		case '5':
			show_staff();
			break;
		case '6':
			clear_tables();
			break;
		case '0':
			echo "\nДо свидания!\n\n";
			exit;
		default:
			echo "Неверный выбор\n";
	}
	
	staff_menu();
}

/**
 * Проверить структуру таблиц
 */
function check_tables() {
	global $wpdb;
	
	echo "\n--- Проверка структуры таблиц ---\n\n";
	
	// Таблица должностей
	echo "1. Таблица wp_arsenal_staff_job_titles:\n";
	$result = $wpdb->get_results( "DESCRIBE {$wpdb->prefix}arsenal_staff_job_titles" );
	
	if ( empty( $result ) ) {
		echo "   ❌ Таблица не существует\n";
	} else {
		echo "   ✓ Существует\n";
		echo "   Поля:\n";
		foreach ( $result as $field ) {
			echo "      - {$field->Field} ({$field->Type})\n";
		}
		
		// Количество записей
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff_job_titles" );
		echo "   Записей: $count\n";
	}
	
	echo "\n2. Таблица wp_arsenal_staff:\n";
	$result = $wpdb->get_results( "DESCRIBE {$wpdb->prefix}arsenal_staff" );
	
	if ( empty( $result ) ) {
		echo "   ❌ Таблица не существует\n";
	} else {
		echo "   ✓ Существует\n";
		echo "   Поля:\n";
		foreach ( $result as $field ) {
			echo "      - {$field->Field} ({$field->Type})\n";
		}
		
		// Количество записей
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff" );
		echo "   Записей: $count\n";
	}
	
	echo "\n";
}

/**
 * Добавить пример должности
 */
function add_sample_job_title() {
	global $wpdb;
	
	echo "\nДобавить новую должность:\n";
	echo "Название должности: ";
	$title = trim( fgets( STDIN ) );
	
	if ( empty( $title ) ) {
		echo "Ошибка: название должности не может быть пустым\n";
		return;
	}
	
	$result = $wpdb->insert(
		$wpdb->prefix . 'arsenal_staff_job_titles',
		array(
			'job_title_name' => $title,
			'is_active' => 1,
		),
		array( '%s', '%d' )
	);
	
	if ( $result ) {
		echo "✓ Должность '$title' успешно добавлена (ID: {$wpdb->insert_id})\n";
	} else {
		echo "❌ Ошибка добавления: {$wpdb->last_error}\n";
	}
}

/**
 * Добавить пример сотрудника
 */
function add_sample_staff() {
	global $wpdb;
	
	// Получить доступные должности
	$job_titles = $wpdb->get_results( 
		"SELECT id, job_title_name FROM {$wpdb->prefix}arsenal_staff_job_titles WHERE is_active = 1" 
	);
	
	if ( empty( $job_titles ) ) {
		echo "\n❌ Нет доступных должностей. Сначала добавьте должность (опция 2)\n";
		return;
	}
	
	echo "\nДобавить нового сотрудника:\n";
	echo "Имя: ";
	$first_name = trim( fgets( STDIN ) );
	
	echo "Фамилия: ";
	$second_name = trim( fgets( STDIN ) );
	
	echo "\nДоступные должности:\n";
	foreach ( $job_titles as $job ) {
		echo "  {$job->id}. {$job->job_title_name}\n";
	}
	echo "Выберите должность (ID): ";
	$job_title_id = (int) trim( fgets( STDIN ) );
	
	echo "Дата рождения (YYYY-MM-DD) или пусто: ";
	$birth_date = trim( fgets( STDIN ) );
	$birth_date = empty( $birth_date ) ? null : $birth_date;
	
	echo "Дата начала контракта (YYYY-MM-DD) или пусто: ";
	$contract_start = trim( fgets( STDIN ) );
	$contract_start = empty( $contract_start ) ? null : $contract_start;
	
	echo "Дата окончания контракта (YYYY-MM-DD) или пусто: ";
	$contract_end = trim( fgets( STDIN ) );
	$contract_end = empty( $contract_end ) ? null : $contract_end;
	
	$result = $wpdb->insert(
		$wpdb->prefix . 'arsenal_staff',
		array(
			'uid' => wp_generate_uuid4(),
			'first_name' => $first_name,
			'second_name' => $second_name,
			'job_title_id' => $job_title_id,
			'birth_date' => $birth_date,
			'contract_start' => $contract_start,
			'contract_end' => $contract_end,
			'is_active' => 1,
		),
		array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d' )
	);
	
	if ( $result ) {
		echo "\n✓ Сотрудник '$first_name $second_name' успешно добавлен (ID: {$wpdb->insert_id})\n";
	} else {
		echo "\n❌ Ошибка добавления: {$wpdb->last_error}\n";
	}
}

/**
 * Показать все должности
 */
function show_job_titles() {
	global $wpdb;
	
	echo "\n--- Все должности ---\n\n";
	
	$titles = $wpdb->get_results( 
		"SELECT id, job_title_name, is_active, created_at FROM {$wpdb->prefix}arsenal_staff_job_titles ORDER BY id" 
	);
	
	if ( empty( $titles ) ) {
		echo "Должностей не найдено\n";
		return;
	}
	
	echo sprintf( "%-5s %-40s %-10s %-20s\n", "ID", "Название", "Активно", "Добавлено" );
	echo str_repeat( "-", 75 ) . "\n";
	
	foreach ( $titles as $title ) {
		$active = $title->is_active ? '✓' : '✗';
		echo sprintf( "%-5d %-40s %-10s %-20s\n", $title->id, $title->job_title_name, $active, $title->created_at );
	}
	
	echo "\n";
}

/**
 * Показать всех сотрудников
 */
function show_staff() {
	global $wpdb;
	
	echo "\n--- Все сотрудники ---\n\n";
	
	$staff = $wpdb->get_results( 
		"SELECT s.id, s.first_name, s.second_name, j.job_title_name, s.contract_start, s.contract_end, s.is_active
		 FROM {$wpdb->prefix}arsenal_staff s
		 LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON s.job_title_id = j.id
		 ORDER BY s.id" 
	);
	
	if ( empty( $staff ) ) {
		echo "Сотрудников не найдено\n";
		return;
	}
	
	echo sprintf( "%-3s %-15s %-15s %-25s %-12s %-12s\n", "ID", "Имя", "Фамилия", "Должность", "Начало", "Конец" );
	echo str_repeat( "-", 82 ) . "\n";
	
	foreach ( $staff as $person ) {
		echo sprintf(
			"%-3d %-15s %-15s %-25s %-12s %-12s\n",
			$person->id,
			$person->first_name,
			$person->second_name,
			$person->job_title_name ?: 'Не указана',
			$person->contract_start ?: '-',
			$person->contract_end ?: '-'
		);
	}
	
	echo "\n";
}

/**
 * Очистить таблицы
 */
function clear_tables() {
	global $wpdb;
	
	echo "\n⚠️  Вы уверены? Это удалит все данные из таблиц сотрудников!\n";
	echo "Введите 'yes' для подтверждения: ";
	
	$confirm = trim( fgets( STDIN ) );
	
	if ( $confirm !== 'yes' ) {
		echo "Отмена\n";
		return;
	}
	
	// Сначала удаляем сотрудников
	$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}arsenal_staff" );
	echo "✓ Таблица wp_arsenal_staff очищена\n";
	
	// Потом удаляем должности
	$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}arsenal_staff_job_titles" );
	echo "✓ Таблица wp_arsenal_staff_job_titles очищена\n";
	
	echo "\n";
}

// Запуск
staff_menu();
