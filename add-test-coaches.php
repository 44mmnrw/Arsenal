<?php
require 'wp-load.php';
global $wpdb;

echo "=== Setting up coaches data ===\n\n";

// Обновляем пустую должность
$wpdb->update( 
	"{$wpdb->prefix}arsenal_staff_job_titles",
	array( 'name' => 'Главный тренер' ),
	array( 'id' => 1 )
);

// Добавляем остальные должности если их нет
$existing = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff_job_titles" );
if ( $existing == 1 ) {
	$wpdb->insert( "{$wpdb->prefix}arsenal_staff_job_titles", array( 'name' => 'Тренер по физической подготовке' ) );
	$wpdb->insert( "{$wpdb->prefix}arsenal_staff_job_titles", array( 'name' => 'Врач команды' ) );
	$wpdb->insert( "{$wpdb->prefix}arsenal_staff_job_titles", array( 'name' => 'Помощник тренера' ) );
	echo "Created job titles\n\n";
}

// Очищаем старые данные (кроме id=1)
$wpdb->query( "DELETE FROM {$wpdb->prefix}arsenal_staff WHERE id > 1" );

// Обновляем первую запись
$wpdb->update(
	"{$wpdb->prefix}arsenal_staff",
	array( 'first_name' => 'Сергей', 'second_name' => 'Коваленко', 'job_title_id' => 1 ),
	array( 'id' => 1 )
);

// Добавляем новых сотрудников
$test_staff = array(
	array( 'first_name' => 'Иван', 'second_name' => 'Петров', 'job_title_id' => 1 ),
	array( 'first_name' => 'Алексей', 'second_name' => 'Морозов', 'job_title_id' => 2 ),
	array( 'first_name' => 'Николай', 'second_name' => 'Волков', 'job_title_id' => 2 ),
	array( 'first_name' => 'Михаил', 'second_name' => 'Сорокин', 'job_title_id' => 3 ),
	array( 'first_name' => 'Андрей', 'second_name' => 'Степанов', 'job_title_id' => 4 ),
	array( 'first_name' => 'Владимир', 'second_name' => 'Горбунов', 'job_title_id' => 4 ),
);

foreach ( $test_staff as $staff ) {
	$wpdb->insert( "{$wpdb->prefix}arsenal_staff", $staff );
}

echo "Inserted test data\n\n";

echo "=== Verification ===\n\n";
$all_staff = $wpdb->get_results( 
	"SELECT s.*, jt.name as job_title 
	 FROM {$wpdb->prefix}arsenal_staff s
	 LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
	 ORDER BY jt.name ASC, s.name ASC" 
);

if ( ! empty( $all_staff ) ) {
	echo "Staff list:\n";
	foreach ( $all_staff as $person ) {
		echo "  • {$person->name} - {$person->job_title}\n";
	}
} else {
	echo "ERROR: No staff data found\n";
}

echo "\nPage: http://arsenal.test/coaches/\n";
