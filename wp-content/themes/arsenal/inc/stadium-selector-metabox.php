<?php
/**
 * Метабокс для выбора стадиона на страницах со шаблоном page-stadium
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Регистрация метабокса для выбора стадиона
 */
function arsenal_register_stadium_selector_metabox() {
	// Добавляем метабокс на все страницы
	add_meta_box(
		'arsenal_stadium_selector',
		__( '🏟️ Выбор стадиона', 'arsenal' ),
		'arsenal_render_stadium_selector_metabox',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'arsenal_register_stadium_selector_metabox' );

/**
 * Вывести содержимое метабокса
 *
 * @param WP_Post $post Объект поста
 * @param array   $metabox Информация о метаоксе
 */
function arsenal_render_stadium_selector_metabox( $post, $metabox ) {
	global $wpdb;

	// Проверяем, используется ли шаблон page-stadium
	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
	
	// Получаем текущий выбранный стадион
	$selected_stadium_id = get_post_meta( $post->ID, '_arsenal_stadium_id', true );

	wp_nonce_field( 'arsenal_stadium_selector_nonce', 'arsenal_stadium_selector_nonce' );

	// Получаем все стадионы из БД
	$stadiums = $wpdb->get_results( "SELECT id, name, city, capacity FROM {$wpdb->prefix}arsenal_stadiums ORDER BY name ASC" );

	?>
	<div class="arsenal-stadium-selector">
		<p style="margin-bottom: 15px;">
			<label for="arsenal_stadium_id" style="display: block; margin-bottom: 8px; font-weight: 500;">
				<?php esc_html_e( 'Выберите стадион:', 'arsenal' ); ?>
			</label>
			<select name="arsenal_stadium_id" id="arsenal_stadium_id" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="">-- <?php esc_html_e( 'Выбрать стадион', 'arsenal' ); ?> --</option>
				<?php
				if ( ! empty( $stadiums ) ) {
					foreach ( $stadiums as $stadium ) {
						$selected = selected( $selected_stadium_id, $stadium->id, false );
						$city = ! empty( $stadium->city ) ? ' (' . esc_html( $stadium->city ) . ')' : '';
						printf(
							'<option value="%d" %s>%s%s</option>',
							intval( $stadium->id ),
							$selected,
							esc_html( $stadium->name ),
							$city
						);
					}
				}
				?>
			</select>
		</p>

		<?php if ( $selected_stadium_id ) : ?>
			<?php
			$selected_stadium = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}arsenal_stadiums WHERE id = %d",
					intval( $selected_stadium_id )
				)
			);
			?>
			<?php if ( $selected_stadium ) : ?>
				<div style="background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 15px; margin-top: 15px;">
					<div style="margin-bottom: 10px;">
						<strong style="color: #333;"><?php esc_html_e( 'Информация о выбранном стадионе:', 'arsenal' ); ?></strong>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 13px; color: #666;">
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Название', 'arsenal' ); ?></div>
							<div><?php echo esc_html( $selected_stadium->name ); ?></div>
						</div>
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Город', 'arsenal' ); ?></div>
							<div><?php echo ! empty( $selected_stadium->city ) ? esc_html( $selected_stadium->city ) : '—'; ?></div>
						</div>
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Вместимость', 'arsenal' ); ?></div>
							<div><?php echo ! is_null( $selected_stadium->capacity ) ? number_format( $selected_stadium->capacity ) . ' ' . __( 'мест', 'arsenal' ) : '—'; ?></div>
						</div>
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Дополнительные поля', 'arsenal' ); ?></div>
							<div><?php 
								$extra_fields = 0;
								if ( ! empty( $selected_stadium->stat_cards ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->contacts ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->description ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->infrastructure ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->tech_features ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->sectors ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->history ) ) $extra_fields++;
								if ( ! empty( $selected_stadium->to_get ) ) $extra_fields++;
								echo $extra_fields > 0 ? '✓ ' . $extra_fields . ' ' . __( 'полей заполнено', 'arsenal' ) : '✗ ' . __( 'Не заполнено', 'arsenal' );
							?></div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<p style="margin-top: 15px; font-size: 12px; color: #666;">
			<?php esc_html_e( 'Совет: Используйте этот метабокс для выбора стадиона, информацию которого вы хотите вывести на этой странице.', 'arsenal' ); ?>
			<br>
			<?php esc_html_e( 'Все данные будут загружены автоматически из базы данных при отображении страницы.', 'arsenal' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Сохранение выбранного стадиона при сохранении страницы
 *
 * @param int $post_id ID страницы
 */
function arsenal_save_stadium_selector_metabox( $post_id ) {
	// Проверяем nonce
	if ( ! isset( $_POST['arsenal_stadium_selector_nonce'] ) || 
	     ! wp_verify_nonce( $_POST['arsenal_stadium_selector_nonce'], 'arsenal_stadium_selector_nonce' ) ) {
		return;
	}

	// Проверяем права
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Получаем значение
	$stadium_id = isset( $_POST['arsenal_stadium_id'] ) ? intval( $_POST['arsenal_stadium_id'] ) : 0;

	// Сохраняем или удаляем метаполе
	if ( $stadium_id > 0 ) {
		update_post_meta( $post_id, '_arsenal_stadium_id', $stadium_id );
	} else {
		delete_post_meta( $post_id, '_arsenal_stadium_id' );
	}
}
add_action( 'save_post_page', 'arsenal_save_stadium_selector_metabox' );

/**
 * Показываем метабокс только для шаблона page-stadium через JavaScript
 */
function arsenal_hide_stadium_selector_on_template() {
	global $post;

	if ( ! $post || get_post_type( $post ) !== 'page' ) {
		return;
	}

	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

	// Добавляем JavaScript для скрытия/показания метабокса
	?>
	<script type="text/javascript">
	(function() {
		const templateSelect = document.getElementById('page_template');
		const stadiumMetabox = document.getElementById('arsenal_stadium_selector');
		
		if (!templateSelect || !stadiumMetabox) {
			return;
		}

		function toggleStadiumMetabox() {
			const selectedTemplate = templateSelect.value;
			if (selectedTemplate === 'page-stadium.php' || selectedTemplate === '') {
				stadiumMetabox.style.display = 'block';
			} else {
				stadiumMetabox.style.display = 'none';
			}
		}

		// Проверяем при загрузке страницы
		toggleStadiumMetabox();

		// Проверяем при изменении шаблона
		templateSelect.addEventListener('change', toggleStadiumMetabox);
	})();
	</script>
	<?php
}
add_action( 'edit_form_after_title', 'arsenal_hide_stadium_selector_on_template' );
