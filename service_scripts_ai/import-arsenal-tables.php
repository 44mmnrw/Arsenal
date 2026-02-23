<?php
/**
 * Импорт только таблиц wp_arsenal_* из SQL-дампа.
 *
 * Запуск:
 *   php service_scripts_ai/import-arsenal-tables.php
 *   php service_scripts_ai/import-arsenal-tables.php path/to/file.sql
 *
 * По умолчанию используется:
 *   wp-content/themes/arsenal/inc/database/create-tables.sql
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

global $wpdb;

$default_sql = WP_CONTENT_DIR . '/themes/arsenal/inc/database/create-tables.sql';
$sql_file    = isset( $argv[1] ) ? $argv[1] : $default_sql;

if ( ! file_exists( $sql_file ) ) {
	echo "Ошибка: SQL-файл не найден ({$sql_file}).\n";
	exit( 1 );
}

$handle = fopen( $sql_file, 'r' );
if ( false === $handle ) {
	echo "Ошибка: не удалось открыть SQL-файл {$sql_file}.\n";
	exit( 1 );
}

$prefix           = $wpdb->prefix;
$processed        = 0;
$executed         = 0;
$errors           = 0;
$statement_buffer = '';

/**
 * Проверяет, относится ли SQL-запрос к таблицам wp_arsenal_*.
 *
 * @param string $statement SQL-запрос.
 * @return bool
 */
function arsenal_is_arsenal_statement( $statement ) {
	$pattern = '/\b(?:DROP\s+TABLE\s+IF\s+EXISTS|CREATE\s+TABLE|INSERT\s+INTO|ALTER\s+TABLE|LOCK\s+TABLES)\s+`?wp_arsenal_[a-z0-9_]+`?/i';
	return (bool) preg_match( $pattern, $statement );
}

/**
 * Нормализует SQL к текущему префиксу таблиц WordPress.
 *
 * @param string $statement SQL-запрос.
 * @param string $prefix    Текущий префикс БД.
 * @return string
 */
function arsenal_apply_db_prefix( $statement, $prefix ) {
	if ( 'wp_' === $prefix ) {
		return $statement;
	}

	return str_replace( 'wp_arsenal_', $prefix . 'arsenal_', $statement );
}

echo "Импорт таблиц wp_arsenal_* из файла: {$sql_file}\n";
echo "Префикс текущей БД: {$prefix}\n";

$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );
$wpdb->query( 'SET UNIQUE_CHECKS=0' );

while ( ( $line = fgets( $handle ) ) !== false ) {
	$trimmed = trim( $line );

	if ( '' === $trimmed || 0 === strpos( $trimmed, '--' ) ) {
		continue;
	}

	$statement_buffer .= $line;

	if ( ! preg_match( '/;\s*$/', $trimmed ) ) {
		continue;
	}

	$statement = trim( $statement_buffer );
	$statement_buffer = '';
	$processed++;

	if ( ! arsenal_is_arsenal_statement( $statement ) ) {
		continue;
	}

	$statement = arsenal_apply_db_prefix( $statement, $prefix );
	$result    = $wpdb->query( $statement );

	if ( false === $result ) {
		$errors++;
		echo "[ERROR] SQL: " . $wpdb->last_error . "\n";
		continue;
	}

	$executed++;

	if ( 0 === ( $executed % 200 ) ) {
		wp_cache_flush();
		echo "Обработано {$executed} запросов...\n";
	}
}

fclose( $handle );

$wpdb->query( 'SET UNIQUE_CHECKS=1' );
$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );

echo "\nГотово.\n";
echo "Всего SQL-блоков прочитано: {$processed}\n";
echo "Выполнено запросов для wp_arsenal_*: {$executed}\n";
echo "Ошибок: {$errors}\n";

if ( $errors > 0 ) {
	exit( 1 );
}

echo "✅ Импорт таблиц wp_arsenal_* успешно завершён.\n";
