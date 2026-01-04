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

<div class="wrap">
    <h1 class="wp-heading-inline">Корректировки турнирной таблицы</h1>
    <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustment-add' ); ?>" class="page-title-action">Добавить корректировку</a>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['message'] ) ) : ?>
        <?php if ( $_GET['message'] === 'created' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Корректировка успешно создана.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'updated' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Корректировка успешно обновлена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Корректировка успешно удалена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'error' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <p>Всего корректировок: <strong><?php echo $total; ?></strong></p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Команда</th>
                <th style="width: 150px;">Сезон</th>
                <th style="width: 100px;">Очки</th>
                <th>Комментарий</th>
                <th style="width: 120px;">Дата применения</th>
                <th style="width: 100px;">Применил</th>
                <th style="width: 150px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $adjustments ) ) : ?>
                <?php foreach ( $adjustments as $adj ) : ?>
                    <tr>
                        <td><?php echo esc_html( $adj->id ); ?></td>
                        <td><strong><?php echo esc_html( $adj->team_name ?: 'Неизвестная команда' ); ?></strong></td>
                        <td><?php echo esc_html( $adj->season_name ?: 'N/A' ); ?></td>
                        <td>
                            <?php if ( $adj->adjustment_points > 0 ) : ?>
                                <span style="color: green; font-weight: bold;">+<?php echo intval( $adj->adjustment_points ); ?></span>
                            <?php elseif ( $adj->adjustment_points < 0 ) : ?>
                                <span style="color: red; font-weight: bold;"><?php echo intval( $adj->adjustment_points ); ?></span>
                            <?php else : ?>
                                <span>0</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( wp_trim_words( $adj->comment, 10 ) ); ?></td>
                        <td><?php echo esc_html( date( 'd.m.Y', strtotime( $adj->applied_date ) ) ); ?></td>
                        <td><?php echo esc_html( $adj->applied_by_name ?: 'N/A' ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustment-edit&adjustment_id=' . $adj->id ); ?>" 
                               class="button button-small">Изменить</a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_adjustment&adjustment_id=' . $adj->id ), 'arsenal_delete_adjustment_' . $adj->id ); ?>" 
                               class="button button-small button-link-delete"
                               onclick="return confirm('Вы уверены, что хотите удалить эту корректировку?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8">Корректировки не найдены.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links( array(
                    'base'      => add_query_arg( 'paged', '%#%' ),
                    'format'    => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total'     => $total_pages,
                    'current'   => $paged,
                ) );
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
