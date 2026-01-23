<?php
/**
 * Theme Bootstrap - Центральная точка загрузки всех компонентов темы
 *
 * @package Arsenal
 * @since 1.0.0
 */

// Безопасность: прямой доступ запрещен
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * === КЛАССЫ ===
 * Основные классы темы для работы с данными
 */

// Установщик темы
require_once ARSENAL_THEME_DIR . '/inc/class-arsenal-installer.php';

// Менеджеры данных
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-players.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-sponsors.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-management-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-department-manager.php';

// Carbon Fields адаптеры
require_once ARSENAL_THEME_DIR . '/inc/classes/class-history-carbon-adapter.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-academy-carbon-adapter.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-academy-history-carbon-adapter.php';

/**
 * === ФУНКЦИИ ===
 * Вспомогательные функции темы
 */
require_once ARSENAL_THEME_DIR . '/inc/functions/template-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/functions/match-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/functions/player-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/functions/timeline-functions.php';

/**
 * === АДМИН ИНТЕРФЕЙС ===
 * Файлы админ-панели загружаются только в контексте админки
 */
if ( is_admin() ) {
	// Metaboxes
	require_once ARSENAL_THEME_DIR . '/inc/admin/metaboxes/squad-selector-metabox.php';
	require_once ARSENAL_THEME_DIR . '/inc/admin/metaboxes/stadium-selector-metabox.php';
	require_once ARSENAL_THEME_DIR . '/inc/admin/metaboxes/staff-department-metabox.php';
}

// Customizer (загружается везде, т.к. работает на фронтенде и в админке)
require_once ARSENAL_THEME_DIR . '/inc/admin/customizer.php';

/**
 * === CARBON FIELDS ===
 * Инициализация Carbon Fields
 */
require_once ARSENAL_THEME_DIR . '/inc/carbon-fields-init.php';
