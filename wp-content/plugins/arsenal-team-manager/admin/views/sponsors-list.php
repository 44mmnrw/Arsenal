<?php
/**
 * Шаблон: Список спонсоров в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Получаем данные для статистики
$total_sponsors = Arsenal_Sponsors::count_sponsors();
$general_sponsors = Arsenal_Sponsors::count_sponsors( 'general_sponsor' );
$partners = Arsenal_Sponsors::count_sponsors( 'partner' );
?>

<div class="wrap">
	<div class="sponsors-wrapper">
		<!-- Заголовок и кнопка -->
		<div class="sponsors-header">
			<h1>🤝 Спонсоры и партнеры</h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-sponsor-add' ) ); ?>" class="button button-primary">
				➕ Добавить спонсора
			</a>
		</div>

		<!-- Сообщения об успехе/ошибке -->
		<?php if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) : ?>
			<div class="notice notice-success is-dismissible" style="margin: 20px 0;">
				<p><?php esc_html_e( 'Спонсор успешно сохранён!', 'arsenal-team-manager' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 ) : ?>
			<div class="notice notice-success is-dismissible" style="margin: 20px 0;">
				<p><?php esc_html_e( 'Спонсор успешно удалён!', 'arsenal-team-manager' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
			<div class="notice notice-error is-dismissible" style="margin: 20px 0;">
				<p><?php esc_html_e( 'Произошла ошибка при обработке спонсора!', 'arsenal-team-manager' ); ?></p>
			</div>
		<?php endif; ?>

		<!-- Статистика -->
		<div class="sponsors-stats">
			<div class="stat-box">
				<div class="stat-number"><?php echo esc_html( $total_sponsors ); ?></div>
				<div class="stat-label">Всего спонсоров</div>
			</div>
			<div class="stat-box">
				<div class="stat-number"><?php echo esc_html( $general_sponsors ); ?></div>
				<div class="stat-label">Генеральных спонсоров</div>
			</div>
			<div class="stat-box">
				<div class="stat-number"><?php echo esc_html( $partners ); ?></div>
				<div class="stat-label">Партнеров</div>
			</div>
		</div>

		<!-- Таблица спонсоров -->
		<div class="sponsors-table">
			<!-- Заголовок таблицы -->
			<div class="sponsors-row sponsors-header">
				<div class="sponsors-col-id">ID</div>
				<div class="sponsors-col-name">Название</div>
				<div class="sponsors-col-type">Тип</div>
				<div class="sponsors-col-industry">Отрасль</div>
				<div class="sponsors-col-status">Статус</div>
				<div class="sponsors-col-action">Действия</div>
			</div>

			<!-- Строки таблицы -->
			<?php
			if ( ! empty( $sponsors ) ) {
				foreach ( $sponsors as $s ) {
					$type_label = 'general_sponsor' === $s->type ? 'Генеральный спонсор' : 'Партнер';
					$status = $s->is_active ? '✓ Активен' : '✗ Неактивен';
					$status_class = $s->is_active ? 'status-active' : 'status-inactive';
					?>
					<div class="sponsors-row">
						<div class="sponsors-col-id"><?php echo esc_html( $s->id ); ?></div>
						<div class="sponsors-col-name">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-sponsor-edit&id=' . $s->id ) ); ?>" class="sponsor-name-link">
								<?php echo esc_html( $s->name ); ?>
							</a>
						</div>
						<div class="sponsors-col-type"><?php echo esc_html( $type_label ); ?></div>
						<div class="sponsors-col-industry"><?php echo esc_html( $s->industry ); ?></div>
						<div class="sponsors-col-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status ); ?></div>
						<div class="sponsors-col-action">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-sponsor-edit&id=' . $s->id ) ); ?>" class="button">Редактировать</a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=arsenal-sponsors&delete_sponsor=1&id=' . $s->id ), 'delete_sponsor_' . $s->id ) ); ?>" class="button button-danger" onclick="return confirm('Вы уверены?');">Удалить</a>
						</div>
					</div>
					<?php
				}
			} else {
				?>
				<div class="sponsors-row sponsors-empty">
					<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #999;">
						Нет добавленных спонсоров
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>
