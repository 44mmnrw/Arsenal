<?php
/**
 * Шаблон: Список сезонов
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Сезоны</h1>
    <a href="<?php echo admin_url( 'admin.php?page=arsenal-season-add' ); ?>" class="page-title-action">Добавить новый</a>
    
    <?php
    // Сообщения
    if ( isset( $_GET['message'] ) ) {
        $message = sanitize_text_field( $_GET['message'] );
        
        switch ( $message ) {
            case 'created':
                echo '<div class="notice notice-success is-dismissible"><p>Сезон успешно создан.</p></div>';
                break;
            case 'updated':
                echo '<div class="notice notice-success is-dismissible"><p>Сезон успешно обновлён.</p></div>';
                break;
            case 'deleted':
                echo '<div class="notice notice-success is-dismissible"><p>Сезон успешно удалён.</p></div>';
                break;
            case 'error':
                echo '<div class="notice notice-error is-dismissible"><p>Произошла ошибка.</p></div>';
                break;
        }
    }
    ?>
    
    <hr class="wp-header-end">
    
    <p class="search-box">
        Всего записей: <strong><?php echo $total; ?></strong>
    </p>
    
    <?php if ( ! empty( $seasons ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 100px;">Код сезона</th>
                    <th>Название</th>
                    <th>Даты</th>
                    <th style="width: 80px;">Активен</th>
                    <th style="width: 150px;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $seasons as $season ) : ?>
                    <tr>
                        <td><?php echo esc_html( $season->id ); ?></td>
                        <td><code><?php echo esc_html( $season->season_id ); ?></code></td>
                        <td>
                            <strong><?php echo esc_html( $season->season_name ); ?></strong>
                        </td>
                        <td>
                            <?php 
                            if ( $season->start_date && $season->end_date ) {
                                echo esc_html( date( 'd.m.Y', strtotime( $season->start_date ) ) );
                                echo ' — ';
                                echo esc_html( date( 'd.m.Y', strtotime( $season->end_date ) ) );
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo $season->is_active ? '✓' : '—'; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-season-edit&season_id=' . $season->id ); ?>" class="button button-small">Редактировать</a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_season&season_id=' . $season->id ), 'arsenal_delete_season_' . $season->id ); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Вы уверены, что хотите удалить этот сезон?');">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ( $total_pages > 1 ) : ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links( array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => __( '&laquo;' ),
                        'next_text' => __( '&raquo;' ),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <p>Сезоны не найдены.</p>
    <?php endif; ?>
</div>
