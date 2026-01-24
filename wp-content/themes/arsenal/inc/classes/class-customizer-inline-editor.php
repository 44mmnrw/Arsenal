<?php
/**
 * Arsenal Customizer Inline Editor
 *
 * Переиспользуемая система для Click-to-Edit функционала
 * Позволяет редактировать любые элементы прямо в Customizer preview через popup
 *
 * @package Arsenal
 * @since 1.0.0
 *
 * ═══════════════════════════════════════════════════════════════
 * КАК ДОБАВИТЬ НОВЫЙ РЕДАКТИРУЕМЫЙ ЭЛЕМЕНТ
 * ═══════════════════════════════════════════════════════════════
 *
 * Два простых шага:
 *
 * ШАГ 1 - Регистрируем setting в customizer.php:
 * ────────────────────────────────────────────────
 * Arsenal_Customizer_Inline_Editor::register_setting(
 *     $wp_customize,
 *     'arsenal_my_element_id',      // Уникальный ID
 *     'Текст по умолчанию'           // Дефолт
 * );
 *
 * ШАГ 2 - Добавляем разметку в шаблон (любой template файл):
 * ──────────────────────────────────────────────────────────────
 * Arsenal_Customizer_Inline_Editor::render_editable_element(
 *     'arsenal_my_element_id',                    // Setting ID из шага 1
 *     'button',                                   // HTML тег
 *     array( 'class' => 'btn', 'id' => 'my-btn' ), // Атрибуты
 *     null,                                       // Контент (берёт из get_theme_mod)
 *     'Текст по умолчанию'                        // Дефолт если не установлен
 * );
 *
 * ГОТОВО! ✅ Всё остальное работает автоматически:
 * ─────────────────────────────────────────────────
 * ✓ Popup появляется при клике на элемент
 * ✓ AJAX сохраняет значение в БД (wp_options)
 * ✓ Live preview обновляет текст в реальном времени
 * ✓ Иконка карандаша появляется автоматически
 * ✓ Keyboard shortcuts работают (Enter/Escape)
 *
 * ПРИМЕРЫ:
 * ────────
 * // Кнопка на странице academy
 * Arsenal_Customizer_Inline_Editor::register_setting(
 *     $wp_customize,
 *     'arsenal_academy_button_new',
 *     'Регистрация'
 * );
 * // В шаблон:
 * Arsenal_Customizer_Inline_Editor::render_editable_element(
 *     'arsenal_academy_button_new',
 *     'button',
 *     array( 'class' => 'btn btn-secondary' )
 * );
 *
 * // Заголовок страницы
 * Arsenal_Customizer_Inline_Editor::register_setting(
 *     $wp_customize,
 *     'arsenal_page_hero_title',
 *     'Добро пожаловать!'
 * );
 * Arsenal_Customizer_Inline_Editor::render_editable_element(
 *     'arsenal_page_hero_title',
 *     'h1',
 *     array( 'class' => 'page-title' )
 * );
 *
 * ═══════════════════════════════════════════════════════════════
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для управления inline editing в Customizer
 */
class Arsenal_Customizer_Inline_Editor {

	/**
	 * Список всех зарегистрированных settings для валидации
	 *
	 * @var array
	 */
	private static $registered_settings = array();

	/**
	 * Регистрирует новый editable setting в Customizer
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer объект
	 * @param string               $setting_id   ID setting (должен быть уникальным)
	 * @param string               $default      Значение по умолчанию
	 * @param array                $args         Дополнительные аргументы (sanitize_callback, etc)
	 *
	 * @return void
	 * @example
	 *   Arsenal_Customizer_Inline_Editor::register_setting(
	 *       $wp_customize,
	 *       'my_button_text',
	 *       'Click me',
	 *       array( 'sanitize_callback' => 'sanitize_text_field' )
	 *   );
	 */
	public static function register_setting( $wp_customize, $setting_id, $default = '', $args = array() ) {
		// Сохраняем ID для использования в AJAX обработчике
		self::$registered_settings[] = $setting_id;

		// Подготавливаем аргументы
		$setting_args = wp_parse_args(
			$args,
			array(
				'default'           => $default,
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage', // Для live preview
			)
		);

		// Регистрируем setting
		$wp_customize->add_setting( $setting_id, $setting_args );
	}

	/**
	 * Выводит элемент с поддержкой inline editing
	 *
	 * @param string $setting_id ID setting для редактирования
	 * @param string $tag        HTML тег (div, button, h1, h2, a, span и т.д.)
	 * @param array  $attrs      Атрибуты элемента (class, id, href и т.д.)
	 * @param string $content    Содержимое элемента (если не указано - берёт из get_theme_mod)
	 * @param string $default    Значение по умолчанию если setting не установлен
	 *
	 * @return void
	 * @example
	 *   // Кнопка
	 *   Arsenal_Customizer_Inline_Editor::render_editable_element(
	 *       'arsenal_button_text',
	 *       'button',
	 *       array( 'class' => 'btn btn-primary' ),
	 *       null,
	 *       'Click me'
	 *   );
	 *
	 *   // Заголовок
	 *   Arsenal_Customizer_Inline_Editor::render_editable_element(
	 *       'arsenal_page_title',
	 *       'h1',
	 *       array( 'class' => 'page-title' ),
	 *       null,
	 *       'Default Title'
	 *   );
	 *
	 *   // Ссылка
	 *   Arsenal_Customizer_Inline_Editor::render_editable_element(
	 *       'arsenal_contact_link',
	 *       'a',
	 *       array( 'href' => '#contacts', 'class' => 'link' )
	 *   );
	 */
	public static function render_editable_element( $setting_id, $tag = 'div', $attrs = array(), $content = null, $default = '' ) {
		// Получаем значение из theme_mod или используем content или default
		if ( $content === null ) {
			$content = get_theme_mod( $setting_id, $default );
		}

		// Экранируем content для безопасности
		$content = esc_html( $content );

		// Подготавливаем атрибуты
		$attrs['data-customize-setting-link'] = $setting_id;

		// Преобразуем массив атрибутов в строку
		$attr_string = '';
		foreach ( $attrs as $key => $value ) {
			if ( $value !== null && $value !== false ) {
				$attr_string .= ' ' . esc_attr( $key );
				if ( $value !== true ) {
					$attr_string .= '="' . esc_attr( $value ) . '"';
				}
			}
		}

		// Выводим элемент
		echo '<' . esc_attr( $tag ) . $attr_string . '>' . $content . '</' . esc_attr( $tag ) . '>';
	}

	/**
	 * Получить список всех зарегистрированных settings
	 * Используется для валидации в AJAX обработчике
	 *
	 * @return array
	 */
	public static function get_registered_settings() {
		return self::$registered_settings;
	}

	/**
	 * Добавить setting в whitelist (для использования в AJAX)
	 *
	 * @param string $setting_id ID setting
	 *
	 * @return void
	 */
	public static function register_for_ajax( $setting_id ) {
		if ( ! in_array( $setting_id, self::$registered_settings, true ) ) {
			self::$registered_settings[] = $setting_id;
		}
	}

	/**
	 * Проверить, разрешено ли редактировать данный setting
	 *
	 * @param string $setting_id ID setting
	 *
	 * @return bool
	 */
	public static function is_setting_allowed( $setting_id ) {
		return in_array( $setting_id, self::$registered_settings, true );
	}
}
