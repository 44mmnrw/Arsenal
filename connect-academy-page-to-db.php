<?php
/**
 * Подключение страницы "Набор в академию" к таблице wp_arsenal_academy_recruitment
 * 
 * Запустить: php connect-academy-page-to-db.php
 * После создания страницы "Набор в академию" в WordPress админке
 */

require_once 'wp-load.php';

global $wpdb;

// 1. Найти страницу по названию (используя современный метод вместо deprecated get_page_by_title)
$pages = get_posts( array(
	'post_type' => 'page',
	'title' => 'Набор в академию',
	'posts_per_page' => 1,
) );
$page = $pages ? $pages[0] : null;

if ( ! $page ) {
	echo "❌ Ошибка: Страница 'Набор в академию' не найдена в WordPress\n";
	echo "   Пожалуйста, создайте её в админке: Pages → Add New\n";
	exit(1);
}

$page_id = $page->ID;
echo "✓ Найдена страница 'Набор в академию' (ID: {$page_id})\n";

// 2. Проверить, есть ли уже запись в таблице
$table = $wpdb->prefix . 'arsenal_academy_recruitment';
$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $table WHERE page_id = %d", $page_id ) );

if ( $existing ) {
	echo "✓ Запись для этой страницы уже существует в БД (id: {$existing->id})\n";
	exit(0);
}

// 3. Вставить дефолтные данные в таблицу
$default_data = array(
	'hero_data' => array(
		'title' => 'Набор в академию',
		'description' => 'СДЮШ "Арсенал" объявляет набор детей в возрасте от 8 до 17 лет.',
		'buttons' => array(
			array( 'text' => 'Подать заявку', 'action' => 'apply', 'style' => 'primary' ),
			array( 'text' => 'Контакты', 'action' => '#contacts', 'style' => 'secondary' ),
		),
	),
	'benefits_data' => array(
		array(
			'icon' => 'coaches',
			'title' => 'Профессиональные тренеры',
			'description' => 'Все наши тренеры имеют лицензии UEFA',
		),
		array(
			'icon' => 'facilities',
			'title' => 'Современная база',
			'description' => 'Два полноразмерных поля, крытый манеж',
		),
		array(
			'icon' => 'safety',
			'title' => 'Безопасность',
			'description' => 'Полная медицинская страховка',
		),
		array(
			'icon' => 'pro',
			'title' => 'Путь в профи',
			'description' => '12 выпускников играют в Высшей лиге',
		),
	),
	'age_groups_data' => array(
		array(
			'name' => 'U-9',
			'age_range' => '8-9 лет',
			'birth_years' => '2016-2017',
			'schedule' => 'Пн, Ср, Пт: 16:00-17:30',
			'spots_available' => 15,
			'spots_status' => 'normal',
		),
	),
	'documents_data' => array(
		'items' => array(
			array( 'text' => 'Медицинская справка' ),
			array( 'text' => 'Свидетельство о рождении' ),
		),
		'notice' => 'Все документы должны быть заверены',
	),
	'schedule_data' => array(
		'items' => array(
			array( 'icon' => 'calendar', 'heading' => 'Каждую субботу', 'text' => '10:00 - 12:00' ),
		),
		'notice' => 'Предварительная запись обязательна!',
	),
	'contacts_data' => array(
		'address' => 'ул. Спортивная, 2, г. Дзержинск',
		'phone' => '+375 (17) 123-45-70',
		'email' => 'academy@arsenal-dzr.by',
	),
	'faq_data' => array(
		array(
			'question' => 'Сколько стоят занятия?',
			'answer' => 'Обучение бесплатное',
		),
	),
);

// Кодировать все JSON поля
$data_to_insert = array(
	'page_id' => $page_id,
	'hero_data' => wp_json_encode( $default_data['hero_data'], JSON_UNESCAPED_UNICODE ),
	'benefits_data' => wp_json_encode( $default_data['benefits_data'], JSON_UNESCAPED_UNICODE ),
	'age_groups_data' => wp_json_encode( $default_data['age_groups_data'], JSON_UNESCAPED_UNICODE ),
	'documents_data' => wp_json_encode( $default_data['documents_data'], JSON_UNESCAPED_UNICODE ),
	'schedule_data' => wp_json_encode( $default_data['schedule_data'], JSON_UNESCAPED_UNICODE ),
	'contacts_data' => wp_json_encode( $default_data['contacts_data'], JSON_UNESCAPED_UNICODE ),
	'faq_data' => wp_json_encode( $default_data['faq_data'], JSON_UNESCAPED_UNICODE ),
);

$inserted = $wpdb->insert( $table, $data_to_insert );

if ( $inserted === false ) {
	echo "❌ Ошибка при вставке данных в БД: " . $wpdb->last_error . "\n";
	exit(1);
}

echo "✅ Страница успешно подключена к БД!\n";
echo "   Запись создана в таблице {$table}\n";
echo "   page_id = {$page_id}\n";
echo "\n💡 Теперь откройте админку: Арсенал → Набор в академию\n";
