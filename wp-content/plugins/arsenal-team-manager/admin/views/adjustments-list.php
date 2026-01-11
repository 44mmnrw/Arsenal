<?php
/**
 * Шаблон: Список корректировок турнирной таблицы
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="adjustments-wrapper">
    <?php if ( isset( $_GET['message'] ) ) : ?>
        <?php if ( $_GET['message'] === 'created' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно создана.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'updated' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно обновлена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно удалена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'error' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="adjustments-header">
        <h1>📊 Корректировки таблицы</h1>
        <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustment-add' ); ?>" class="button button-primary">➕ Добавить корректировку</a>
    </div>

    <div class="adjustments-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo $total; ?></span>
            <span class="stat-label">Всего корректировок</span>
        </div>
    </div>

    <?php if ( ! empty( $adjustments ) ) : ?>
        <div class="adjustments-table">
            <div class="adjustments-row adjustments-header">
                <div class="adjustments-col-id">ID</div>
                <div class="adjustments-col-team">👥 Команда</div>
                <div class="adjustments-col-season📅 Сезон</div>
                <div class="adjustments-col-points">🌟 Очки</div>
                <div class="adjustments-col-comment">🗒️ Коммент</div>
                <div class="adjustments-col-date">📅 Дата</div>
                <div class="adjustments-col-action">✏️</div>
                <div class="adjustments-col-action">🗑️</div>
            </div>

            <?php foreach ( $adjustments as $adj ) : ?>
                <div class="adjustments-row">
                    <div class="adjustments-col-id"><?php echo esc_html( $adj->id ); ?></div>
                    <div class="adjustments-col-team">
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustment-edit&adjustment_id=' . $adj->id ); ?>" 
                           class="adjustment-team-link"><?php echo esc_html( $adj->team_name ?: 'Неизвестная' ); ?></a>
                    </div>
                    <div class="adjustments-col-season"><?php echo esc_html( $adj->season_name ?: '—' ); ?></div>
                    <div class="adjustments-col-points">
                        <?php if ( $adj->adjustment_points > 0 ) : ?>
                            <span class="points-positive">+<?php echo intval( $adj->adjustment_points ); ?></span>
                        <?php elseif ( $adj->adjustment_points < 0 ) : ?>
                            <span class="points-negative"><?php echo intval( $adj->adjustment_points ); ?></span>
                        <?php else : ?>
                            <span>0</span>
                        <?php endif; ?>
                    </div>
                    <div class="adjustments-col-comment"><?php echo esc_html( wp_trim_words( $adj->comment, 5 ) ); ?></div>
                    <div class="adjustments-col-date"><?php echo esc_html( date( 'd.m.Y', strtotime( $adj->applied_date ) ) ); ?></div>
                    <div class="adjustments-col-action">
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustment-edit&adjustment_id=' . $adj->id ); ?>" 
                           class="button" title="Редактировать">✏️</a>
                    </div>
                    <div class="adjustments-col-action">
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_adjustment&adjustment_id=' . $adj->id ), 'arsenal_delete_adjustment_' . $adj->id ); ?>" 
                           class="button" 
                           onclick="return confirm('Вы уверены, что хотите удалить эту корректировку?');" 
                           title="Удалить">🗑️</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="adjustments-pagination">
                <div class="pagination">
                    <?php
                    echo paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '← Предыдущая',
                        'next_text' => 'Следующая →',
                        'total'     => $total_pages,
                        'current'   => $paged,
                    ) );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="adjustments-empty-message">
            <p>🏆 Корректировки не найдены. <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustment-add' ); ?>">Создайте первую корректировку</a></p>
        </div>
    <?php endif; ?>
</div>
