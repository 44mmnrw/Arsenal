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
			'hero_title'       => self::get_hero_title( $post_id ),
			'hero_description' => self::get_hero_description( $post_id ),
			'stat_cards'       => self::get_stat_cards( $post_id ),
			'timeline_events'  => self::get_timeline_events( $post_id ),
			'staff_members'    => self::get_staff_members( $post_id ),
			'facilities'       => self::get_facilities( $post_id ),
			'contacts'         => self::get_contacts( $post_id ),
			'enrollment'       => self::get_enrollment( $post_id ),
		);
	}

	/**
	 * Получить заголовок hero секции
	 *
	 * @param int $post_id ID страницы
	 * @return string
	 */
	private static function get_hero_title( $post_id ) {
		return carbon_get_post_meta( $post_id, '_academy_history_hero_title' ) ?: 'История ДЮСШ';
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
					'icon'        => $event['icon'] ?? 'icon-calendar',
				);
			}
		}

		return $result;
	}

	/**
	 * Получить информацию о тренерском штабе
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
			if ( is_array( $member ) && isset( $member['name'] ) ) {
				$result[] = array(
					'name'     => $member['name'] ?? '',
					'position' => $member['position'] ?? '',
					'since'    => $member['since'] ?? '',
				);
			}
		}

		return $result;
	}

	/**
	 * Получить информацию о тренировочной базе
	 *
	 * @param int $post_id ID страницы
	 * @return array
	 */
	private static function get_facilities( $post_id ) {
		$facilities = carbon_get_post_meta( $post_id, '_academy_history_facilities' );
		
		if ( ! is_array( $facilities ) ) {
			return array();
		}

		$result = array();
		foreach ( $facilities as $facility ) {
			if ( is_array( $facility ) && isset( $facility['title'] ) ) {
				$items = array();
				if ( isset( $facility['items'] ) && is_array( $facility['items'] ) ) {
					foreach ( $facility['items'] as $item ) {
						if ( isset( $item['text'] ) ) {
							$items[] = $item['text'];
						}
					}
				}

				$result[] = array(
					'title' => $facility['title'] ?? '',
					'icon'  => $facility['icon'] ?? 'icon-building',
					'items' => $items,
				);
			}
		}

		return $result;
	}

	/**
	 * Получить контактную информацию
	 *
	 * @param int $post_id ID страницы
	 * @return array
	 */
	private static function get_contacts( $post_id ) {
		return array(
			'address'    => carbon_get_post_meta( $post_id, '_academy_history_contacts_address' ) ?? 'ул. Спортивная, 2, г. Дзержинск',
			'phone'      => carbon_get_post_meta( $post_id, '_academy_history_contacts_phone' ) ?? '+375 (17) 123-45-70',
			'email'      => carbon_get_post_meta( $post_id, '_academy_history_contacts_email' ) ?? 'academy@arsenal-dzr.by',
			'schedule'   => carbon_get_post_meta( $post_id, '_academy_history_contacts_schedule' ) ?? 'каждую субботу в 10:00',
		);
	}

	/**
	 * Получить информацию о записи в академию
	 *
	 * @param int $post_id ID страницы
	 * @return array
	 */
	private static function get_enrollment( $post_id ) {
		return array(
			'title'       => carbon_get_post_meta( $post_id, '_academy_history_enrollment_title' ) ?? 'Запись в академию',
			'description' => carbon_get_post_meta( $post_id, '_academy_history_enrollment_description' ) ?? 'Мы приглашаем детей от 8 до 17 лет на занятия в нашей футбольной академии. Тренировки проводятся профессиональными тренерами с лицензиями UEFA.',
		);
	}

	/**
	 * Получить данные по умолчанию
	 *
	 * @return array
	 */
	private static function get_default_data() {
		return array(
			'hero_title'       => 'История ДЮСШ',
			'hero_description' => 'Спортивная детско-юношеская школа "Арсенал" — футбольная академия клуба, основанная в 2010 году.',
			'stat_cards'       => array(),
			'timeline_events'  => array(),
			'staff_members'    => array(),
			'facilities'       => array(),
			'contacts'         => array(),
			'enrollment'       => array(
				'title'       => 'Запись в академию',
				'description' => '',
			),
		);
	}
}
