<?php
/**
 * Arsenal Banner & Footer Settings - ПРОСТАЯ админ-страница
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Добавляем страницу в меню "Настройки"
add_action( 'admin_menu', 'arsenal_add_settings_page' );
function arsenal_add_settings_page() {
	add_menu_page(
		'Настройки Arsenal',     // Page title
		'Arsenal Настройки',     // Menu title
		'manage_options',        // Capability
		'arsenal-settings',      // Menu slug
		'arsenal_render_settings_page',  // Function
		'dashicons-admin-generic',       // Icon
		61                               // Position
	);
}

// Регистрируем настройки
add_action( 'admin_init', 'arsenal_register_settings' );
function arsenal_register_settings() {
	// БАННЕР
	register_setting( 'arsenal_settings_group', 'arsenal_banner_image' );
	register_setting( 'arsenal_settings_group', 'arsenal_banner_title' );
	register_setting( 'arsenal_settings_group', 'arsenal_banner_subtitle' );
	register_setting( 'arsenal_settings_group', 'arsenal_banner_button_text' );
	register_setting( 'arsenal_settings_group', 'arsenal_banner_button_link' );
	
	// ФУТЕР
	register_setting( 'arsenal_settings_group', 'arsenal_footer_logo' );
	register_setting( 'arsenal_settings_group', 'arsenal_footer_text' );
	register_setting( 'arsenal_settings_group', 'arsenal_footer_social_vk' );
	register_setting( 'arsenal_settings_group', 'arsenal_footer_social_instagram' );
	register_setting( 'arsenal_settings_group', 'arsenal_footer_social_youtube' );
}

// Подключаем медиа-загрузчик
add_action( 'admin_enqueue_scripts', 'arsenal_settings_enqueue_media' );
function arsenal_settings_enqueue_media( $hook ) {
	if ( 'toplevel_page_arsenal-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script( 'arsenal-admin-settings', get_template_directory_uri() . '/assets/js/admin-settings.js', array( 'jquery' ), time(), true );
	wp_enqueue_style( 'arsenal-admin-settings', get_template_directory_uri() . '/assets/css/admin-settings.css', array(), time() );
}

// Отображаем страницу
function arsenal_render_settings_page() {
	?>
	<div class="wrap">
		<h1>⚽ Настройки Arsenal: Баннер и Футер</h1>
		
		<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><strong>✅ Настройки сохранены!</strong></p>
			</div>
		<?php endif; ?>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'arsenal_settings_group' );
			do_settings_sections( 'arsenal_settings_group' );
			?>
			
			<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
				<h2>🎨 Баннер главной страницы</h2>
				
				<table class="form-table">
					<tr>
						<th><label>Фоновое изображение</label></th>
						<td>
							<input type="text" name="arsenal_banner_image" id="arsenal_banner_image" value="<?php echo esc_attr( get_option( 'arsenal_banner_image' ) ); ?>" class="regular-text" />
							<button type="button" class="button upload-image-button" data-target="arsenal_banner_image">Выбрать изображение</button>
							<p class="description">Рекомендуемый размер: 1920x600px</p>
						</td>
					</tr>
					<tr>
						<th><label>Заголовок</label></th>
						<td><input type="text" name="arsenal_banner_title" value="<?php echo esc_attr( get_option( 'arsenal_banner_title', 'ФК Арсенал Дзержинск' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label>Подзаголовок</label></th>
						<td><input type="text" name="arsenal_banner_subtitle" value="<?php echo esc_attr( get_option( 'arsenal_banner_subtitle', 'Официальный сайт' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label>Текст кнопки</label></th>
						<td><input type="text" name="arsenal_banner_button_text" value="<?php echo esc_attr( get_option( 'arsenal_banner_button_text', 'Смотреть матчи' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label>Ссылка кнопки</label></th>
						<td><input type="text" name="arsenal_banner_button_link" value="<?php echo esc_attr( get_option( 'arsenal_banner_button_link', '#matches' ) ); ?>" class="regular-text" /></td>
					</tr>
				</table>
			</div>
			
			<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
				<h2>🔻 Настройки футера</h2>
				
				<table class="form-table">
					<tr>
						<th><label>Логотип в футере</label></th>
						<td>
							<input type="text" name="arsenal_footer_logo" id="arsenal_footer_logo" value="<?php echo esc_attr( get_option( 'arsenal_footer_logo' ) ); ?>" class="regular-text" />
							<button type="button" class="button upload-image-button" data-target="arsenal_footer_logo">Выбрать изображение</button>
						</td>
					</tr>
					<tr>
						<th><label>Текст копирайта</label></th>
						<td><textarea name="arsenal_footer_text" class="large-text" rows="3"><?php echo esc_textarea( get_option( 'arsenal_footer_text', '© 2025 ФК Арсенал Дзержинск. Все права защищены.' ) ); ?></textarea></td>
					</tr>
					<tr>
						<th><label>ВКонтакте (URL)</label></th>
						<td><input type="url" name="arsenal_footer_social_vk" value="<?php echo esc_attr( get_option( 'arsenal_footer_social_vk' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label>Instagram (URL)</label></th>
						<td><input type="url" name="arsenal_footer_social_instagram" value="<?php echo esc_attr( get_option( 'arsenal_footer_social_instagram' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label>YouTube (URL)</label></th>
						<td><input type="url" name="arsenal_footer_social_youtube" value="<?php echo esc_attr( get_option( 'arsenal_footer_social_youtube' ) ); ?>" class="regular-text" /></td>
					</tr>
				</table>
			</div>
			
			<?php submit_button( '💾 Сохранить все настройки', 'primary', 'submit', true, array( 'style' => 'font-size: 16px; padding: 10px 30px; height: auto;' ) ); ?>
		</form>
	</div>
	<?php
}
