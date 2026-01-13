<?php
/**
 * Шаблон: Форма редактирования спонсора в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<!-- Сообщения об успехе/ошибке -->
	<?php if ( isset( $success ) && $success ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><strong>✅ Успешно!</strong> Спонсор сохранен.</p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $deleted ) && $deleted ) : ?>
		<div class="notice notice-warning is-dismissible">
			<p><strong>🗑 Удалено!</strong> Спонсор удален.</p>
		</div>
	<?php endif; ?>
	
	<form method="post" class="player-form-wrapper">
		<?php wp_nonce_field( 'save_sponsor', 'sponsor_nonce' ); ?>
		
		<div class="player-form-section">
			<h3>Основная информация</h3>
			
			<div class="form-group">
				<label for="sponsor_name">Название спонсора *</label>
				<input 
					type="text" 
					id="sponsor_name" 
					name="sponsor_name" 
					value="<?php echo esc_attr( $sponsor->name ); ?>" 
					required
					placeholder="Например: БелТехПром"
				>
			</div>

			<div class="form-group">
				<label for="sponsor_type">Тип спонсора</label>
				<select id="sponsor_type" name="sponsor_type">
					<option value="partner" <?php selected( $sponsor->type, 'partner' ); ?>>Партнер</option>
					<option value="general_sponsor" <?php selected( $sponsor->type, 'general_sponsor' ); ?>>Генеральный спонсор</option>
				</select>
			</div>

			<div class="form-group">
				<label for="sponsor_industry">Отрасль</label>
				<input 
					type="text" 
					id="sponsor_industry" 
					name="sponsor_industry" 
					value="<?php echo esc_attr( $sponsor->industry ); ?>"
					placeholder="Например: Промышленность"
				>
			</div>

			<div class="form-group">
				<label for="sponsor_order_index">Порядок отображения</label>
				<input 
					type="number" 
					id="sponsor_order_index" 
					name="sponsor_order_index" 
					value="<?php echo esc_attr( $sponsor->order_index ); ?>"
					min="0"
				>
			</div>

			<div class="form-group">
				<label>
					<input 
						type="checkbox" 
						name="sponsor_is_active" 
						<?php checked( $sponsor->is_active, 1 ); ?>
						style="width: 18px; height: 18px; min-width: 18px; margin-right: 8px; vertical-align: middle; cursor: pointer;"
					>
					<span style="vertical-align: middle;">Активен</span>
				</label>
			</div>

			<div class="form-group">
				<label for="sponsor_description">Описание</label>
				<textarea 
					id="sponsor_description" 
					name="sponsor_description" 
					rows="6"
					placeholder="Подробное описание спонсора или партнера"
					style="width: 100%;"
				><?php echo esc_textarea( $sponsor->description ); ?></textarea>
			</div>
		</div>

		<div class="player-form-section">
			<h3>Дополнительно</h3>

			<div class="player-photo-box">
				<h4 style="margin-top: 0;">Логотип спонсора</h4>
				<div id="logo_preview">
					<?php if ( $sponsor->logo_url ) : ?>
						<img src="<?php echo esc_url( $sponsor->logo_url ); ?>" 
						     alt="Логотип спонсора" style="max-height: 150px; width: auto;">
					<?php else: ?>
						<p style="color: #999; padding: 40px 10px;">Нет логотипа</p>
					<?php endif; ?>
				</div>
				
				<input type="hidden" id="logo_url" name="sponsor_logo_url" 
				       value="<?php echo esc_attr( $sponsor->logo_url ); ?>">
				
				<button type="button" class="button button-primary" id="upload_logo_button">
					🖼️ Выбрать логотип
				</button>
				
				<?php if ( $sponsor->logo_url ) : ?>
				<button type="button" class="button" id="remove_logo_button" style="margin-top: 8px; color: #c00;">
					🗑️ Удалить логотип
				</button>
				<?php endif; ?>
			</div>

			<div class="form-group">
				<label for="sponsor_website_url">Сайт спонсора</label>
				<input 
					type="text" 
					id="sponsor_website_url" 
					name="sponsor_website_url" 
					value="<?php echo esc_attr( $sponsor->website_url ); ?>"
					placeholder="https://example.com"
				>
			</div>
		</div>

		<div class="form-buttons">
			<button type="submit" name="save_sponsor" class="button button-primary">
				<?php echo $sponsor->id ? 'Обновить спонсора' : 'Добавить спонсора'; ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-sponsors' ) ); ?>" class="button">Отмена</a>
			<?php if ( $sponsor->id ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=arsenal-sponsors&delete_sponsor=1&id=' . $sponsor->id ), 'delete_sponsor_' . $sponsor->id ) ); ?>" class="button button-danger" onclick="return confirm('Вы уверены?');">Удалить</a>
			<?php endif; ?>
		</div>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	// Загрузка логотипа через Media Library
	$('#upload_logo_button').on('click', function(e) {
		e.preventDefault();
		
		var mediaUploader = wp.media({
			title: 'Выберите логотип спонсора',
			button: {
				text: 'Использовать этот логотип'
			},
			multiple: false
		});
		
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#logo_url').val(attachment.url);
			
			// Обновляем превью
			var previewHtml = '<img src="' + attachment.url + '" alt="Логотип спонсора" style="max-height: 150px; width: auto;">';
			$('#logo_preview').html(previewHtml);
			
			// Добавляем кнопку удаления, если её нет
			if ($('#remove_logo_button').length === 0) {
				$('#upload_logo_button').after('<button type="button" class="button" id="remove_logo_button" style="margin-top: 8px; color: #c00;">🗑️ Удалить логотип</button>');
				addRemoveLogoHandler();
			}
		});
		
		mediaUploader.open();
	});
	
	// Удаление логотипа
	function addRemoveLogoHandler() {
		$('#remove_logo_button').on('click', function(e) {
			e.preventDefault();
			$('#logo_url').val('');
			$('#logo_preview').html('<p style="color: #999; padding: 40px 10px;">Нет логотипа</p>');
			$(this).remove();
		});
	}
	
	// Инициализируем удаление логотипа, если кнопка уже есть
	if ($('#remove_logo_button').length > 0) {
		addRemoveLogoHandler();
	}
});
</script>
