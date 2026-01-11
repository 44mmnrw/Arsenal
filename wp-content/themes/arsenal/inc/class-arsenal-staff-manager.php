<?php
/**
 * Arsenal Staff Manager Class
 * 
 * Управление сотрудниками клуба и их должностями
 * 
 * @package Arsenal_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Staff_Manager {

	/**
	 * Экземпляр класса (Singleton)
	 */
	private static $instance = null;

	/**
	 * Получить экземпляр класса
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
	 * Получить все должности
	 *
	 * @param bool $active_only Только активные должности
	 * @return array Массив должностей
	 */
	public static function get_job_titles( $active_only = false ) {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->prefix}arsenal_staff_job_titles";
		$query .= " ORDER BY sort_order ASC, job_title_name ASC";

		return $wpdb->get_results( $query );
	}

	/**
	 * Получить должность по ID
	 *
	 * @param int $job_title_id ID должности
	 * @return object|null Объект должности или null
	 */
	public static function get_job_title( $job_title_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}arsenal_staff_job_titles WHERE id = %d",
				$job_title_id
			)
		);
	}

	/**
	 * Добавить новую должность
	 *
	 * @param string $job_title_name Название должности
	 * @param string $description Описание (опционально)
	 * @param int $sort_order Порядок сортировки
	 * @return int|false ID созданной должности или false
	 */
	public static function add_job_title( $job_title_name, $description = '', $sort_order = 0 ) {
		global $wpdb;

		$result = $wpdb->insert(
			$wpdb->prefix . 'arsenal_staff_job_titles',
			array(
				'job_title_name' => sanitize_text_field( $job_title_name ),
				'description' => sanitize_textarea_field( $description ),
				'sort_order' => (int) $sort_order,
			),
			array( '%s', '%s', '%d' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Обновить должность
	 *
	 * @param int $job_title_id ID должности
	 * @param array $data Данные для обновления
	 * @return bool Результат обновления
	 */
	public static function update_job_title( $job_title_id, $data ) {
		global $wpdb;

		$update = array();
		$format = array();

		if ( isset( $data['job_title_name'] ) ) {
			$update['job_title_name'] = sanitize_text_field( $data['job_title_name'] );
			$format[] = '%s';
		}

		if ( isset( $data['description'] ) ) {
			$update['description'] = sanitize_textarea_field( $data['description'] );
			$format[] = '%s';
		}

		if ( isset( $data['sort_order'] ) ) {
			$update['sort_order'] = (int) $data['sort_order'];
			$format[] = '%d';
		}

		// Если нет полей для обновления, возвращаем false
		if ( empty( $update ) ) {
			return false;
		}

		return (bool) $wpdb->update(
			$wpdb->prefix . 'arsenal_staff_job_titles',
			$update,
			array( 'id' => $job_title_id ),
			$format,
			array( '%d' )
		);
	}

	/**
	 * Удалить должность
	 *
	 * @param int $job_title_id ID должности
	 * @return bool Результат удаления
	 */
	public static function delete_job_title( $job_title_id ) {
		global $wpdb;

		return $wpdb->delete(
			$wpdb->prefix . 'arsenal_staff_job_titles',
			array( 'id' => $job_title_id ),
			array( '%d' )
		);
	}

	/**
	 * Получить всех сотрудников
	 *
	 * @param array $args Аргументы фильтрации
	 * @return array Массив сотрудников
	 */
	public static function get_staff( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'active_only' => false,
			'job_title_id' => null,
			'search' => '',
			'order' => 'ASC',
			'orderby' => 'second_name',
			'per_page' => -1,
			'offset' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT s.*, j.job_title_name FROM {$wpdb->prefix}arsenal_staff s
				  LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON s.job_title_id = j.id
				  WHERE 1=1";

		if ( $args['active_only'] ) {
			$query .= " AND s.is_active = 1";
		}

		if ( ! empty( $args['job_title_id'] ) ) {
			$query .= $wpdb->prepare( " AND s.job_title_id = %d", $args['job_title_id'] );
		}

		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$query .= $wpdb->prepare(
				" AND (s.first_name LIKE %s OR s.second_name LIKE %s OR j.job_title_name LIKE %s)",
				$search,
				$search,
				$search
			);
		}

		// Сортировка
		$allowed_orderby = array( 'id', 'first_name', 'second_name', 'job_title_name', 'contract_start', 'contract_end' );
		$orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'second_name';
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$query .= " ORDER BY {$orderby} {$order}";

		if ( (int) $args['per_page'] > 0 ) {
			$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['per_page'], $args['offset'] );
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Получить сотрудника по ID
	 *
	 * @param int $staff_id ID сотрудника
	 * @return object|null Объект сотрудника
	 */
	public static function get_staff_member( $staff_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT s.*, j.job_title_name FROM {$wpdb->prefix}arsenal_staff s
				 LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON s.job_title_id = j.id
				 WHERE s.id = %d",
				$staff_id
			)
		);
	}

	/**
	 * Добавить сотрудника
	 *
	 * @param array $data Данные сотрудника
	 * @return int|false ID созданного сотрудника или false
	 */
	public static function add_staff( $data ) {
		global $wpdb;

		$insert = array(
			'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
			'second_name' => sanitize_text_field( $data['second_name'] ?? '' ),
		);

		$format = array( '%s', '%s' );

		// Опциональные поля
		if ( ! empty( $data['job_title_id'] ) ) {
			$insert['job_title_id'] = (int) $data['job_title_id'];
			$format[] = '%d';
		}

		if ( ! empty( $data['birth_date'] ) ) {
			$insert['birth_date'] = sanitize_text_field( $data['birth_date'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['contract_start'] ) ) {
			$insert['contract_start'] = sanitize_text_field( $data['contract_start'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['contract_end'] ) ) {
			$insert['contract_end'] = sanitize_text_field( $data['contract_end'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['phone'] ) ) {
			$insert['phone'] = sanitize_text_field( $data['phone'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['email'] ) ) {
			$insert['email'] = sanitize_email( $data['email'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['photo_url'] ) ) {
			$insert['photo_url'] = esc_url_raw( $data['photo_url'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['bio'] ) ) {
			$insert['bio'] = sanitize_textarea_field( $data['bio'] );
			$format[] = '%s';
		}

		$result = $wpdb->insert(
			$wpdb->prefix . 'arsenal_staff',
			$insert,
			$format
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Обновить сотрудника
	 *
	 * @param int $staff_id ID сотрудника
	 * @param array $data Данные для обновления
	 * @return bool|int Результат обновления
	 */
	public static function update_staff( $staff_id, $data ) {
		global $wpdb;

		$update = array();
		$format = array();

		if ( isset( $data['first_name'] ) ) {
			$update['first_name'] = sanitize_text_field( $data['first_name'] );
			$format[] = '%s';
		}

		if ( isset( $data['second_name'] ) ) {
			$update['second_name'] = sanitize_text_field( $data['second_name'] );
			$format[] = '%s';
		}

		if ( isset( $data['job_title_id'] ) ) {
			$update['job_title_id'] = empty( $data['job_title_id'] ) ? null : (int) $data['job_title_id'];
			$format[] = '%d';
		}

		if ( isset( $data['birth_date'] ) ) {
			$update['birth_date'] = empty( $data['birth_date'] ) ? null : sanitize_text_field( $data['birth_date'] );
			$format[] = '%s';
		}

		if ( isset( $data['contract_start'] ) ) {
			$update['contract_start'] = empty( $data['contract_start'] ) ? null : sanitize_text_field( $data['contract_start'] );
			$format[] = '%s';
		}

		if ( isset( $data['contract_end'] ) ) {
			$update['contract_end'] = empty( $data['contract_end'] ) ? null : sanitize_text_field( $data['contract_end'] );
			$format[] = '%s';
		}

		if ( isset( $data['phone'] ) ) {
			$update['phone'] = sanitize_text_field( $data['phone'] );
			$format[] = '%s';
		}

		if ( isset( $data['email'] ) ) {
			$update['email'] = sanitize_email( $data['email'] );
			$format[] = '%s';
		}

		if ( isset( $data['photo_url'] ) ) {
			$update['photo_url'] = esc_url_raw( $data['photo_url'] );
			$format[] = '%s';
		}

		if ( isset( $data['bio'] ) ) {
			$update['bio'] = sanitize_textarea_field( $data['bio'] );
			$format[] = '%s';
		}

		if ( isset( $data['is_active'] ) ) {
			$update['is_active'] = (int) $data['is_active'];
			$format[] = '%d';
		}

		$format[] = '%d'; // для WHERE

		return $wpdb->update(
			$wpdb->prefix . 'arsenal_staff',
			$update,
			array( 'id' => $staff_id ),
			$format,
			array( '%d' )
		);
	}

	/**
	 * Удалить сотрудника
	 *
	 * @param int $staff_id ID сотрудника
	 * @return bool Результат удаления
	 */
	public static function delete_staff( $staff_id ) {
		global $wpdb;

		return $wpdb->delete(
			$wpdb->prefix . 'arsenal_staff',
			array( 'id' => $staff_id ),
			array( '%d' )
		);
	}

	/**
	 * Получить количество сотрудников
	 *
	 * @param bool $active_only Только активные
	 * @return int Количество сотрудников
	 */
	public static function count_staff( $active_only = false ) {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff";

		if ( $active_only ) {
			$query .= " WHERE is_active = 1";
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Получить сотрудников по должности
	 *
	 * @param int $job_title_id ID должности
	 * @param bool $active_only Только активные
	 * @return array Массив сотрудников
	 */
	public static function get_staff_by_job_title( $job_title_id, $active_only = false ) {
		return self::get_staff(
			array(
				'job_title_id' => $job_title_id,
				'active_only' => $active_only,
				'orderby' => 'second_name',
			)
		);
	}
}
