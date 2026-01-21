<?php
/**
 * Тест сохранения данных формы Academy Recruitment
 * Запустите: http://arsenal.test/test-academy-save.php
 */

// Загрузить WordPress
require_once __DIR__ . '/wp-load.php';

global $wpdb;

echo "<h2>🧪 Тест Academy Recruitment Form</h2>";

// 1. Проверить таблицу
$table = $wpdb->prefix . 'arsenal_academy_recruitment';
echo "<h3>1. Проверка таблицы БД</h3>";

$exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" );
if ( $exists ) {
	echo "✅ Таблица существует<br>";
	
	// Проверить все колонки
	$columns = $wpdb->get_results( "SHOW COLUMNS FROM $table" );
	echo "<pre>";
	foreach ( $columns as $col ) {
		echo "$col->Field ($col->Type)\n";
	}
	echo "</pre>";
} else {
	echo "❌ Таблица НЕ существует!<br>";
}

// 2. Проверить данные
echo "<h3>2. Текущие данные в БД</h3>";
$row = $wpdb->get_row( "SELECT * FROM $table WHERE page_id = 1", ARRAY_A );
if ( $row ) {
	echo "Запись найдена (id={$row['id']})<br>";
	echo "<pre>";
	foreach ( $row as $key => $val ) {
		if ( in_array( $key, array( 'hero_data', 'benefits_data', 'contacts_data', 'directions_data', 'social_data', 'faq_data' ) ) ) {
			echo "$key: " . substr( $val, 0, 100 ) . "...\n";
		} else {
			echo "$key: $val\n";
		}
	}
	echo "</pre>";
	
	// 3. Декодировать и показать социальные сети
	if ( ! empty( $row['social_data'] ) ) {
		$social = json_decode( $row['social_data'], true );
		echo "<h3>3. Социальные сети в БД</h3>";
		echo "<pre>";
		print_r( $social );
		echo "</pre>";
	} else {
		echo "<h3>3. ⚠️ Социальные сети НЕ НАЙДЕНЫ в БД!</h3>";
	}
	
	// 4. Декодировать и показать маршруты
	if ( ! empty( $row['directions_data'] ) ) {
		$directions = json_decode( $row['directions_data'], true );
		echo "<h3>4. Маршруты в БД</h3>";
		echo "<pre>";
		print_r( $directions );
		echo "</pre>";
	} else {
		echo "<h3>4. ⚠️ Маршруты НЕ НАЙДЕНЫ в БД!</h3>";
	}
} else {
	echo "❌ Запись с page_id=1 не найдена<br>";
}

// 5. Инициализировать таблицу если надо
echo "<h3>5. Инициализация таблицы</h3>";
require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';
Arsenal_Academy_Recruitment_Manager::create_table();
echo "✅ Таблица инициализирована<br>";

echo "<hr>";
echo "<p><a href='" . admin_url( 'admin.php?page=arsenal-academy-recruitment' ) . "'>← Вернуться в админку</a></p>";
?>
