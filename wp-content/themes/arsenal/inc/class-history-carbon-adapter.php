<?php
/**
 * Arsenal History Carbon Adapter
 * 
 * Адаптер для получения данных истории клуба из Carbon Fields
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс адаптера для истории из Carbon Fields
 */
class Arsenal_History_Carbon_Adapter {

	/**
	 * Получить все данные истории со страницы
	 *
	 * @param int $post_id ID страницы (если не указан, используется текущая).
	 * @return array Данные истории.
	 */
	public static function get_page_data( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		return array(
			'title'               => self::get_value( '_history_title', $post_id, 'История клуба' ),
			'description'         => self::get_value( '_history_description', $post_id, '' ),
			'scale'               => self::get_scale( $post_id ),
			'title_second'        => self::get_value( '_history_title_second', $post_id, 'Рекорды и достижения' ),
			'records'             => self::get_records( $post_id ),
			'achievements'        => self::get_achievements( $post_id ),
			'title_third'         => self::get_value( '_history_title_third', $post_id, 'Дополнительная информация' ),
			'additional_cards'    => self::get_additional_cards( $post_id ),
		);
	}

	/**
	 * Получить значение простого поля
	 *
	 * @param string $key Ключ поля.
	 * @param int    $post_id ID страницы.
	 * @param mixed  $default Значение по умолчанию.
	 * @return mixed Значение поля.
	 */
	private static function get_value( $key, $post_id, $default = '' ) {
		$value = carbon_get_post_meta( $post_id, $key );
		return $value ?: $default;
	}

	/**
	 * Получить временную шкалу
	 *
	 * @param int $post_id ID страницы.
	 * @return array Массив событий.
	 */
	private static function get_scale( $post_id ) {
		$scale = carbon_get_post_meta( $post_id, '_history_scale' );
		
		if ( ! is_array( $scale ) ) {
			return array();
		}

		$result = array();
		foreach ( $scale as $item ) {
			$result[] = array(
				'year'  => isset( $item['year'] ) ? intval( $item['year'] ) : 0,
				'event' => isset( $item['event'] ) ? $item['event'] : '',
			);
		}

		return $result;
	}

	/**
	 * Получить рекорды
	 *
	 * @param int $post_id ID страницы.
	 * @return array Массив рекордов.
	 */
	private static function get_records( $post_id ) {
		$records = carbon_get_post_meta( $post_id, '_history_records' );
		
		if ( ! is_array( $records ) ) {
			return array();
		}

		$result = array();
		foreach ( $records as $record ) {
			$items = array();
			if ( isset( $record['items'] ) && is_array( $record['items'] ) ) {
				foreach ( $record['items'] as $item ) {
					if ( isset( $item['text'] ) ) {
						$items[] = $item['text'];
					}
				}
			}

			$result[] = array(
				'title' => isset( $record['title'] ) ? $record['title'] : '',
				'style' => isset( $record['style'] ) ? $record['style'] : 'primary',
				'icon'  => isset( $record['icon'] ) ? $record['icon'] : 'icon-cup',
				'items' => $items,
			);
		}

		return $result;
	}

	/**
	 * Получить достижения
	 *
	 * @param int $post_id ID страницы.
	 * @return array Массив достижений.
	 */
	private static function get_achievements( $post_id ) {
		$achievements = carbon_get_post_meta( $post_id, '_history_achievements' );
		
		if ( ! is_array( $achievements ) ) {
			return array();
		}

		$result = array();
		foreach ( $achievements as $achievement ) {
			$result[] = array(
				'label' => isset( $achievement['label'] ) ? $achievement['label'] : '',
				'value' => isset( $achievement['description'] ) ? $achievement['description'] : '',
			);
		}

		return $result;
	}

	/**
	 * Получить дополнительные карточки
	 *
	 * @param int $post_id ID страницы.
	 * @return array Массив карточек.
	 */
	private static function get_additional_cards( $post_id ) {
		$cards = carbon_get_post_meta( $post_id, '_history_additional_cards' );
		
		if ( ! is_array( $cards ) ) {
			return array();
		}

		$result = array();
		foreach ( $cards as $card ) {
			$result[] = array(
				'label' => isset( $card['label'] ) ? $card['label'] : '',
				'value' => isset( $card['description'] ) ? $card['description'] : '',
				'icon'  => isset( $card['icon'] ) ? $card['icon'] : 'icon-stadium',
			);
		}

		return $result;
	}
}
