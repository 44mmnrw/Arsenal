<?php
/**
 * Arsenal Theme Settings Page
 * Кастомная админ-страница настроек темы (БЕЗ Customizer API)
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Arsenal_Settings_Page {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}
	
	/**
	 * Добавить страницу в меню Внешний вид
	 */
	public function add_settings_page() {
		error_log('ARSENAL SETTINGS: Регистрируем страницу в меню');
		$page = add_theme_page(
			'Настройки Arsenal',           // Page title
			'Настройки Arsenal',           // Menu title
			'edit_theme_options',          // Capability
			'arsenal-settings',            // Menu slug
			array( $this, 'render_page' )  // Callback
		);
		error_log('ARSENAL SETTINGS: Страница зарегистрирована, hook: ' . $page);
	}
	
	/**
	 * Регистрация настроек
	 */
	public function register_settings() {
		// БАННЕР
		register_setting( 'arsenal_settings', 'arsenal_banner_image' );
		register_setting( 'arsenal_settings', 'arsenal_banner_title' );
		register_setting( 'arsenal_settings', 'arsenal_banner_subtitle' );
		register_setting( 'arsenal_settings', 'arsenal_banner_button_text' );
		register_setting( 'arsenal_settings', 'arsenal_banner_button_link' );
		
		// ФУТЕР
		register_setting( 'arsenal_settings', 'arsenal_footer_logo' );
		register_setting( 'arsenal_settings', 'arsenal_footer_text' );
		register_setting( 'arsenal_settings', 'arsenal_footer_social_vk' );
		register_setting( 'arsenal_settings', 'arsenal_footer_social_instagram' );
		register_setting( 'arsenal_settings', 'arsenal_footer_social_youtube' );
	}
	
	/**
	 * Подключить скрипты для загрузки медиа
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'appearance_page_arsenal-settings' !== $hook ) {
			return;
		}
		
		wp_enqueue_media();
		wp_enqueue_style( 'arsenal-settings', get_template_directory_uri() . '/assets/css/admin-settings.css', array(), time() );
		wp_enqueue_script( 'arsenal-settings', get_template_directory_uri() . '/assets/js/admin-settings.js', array( 'jquery' ), time(), true );
	}
	
	/**
	 * Отобразить страницу настроек
	 */
	public function render_page() {
		error_log('ARSENAL SETTINGS: Начало render_page()');
		
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			error_log('ARSENAL SETTINGS: Нет прав доступа');
			wp_die( 'У вас нет прав доступа к этой странице.' );
		}
		
		error_log('ARSENAL SETTINGS: Права проверены, рендерим форму');
		
		// Сохранение настроек
		if ( isset( $_POST['arsenal_settings_nonce'] ) && wp_verify_nonce( $_POST['arsenal_settings_nonce'], 'arsenal_save_settings' ) ) {
			// Сохраняем все поля
			$fields = array(
				'arsenal_banner_image',
				'arsenal_banner_title',
				'arsenal_banner_subtitle',
				'arsenal_banner_button_text',
				'arsenal_banner_button_link',
				'arsenal_footer_logo',
				'arsenal_footer_text',
				'arsenal_footer_social_vk',
				'arsenal_footer_social_instagram',
				'arsenal_footer_social_youtube',
			);
			
			foreach ( $fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) {
					update_option( $field, sanitize_text_field( $_POST[ $field ] ) );
				}
			}
			
			echo '<div class="notice notice-success is-dismissible"><p>Настройки сохранены!</p></div>';
		}
		
		// Получаем текущие значения
		$banner_image        = get_option( 'arsenal_banner_image', '' );
		$banner_title        = get_option( 'arsenal_banner_title', 'ФК Арсенал Дзержинск' );
		$banner_subtitle     = get_option( 'arsenal_banner_subtitle', 'Официальный сайт' );
		$banner_button_text  = get_option( 'arsenal_banner_button_text', 'Смотреть матчи' );
		$banner_button_link  = get_option( 'arsenal_banner_button_link', '#matches' );
		
		$footer_logo         = get_option( 'arsenal_footer_logo', '' );
		$footer_text         = get_option( 'arsenal_footer_text', '© 2025 ФК Арсенал Дзержинск. Все права защищены.' );
		$footer_social_vk    = get_option( 'arsenal_footer_social_vk', '' );
		$footer_social_instagram = get_option( 'arsenal_footer_social_instagram', '' );
		$footer_social_youtube   = get_option( 'arsenal_footer_social_youtube', '' );
		?>
		
		<div class="wrap arsenal-settings-wrap">
			<h1>⚽ Настройки темы Arsenal</h1>
			<p class="description">Управление баннером, футером и другими элементами темы</p>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'arsenal_save_settings', 'arsenal_settings_nonce' ); ?>
				
				<!-- БАННЕР -->
				<div class="arsenal-settings-section">
					<h2>🎨 Баннер главной страницы</h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><label for="arsenal_banner_image">Фоновое изображение</label></th>
							<td>
								<div class="arsenal-image-upload">
									<input type="hidden" id="arsenal_banner_image" name="arsenal_banner_image" value="<?php echo esc_attr( $banner_image ); ?>" />
									<img src="<?php echo esc_url( $banner_image ); ?>" class="arsenal-preview-image" style="<?php echo $banner_image ? '' : 'display:none;'; ?>" />
									<button type="button" class="button arsenal-upload-button" data-target="arsenal_banner_image">Выбрать изображение</button>
									<button type="button" class="button arsenal-remove-button" style="<?php echo $banner_image ? '' : 'display:none;'; ?>">Удалить</button>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_banner_title">Заголовок</label></th>
							<td><input type="text" id="arsenal_banner_title" name="arsenal_banner_title" value="<?php echo esc_attr( $banner_title ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_banner_subtitle">Подзаголовок</label></th>
							<td><input type="text" id="arsenal_banner_subtitle" name="arsenal_banner_subtitle" value="<?php echo esc_attr( $banner_subtitle ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_banner_button_text">Текст кнопки</label></th>
							<td><input type="text" id="arsenal_banner_button_text" name="arsenal_banner_button_text" value="<?php echo esc_attr( $banner_button_text ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_banner_button_link">Ссылка кнопки</label></th>
							<td><input type="text" id="arsenal_banner_button_link" name="arsenal_banner_button_link" value="<?php echo esc_attr( $banner_button_link ); ?>" class="regular-text" /></td>
						</tr>
					</table>
				</div>
				
				<!-- ФУТЕР -->
				<div class="arsenal-settings-section">
					<h2>🔻 Настройки футера</h2>
					
					<table class="form-table">
						<tr>
							<th scope="row"><label for="arsenal_footer_logo">Логотип в футере</label></th>
							<td>
								<div class="arsenal-image-upload">
									<input type="hidden" id="arsenal_footer_logo" name="arsenal_footer_logo" value="<?php echo esc_attr( $footer_logo ); ?>" />
									<img src="<?php echo esc_url( $footer_logo ); ?>" class="arsenal-preview-image" style="<?php echo $footer_logo ? '' : 'display:none;'; ?>" />
									<button type="button" class="button arsenal-upload-button" data-target="arsenal_footer_logo">Выбрать изображение</button>
									<button type="button" class="button arsenal-remove-button" style="<?php echo $footer_logo ? '' : 'display:none;'; ?>">Удалить</button>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_footer_text">Текст копирайта</label></th>
							<td><textarea id="arsenal_footer_text" name="arsenal_footer_text" class="large-text" rows="3"><?php echo esc_textarea( $footer_text ); ?></textarea></td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_footer_social_vk">ВКонтакте (URL)</label></th>
							<td><input type="url" id="arsenal_footer_social_vk" name="arsenal_footer_social_vk" value="<?php echo esc_attr( $footer_social_vk ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_footer_social_instagram">Instagram (URL)</label></th>
							<td><input type="url" id="arsenal_footer_social_instagram" name="arsenal_footer_social_instagram" value="<?php echo esc_attr( $footer_social_instagram ); ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="arsenal_footer_social_youtube">YouTube (URL)</label></th>
							<td><input type="url" id="arsenal_footer_social_youtube" name="arsenal_footer_social_youtube" value="<?php echo esc_attr( $footer_social_youtube ); ?>" class="regular-text" /></td>
						</tr>
					</table>
				</div>
				
				<p class="submit">
					<button type="submit" class="button button-primary button-large">💾 Сохранить все настройки</button>
				</p>
			</form>
		</div>
		
		<?php
	}
}

// Initialize
error_log('ARSENAL SETTINGS PAGE: Инициализация класса');
add_action( 'after_setup_theme', function() {
	Arsenal_Settings_Page::get_instance();
	error_log('ARSENAL SETTINGS PAGE: Класс инициализирован успешно');
}, 1 );
