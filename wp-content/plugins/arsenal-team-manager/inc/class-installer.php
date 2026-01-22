<?php
/**
 * Arsenal Team Manager - Plugin Installer
 * 
 * Выполняется при активации плагина
 */

class Arsenal_Team_Manager_Installer {
	
	/**
	 * Инициализация установки
	 */
	public static function init() {
		register_activation_hook( ARSENAL_TM_PLUGIN_DIR . 'arsenal-team-manager.php', [ __CLASS__, 'install' ] );
	}
	
	/**
	 * Основной метод установки
	 */
	public static function install() {
		// Создаем таблицы БД для плагина
		self::create_database_tables();
		
		// Отмечаем, что установка выполнена
		update_option( 'arsenal_team_manager_installed', current_time( 'mysql' ) );
	}
	
	/**
	 * Создание таблиц БД
	 */
	private static function create_database_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		
		// Таблица игроков
		$table_players = $wpdb->prefix . 'arsenal_players';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_players'" ) !== $table_players ) {
			$wpdb->query( "
				CREATE TABLE $table_players (
					id INT AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(255) NOT NULL,
					position VARCHAR(100),
					number INT,
					photo_id INT,
					status VARCHAR(50),
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				) $charset_collate;
			" );
		}
		
		// Таблица матчей
		$table_matches = $wpdb->prefix . 'arsenal_matches';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_matches'" ) !== $table_matches ) {
			$wpdb->query( "
				CREATE TABLE $table_matches (
					id INT AUTO_INCREMENT PRIMARY KEY,
					home_team VARCHAR(255),
					away_team VARCHAR(255),
					home_score INT,
					away_score INT,
					match_date DATETIME,
					status VARCHAR(50),
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				) $charset_collate;
			" );
		}
	}
}

// Инициализируем установщик при загрузке плагина
if ( defined( 'ABSPATH' ) ) {
	Arsenal_Team_Manager_Installer::init();
}
