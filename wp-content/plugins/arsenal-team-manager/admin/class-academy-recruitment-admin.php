<?php
/**
 * Admin Class для управления страницей набора в академию
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Arsenal_Academy_Recruitment_Admin {

	/**
	 * Инициализация админ-интерфейса
	 */
	public function __init__() {
		add_action( 'admin_post_arsenal_update_academy_recruitment', array( $this, 'save_form' ) );
	}

	/**
	 * Вывод страницы формы
	 */
	public function render_page() {
		// Подключить стили ТОЛЬКО ТУТ
		wp_enqueue_style(
			'academy-recruitment-form',
			plugin_dir_url( __FILE__ ) . 'assets/css/academy-recruitment-form.css',
			array(),
			'1.0.4'
		);

		// Получить или создать ID для записи (используем page_id = 1)
		$page_id = 1;

		// Получить данные из таблицы
		require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';
		$data = Arsenal_Academy_Recruitment_Manager::get_page_data( $page_id );

		if ( ! $data ) {
			// Если данных нет, получить дефолтные
			$data = array(
				'hero_data'        => Arsenal_Academy_Recruitment_Manager::get_default_data()['hero_data'],
				'benefits_data'    => Arsenal_Academy_Recruitment_Manager::get_default_data()['benefits_data'],
				'age_groups_data'  => Arsenal_Academy_Recruitment_Manager::get_default_data()['age_groups_data'],
				'documents_data'   => Arsenal_Academy_Recruitment_Manager::get_default_data()['documents_data'],
				'schedule_data'    => Arsenal_Academy_Recruitment_Manager::get_default_data()['schedule_data'],
				'contacts_data'    => Arsenal_Academy_Recruitment_Manager::get_default_data()['contacts_data'],
				'faq_data'         => Arsenal_Academy_Recruitment_Manager::get_default_data()['faq_data'],
			);
		}

		// Вывести форму
		include plugin_dir_path( __FILE__ ) . 'views/academy-recruitment-form.php';
	}

	/**
	 * Получить ID страницы набора в академию
	 *
	 * @return int|null ID страницы или null
	 */
	private function get_academy_page_id() {
		// Просто возвращаем статический ID
		// Таблица может работать без привязки к реальной странице
		return 1;
	}

	/**
	 * Сохранение формы
	 */
	public function save_form() {
		// Проверка nonce
		if ( ! isset( $_POST['arsenal_academy_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['arsenal_academy_nonce'], 'arsenal_academy_recruitment_form' ) ) {
			wp_safe_remote_post( admin_url( 'admin.php?page=arsenal-academy-recruitment&error=1' ) );
			wp_die();
		}

		// Проверка прав
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Доступ запрещен' );
		}

		require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';

		$page_id = $this->get_academy_page_id();

		// Собрать данные
		$data = array();

		// Hero секция (из скрытого JSON поля)
		if ( ! empty( $_POST['hero_data'] ) ) {
			$data['hero_data'] = json_decode( wp_unslash( $_POST['hero_data'] ), true );
		}

		// Преимущества
		if ( ! empty( $_POST['benefits_data'] ) ) {
			$data['benefits_data'] = json_decode( wp_unslash( $_POST['benefits_data'] ), true );
		}

		// Возрастные группы
		if ( ! empty( $_POST['age_groups_data'] ) ) {
			$data['age_groups_data'] = json_decode( wp_unslash( $_POST['age_groups_data'] ), true );
		}

		// Документы
		if ( ! empty( $_POST['documents_data'] ) ) {
			$data['documents_data'] = json_decode( wp_unslash( $_POST['documents_data'] ), true );
		}

		// Расписание
		if ( ! empty( $_POST['schedule_data'] ) ) {
			$data['schedule_data'] = json_decode( wp_unslash( $_POST['schedule_data'] ), true );
		}

		// Контакты (из скрытого JSON поля)
		if ( ! empty( $_POST['contacts_data'] ) ) {
			$contacts = json_decode( wp_unslash( $_POST['contacts_data'] ), true );
			if ( is_array( $contacts ) ) {
				// Валидировать и санитизировать контакты
				$data['contacts_data'] = array(
					'address'           => sanitize_text_field( $contacts['address'] ?? '' ),
					'phone'             => sanitize_text_field( $contacts['phone'] ?? '' ),
					'email'             => sanitize_email( $contacts['email'] ?? '' ),
					'working_schedule'  => is_array( $contacts['working_schedule'] ?? null ) ? array_map( function( $item ) {
						return array(
							'day'  => sanitize_text_field( $item['day'] ?? '' ),
							'time' => sanitize_text_field( $item['time'] ?? '' ),
						);
					}, $contacts['working_schedule'] ) : array(),
					'google_maps_url'   => esc_url_raw( $contacts['google_maps_url'] ?? '' ),
					'yandex_maps_url'   => esc_url_raw( $contacts['yandex_maps_url'] ?? '' ),
					'director'          => is_array( $contacts['director'] ?? null ) ? array(
						'title'    => 'Директор СДЮШ',
						'name'     => sanitize_text_field( $contacts['director']['name'] ?? '' ),
						'role'     => sanitize_text_field( $contacts['director']['role'] ?? '' ),
						'contacts' => is_array( $contacts['director']['contacts'] ?? null ) ? array_map( function( $item ) {
							return array(
								'type'  => sanitize_text_field( $item['type'] ?? '' ),
								'value' => 'phone' === $item['type'] ? sanitize_text_field( $item['value'] ?? '' ) : sanitize_email( $item['value'] ?? '' ),
							);
						}, $contacts['director']['contacts'] ) : array(),
					) : array(),
				);
			}
		}

		// Социальные сети (ОТДЕЛЬНЫЙ СТОЛБЕЦ social_data)
		// Передаются ВСЕГДА, даже если пусто (как JSON массив)
		$social = array();
		if ( isset( $_POST['social_data'] ) ) {
			$social_raw = json_decode( wp_unslash( $_POST['social_data'] ), true );
			if ( is_array( $social_raw ) ) {
				$social = array_map( function( $item ) {
					return array(
						'icon' => sanitize_text_field( $item['icon'] ?? '' ),
						'url'  => esc_url_raw( $item['url'] ?? '' ),
					);
				}, $social_raw );
			}
		}
		
		// Сохраняем социальные сети ОТДЕЛЬНО
		$data['social_data'] = $social;

		// Как добраться
		if ( ! empty( $_POST['directions_data'] ) ) {
			$data['directions_data'] = json_decode( wp_unslash( $_POST['directions_data'] ), true );
		}

		// FAQ
		if ( ! empty( $_POST['faq_data'] ) ) {
			$data['faq_data'] = json_decode( wp_unslash( $_POST['faq_data'] ), true );
		}

		// Сохранить в БД
		$success = Arsenal_Academy_Recruitment_Manager::update_page_data( $page_id, $data );

		// Редирект
		$redirect_url = admin_url( 'admin.php?page=arsenal-academy-recruitment&saved=' . ( $success ? 1 : 0 ) );
		wp_safe_redirect( $redirect_url );
		exit;
	}
}
