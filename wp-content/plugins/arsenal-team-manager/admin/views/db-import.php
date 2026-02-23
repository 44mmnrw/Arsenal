<?php
/**
 * Страница импорта БД (wp_arsenal_*)
 *
 * @var bool  $import_done Флаг завершённого импорта.
 * @var array $state       Текущее состояние импорта.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_url = defined( 'Arsenal_Db_Import_Admin::DEFAULT_SQL_URL' )
	? Arsenal_Db_Import_Admin::DEFAULT_SQL_URL
	: 'https://raw.githubusercontent.com/44mmnrw/Arsenal/dev_main/release/wp_arsenal_only.sql';

$current_url = ! empty( $state['sql_url'] ) ? $state['sql_url'] : $default_url;
$nonce       = wp_create_nonce( 'arsenal_db_import_nonce' );
?>

<div class="wrap">
	<h1>Импорт данных Arsenal</h1>
	<p>
		Импортируются только таблицы <code>wp_arsenal_*</code> из SQL-файла по URL.
		Тема и плагин работают автономно, импорт запускается отдельно по кнопке ниже.
	</p>

	<?php if ( $import_done ) : ?>
		<div class="notice notice-success">
			<p><strong>Импорт уже выполнен.</strong> Кнопка скрыта, чтобы избежать повторного запуска.</p>
		</div>
	<?php else : ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="arsenal-sql-url">URL SQL файла</label></th>
				<td>
					<input type="url" id="arsenal-sql-url" class="regular-text" value="<?php echo esc_attr( $current_url ); ?>" />
					<p class="description">Например raw-ссылка на файл в GitHub.</p>
				</td>
			</tr>
		</table>

		<p>
			<button type="button" id="arsenal-db-import-btn" class="button button-primary">
				Импортировать данные
			</button>
		</p>
	<?php endif; ?>

	<div id="arsenal-db-progress-wrap" style="max-width:720px; margin-top:12px; <?php echo $import_done ? '' : 'display:none;'; ?>">
		<div style="background:#f0f0f1; border-radius:8px; overflow:hidden; height:22px;">
			<div id="arsenal-db-progress-bar" style="width:<?php echo $import_done ? '100' : '0'; ?>%; background:#2271b1; color:#fff; font-weight:600; text-align:center; height:22px; line-height:22px; transition:width .2s;">
				<?php echo $import_done ? '100%' : '0%'; ?>
			</div>
		</div>
		<p id="arsenal-db-progress-text" style="margin-top:10px;"></p>
	</div>
</div>

<script>
(function($){
	const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	const nonce = '<?php echo esc_js( $nonce ); ?>';
	const startBtn = $('#arsenal-db-import-btn');
	const sqlUrlInput = $('#arsenal-sql-url');
	const wrap = $('#arsenal-db-progress-wrap');
	const bar = $('#arsenal-db-progress-bar');
	const text = $('#arsenal-db-progress-text');

	function setProgress(percent, message, details) {
		const p = Math.max(0, Math.min(100, parseInt(percent || 0, 10)));
		bar.css('width', p + '%').text(p + '%');
		text.html((message || '') + (details ? '<br><small>' + details + '</small>' : ''));
	}

	function disableButton(flag) {
		if (!startBtn.length) return;
		startBtn.prop('disabled', !!flag);
	}

	function processStep() {
		$.post(ajaxUrl, {
			action: 'arsenal_db_import_process',
			nonce: nonce
		}).done(function(resp){
			if (!resp || !resp.success) {
				const msg = resp && resp.data && resp.data.message ? resp.data.message : 'Ошибка на этапе импорта.';
				setProgress(0, msg);
				disableButton(false);
				return;
			}

			const d = resp.data || {};
			setProgress(
				d.progress_percent || 0,
				d.message || 'Импорт выполняется...',
				'Обработано: ' + (d.processed || 0) + ' | Выполнено: ' + (d.executed || 0) + ' | Ошибки: ' + (d.errors || 0)
			);

			if (d.done) {
				disableButton(true);
				if (startBtn.length) {
					startBtn.closest('p').hide();
				}
				sqlUrlInput.prop('disabled', true);
				return;
			}

			setTimeout(processStep, 180);
		}).fail(function(){
			setProgress(0, 'Ошибка сети при импорте. Можно нажать кнопку снова для продолжения.');
			disableButton(false);
		});
	}

	if (startBtn.length) {
		startBtn.on('click', function(){
			const sqlUrl = (sqlUrlInput.val() || '').trim();
			if (!sqlUrl) {
				setProgress(0, 'Укажите URL SQL файла.');
				wrap.show();
				return;
			}

			wrap.show();
			disableButton(true);
			setProgress(0, 'Скачивание SQL файла и подготовка импорта...');

			$.post(ajaxUrl, {
				action: 'arsenal_db_import_start',
				nonce: nonce,
				sql_url: sqlUrl
			}).done(function(resp){
				if (!resp || !resp.success) {
					const msg = resp && resp.data && resp.data.message ? resp.data.message : 'Не удалось запустить импорт.';
					setProgress(0, msg);
					disableButton(false);
					return;
				}

				setProgress(0, 'Импорт запущен. Выполняем обработку...');
				processStep();
			}).fail(function(){
				setProgress(0, 'Ошибка сети при запуске импорта.');
				disableButton(false);
			});
		});
	}
})(jQuery);
</script>
