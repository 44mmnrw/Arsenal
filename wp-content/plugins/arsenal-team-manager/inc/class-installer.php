<?php
/**
 * Arsenal Team Manager - Plugin Installer
 *
 * Выполняется при активации плагина.
 * Инициализация БД выполняется через меню «Импорт данных» (GitHub → SQL).
 */

class Arsenal_Team_Manager_Installer {

	/**
	 * Регистрация хука активации.
	 */
	public static function init() {
		$plugin_file = dirname( dirname( __FILE__ ) ) . '/arsenal-team-manager.php';
		register_activation_hook( $plugin_file, array( __CLASS__, 'install' ) );
	}

	/**
	 * Запись версии при активации плагина.
	 */
	public static function install() {
		update_option( 'arsenal_team_manager_installed', current_time( 'mysql' ) );
		update_option( 'arsenal_team_manager_version', ARSENAL_TM_VERSION );
	}
}

if ( defined( 'ABSPATH' ) ) {
	Arsenal_Team_Manager_Installer::init();
}
