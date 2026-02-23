<?php
/**
 * Установка и активация плагина Arsenal Team Manager.
 *
 * Скрипт автономный: НЕ импортирует таблицы БД.
 * Запуск:
 *   php service_scripts_ai/install-plugin.php
 *
 * @package Arsenal
 */

if ( php_sapi_name() !== 'cli' ) {
	echo "Этот скрипт запускается только из CLI.\n";
	exit( 1 );
}

$root_dir = dirname( __DIR__ );
$wp_load  = $root_dir . '/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
	echo "Ошибка: не найден wp-load.php ({$wp_load})\n";
	exit( 1 );
}

require_once $wp_load;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( ! defined( 'ABSPATH' ) ) {
	echo "Ошибка: WordPress не инициализирован.\n";
	exit( 1 );
}

$plugin_file = 'arsenal-team-manager/arsenal-team-manager.php';
$plugin_path = WP_PLUGIN_DIR . '/arsenal-team-manager/arsenal-team-manager.php';

if ( ! file_exists( $plugin_path ) ) {
	echo "Ошибка: не найден файл плагина {$plugin_path}.\n";
	exit( 1 );
}

$result = activate_plugin( $plugin_file );
if ( is_wp_error( $result ) ) {
	echo 'Ошибка активации плагина: ' . $result->get_error_message() . "\n";
	exit( 1 );
}

if ( ! is_plugin_active( $plugin_file ) ) {
	echo "Ошибка: плагин {$plugin_file} не активировался.\n";
	exit( 1 );
}

echo "✅ Плагин '{$plugin_file}' успешно установлен и активирован.\n";
echo "ℹ️ Импорт таблиц БД этим скриптом НЕ выполняется.\n";
