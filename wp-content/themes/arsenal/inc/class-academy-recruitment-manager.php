<?php
/**
 * Academy Recruitment Manager
 * 
 * Управление данными страницы набора в академию через JSON
 * Похоже на Arsenal_Stat_Cards_Manager, но для всей страницы набора
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Arsenal_Academy_Recruitment_Manager {
	
	/**
	 * Инициализация класса
	 */
	public static function init() {
		add_action( 'wp_loaded', array( __CLASS__, 'create_table' ) );
	}
	
	/**
	 * Получить все данные страницы набора
	 *
	 * @param int $page_id ID страницы
	 * @return array|null Массив данных или null
	 */
	public static function get_page_data( $page_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'arsenal_academy_recruitment';
		
		$data = $wpdb->get_row( 
			$wpdb->prepare( "SELECT * FROM $table WHERE page_id = %d", $page_id ),
			ARRAY_A
		);
		
		if ( ! $data ) {
			return null;
		}
		
		// Декодировать JSON поля
		$json_fields = array(
			'hero_data',
			'benefits_data',
			'age_groups_data',
			'documents_data',
			'schedule_data',
			'contacts_data',
			'directions_data',
			'faq_data',
			'social_data'  // Отдельный столбец для социальных сетей
		);
		
		foreach ( $json_fields as $field ) {
			if ( isset( $data[ $field ] ) && ! empty( $data[ $field ] ) ) {
				$data[ $field ] = json_decode( $data[ $field ], true );
			}
		}
		
		// Добавить социальные сети обратно в contacts_data для совместимости
		if ( isset( $data['social_data'] ) && ! empty( $data['social_data'] ) ) {
			if ( ! isset( $data['contacts_data'] ) ) {
				$data['contacts_data'] = array();
			}
			if ( ! is_array( $data['contacts_data'] ) ) {
				$data['contacts_data'] = array();
			}
			$data['contacts_data']['social'] = $data['social_data'];
		}
		
		return $data;
	}
	
	/**
	 * Получить конкретную секцию
	 *
	 * @param int    $page_id ID страницы
	 * @param string $section Название секции (hero, benefits, age_groups, etc.)
	 * @return mixed Данные секции или null
	 */
	public static function get_section( $page_id, $section ) {
		$data = self::get_page_data( $page_id );
		
		if ( ! $data ) {
			return null;
		}
		
		$field_name = $section . '_data';
		return isset( $data[ $field_name ] ) ? $data[ $field_name ] : null;
	}
	
	/**
	 * Обновить данные страницы
	 *
	 * @param int   $page_id ID страницы
	 * @param array $data    Массив данных для обновления
	 * @return bool Успех операции
	 */
	public static function update_page_data( $page_id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'arsenal_academy_recruitment';
		
		// Кодировать JSON поля
		$json_fields = array(
			'hero_data',
			'benefits_data',
			'age_groups_data',
			'documents_data',
			'schedule_data',
			'contacts_data',
			'directions_data',
			'faq_data',
			'social_data'  // Отдельный столбец для социальных сетей
		);
		
		// Обработать социальные сети - могут быть в двух местах
		$social_data = null;
		
		// 1. Проверить в contacts_data (старый формат для совместимости)
		if ( isset( $data['contacts_data'] ) && is_array( $data['contacts_data'] ) ) {
			if ( isset( $data['contacts_data']['social'] ) ) {
				$social_data = $data['contacts_data']['social'];
				unset( $data['contacts_data']['social'] );  // Удалить из contacts_data чтобы не было дублирования
			}
		}
		
		// 2. Проверить в социальных сетях (новый формат - приоритет)
		if ( isset( $data['social_data'] ) && is_array( $data['social_data'] ) ) {
			$social_data = $data['social_data'];
		}
		
		// Теперь кодируем все JSON поля
		foreach ( $json_fields as $field ) {
			if ( isset( $data[ $field ] ) && is_array( $data[ $field ] ) ) {
				$data[ $field ] = wp_json_encode( $data[ $field ], JSON_UNESCAPED_UNICODE );
			}
		}
		
		// Добавить социальные сети в данные для сохранения
		if ( $social_data !== null ) {
			$data['social_data'] = wp_json_encode( $social_data, JSON_UNESCAPED_UNICODE );
		}
		
		// Проверить существование записи
		$exists = $wpdb->get_var( 
			$wpdb->prepare( "SELECT id FROM $table WHERE page_id = %d", $page_id )
		);
		
		if ( $exists ) {
			// При обновлении: загрузить существующие данные и мёржить с новыми
			$existing = $wpdb->get_row( 
				$wpdb->prepare( "SELECT * FROM $table WHERE page_id = %d", $page_id ),
				ARRAY_A
			);
			
			if ( $existing ) {
				// Для каждого JSON поля: если не передано новое значение, сохранить старое
				foreach ( $json_fields as $field ) {
					if ( ! isset( $data[ $field ] ) && isset( $existing[ $field ] ) ) {
						$data[ $field ] = $existing[ $field ];
					}
				}
			}
			
			// Обновить с мёрженными данными
			return $wpdb->update(
				$table,
				$data,
				array( 'page_id' => $page_id ),
				null,
				array( '%d' )
			) !== false;
		} else {
			// Вставить
			$data['page_id'] = $page_id;
			return $wpdb->insert( $table, $data ) !== false;
		}
	}
	
	/**
	 * Обновить конкретную секцию
	 *
	 * @param int    $page_id      ID страницы
	 * @param string $section      Название секции
	 * @param mixed  $section_data Данные секции
	 * @return bool Успех операции
	 */
	public static function update_section( $page_id, $section, $section_data ) {
		$field_name = $section . '_data';
		return self::update_page_data( $page_id, array( $field_name => $section_data ) );
	}
	
	/**
	 * Создать таблицу в БД
	 *
	 * @return bool Успех операции
	 */
	public static function create_table() {
		global $wpdb;
		$table = $wpdb->prefix . 'arsenal_academy_recruitment';
		
		// Проверить существует ли таблица
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
			// Таблица существует - добавить колонку social_data если её нет
			$column_exists = $wpdb->get_var( "SHOW COLUMNS FROM $table LIKE 'social_data'" );
			if ( ! $column_exists ) {
				$wpdb->query( "ALTER TABLE $table ADD COLUMN `social_data` JSON DEFAULT NULL COMMENT 'Социальные сети (отдельный столбец)'" );
			}
			return true;
		}

		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS `$table` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`page_id` bigint(20) unsigned NOT NULL COMMENT 'ID страницы в wp_posts',
			`hero_data` JSON DEFAULT NULL COMMENT 'Данные hero секции',
			`benefits_data` JSON DEFAULT NULL COMMENT 'Массив карточек преимуществ',
			`age_groups_data` JSON DEFAULT NULL COMMENT 'Массив возрастных групп',
			`documents_data` JSON DEFAULT NULL COMMENT 'Массив необходимых документов',
			`schedule_data` JSON DEFAULT NULL COMMENT 'Расписание просмотров',
			`contacts_data` JSON DEFAULT NULL COMMENT 'Контактная информация (без соцсетей)',
			`directions_data` JSON DEFAULT NULL COMMENT 'Маршруты общественного транспорта',
			`faq_data` JSON DEFAULT NULL COMMENT 'Частые вопросы',
			`social_data` JSON DEFAULT NULL COMMENT 'Социальные сети (отдельный столбец)',
			`created_at` datetime DEFAULT CURRENT_TIMESTAMP,
			`updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `page_id` (`page_id`)
		) $charset_collate COMMENT='Управление контентом страницы набора в академию';";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		return true;
	}
	
	/**
	 * Получить дефолтные данные для новой страницы
	 *
	 * @return array Массив с дефолтными данными
	 */
	public static function get_default_data() {
		return array(
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
				'working_schedule' => array(
					array( 'day' => 'Понедельник', 'time' => '09:00 - 18:00' ),
					array( 'day' => 'Вторник', 'time' => '09:00 - 18:00' ),
					array( 'day' => 'Среда', 'time' => '09:00 - 18:00' ),
					array( 'day' => 'Четверг', 'time' => '09:00 - 18:00' ),
					array( 'day' => 'Пятница', 'time' => '09:00 - 18:00' ),
					array( 'day' => 'Суббота', 'time' => '10:00 - 16:00' ),
				),
				'google_maps_url' => '',
				'yandex_maps_url' => '',
				'director' => array(
					'title' => 'Директор СДЮШ',
					'name' => '',
					'role' => '',
					'contacts' => array(
						array( 'type' => 'phone', 'value' => '' ),
						array( 'type' => 'email', 'value' => '' ),
					),
				),
				'social' => array(
					array( 'icon' => 'instagram', 'name' => 'Instagram', 'url' => '' ),
					array( 'icon' => 'facebook', 'name' => 'Facebook', 'url' => '' ),
					array( 'icon' => 'youtube', 'name' => 'YouTube', 'url' => '' ),
					array( 'icon' => 'telegram', 'name' => 'Telegram', 'url' => '' ),
				),
			),
			'directions_data' => array(
				'items' => array(
					array(
						'transport' => 'На автомобиле',
						'icon' => 'icon-car',
						'route' => '',
						'time' => '15 минут',
					),
				),
			),
			'faq_data' => array(
				array(
					'question' => 'Сколько стоят занятия?',
					'answer' => 'Обучение бесплатное',
				),
			),
		);
	}
}
