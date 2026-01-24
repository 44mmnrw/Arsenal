<?php
/**
 * Библиотека иконок из sprite.svg
 *
 * Страница админки для просмотра всех доступных иконок
 * Иконки динамически парсятся из sprite.svg
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Путь к sprite.svg
$sprite_path = get_template_directory() . '/assets/images/sprite.svg';
$sprite_url = get_template_directory_uri() . '/assets/images/sprite.svg';

// Загружаем и парсим SVG файл
$icons = array();
if ( file_exists( $sprite_path ) ) {
	$svg_content = file_get_contents( $sprite_path );
	
	// Создаем SimpleXMLElement для парсинга
	$xml = simplexml_load_string( $svg_content );
	
	// Пройдемся по всем символам
	if ( $xml && isset( $xml->symbol ) ) {
		foreach ( $xml->symbol as $symbol ) {
			$icon_id = (string) $symbol['id'];
			$icon_name = (string) $symbol['data-name'];
			$icon_category = (string) $symbol['data-category'];
			
			// Пропускаем иконки без метаданных
			if ( empty( $icon_name ) || empty( $icon_category ) ) {
				continue;
			}
			
			$icons[] = array(
				'id' => $icon_id,
				'name' => $icon_name,
				'category' => $icon_category,
			);
		}
	}
}

// Если парсинг не сработал, используем пустой массив
if ( empty( $icons ) ) {
	$icons = array();
}

// Сгруппировать иконки по категориям
$icons_by_category = array();
foreach ( $icons as $icon ) {
	$category = $icon['category'];
	if ( ! isset( $icons_by_category[ $category ] ) ) {
		$icons_by_category[ $category ] = array();
	}
	$icons_by_category[ $category ][] = $icon;
}

// Сортировка категорий по алфавиту
ksort( $icons_by_category );
?>

<div class="wrap icon-library-wrapper">
	<h1>Библиотека иконок</h1>
	
	<div class="icon-library-header">
		<p>Все доступные иконки из <code>sprite.svg</code>. Иконки динамически парсятся из файла.</p>
		<p><strong>Всего иконок:</strong> <?php echo count( $icons ); ?> | <strong>Категорий:</strong> <?php echo count( $icons_by_category ); ?></p>
	</div>
	
	<?php if ( ! empty( $icons ) ) : ?>
		<?php foreach ( $icons_by_category as $category => $category_icons ) : ?>
			<div class="icon-category">
				<h2><?php echo esc_html( $category ); ?> (<?php echo count( $category_icons ); ?>)</h2>
				<div class="icon-grid">
					<?php foreach ( $category_icons as $icon ) : ?>
						<div class="icon-item">
							<div class="icon-preview">
								<svg class="icon" width="12" height="12">
									<use xlink:href="<?php echo esc_url( $sprite_url ); ?>#<?php echo esc_attr( $icon['id'] ); ?>"></use>
								</svg>
							</div>
							<div class="icon-details">
								<div class="icon-name"><?php echo esc_html( $icon['name'] ); ?></div>
								<div class="icon-id">ID: <code><?php echo esc_html( $icon['id'] ); ?></code></div>
								<button class="button button-small copy-icon-id" data-id="<?php echo esc_attr( $icon['id'] ); ?>">
									Скопировать
								</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="icon-library-error">
			<p>Не удалось загрузить иконки. Проверьте, что файл <code>sprite.svg</code> существует и содержит иконки с метаданными <code>data-name</code> и <code>data-category</code>.</p>
		</div>
	<?php endif; ?>
</div>

<style>
.icon-library-wrapper {
	max-width: 1200px;
	margin: 20px auto;
}

.icon-library-header {
	background: #fff;
	padding: 20px;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	margin-bottom: 30px;
}

.icon-library-header p {
	margin: 10px 0;
	font-size: 14px;
}

.icon-library-error {
	background: #fff;
	padding: 25px;
	margin-bottom: 25px;
	border: 1px solid #cc0000;
	border-radius: 4px;
	box-shadow: 0 1px 1px rgba(204, 0, 0, 0.1);
}

.icon-library-error p {
	margin: 0;
	color: #cc0000;
	font-size: 14px;
	line-height: 1.6;
}

.icon-category {
	background: #fff;
	padding: 25px;
	margin-bottom: 25px;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.icon-category h2 {
	margin: 0 0 20px 0;
	padding-bottom: 10px;
	border-bottom: 2px solid #0073aa;
	color: #0073aa;
	font-size: 16px;
}

.icon-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 20px;
}

.icon-item {
	padding: 15px;
	background: #f9f9f9;
	border: 1px solid #eee;
	border-radius: 4px;
	transition: all 0.3s ease;
	text-align: center;
}

.icon-item:hover {
	box-shadow: 0 2px 8px rgba(0,115,170,0.15);
	border-color: #0073aa;
	transform: translateY(-2px);
}

.icon-preview {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 70px;
	margin-bottom: 15px;
	background: #fff;
	border-radius: 4px;
	border: 1px dashed #ccc;
}

.icon-preview .icon {
	width: 60%;
	height: 60%;
	color: #0073aa;
	flex-shrink: 0;
}

.icon-details {
	text-align: left;
}

.icon-name {
	font-weight: 600;
	color: #333;
	font-size: 13px;
	margin-bottom: 8px;
	min-height: 36px;
	display: flex;
	align-items: center;
}

.icon-id {
	background: #f0f0f0;
	padding: 6px 8px;
	border-radius: 3px;
	font-size: 12px;
	margin-bottom: 10px;
	word-break: break-all;
}

.icon-id code {
	background: transparent;
	color: #0073aa;
	font-weight: 600;
}

.copy-icon-id {
	width: 100%;
	text-align: center;
	border-color: #0073aa;
	color: #0073aa;
	background: transparent;
}

.copy-icon-id:hover {
	background: #0073aa;
	color: #fff;
}

@media (max-width: 782px) {
	.icon-grid {
		grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
		gap: 15px;
	}

	.icon-category {
		padding: 15px;
	}

	.icon-item {
		padding: 12px;
	}

	.icon-preview {
		height: 50px;
	}

	.icon-preview .icon {
		width: 10px;
		height: 10px;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	$('.copy-icon-id').on('click', function(e) {
		e.preventDefault();

		var iconId = $(this).data('id');
		var $btn = $(this);

		// Скопировать в буфер обмена
		if (navigator.clipboard) {
			navigator.clipboard.writeText(iconId).then(function() {
				var originalText = $btn.text();
				$btn.text('✓ Скопировано');

				setTimeout(function() {
					$btn.text(originalText);
				}, 2000);
			});
		} else {
			// Fallback для старых браузеров
			var $temp = $('<input>');
			$('body').append($temp);
			$temp.val(iconId).select();
			document.execCommand('copy');
			$temp.remove();

			var originalText = $btn.text();
			$btn.text('✓ Скопировано');

			setTimeout(function() {
				$btn.text(originalText);
			}, 2000);
		}
	});
});
</script>
