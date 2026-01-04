<?php
/**
 * Шаблон: Список лиг
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Лиги</h1>
    <a href="<?php echo admin_url( 'admin.php?page=arsenal-league-add' ); ?>" class="page-title-action">Добавить лигу</a>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['message'] ) ) : ?>
        <?php if ( $_GET['message'] === 'created' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Лига успешно создана.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'updated' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Лига успешно обновлена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Лига успешно удалена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'error' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <p>Всего лиг: <strong><?php echo $total; ?></strong></p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 100px;">Код</th>
                <th>Название лиги</th>
                <th style="width: 150px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $leagues ) ) : ?>
                <?php foreach ( $leagues as $league ) : ?>
                    <tr>
                        <td><?php echo esc_html( $league->id ); ?></td>
                        <td><code><?php echo esc_html( $league->league_id ); ?></code></td>
                        <td><strong><?php echo esc_html( $league->league_name ); ?></strong></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-league-edit&league_id=' . $league->id ); ?>" 
                               class="button button-small">Изменить</a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_league&league_id=' . $league->id ), 'arsenal_delete_league_' . $league->id ); ?>" 
                               class="button button-small button-link-delete"
                               onclick="return confirm('Вы уверены, что хотите удалить эту лигу?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4">Лиги не найдены.</td>
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
