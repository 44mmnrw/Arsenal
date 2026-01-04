<?php
/**
 * Arsenal Database Manager
 * 
 * Управление структурой БД для Arsenal FC
 * 
 * @package Arsenal_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Database {

	/**
	 * Версия схемы БД
	 */
	const DB_VERSION = '1.0';

	/**
	 * Опция для хранения версии БД
	 */
	const DB_VERSION_OPTION = 'arsenal_db_version';

	/**
	 * Экземпляр класса
	 */
	private static $instance = null;

	/**
	 * Получить экземпляр класса (Singleton)
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Конструктор
	 */
	private function __construct() {
		// Приватный конструктор для Singleton
	}

	/**
	 * Инициализация БД - создание/обновление таблиц
	 */
	public function init() {
		$current_version = get_option( self::DB_VERSION_OPTION, '0' );

		if ( version_compare( $current_version, self::DB_VERSION, '<' ) ) {
			$this->create_tables();
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
		}
	}

	/**
	 * Создание всех таблиц
	 */
	public function create_tables() {
		global $wpdb;

		$theme_dir = get_template_directory() . '/inc/database';
		$simple_file = $theme_dir . '/create-tables-simple.sql';
		$fk_file = $theme_dir . '/add-foreign-keys.sql';

		if ( ! file_exists( $simple_file ) ) {
			return new WP_Error( 'file_not_found', 'Файл SQL миграции не найден: ' . $simple_file );
		}

		// 1. Создаём таблицы БЕЗ внешних ключей
		$sql = file_get_contents( $simple_file );
		
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );
		
		// Разделяем по CREATE TABLE
		$tables = preg_split( '/(?=CREATE TABLE)/i', $sql );
		
		$created = 0;
		foreach ( $tables as $table_sql ) {
			$table_sql = trim( $table_sql );
			
			if ( empty( $table_sql ) || ! preg_match( '/^CREATE TABLE/i', $table_sql ) ) {
				continue;
			}
			
			// Удаляем все до CREATE TABLE
			$table_sql = preg_replace( '/^.*?(CREATE TABLE)/is', '$1', $table_sql );
			
			// Выполняем запрос
			$result = $wpdb->query( $table_sql );
			
			if ( false === $result && $wpdb->last_error ) {
				error_log( 'Arsenal DB Error: ' . $wpdb->last_error );
				error_log( 'SQL: ' . substr( $table_sql, 0, 200 ) );
			} else {
				$created++;
			}
		}
		
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );

		// 2. Добавляем внешние ключи
		if ( file_exists( $fk_file ) ) {
			$fk_sql = file_get_contents( $fk_file );
			
			// Разбиваем по точкам с запятой
			$fk_queries = preg_split( '/;[\s]*$/m', $fk_sql, -1, PREG_SPLIT_NO_EMPTY );
			
			foreach ( $fk_queries as $fk_query ) {
				$fk_query = trim( $fk_query );
				
				if ( empty( $fk_query ) || strpos( $fk_query, '--' ) === 0 ) {
					continue;
				}
				
				// Выполняем ALTER TABLE
				$wpdb->query( $fk_query );
				
				// Игнорируем ошибки FK (возможно уже существуют)
			}
		}

		return true;
	}

	/**
	 * Удаление всех таблиц (осторожно!)
	 */
	public function drop_tables() {
		global $wpdb;

		$tables = array(
			'wp_arsenal_sync_log',
			'wp_arsenal_standings',
			'wp_arsenal_player_stats',
			'wp_arsenal_match_lineups',
			'wp_arsenal_match_events',
			'wp_arsenal_matches',
			'wp_arsenal_player_transfers',
			'wp_arsenal_players',
			'wp_arsenal_teams',
			'wp_arsenal_seasons',
			'wp_arsenal_leagues',
		);

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );

		delete_option( self::DB_VERSION_OPTION );

		return true;
	}

	/**
	 * Проверка существования таблиц
	 */
	public function tables_exist() {
		global $wpdb;

		$table = $wpdb->get_var(
			"SHOW TABLES LIKE 'wp_arsenal_leagues'"
		);

		return ( 'wp_arsenal_leagues' === $table );
	}

	/**
	 * Получить информацию о таблицах
	 */
	public function get_tables_info() {
		global $wpdb;

		$tables = array(
			'leagues',
			'seasons',
			'teams',
			'players',
			'player_transfers',
			'matches',
			'match_events',
			'match_lineups',
			'player_stats',
			'standings',
			'sync_log',
		);

		$info = array();

		foreach ( $tables as $table ) {
			$full_table_name = 'wp_arsenal_' . $table;
			
			$count = $wpdb->get_var(
				"SELECT COUNT(*) FROM `{$full_table_name}`"
			);

			$size = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT 
						ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024, 2) as size_kb
					FROM information_schema.TABLES 
					WHERE TABLE_SCHEMA = %s 
					AND TABLE_NAME = %s",
					DB_NAME,
					$full_table_name
				)
			);

			$info[ $table ] = array(
				'name'  => $full_table_name,
				'count' => (int) $count,
				'size'  => $size ? $size->size_kb . ' KB' : '0 KB',
			);
		}

		return $info;
	}

	/**
	 * Сброс AUTO_INCREMENT для всех таблиц
	 */
	public function reset_auto_increment() {
		global $wpdb;

		$tables = array(
			'wp_arsenal_seasons',
			'wp_arsenal_player_transfers',
			'wp_arsenal_match_events',
			'wp_arsenal_match_lineups',
			'wp_arsenal_player_stats',
			'wp_arsenal_standings',
			'wp_arsenal_sync_log',
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "ALTER TABLE `{$table}` AUTO_INCREMENT = 1" );
		}

		return true;
	}

	/**
	 * Очистка всех данных (таблицы остаются)
	 */
	public function truncate_all() {
		global $wpdb;

		$tables = array(
			'wp_arsenal_sync_log',
			'wp_arsenal_standings',
			'wp_arsenal_player_stats',
			'wp_arsenal_match_lineups',
			'wp_arsenal_match_events',
			'wp_arsenal_matches',
			'wp_arsenal_player_transfers',
			'wp_arsenal_players',
			'wp_arsenal_teams',
			'wp_arsenal_seasons',
			'wp_arsenal_leagues',
		);

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );

		foreach ( $tables as $table ) {
			$wpdb->query( "TRUNCATE TABLE `{$table}`" );
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );

		return true;
	}

	/**
	 * Получить статистику БД
	 */
	public function get_stats() {
		global $wpdb;

		$stats = array(
			'version'      => get_option( self::DB_VERSION_OPTION, 'не установлена' ),
			'tables_exist' => $this->tables_exist(),
		);

		if ( $stats['tables_exist'] ) {
			$stats['leagues']   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM wp_arsenal_leagues" );
			$stats['teams']     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM wp_arsenal_teams" );
			$stats['players']   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM wp_arsenal_players" );
			$stats['matches']   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM wp_arsenal_matches" );
			$stats['events']    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM wp_arsenal_match_events" );
		}

		return $stats;
	}
}
