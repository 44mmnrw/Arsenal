<?php
/**
 * Arsenal Team Manager - Plugin Installer
 * 
 * Выполняется при активации плагина
 * Создает все необходимые таблицы БД для управления командой
 */

class Arsenal_Team_Manager_Installer {
	
	/**
	 * Инициализация установки
	 */
	public static function init() {
		// Регистрируем хук активации
		$plugin_file = dirname( dirname( __FILE__ ) ) . '/arsenal-team-manager.php';
		register_activation_hook( $plugin_file, [ __CLASS__, 'install' ] );
	}
	
	/**
	 * Основной метод установки
	 */
	public static function install() {
		// Создаем все таблицы БД
		self::create_database_tables();
		
		// Отмечаем, что установка выполнена
		update_option( 'arsenal_team_manager_installed', current_time( 'mysql' ) );
		update_option( 'arsenal_team_manager_version', ARSENAL_TM_VERSION );
	}
	
	/**
	 * Создание таблиц БД из SQL файла
	 * Если файла нет - создаем минимум необходимых таблиц
	 */
	private static function create_database_tables() {
		global $wpdb;
		
		// Сначала пытаемся загрузить SQL из папки плагина
		$plugin_sql_file = dirname( dirname( __FILE__ ) ) . '/inc/create-tables.sql';
		
		if ( file_exists( $plugin_sql_file ) ) {
			// Используем SQL из плагина (который содержит всё)
			self::execute_sql_file( $plugin_sql_file );
			return;
		}
		
		// Fallback: пытаемся загрузить из темы
		$theme_sql_file = get_template_directory() . '/inc/database/create-tables.sql';
		
		if ( file_exists( $theme_sql_file ) ) {
			// Используем SQL из темы
			self::execute_sql_file( $theme_sql_file );
			return;
		}
		
		// Последний fallback: создаем базовые таблицы если ничего нет
		self::create_basic_tables();
	}
	
	/**
	 * Выполнить SQL файл с обработкой больших файлов
	 */
	private static function execute_sql_file( $file_path ) {
		global $wpdb;
		
		if ( ! file_exists( $file_path ) ) {
			return;
		}
		
		// Увеличиваем timeout для больших файлов
		set_time_limit( 600 ); // 10 минут
		
		// Читаем SQL файл
		$sql = file_get_contents( $file_path );
		
		if ( empty( $sql ) ) {
			return;
		}
		
		// Удаляем комментарии и пустые строки
		$sql = preg_replace( '/--.*?\n/', "\n", $sql );
		$sql = preg_replace( '/^\s*\n/m', '', $sql );
		
		// Разбиваем на отдельные запросы
		$queries = array_filter( array_map( 'trim', explode( ';', $sql ) ) );
		
		// Отключаем проверку FK для скорости
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );
		
		// Отключаем индексы во время импорта (ОЧЕНЬ БЫСТРО)
		$wpdb->query( 'SET UNIQUE_CHECKS=0' );
		
		// Выполняем каждый запрос
		$count = 0;
		foreach ( $queries as $query ) {
			if ( ! empty( $query ) ) {
				// Пропускаем DROP TABLE - создаем новые
				if ( strpos( $query, 'DROP TABLE' ) === 0 ) {
					$wpdb->query( $query );
					continue;
				}
				
				// Заменяем wp_ на префикс БД
				$query = str_replace( 'wp_', $wpdb->prefix, $query );
				
				// Выполняем запрос
				$wpdb->query( $query );
				$count++;
				
				// Каждые 500 запросов - флаш памяти
				if ( $count % 500 === 0 ) {
					wp_cache_flush();
				}
			}
		}
		
		// Включаем индексы и FK проверку обратно
		$wpdb->query( 'SET UNIQUE_CHECKS=1' );
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );
		
		// Оптимизируем таблицы
		$tables = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . "arsenal_%'" );
		foreach ( $tables as $table_obj ) {
			$table = current( (array) $table_obj );
			$wpdb->query( "OPTIMIZE TABLE $table" );
		}
	}
	
	/**
	 * Создание базовых таблиц (fallback)
	 */
	private static function create_basic_tables() {
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
