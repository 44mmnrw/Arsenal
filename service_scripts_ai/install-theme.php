<?php
/**
 * Установка и активация темы Arsenal.
 *
 * Скрипт автономный: НЕ импортирует таблицы БД.
 * Запуск:
 *   php service_scripts_ai/install-theme.php
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

if ( ! defined( 'ABSPATH' ) ) {
	echo "Ошибка: WordPress не инициализирован.\n";
	exit( 1 );
}

$theme_slug = 'arsenal';
$theme_dir  = WP_CONTENT_DIR . '/themes/' . $theme_slug;
$theme_zip  = WP_CONTENT_DIR . '/themes/arsenal-ready.zip';

if ( ! is_dir( $theme_dir ) ) {
	if ( ! file_exists( $theme_zip ) ) {
		echo "Ошибка: тема {$theme_slug} не найдена и архив {$theme_zip} отсутствует.\n";
		exit( 1 );
	}

	if ( ! class_exists( 'ZipArchive' ) ) {
		echo "Ошибка: для распаковки нужен ZipArchive (расширение php_zip).\n";
		exit( 1 );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $theme_zip ) ) {
		echo "Ошибка: не удалось открыть архив темы {$theme_zip}.\n";
		exit( 1 );
	}

	$extracted = $zip->extractTo( WP_CONTENT_DIR . '/themes/' );
	$zip->close();

	if ( ! $extracted || ! is_dir( $theme_dir ) ) {
		echo "Ошибка: не удалось распаковать тему {$theme_slug}.\n";
		exit( 1 );
	}

	echo "Тема {$theme_slug} распакована из архива.\n";
}

switch_theme( $theme_slug );

$current_theme = wp_get_theme();
if ( $current_theme->get_stylesheet() !== $theme_slug ) {
	echo "Ошибка: тема {$theme_slug} не активировалась. Текущая: " . $current_theme->get_stylesheet() . "\n";
	exit( 1 );
}

echo "✅ Тема '{$theme_slug}' успешно установлена и активирована.\n";
echo "ℹ️ Импорт таблиц БД этим скриптом НЕ выполняется.\n";
