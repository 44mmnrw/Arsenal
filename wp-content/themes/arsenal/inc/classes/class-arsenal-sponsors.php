<?php
/**
 * Класс для управления спонсорами и партнерами
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Sponsors {

	/**
	 * Получить всех спонсоров
	 *
	 * @param array $args Аргументы запроса
	 * @return array Массив спонсоров
	 */
	public static function get_sponsors( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'type'      => '',
			'is_active' => true,
			'orderby'   => 'order_index',
			'order'     => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT * FROM {$wpdb->prefix}arsenal_sponsors WHERE 1=1";

		if ( ! empty( $args['type'] ) ) {
			$query .= $wpdb->prepare( " AND type = %s", $args['type'] );
		}

		if ( true === $args['is_active'] ) {
			$query .= " AND is_active = 1";
		}

		// Безопасное добавление ORDER BY (уже проверенные значения)
		$allowed_orderby = array( 'id', 'name', 'type', 'order_index', 'created_at' );
		$orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'order_index';
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$query .= " ORDER BY {$orderby} {$order}";

		return $wpdb->get_results( $query );
	}

	/**
	 * Получить генерального спонсора
	 *
	 * @return object|null Объект генерального спонсора или null
	 */
	public static function get_general_sponsor() {
		global $wpdb;

		return $wpdb->get_row(
			"SELECT * FROM {$wpdb->prefix}arsenal_sponsors 
			WHERE type = 'general_sponsor' AND is_active = 1 
			LIMIT 1"
		);
	}

	/**
	 * Получить партнеров
	 *
	 * @return array Массив партнеров
	 */
	public static function get_partners() {
		return self::get_sponsors( array( 'type' => 'partner' ) );
	}

	/**
	 * Получить спонсора по ID
	 *
	 * @param int $sponsor_id ID спонсора
	 * @return object|null Объект спонсора или null
	 */
	public static function get_sponsor( $sponsor_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}arsenal_sponsors WHERE id = %d",
				$sponsor_id
			)
		);
	}

	/**
	 * Создать спонсора
	 *
	 * @param array $data Данные спонсора
	 * @return int|false ID нового спонсора или false при ошибке
	 */
	public static function create_sponsor( $data ) {
		global $wpdb;

		$defaults = array(
			'name'        => '',
			'description' => '',
			'type'        => 'partner',
			'industry'    => '',
			'logo_url'    => '',
			'website_url' => '',
			'is_active'   => 1,
			'order_index' => 0,
		);

		$data = wp_parse_args( $data, $defaults );

		$inserted = $wpdb->insert(
			"{$wpdb->prefix}arsenal_sponsors",
			array(
				'name'        => sanitize_text_field( $data['name'] ?? '' ),
				'description' => wp_kses_post( $data['description'] ?? '' ),
				'type'        => sanitize_text_field( $data['type'] ?? '' ),
				'industry'    => sanitize_text_field( $data['industry'] ?? '' ),
				'logo_url'    => esc_url_raw( $data['logo_url'] ?? '' ),
				'website_url' => esc_url_raw( $data['website_url'] ?? '' ),
				'is_active'   => absint( $data['is_active'] ?? 0 ),
				'order_index' => absint( $data['order_index'] ?? 0 ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Обновить спонсора
	 *
	 * @param int   $sponsor_id ID спонсора
	 * @param array $data Данные для обновления
	 * @return bool Успешность операции
	 */
	public static function update_sponsor( $sponsor_id, $data ) {
		global $wpdb;

		$update_data = array();

		if ( isset( $data['name'] ) ) {
			$update_data['name'] = sanitize_text_field( $data['name'] ?? '' );
		}
		if ( isset( $data['description'] ) ) {
			$update_data['description'] = wp_kses_post( $data['description'] ?? '' );
		}
		if ( isset( $data['type'] ) ) {
			$update_data['type'] = sanitize_text_field( $data['type'] ?? '' );
		}
		if ( isset( $data['industry'] ) ) {
			$update_data['industry'] = sanitize_text_field( $data['industry'] ?? '' );
		}
		if ( isset( $data['logo_url'] ) ) {
			$update_data['logo_url'] = esc_url_raw( $data['logo_url'] );
		}
		if ( isset( $data['website_url'] ) ) {
			$update_data['website_url'] = esc_url_raw( $data['website_url'] );
		}
		if ( isset( $data['is_active'] ) ) {
			$update_data['is_active'] = absint( $data['is_active'] );
		}
		if ( isset( $data['order_index'] ) ) {
			$update_data['order_index'] = absint( $data['order_index'] );
		}

		if ( empty( $update_data ) ) {
			return false;
		}

		$result = $wpdb->update(
			"{$wpdb->prefix}arsenal_sponsors",
			$update_data,
			array( 'id' => $sponsor_id ),
			null,
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Удалить спонсора
	 *
	 * @param int $sponsor_id ID спонсора
	 * @return bool Успешность операции
	 */
	public static function delete_sponsor( $sponsor_id ) {
		global $wpdb;

		$result = $wpdb->delete(
			"{$wpdb->prefix}arsenal_sponsors",
			array( 'id' => $sponsor_id ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Получить количество спонсоров
	 *
	 * @param string $type Тип спонсора (general_sponsor, partner или пусто для всех)
	 * @return int Количество спонсоров
	 */
	public static function count_sponsors( $type = '' ) {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_sponsors WHERE is_active = 1";

		if ( ! empty( $type ) ) {
			$query .= $wpdb->prepare( " AND type = %s", $type );
		}

		return (int) $wpdb->get_var( $query );
	}
}
