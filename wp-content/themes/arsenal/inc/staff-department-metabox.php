<?php
/**
 * Метаокс для выбора фильтра отдела на странице сотрудников
 * 
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! function_exists( 'arsenal_register_staff_filter_metabox' ) ) {
	function arsenal_register_staff_filter_metabox() {
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
			'arsenal_staff_filters',
			'⚙️ Фильтр сотрудников',
			'arsenal_staff_filters_metabox_callback',
			'page',
			'side',
			'high',
			array( 'show_in_rest' => true )
		);
	}

	add_action( 'add_meta_boxes', 'arsenal_register_staff_filter_metabox' );
}

if ( ! function_exists( 'arsenal_staff_filters_metabox_callback' ) ) {
	function arsenal_staff_filters_metabox_callback( $post ) {
		global $wpdb;
		require_once get_template_directory() . '/inc/classes/class-arsenal-staff-department-manager.php';

		$squad_id = get_post_meta( $post->ID, '_arsenal_staff_squad_id_filter', true );
		$department_id = get_post_meta( $post->ID, '_arsenal_staff_department_filter', true );
		
		// Получаем все составы
		$squads = $wpdb->get_results( "SELECT id, squad_name FROM {$wpdb->prefix}arsenal_squad ORDER BY squad_name ASC" );
		
		// Получаем все отделы или только из выбранного состава
		if ( ! empty( $squad_id ) ) {
			$squad_obj = $wpdb->get_row( $wpdb->prepare(
				"SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d",
				intval( $squad_id )
			) );
			
			if ( $squad_obj ) {
				$departments = $wpdb->get_results( $wpdb->prepare(
					"SELECT id, department_name FROM {$wpdb->prefix}arsenal_staff_department 
					 WHERE squad_id = %s 
					 ORDER BY department_name ASC",
					$squad_obj->squad_id
				) );
			} else {
				$departments = array();
			}
		} else {
			$departments = Arsenal_Staff_Department_Manager::get_all_departments();
		}
		
		wp_nonce_field( 'arsenal_staff_filters', 'arsenal_staff_filters_nonce' );
		?>
		<!-- Фильтр состава/клуба -->
		<div style="margin-bottom: 20px;">
			<label for="arsenal_staff_squad_id_filter_select" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
				📋 Состав/Клуб:
			</label>
			<select 
				id="arsenal_staff_squad_id_filter_select"
				name="arsenal_staff_squad_id_filter" 
				style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="">— Все составы —</option>
				<?php foreach ( $squads as $s ) : ?>
					<option value="<?php echo esc_attr( $s->id ); ?>" 
						<?php selected( $squad_id, $s->id ); ?>>
						<?php echo esc_html( $s->squad_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<!-- Фильтр отдела (зависимый от состава) -->
		<div style="margin-bottom: 15px;">
			<label for="arsenal_staff_department_filter_select" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
				🏢 Отдел:
			</label>
			<select 
				id="arsenal_staff_department_filter_select"
				name="arsenal_staff_department_filter" 
				style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="">— Все отделы —</option>
				<?php foreach ( $departments as $dept ) : ?>
					<option value="<?php echo esc_attr( $dept->id ); ?>" 
						<?php selected( $department_id, $dept->id ); ?>>
						<?php echo esc_html( $dept->department_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<p style="font-size: 12px; color: #666; margin: 0;">
			Оставьте оба поля пустыми для отображения всех сотрудников
		</p>

		<hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
		<p style="font-size: 11px; color: #999; margin: 10px 0 0 0;">
			<strong>Как это работает:</strong><br>
			1️⃣ Выберите состав → список отделов обновится<br>
			2️⃣ Выберите отдел → отобразятся сотрудники этого отдела
		</p>
		<?php
	}
}

if ( ! function_exists( 'arsenal_save_staff_filters' ) ) {
	function arsenal_save_staff_filters( $post_id ) {
		global $wpdb;
		
		// Проверяем nonce
		if ( ! isset( $_POST['arsenal_staff_filters_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['arsenal_staff_filters_nonce'], 'arsenal_staff_filters' ) ) {
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

		// Сохраняем состав/клуб
		if ( isset( $_POST['arsenal_staff_squad_id_filter'] ) && ! empty( $_POST['arsenal_staff_squad_id_filter'] ) ) {
			$squad_id = intval( $_POST['arsenal_staff_squad_id_filter'] );
			update_post_meta( $post_id, '_arsenal_staff_squad_id_filter', $squad_id );
		} else {
			delete_post_meta( $post_id, '_arsenal_staff_squad_id_filter' );
		}

		// Сохраняем отдел
		if ( isset( $_POST['arsenal_staff_department_filter'] ) ) {
			$department_id = intval( $_POST['arsenal_staff_department_filter'] );
			
			// Валидация: если выбран состав, проверяем что отдел принадлежит этому составу
			$squad_id_filter = get_post_meta( $post_id, '_arsenal_staff_squad_id_filter', true );
			if ( ! empty( $department_id ) && ! empty( $squad_id_filter ) ) {
				// Получаем squad_id (VARCHAR) по numeric ID
				$squad = $wpdb->get_row( $wpdb->prepare(
					"SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d",
					intval( $squad_id_filter )
				) );
				
				if ( $squad ) {
					// Проверяем что отдел принадлежит этому составу
					$dept_check = $wpdb->get_row( $wpdb->prepare(
						"SELECT id FROM {$wpdb->prefix}arsenal_staff_department 
						 WHERE id = %d AND squad_id = %s",
						intval( $department_id ),
						$squad->squad_id
					) );
					
					if ( ! $dept_check ) {
						// Отдел не принадлежит составу - очищаем выбор
						delete_post_meta( $post_id, '_arsenal_staff_department_filter' );
						return;
					}
				}
			}
			
			update_post_meta( $post_id, '_arsenal_staff_department_filter', $department_id );
		} else {
			delete_post_meta( $post_id, '_arsenal_staff_department_filter' );
		}
	}

	add_action( 'save_post_page', 'arsenal_save_staff_filters' );
}

// AJAX обработчик для загрузки отделов по выбранному составу
if ( ! function_exists( 'arsenal_get_departments_by_squad_metabox_ajax' ) ) {
	function arsenal_get_departments_by_squad_metabox_ajax() {
		global $wpdb;
		
		check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );
		
		$squad_id = intval( $_POST['squad_id'] ?? 0 );
		
		if ( empty( $squad_id ) ) {
			wp_send_json_error( 'Squad ID not provided' );
		}
		
		// Получаем squad_id (VARCHAR) по numeric ID
		$squad = $wpdb->get_row( $wpdb->prepare(
			"SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d",
			$squad_id
		) );
		
		if ( ! $squad ) {
			wp_send_json_success( array( 'departments' => array() ) );
		}
		
		// Получаем отделы для этого состава
		$departments = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, department_name FROM {$wpdb->prefix}arsenal_staff_department 
			 WHERE squad_id = %s 
			 ORDER BY department_name ASC",
			$squad->squad_id
		) );
		
		wp_send_json_success( array( 'departments' => $departments ) );
	}
	
	add_action( 'wp_ajax_arsenal_get_departments_by_squad_metabox', 'arsenal_get_departments_by_squad_metabox_ajax' );
}

// JavaScript для каскадного селекта в объединенном метабоксе
if ( ! function_exists( 'arsenal_enqueue_metabox_script' ) ) {
	function arsenal_enqueue_metabox_script() {
		$current_screen = get_current_screen();
		
		// Только на странице редактирования поста со страницей staff-grid
		if ( $current_screen && $current_screen->base === 'post' && isset( $_GET['post'] ) ) {
			$post_id = intval( $_GET['post'] );
			$template = get_page_template_slug( $post_id );
			
			if ( $template === 'templates/page-staff-grid.php' ) {
				?><script>
				document.addEventListener('DOMContentLoaded', function() {
					const squadSelect = document.getElementById('arsenal_staff_squad_id_filter_select');
					const departmentSelect = document.getElementById('arsenal_staff_department_filter_select');
					
					if (squadSelect && departmentSelect) {
						squadSelect.addEventListener('change', function() {
							const squadId = this.value;
							
							if (!squadId) {
								// Если состав не выбран - перезагружаем страницу
								location.reload();
								return;
							}
							
							const formData = new FormData();
							formData.append('action', 'arsenal_get_departments_by_squad_metabox');
							formData.append('squad_id', squadId);
							formData.append('nonce', '<?php echo wp_create_nonce( "arsenal_staff_nonce" ); ?>');
							
							fetch(ajaxurl, {
								method: 'POST',
								body: formData
							})
							.then(response => response.json())
							.then(data => {
								if (data.success) {
									// Очищаем старые опции
									departmentSelect.innerHTML = '<option value="">— Все отделы —</option>';
									
									// Добавляем новые опции
									data.data.departments.forEach(function(dept) {
										const option = document.createElement('option');
										option.value = dept.id;
										option.textContent = dept.department_name;
										departmentSelect.appendChild(option);
									});
								}
							});
						});
					}
				});
				</script><?php
			}
		}
	}
	
	add_action( 'admin_footer', 'arsenal_enqueue_metabox_script' );
}

// Для page-management.php остается отдельный метабокс
if ( ! function_exists( 'arsenal_register_management_squad_id_metabox' ) ) {
	function arsenal_register_management_squad_id_metabox() {
		// Показываем метаокс на странице редактирования поста
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}
		
		$post_id = intval( $_GET['post'] );
		$template = get_page_template_slug( $post_id );
		
		// Показываем метаокс только для page-management.php
		if ( $template !== 'templates/page-management.php' ) {
			return;
		}

		add_meta_box(
			'arsenal_management_squad_id_filter',
			'Фильтр типа клуба',
			'arsenal_management_squad_id_metabox_callback',
			'page',
			'side',
			'high',
			array( 'show_in_rest' => true )
		);
	}

	add_action( 'add_meta_boxes', 'arsenal_register_management_squad_id_metabox' );
}

if ( ! function_exists( 'arsenal_management_squad_id_metabox_callback' ) ) {
	function arsenal_management_squad_id_metabox_callback( $post ) {
		global $wpdb;
		
		$squad_id = get_post_meta( $post->ID, '_arsenal_management_squad_id_filter', true );
		
		// Получаем составы из таблицы wp_arsenal_squad
		$squads = $wpdb->get_results( "SELECT id, squad_name FROM {$wpdb->prefix}arsenal_squad ORDER BY squad_name ASC" );
		
		wp_nonce_field( 'arsenal_management_squad_id_filter', 'arsenal_management_squad_id_filter_nonce' );
		?>
		<div style="margin-bottom: 15px;">
			<label for="arsenal_management_squad_id_filter_select" style="display: block; margin-bottom: 8px; font-weight: 500;">
				Выбрать тип клуба:
			</label>
			<select 
				id="arsenal_management_squad_id_filter_select"
				name="arsenal_management_squad_id_filter" 
				style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="">— Все типы —</option>
				<?php foreach ( $squads as $squad ) : ?>
					<option value="<?php echo esc_attr( $squad->id ); ?>" 
						<?php selected( $squad_id, $squad->id ); ?>>
						<?php echo esc_html( $squad->squad_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<p style="font-size: 12px; color: #666; margin: 0;">
			Оставьте пустым для отображения всех
		</p>
		<?php
	}
}

if ( ! function_exists( 'arsenal_save_management_squad_id_filter' ) ) {
	function arsenal_save_management_squad_id_filter( $post_id ) {
		// Проверяем nonce
		if ( ! isset( $_POST['arsenal_management_squad_id_filter_nonce'] ) || 
			 ! wp_verify_nonce( $_POST['arsenal_management_squad_id_filter_nonce'], 'arsenal_management_squad_id_filter' ) ) {
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
		if ( isset( $_POST['arsenal_management_squad_id_filter'] ) ) {
			$squad_id = intval( $_POST['arsenal_management_squad_id_filter'] );
			update_post_meta( $post_id, '_arsenal_management_squad_id_filter', $squad_id );
		} else {
			delete_post_meta( $post_id, '_arsenal_management_squad_id_filter' );
		}
	}

	add_action( 'save_post_page', 'arsenal_save_management_squad_id_filter' );
}
