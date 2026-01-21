<?php
/**
 * Academy History Carbon Fields Data Adapter
 * 
 * Адаптер для чтения данных истории академии из Carbon Fields
 * и преобразования их в формат, ожидаемый фронтенд-шаблоном
 *
 * @package Arsenal
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Academy_History_Carbon_Adapter {
	
	/**
	 * Получить все данные страницы истории академии
	 *
	 * @param int $post_id ID поста (страницы)
	 * @return array Все данные страницы
	 */
	public static function get_page_data( $post_id ) {
		if ( ! $post_id ) {
			return self::get_default_data();
		}

		return array(
			'hero_description' => self::get_hero_description( $post_id ),
			'stat_cards'       => self::get_stat_cards( $post_id ),
			'timeline_events'  => self::get_timeline_events( $post_id ),
			'staff_members'    => self::get_staff_members( $post_id ),
		);
	}

	/**
	 * Получить описание в hero секции
	 *
	 * @param int $post_id ID страницы
	 * @return string
	 */
	private static function get_hero_description( $post_id ) {
		return carbon_get_post_meta( $post_id, '_academy_history_hero_description' ) ?: 
			'Спортивная детско-юношеская школа "Арсенал" — футбольная академия клуба, основанная в 2010 году. За 15 лет работы школа подготовила более 500 молодых футболистов.';
	}

	/**
	 * Получить статистические карточки
	 *
	 * @param int $post_id ID страницы
	 * @return array
	 */
	private static function get_stat_cards( $post_id ) {
		$cards = carbon_get_post_meta( $post_id, '_academy_history_stat_cards' );
		
		if ( ! is_array( $cards ) ) {
			return array();
		}

		$result = array();
		foreach ( $cards as $card ) {
			if ( is_array( $card ) && isset( $card['number'], $card['label'] ) ) {
				$result[] = array(
					'number' => $card['number'] ?? '',
					'label'  => $card['label'] ?? '',
					'icon'   => $card['icon'] ?? 'icon-cup',
				);
			}
		}

		return $result;
	}

	/**
	 * Получить события timeline
	 *
	 * @param int $post_id ID страницы
	 * @return array
	 */
	private static function get_timeline_events( $post_id ) {
		$events = carbon_get_post_meta( $post_id, '_academy_history_timeline' );
		
		if ( ! is_array( $events ) ) {
			return array();
		}

		$result = array();
		foreach ( $events as $event ) {
			if ( is_array( $event ) && isset( $event['title'], $event['year'], $event['description'] ) ) {
				$result[] = array(
					'title'       => $event['title'] ?? '',
					'year'        => $event['year'] ?? '',
					'description' => $event['description'] ?? '',
				);
			}
		}

		return $result;
	}

	/**
	 * Получить членов тренерского штаба
	 *
	 * @param int $post_id ID страницы
	 * @return array
	 */
	private static function get_staff_members( $post_id ) {
		$staff = carbon_get_post_meta( $post_id, '_academy_history_staff' );
		
		if ( ! is_array( $staff ) ) {
			return array();
		}

		$result = array();
		foreach ( $staff as $member ) {
			if ( is_array( $member ) && isset( $member['name'], $member['position'] ) ) {
				$result[] = array(
					'name'     => $member['name'] ?? '',
					'position' => $member['position'] ?? '',
					'since'    => isset( $member['since'] ) ? 'С ' . $member['since'] . ' года' : '',
				);
			}
		}

		return $result;
	}

	/**
	 * Данные по умолчанию
	 *
	 * @return array
	 */
	private static function get_default_data() {
		return array(
			'hero_description' => 'Спортивная детско-юношеская школа "Арсенал" — футбольная академия клуба, основанная в 2010 году.',
			'stat_cards'       => array(),
			'timeline_events'  => array(),
			'staff_members'    => array(),
		);
	}
}
