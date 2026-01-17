<?php
/**
 * Arsenal Players Manager Class
 * 
 * Управление игроками клуба
 * 
 * @package Arsenal_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Players {

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
	 * Получить всех игроков
	 *
	 * @param array $args Аргументы фильтрации
	 * @return array Массив игроков
	 */
	public static function get_players( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'active_only' => false,
			'position_id' => null,
			'search' => '',
			'order' => 'ASC',
			'orderby' => 'last_name',
			'per_page' => -1,
			'offset' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT p.*, pos.name as position_name 
				  FROM {$wpdb->prefix}arsenal_players p
				  LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
				  WHERE 1=1";

		if ( ! empty( $args['position_id'] ) ) {
			$query .= $wpdb->prepare( " AND p.position_id = %s", $args['position_id'] );
		}

		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$query .= $wpdb->prepare(
				" AND (p.first_name LIKE %s OR p.last_name LIKE %s OR p.full_name LIKE %s)",
				$search,
				$search,
				$search
			);
		}

		// Сортировка
		$allowed_orderby = array( 'id', 'player_id', 'first_name', 'last_name', 'full_name', 'position_name', 'shirt_number', 'birth_date' );
		$orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'last_name';
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$query .= " ORDER BY {$orderby} {$order}";

		if ( (int) $args['per_page'] > 0 ) {
			$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['per_page'], $args['offset'] );
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Получить игрока по ID
	 *
	 * @param string|int $player_id ID игрока (player_id или id)
	 * @return object|null Объект игрока
	 */
	public static function get_player( $player_id ) {
		global $wpdb;

		// Пробуем сначала по player_id (строка)
		$player = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT p.*, pos.name as position_name 
				 FROM {$wpdb->prefix}arsenal_players p
				 LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
				 WHERE p.player_id = %s LIMIT 1",
				sanitize_text_field( $player_id )
			)
		);

		// Если не найден, пробуем по числовому id
		if ( ! $player && is_numeric( $player_id ) ) {
			$player = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT p.*, pos.name as position_name 
					 FROM {$wpdb->prefix}arsenal_players p
					 LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
					 WHERE p.id = %d LIMIT 1",
					intval( $player_id )
				)
			);
		}

		return $player;
	}

	/**
	 * Добавить игрока
	 *
	 * @param array $data Данные игрока
	 * @return int|false ID созданного игрока или false
	 */
	public static function add_player( $data ) {
		global $wpdb;

		$insert = array(
			'player_id' => sanitize_text_field( $data['player_id'] ?? '' ),
			'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
			'last_name' => sanitize_text_field( $data['last_name'] ?? '' ),
		);

		$format = array( '%s', '%s', '%s' );

		// Опциональные поля
		if ( ! empty( $data['full_name'] ) ) {
			$insert['full_name'] = sanitize_text_field( $data['full_name'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['shirt_number'] ) ) {
			$insert['shirt_number'] = intval( $data['shirt_number'] );
			$format[] = '%d';
		}

		if ( ! empty( $data['position_id'] ) ) {
			$insert['position_id'] = sanitize_text_field( $data['position_id'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['birth_date'] ) ) {
			$insert['birth_date'] = sanitize_text_field( $data['birth_date'] ?? '' );
			$format[] = '%s';
		}

		if ( ! empty( $data['citizenship'] ) ) {
			$insert['citizenship'] = sanitize_text_field( $data['citizenship'] ?? '' );
			$format[] = '%s';
		}

		if ( ! empty( $data['height_cm'] ) ) {
			$insert['height_cm'] = intval( $data['height_cm'] );
			$format[] = '%d';
		}

		if ( ! empty( $data['weight_kg'] ) ) {
			$insert['weight_kg'] = intval( $data['weight_kg'] );
			$format[] = '%d';
		}

		if ( ! empty( $data['photo_url'] ) ) {
			$insert['photo_url'] = esc_url_raw( $data['photo_url'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['biography'] ) ) {
			$insert['biography'] = wp_kses_post( $data['biography'] );
			$format[] = '%s';
		}

		if ( ! empty( $data['team_id'] ) ) {
			$insert['team_id'] = intval( $data['team_id'] );
			$format[] = '%d';
		}

		$result = $wpdb->insert(
			$wpdb->prefix . 'arsenal_players',
			$insert,
			$format
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Обновить игрока
	 *
	 * @param int|string $player_id ID игрока
	 * @param array $data Данные для обновления
	 * @return bool|int Результат обновления
	 */
	public static function update_player( $player_id, $data ) {
		global $wpdb;

		$update = array();
		$format = array();
		$where_field = is_numeric( $player_id ) ? 'id' : 'player_id';
		$where_value = is_numeric( $player_id ) ? intval( $player_id ) : sanitize_text_field( $player_id );

		if ( isset( $data['first_name'] ) ) {
			$update['first_name'] = sanitize_text_field( $data['first_name'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['last_name'] ) ) {
			$update['last_name'] = sanitize_text_field( $data['last_name'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['full_name'] ) ) {
			$update['full_name'] = sanitize_text_field( $data['full_name'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['shirt_number'] ) ) {
			$update['shirt_number'] = intval( $data['shirt_number'] ?? 0 );
			$format[] = '%d';
		}

		if ( isset( $data['position_id'] ) ) {
			$update['position_id'] = sanitize_text_field( $data['position_id'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['birth_date'] ) ) {
			$update['birth_date'] = empty( $data['birth_date'] ) ? null : sanitize_text_field( $data['birth_date'] );
			$format[] = '%s';
		}

		if ( isset( $data['citizenship'] ) ) {
			$update['citizenship'] = sanitize_text_field( $data['citizenship'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['height_cm'] ) ) {
			$update['height_cm'] = intval( $data['height_cm'] ?? 0 );
			$format[] = '%d';
		}

		if ( isset( $data['weight_kg'] ) ) {
			$update['weight_kg'] = intval( $data['weight_kg'] ?? 0 );
			$format[] = '%d';
		}

		if ( isset( $data['photo_url'] ) ) {
			$update['photo_url'] = esc_url_raw( $data['photo_url'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['biography'] ) ) {
			$update['biography'] = wp_kses_post( $data['biography'] ?? '' );
			$format[] = '%s';
		}

		if ( isset( $data['team_id'] ) ) {
			$update['team_id'] = intval( $data['team_id'] ?? 0 );
			$format[] = '%d';
		}

		// Если нет полей для обновления, возвращаем false
		if ( empty( $update ) ) {
			return false;
		}

		$format[] = is_numeric( $where_value ) ? '%d' : '%s'; // для WHERE

		return $wpdb->update(
			$wpdb->prefix . 'arsenal_players',
			$update,
			array( $where_field => $where_value ),
			$format,
			is_numeric( $where_value ) ? array( '%d' ) : array( '%s' )
		);
	}

	/**
	 * Удалить игрока
	 *
	 * @param int|string $player_id ID игрока
	 * @return bool Результат удаления
	 */
	public static function delete_player( $player_id ) {
		global $wpdb;

		$where_field = is_numeric( $player_id ) ? 'id' : 'player_id';
		$where_value = is_numeric( $player_id ) ? intval( $player_id ) : sanitize_text_field( $player_id );

		return $wpdb->delete(
			$wpdb->prefix . 'arsenal_players',
			array( $where_field => $where_value ),
			is_numeric( $where_value ) ? array( '%d' ) : array( '%s' )
		);
	}

	/**
	 * Получить количество игроков
	 *
	 * @return int Количество игроков
	 */
	public static function count_players() {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_players" );
	}

	/**
	 * Получить позицию игрока
	 *
	 * @param string $position_id ID позиции
	 * @return object|null Объект позиции
	 */
	public static function get_position( $position_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}arsenal_positions WHERE position_id = %s LIMIT 1",
				sanitize_text_field( $position_id )
			)
		);
	}

	/**
	 * Получить все позиции
	 *
	 * @return array Массив позиций
	 */
	public static function get_positions() {
		global $wpdb;

		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}arsenal_positions ORDER BY name ASC" );
	}

	/**
	 * Получить статистику игрока
	 *
	 * @param string $player_id ID игрока
	 * @param string $tournament_id ID турнира (опционально)
	 * @param int $year Год (опционально)
	 * @return array Статистика игрока
	 */
	public static function get_player_stats( $player_id, $tournament_id = null, $year = null ) {
		global $wpdb;

		$where = $wpdb->prepare( "WHERE ml.player_id = %s", sanitize_text_field( $player_id ) );

		if ( ! empty( $tournament_id ) ) {
			$where .= $wpdb->prepare( " AND m.tournament_id = %s", sanitize_text_field( $tournament_id ) );
		}

		if ( ! empty( $year ) ) {
			$where .= $wpdb->prepare( " AND YEAR(m.match_date) = %d", intval( $year ) );
		}

		$query = "
			SELECT 
				ml.player_id,
				COUNT(DISTINCT m.match_id) as matches_played,
				SUM(CASE WHEN ml.is_starting = 1 THEN 1 ELSE 0 END) as matches_started,
				SUM(CASE WHEN e.event_type = 'goal' AND e.player_id = ml.player_id THEN 1 ELSE 0 END) as goals,
				SUM(CASE WHEN e.event_type = 'yellow_card' AND e.player_id = ml.player_id THEN 1 ELSE 0 END) as yellow_cards,
				SUM(CASE WHEN e.event_type = 'red_card' AND e.player_id = ml.player_id THEN 1 ELSE 0 END) as red_cards,
				SUM(CASE WHEN e.event_type = 'assist' AND e.player_id = ml.player_id THEN 1 ELSE 0 END) as assists
			FROM {$wpdb->prefix}arsenal_match_lineups ml
			INNER JOIN {$wpdb->prefix}arsenal_matches m ON ml.match_id = m.match_id
			LEFT JOIN {$wpdb->prefix}arsenal_match_events e ON m.match_id = e.match_id
			{$where}
			GROUP BY ml.player_id
		";

		return $wpdb->get_row( $query );
	}

	/**
	 * Получить игроков по позиции
	 *
	 * @param string $position_id ID позиции
	 * @return array Массив игроков
	 */
	public static function get_players_by_position( $position_id ) {
		return self::get_players(
			array(
				'position_id' => $position_id,
				'orderby' => 'shirt_number',
				'order' => 'ASC',
			)
		);
	}
}
