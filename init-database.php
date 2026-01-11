<?php
/**
 * Database Initialization Script
 * 
 * Инициализирует таблицы БД через класс Arsenal_Database
 * 
 * Использование:
 *   php init-database.php
 *
 * @package Arsenal
 */

// Загружаем WordPress
require_once dirname( __FILE__ ) . '/wp-load.php';

// Проверка прав доступа (только из командной строки)
if ( php_sapi_name() !== 'cli' ) {
	wp_die( 'Этот скрипт можно запускать только из командной строки' );
}

// Загружаем класс БД
require_once get_template_directory() . '/inc/database/class-arsenal-database.php';

echo "\n";
echo "===========================================\n";
echo "  Arsenal Database Initialization\n";
echo "===========================================\n";
echo "\n";

// Инициализируем БД
echo "🔄 Инициализация таблиц БД...\n\n";

$db = Arsenal_Database::get_instance();
$db->init();

echo "✅ Инициализация завершена!\n";
echo "\nТеперь вы можете проверить таблицы командой:\n";
echo "  php service_scripts_ai/check-database.php\n";
echo "\nИли управлять сотрудниками:\n";
echo "  php service_scripts_ai/staff-manager.php\n";
echo "\n";
