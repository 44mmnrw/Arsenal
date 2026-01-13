<?php
/**
 * Класс для управления админ-страницей спонсоров
 *
 * @package Arsenal_Team_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Sponsors_Admin {

	/**
	 * Конструктор
	 */
	public function __construct() {
		// Хуки добавляются в главный файл плагина
	}

	/**
	 * Отобразить список спонсоров
	 */
	public function render_sponsors_list() {
		// Проверка прав доступа
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'У вас нет прав для доступа к этому разделу.' );
		}

		// Подключаем класс спонсоров
		require_once get_template_directory() . '/inc/class-arsenal-sponsors.php';

		// Получение данных
		$sponsors = Arsenal_Sponsors::get_sponsors( array( 'orderby' => 'order_index', 'is_active' => false ) );

		// Включение шаблона
		include dirname( __FILE__ ) . '/views/sponsors-list.php';
	}

	/**
	 * Отобразить форму добавления/редактирования спонсора
	 */
	public function render_sponsor_form() {
		// Проверка прав доступа
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'У вас нет прав для доступа к этому разделу.' );
		}

		// Подключаем класс спонсоров
		require_once get_template_directory() . '/inc/class-arsenal-sponsors.php';

		$sponsor = null;
		$is_edit = false;
		$page_title = 'Добавить спонсора';

		// Если редактируем спонсора
		if ( isset( $_GET['id'] ) ) {
			$sponsor_id = absint( $_GET['id'] );
			$sponsor = Arsenal_Sponsors::get_sponsor( $sponsor_id );

			if ( ! $sponsor ) {
				wp_die( 'Спонсор не найден.' );
			}

			$is_edit = true;
			$page_title = 'Редактировать спонсора: ' . $sponsor->name;
		} else {
			$sponsor = (object) array(
				'id'          => 0,
				'name'        => '',
				'description' => '',
				'type'        => 'partner',
				'industry'    => '',
				'logo_url'    => '',
				'website_url' => '',
				'is_active'   => 1,
				'order_index' => 0,
			);
		}

		// Обработка сообщений успеха
		$success = isset( $_GET['success'] ) ? sanitize_text_field( $_GET['success'] ) : '';
		$deleted = isset( $_GET['deleted'] ) ? sanitize_text_field( $_GET['deleted'] ) : '';

		// Обработка удаления
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) ) {
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_sponsor_' . $_GET['id'] ) ) {
				wp_die( 'Ошибка проверки безопасности.' );
			}

			$sponsor_id = absint( $_GET['id'] );
			Arsenal_Sponsors::delete_sponsor( $sponsor_id );

			wp_safe_redirect( add_query_arg( 'deleted', 1, admin_url( 'admin.php?page=arsenal-sponsors' ) ) );
			exit;
		}

		// Включение шаблона
		include dirname( __FILE__ ) . '/views/sponsor-form.php';
	}
}


