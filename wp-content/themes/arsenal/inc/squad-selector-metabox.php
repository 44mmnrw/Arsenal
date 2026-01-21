<?php
/**
 * Метабокс для выбора состава на страницах со шаблоном page-squad-grid
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Регистрация метабокса для выбора состава
 */
function arsenal_register_squad_selector_metabox() {
	global $post;
	
	// Проверяем, что это страница и используется шаблон page-squad-grid
	if ( ! $post ) {
		return;
	}
	
	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
	
	// Добавляем метабокс только если выбран шаблон Команда / Состав
	if ( 'templates/page-squad-grid.php' === $page_template ) {
		add_meta_box(
			'arsenal_squad_selector',
			__( '⚽ Выбор состава', 'arsenal' ),
			'arsenal_render_squad_selector_metabox',
			'page',
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'arsenal_register_squad_selector_metabox' );

/**
 * Вывести содержимое метабокса
 *
 * @param WP_Post $post Объект поста
 * @param array   $metabox Информация о метаоксе
 */
function arsenal_render_squad_selector_metabox( $post, $metabox ) {
	global $wpdb;

	// Получаем текущий выбранный squad_id
	$selected_squad_id = get_post_meta( $post->ID, '_arsenal_squad_id', true );

	wp_nonce_field( 'arsenal_squad_selector_nonce', 'arsenal_squad_selector_nonce' );

	// Получаем все составы из БД
	$squads = $wpdb->get_results( "SELECT id, squad_id, squad_name FROM {$wpdb->prefix}arsenal_squad ORDER BY squad_name ASC" );

	?>
	<div class="arsenal-squad-selector">
		<p style="margin-bottom: 15px;">
			<label for="arsenal_squad_id" style="display: block; margin-bottom: 8px; font-weight: 500;">
				<?php esc_html_e( 'Выберите состав:', 'arsenal' ); ?>
			</label>
			<select name="arsenal_squad_id" id="arsenal_squad_id" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
				<option value="">-- <?php esc_html_e( 'Выбрать состав', 'arsenal' ); ?> --</option>
				<?php
				if ( ! empty( $squads ) ) {
					foreach ( $squads as $squad ) {
						$selected = selected( $selected_squad_id, $squad->squad_id, false );
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $squad->squad_id ),
							$selected,
							esc_html( $squad->squad_name )
						);
					}
				}
				?>
			</select>
		</p>

		<?php if ( $selected_squad_id ) : ?>
			<?php
			// Получаем информацию о выбранном составе
			$selected_squad = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}arsenal_squad WHERE squad_id = %s",
					$selected_squad_id
				)
			);
			
			// Получаем количество игроков в составе
			$players_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT player_id) 
					 FROM {$wpdb->prefix}arsenal_team_contracts 
					 WHERE squad_id = %s 
					 AND (contract_end IS NULL OR contract_end >= CURDATE())",
					$selected_squad_id
				)
			);
			?>
			<?php if ( $selected_squad ) : ?>
				<div style="background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 15px; margin-top: 15px;">
					<div style="margin-bottom: 10px;">
						<strong style="color: #333;"><?php esc_html_e( 'Информация о выбранном составе:', 'arsenal' ); ?></strong>
					</div>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 13px; color: #666;">
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Название состава', 'arsenal' ); ?></div>
							<div><?php echo esc_html( $selected_squad->squad_name ); ?></div>
						</div>
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'ID состава', 'arsenal' ); ?></div>
							<div><code><?php echo esc_html( $selected_squad->squad_id ); ?></code></div>
						</div>
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Количество игроков', 'arsenal' ); ?></div>
							<div><?php echo $players_count > 0 ? '✓ ' . intval( $players_count ) . ' ' . __( 'игроков', 'arsenal' ) : '✗ ' . __( 'Нет игроков', 'arsenal' ); ?></div>
						</div>
						<div>
							<div style="font-weight: 500; color: #333; margin-bottom: 3px;"><?php esc_html_e( 'Дата создания', 'arsenal' ); ?></div>
							<div><?php echo ! empty( $selected_squad->created_at ) ? esc_html( date_i18n( 'd.m.Y', strtotime( $selected_squad->created_at ) ) ) : '—'; ?></div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<p style="margin-top: 15px; font-size: 12px; color: #666;">
			<?php esc_html_e( 'Совет: Используйте этот метабокс для выбора состава команды, который будет отображаться на этой странице.', 'arsenal' ); ?>
			<br>
			<?php esc_html_e( 'Все данные игроков будут загружены автоматически из таблицы wp_arsenal_team_contracts.', 'arsenal' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Сохранение выбранного состава при сохранении страницы
 *
 * @param int $post_id ID страницы
 */
function arsenal_save_squad_selector_metabox( $post_id ) {
	// Проверяем nonce
	if ( ! isset( $_POST['arsenal_squad_selector_nonce'] ) || 
	     ! wp_verify_nonce( $_POST['arsenal_squad_selector_nonce'], 'arsenal_squad_selector_nonce' ) ) {
		return;
	}

	// Проверяем права
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Получаем значение
	$squad_id = isset( $_POST['arsenal_squad_id'] ) ? sanitize_text_field( $_POST['arsenal_squad_id'] ) : '';

	// Сохраняем или удаляем метаполе
	if ( ! empty( $squad_id ) ) {
		update_post_meta( $post_id, '_arsenal_squad_id', $squad_id );
	} else {
		delete_post_meta( $post_id, '_arsenal_squad_id' );
	}
}
add_action( 'save_post_page', 'arsenal_save_squad_selector_metabox' );
