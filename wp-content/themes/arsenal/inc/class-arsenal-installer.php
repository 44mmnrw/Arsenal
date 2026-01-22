<?php
/**
 * Arsenal Theme Installation/Migration
 * 
 * Этот скрипт выполняется при активации темы и создает:
 * 1. Все необходимые страницы
 * 2. Главное меню с пунктами
 * 3. Кастомные таблицы БД
 */

class Arsenal_Theme_Installer {
	
	/**
	 * Инициализация установки темы
	 */
	public static function init() {
		add_action( 'after_switch_theme', [ __CLASS__, 'install' ] );
	}
	
	/**
	 * Основной метод установки
	 */
	public static function install() {
		// Создаем страницы
		self::create_pages();
		
		// Создаем меню
		self::create_menu();
		
		// Создаем таблицы БД
		self::create_database_tables();
		
		// Отмечаем, что установка выполнена
		update_option( 'arsenal_theme_installed', current_time( 'mysql' ) );
	}
	
	/**
	 * Создание всех страниц
	 */
	private static function create_pages() {
		$pages = [
			[
				'post_title'     => 'Основной состав',
				'post_name'      => 'basic-squad',
				'post_template'  => 'page-squad.php',
			],
			[
				'post_title'     => 'Дубль',
				'post_name'      => 'reserve-squad',
				'post_template'  => 'page-reserve.php',
			],
			[
				'post_title'     => 'Тренерский штаб',
				'post_name'      => 'coaching-staff',
				'post_template'  => 'page-staff.php',
			],
			[
				'post_title'     => 'Врачи команды',
				'post_name'      => 'team-doctors',
				'post_template'  => 'page-doctors.php',
			],
			[
				'post_title'     => 'История клуба',
				'post_name'      => 'club-history',
				'post_template'  => 'page-history.php',
			],
			[
				'post_title'     => 'Руководство',
				'post_name'      => 'management',
				'post_template'  => 'page-management.php',
			],
			[
				'post_title'     => 'Стадион',
				'post_name'      => 'stadium',
				'post_template'  => 'page-stadium.php',
			],
			[
				'post_title'     => 'Спонсоры',
				'post_name'      => 'sponsors',
				'post_template'  => 'page-sponsors.php',
			],
			[
				'post_title'     => 'Матчи',
				'post_name'      => 'matches',
				'post_template'  => 'page-matches.php',
			],
			[
				'post_title'     => 'Турнирная таблица',
				'post_name'      => 'standings',
				'post_template'  => 'page-standings.php',
			],
			[
				'post_title'     => 'Турнирная сетка',
				'post_name'      => 'tournament',
				'post_template'  => 'page-tournament.php',
			],
			[
				'post_title'     => 'Календарь',
				'post_name'      => 'calendar',
				'post_template'  => 'page-calendar.php',
			],
			[
				'post_title'     => 'Руководство СДЮШ',
				'post_name'      => 'academy-management',
				'post_template'  => 'page-academy-management.php',
			],
			[
				'post_title'     => 'Тренеры СДЮШ',
				'post_name'      => 'academy-coaches',
				'post_template'  => 'page-academy-coaches.php',
			],
			[
				'post_title'     => 'Набор в школу',
				'post_name'      => 'academy-recruitment',
				'post_template'  => 'page-academy-recruitment.php',
			],
			[
				'post_title'     => 'История ДЮСШ',
				'post_name'      => 'academy-history',
				'post_template'  => 'page-academy-history.php',
			],
			[
				'post_title'     => 'Новости',
				'post_name'      => 'news',
				'post_template'  => 'page-news.php',
			],
			[
				'post_title'     => 'Новый отдел',
				'post_name'      => 'new-department',
				'post_template'  => 'page-new-department.php',
			],
		];
		
		foreach ( $pages as $page_data ) {
			// Проверяем, существует ли страница
			$existing = get_page_by_path( $page_data['post_name'], OBJECT, 'page' );
			
			if ( ! $existing ) {
				wp_insert_post( array_merge( $page_data, [
					'post_type'   => 'page',
					'post_status' => 'publish',
				] ) );
			}
		}
	}
	
	/**
	 * Создание главного меню
	 */
	private static function create_menu() {
		// Проверяем, существует ли меню
		$menu_id = self::get_menu_id( 'Главное меню' );
		if ( ! $menu_id ) {
			$menu_id = wp_create_nav_menu( 'Главное меню' );
		}
		
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
		
		// Структура меню
		$menu_items = [
			[
				'title' => 'Команда',
				'type'  => 'custom',
				'url'   => '#',
			],
			[
				'title'    => 'Основной состав',
				'type'     => 'page',
				'slug'     => 'basic-squad',
				'parent'   => 0,
			],
			[
				'title'    => 'Дубль',
				'type'     => 'page',
				'slug'     => 'reserve-squad',
				'parent'   => 0,
			],
			[
				'title'    => 'Тренерский штаб',
				'type'     => 'page',
				'slug'     => 'coaching-staff',
				'parent'   => 0,
			],
			[
				'title'    => 'Врачи команды',
				'type'     => 'page',
				'slug'     => 'team-doctors',
				'parent'   => 0,
			],
			[
				'title'    => 'Новый отдел',
				'type'     => 'page',
				'slug'     => 'new-department',
				'parent'   => 0,
			],
			[
				'title' => 'Клуб',
				'type'  => 'custom',
				'url'   => '#',
			],
			[
				'title'    => 'История клуба',
				'type'     => 'page',
				'slug'     => 'club-history',
				'parent'   => 0,
			],
			[
				'title'    => 'Руководство',
				'type'     => 'page',
				'slug'     => 'management',
				'parent'   => 0,
			],
			[
				'title'    => 'Стадион',
				'type'     => 'page',
				'slug'     => 'stadium',
				'parent'   => 0,
			],
			[
				'title'    => 'Спонсоры',
				'type'     => 'page',
				'slug'     => 'sponsors',
				'parent'   => 0,
			],
			[
				'title'    => 'Матчи',
				'type'     => 'page',
				'slug'     => 'matches',
				'parent'   => 0,
			],
			[
				'title'    => 'Турнирная таблица',
				'type'     => 'page',
				'slug'     => 'standings',
				'parent'   => 0,
			],
			[
				'title'    => 'Турнирная сетка',
				'type'     => 'page',
				'slug'     => 'tournament',
				'parent'   => 0,
			],
			[
				'title'    => 'Календарь',
				'type'     => 'page',
				'slug'     => 'calendar',
				'parent'   => 0,
			],
			[
				'title' => 'ДЮСШ',
				'type'  => 'custom',
				'url'   => '#',
			],
			[
				'title'    => 'Руководство СДЮШ',
				'type'     => 'page',
				'slug'     => 'academy-management',
				'parent'   => 0,
			],
			[
				'title'    => 'Тренеры СДЮШ',
				'type'     => 'page',
				'slug'     => 'academy-coaches',
				'parent'   => 0,
			],
			[
				'title'    => 'Набор в школу',
				'type'     => 'page',
				'slug'     => 'academy-recruitment',
				'parent'   => 0,
			],
			[
				'title'    => 'История ДЮСШ',
				'type'     => 'page',
				'slug'     => 'academy-history',
				'parent'   => 0,
			],
			[
				'title'    => 'Новости',
				'type'     => 'page',
				'slug'     => 'news',
				'parent'   => 0,
			],
		];
		
		// Добавляем пункты меню
		foreach ( $menu_items as $item ) {
			$item_args = [
				'menu-item-title'   => $item['title'],
				'menu-item-type'    => $item['type'],
				'menu-item-status'  => 'publish',
			];
			
			if ( 'page' === $item['type'] ) {
				$page = get_page_by_path( $item['slug'], OBJECT, 'page' );
				if ( $page ) {
					$item_args['menu-item-object-id'] = $page->ID;
					$item_args['menu-item-object']    = 'page';
				}
			} else {
				$item_args['menu-item-url'] = $item['url'];
			}
			
			wp_update_nav_menu_item( $menu_id, 0, $item_args );
		}
		
		// Устанавливаем меню как основное в теме
		set_theme_mod( 'nav_menu_locations', [ 'primary' => $menu_id ] );
	}
	
	/**
	 * Получить ID меню по названию
	 */
	private static function get_menu_id( $menu_name ) {
		$menu = get_term_by( 'name', $menu_name, 'nav_menu' );
		return $menu ? $menu->term_id : false;
	}
	
	/**
	 * Создание кастомных таблиц БД
	 */
	private static function create_database_tables() {
		global $wpdb;
		
		// Путь к SQL файлу
		$sql_file = get_template_directory() . '/inc/database/create-tables.sql';
		
		if ( ! file_exists( $sql_file ) ) {
			return; // Файл не найден
		}
		
		// Читаем SQL файл
		$sql = file_get_contents( $sql_file );
		
		// Разбиваем на отдельные запросы (разделены ;)
		$queries = array_filter( array_map( 'trim', explode( ';', $sql ) ) );
		
		// Выполняем каждый запрос
		foreach ( $queries as $query ) {
			if ( ! empty( $query ) ) {
				// Заменяем wp_ на префикс базы
				$query = str_replace( 'wp_', $wpdb->prefix, $query );
				$wpdb->query( $query );
			}
		}
	}
}

// Инициализируем установщик
Arsenal_Theme_Installer::init();
