<?php
/**
 * Метаокс для выбора фильтра отдела на странице сотрудников
 * 
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! function_exists( 'arsenal_register_staff_department_metabox' ) ) {
	function arsenal_register_staff_department_metabox() {
		// Показываем метаокс на странице редактирования поста
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}
		
		$post_id = intval( $_GET['post'] );
		$template = get_page_template_slug( $post_id );
		
		// Показываем метаокс только для page-staff-grid.php
		if ( $template !== 'templates/page-staff-grid.php' ) {
			return;
		}
		
		require_once get_template_directory() . '/inc/classes/class-arsenal-staff-department-manager.php';

		add_meta_box(
			'arsenal_staff_department_filter',
			'Фильтр отдела',
			'arsenal_staff_department_metabox_callback',
			'page',
			'side',
			'high',
			array( 'show_in_rest' => true )
		);
	}

	add_action( 'add_meta_boxes', 'arsenal_register_staff_department_metabox' );
}

if ( ! function_exists( 'arsenal_staff_department_metabox_callback' ) ) {
	function arsenal_staff_department_metabox_callback( $post ) {
		require_once get_template_directory() . '/inc/classes/class-arsenal-staff-department-manager.php';

		$department_id = get_post_meta( $post->ID, '_arsenal_staff_department_filter', true );
		$departments = Arsenal_Staff_Department_Manager::get_all_departments();
		
		wp_nonce_field( 'arsenal_staff_department_filter', 'arsenal_staff_department_filter_nonce' );
		?>
		<div style="margin-bottom: 15px;">
			<label for="arsenal_staff_department_filter_select" style="display: block; margin-bottom: 8px; font-weight: 500;">
				Выбрать отдел:
			</label>
			<select 
				id="arsenal_staff_department_filter_select"
				name="arsenal_staff_department_filter" 
				style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="">— Все сотрудники —</option>
				<?php foreach ( $departments as $dept ) : ?>
					<option value="<?php echo esc_attr( $dept->id ); ?>" 
						<?php selected( $department_id, $dept->id ); ?>>
						<?php echo esc_html( $dept->department_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<p style="font-size: 12px; color: #666; margin: 0;">
			Оставьте пустым для отображения всех сотрудников
		</p>
		<?php
	}
}

if ( ! function_exists( 'arsenal_save_staff_department_filter' ) ) {
	function arsenal_save_staff_department_filter( $post_id ) {
		// Проверяем nonce
		if ( ! isset( $_POST['arsenal_staff_department_filter_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['arsenal_staff_department_filter_nonce'], 'arsenal_staff_department_filter' ) ) {
			return;
		}

		// Проверяем, что это не автосохранение
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Проверяем права
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Сохраняем значение
		if ( isset( $_POST['arsenal_staff_department_filter'] ) ) {
			$department_id = sanitize_text_field( $_POST['arsenal_staff_department_filter'] );
			update_post_meta( $post_id, '_arsenal_staff_department_filter', $department_id );
		} else {
			delete_post_meta( $post_id, '_arsenal_staff_department_filter' );
		}
	}

	add_action( 'save_post_page', 'arsenal_save_staff_department_filter' );
}
