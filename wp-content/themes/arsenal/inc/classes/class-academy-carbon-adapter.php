<?php
/**
 * Carbon Fields Data Adapter
 * 
 * Адаптер для чтения данных из Carbon Fields метабоксов
 * и преобразования их в формат, ожидаемый фронтенд-шаблоном
 *
 * @package Arsenal
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Academy_Carbon_Adapter {
	/**
	 * Получить все данные страницы из Carbon Fields
	 *
	 * @param int $post_id ID поста (страницы)
	 * @return array Все данные страницы
	 */
	public static function get_page_data( $post_id ) {
		if ( ! $post_id ) {
			return self::get_default_data();
		}

		return array(
			'hero_data'       => self::get_hero_data( $post_id ),
			'benefits_data'   => self::get_benefits_data( $post_id ),
			'age_groups_data' => self::get_age_groups_data( $post_id ),
			'documents_data'  => self::get_documents_data( $post_id ),
			'schedule_data'   => self::get_schedule_data( $post_id ),
			'contacts_data'   => self::get_contacts_data( $post_id ),
			'directions_data' => self::get_directions_data( $post_id ),
			'social_data'     => self::get_social_data( $post_id ),
			'faq_data'        => self::get_faq_data( $post_id ),
		);
	}

	/**
	 * Hero секция
	 */
	private static function get_hero_data( $post_id ) {
		return array(
			'title'       => carbon_get_post_meta( $post_id, '_academy_hero_title' ) ?: 'Набор в академию',
			'description' => carbon_get_post_meta( $post_id, '_academy_hero_description' ) ?: '',
			'buttons'     => array(
				array( 'text' => 'Подать заявку', 'action' => 'apply', 'style' => 'primary' ),
				array( 'text' => 'Контакты', 'action' => '#contacts', 'style' => 'secondary' ),
			),
		);
	}

	/**
	 * Преимущества
	 */
	private static function get_benefits_data( $post_id ) {
		$benefits = carbon_get_post_meta( $post_id, '_academy_benefits' );
		return is_array( $benefits ) ? $benefits : array();
	}

	/**
	 * Возрастные группы
	 */
	private static function get_age_groups_data( $post_id ) {
		$groups = carbon_get_post_meta( $post_id, '_academy_age_groups' );
		return is_array( $groups ) ? $groups : array();
	}

	/**
	 * Документы
	 */
	private static function get_documents_data( $post_id ) {
		$items = carbon_get_post_meta( $post_id, '_academy_documents' );
		$notice = carbon_get_post_meta( $post_id, '_academy_documents_notice' );
		
		return array(
			'items'  => is_array( $items ) ? $items : array(),
			'notice' => $notice ?: '',
		);
	}

	/**
	 * Расписание просмотров
	 */
	private static function get_schedule_data( $post_id ) {
		$items = carbon_get_post_meta( $post_id, '_academy_schedule' );
		$notice = carbon_get_post_meta( $post_id, '_academy_schedule_notice' );
		
		return array(
			'items'  => is_array( $items ) ? $items : array(),
			'notice' => $notice ?: '',
		);
	}

	/**
	 * Контакты
	 */
	private static function get_contacts_data( $post_id ) {
		$working_schedule = carbon_get_post_meta( $post_id, '_academy_working_schedule' );
		
		$director_contacts = array();
		$director_phone = carbon_get_post_meta( $post_id, '_academy_director_phone' );
		$director_email = carbon_get_post_meta( $post_id, '_academy_director_email' );
		
		if ( $director_phone ) {
			$director_contacts[] = array( 'type' => 'phone', 'value' => $director_phone );
		}
		if ( $director_email ) {
			$director_contacts[] = array( 'type' => 'email', 'value' => $director_email );
		}
		
		return array(
			'address'          => carbon_get_post_meta( $post_id, '_academy_contacts_address' ) ?: '',
			'phone'            => carbon_get_post_meta( $post_id, '_academy_contacts_phone' ) ?: '',
			'email'            => carbon_get_post_meta( $post_id, '_academy_contacts_email' ) ?: '',
			'working_schedule' => is_array( $working_schedule ) ? $working_schedule : array(),
			'map_url'          => carbon_get_post_meta( $post_id, '_academy_map_url' ) ?: '',
			'director'         => array(
				'title'    => 'Директор СДЮШ',
				'name'     => carbon_get_post_meta( $post_id, '_academy_director_name' ) ?: '',
				'role'     => carbon_get_post_meta( $post_id, '_academy_director_role' ) ?: '',
				'contacts' => $director_contacts,
			),
		);
}

/**
 * Маршруты проезда
 */
private static function get_directions_data( $post_id ) {
		$items = carbon_get_post_meta( $post_id, '_academy_directions' );
		
		return array(
			'items' => is_array( $items ) ? $items : array(),
		);
	}

	/**
	 * Социальные сети
	 */
	private static function get_social_data( $post_id ) {
		$social = carbon_get_post_meta( $post_id, '_academy_social' );
		return is_array( $social ) ? $social : array();
	}

	/**
	 * FAQ
	 */
	private static function get_faq_data( $post_id ) {
		$faq = carbon_get_post_meta( $post_id, '_academy_faq' );
		return is_array( $faq ) ? $faq : array();
	}

	/**
	 * Дефолтные данные (если страница новая)
	 */
	private static function get_default_data() {
		return array(
			'hero_data'       => array(
				'title'       => 'Набор в академию',
				'description' => '',
				'buttons'     => array(
					array( 'text' => 'Подать заявку', 'action' => 'apply', 'style' => 'primary' ),
					array( 'text' => 'Контакты', 'action' => '#contacts', 'style' => 'secondary' ),
				),
			),
			'benefits_data'   => array(),
			'age_groups_data' => array(),
			'documents_data'  => array( 'items' => array(), 'notice' => '' ),
			'schedule_data'   => array( 'items' => array(), 'notice' => '' ),
			'contacts_data'   => array(
				'address'          => '',
				'phone'            => '',
				'email'            => '',
				'working_schedule' => array(),
			'map_url'          => '',
				'director'         => array(
					'title'    => 'Директор СДЮШ',
					'name'     => '',
					'role'     => '',
					'contacts' => array(),
				),
			),
			'directions_data' => array( 'items' => array() ),
			'social_data'     => array(),
			'faq_data'        => array(),
		);
	}

	/**
	 * Генерация SVG иконки из спрайта
	 *
	 * @param string $icon_id ID иконки в спрайте
	 * @param string $class   CSS класс (по умолчанию 'icon-20')
	 * @return string HTML код SVG
	 */
	public static function render_icon( $icon_id, $class = 'icon-20' ) {
		$sprite_url = get_template_directory_uri() . '/assets/images/sprite.svg';
		return sprintf(
			'<svg class="%s" viewBox="0 0 24 24" aria-hidden="true"><use xlink:href="%s#%s"></use></svg>',
			esc_attr( $class ),
			esc_url( $sprite_url ),
			esc_attr( $icon_id )
		);
	}
}
