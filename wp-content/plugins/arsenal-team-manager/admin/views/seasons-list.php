<?php
/**
 * Шаблон: Список сезонов
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <div class="seasons-wrapper">
        <div class="seasons-header">
            <h1>📅 Сезоны</h1>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-season-add' ); ?>" class="button button-primary">
                ➕ Добавить сезон
            </a>
        </div>

        <?php
        // Сообщения
        if ( isset( $_GET['message'] ) ) {
            $message = sanitize_text_field( $_GET['message'] );

            switch ( $message ) {
                case 'created':
                    echo '<div class="notice notice-success is-dismissible" style="margin: 20px 0;"><p>Сезон успешно создан.</p></div>';
                    break;
                case 'updated':
                    echo '<div class="notice notice-success is-dismissible" style="margin: 20px 0;"><p>Сезон успешно обновлён.</p></div>';
                    break;
                case 'deleted':
                    echo '<div class="notice notice-success is-dismissible" style="margin: 20px 0;"><p>Сезон успешно удалён.</p></div>';
                    break;
                case 'error':
                    $error_text = isset( $_GET['error_text'] ) ? urldecode( sanitize_text_field( $_GET['error_text'] ) ) : 'Произошла ошибка.';
                    echo '<div class="notice notice-error is-dismissible" style="margin: 20px 0;"><p>' . esc_html( $error_text ) . '</p></div>';
                    break;
            }
        }
        ?>

        <div class="seasons-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo $total; ?></span>
                <span class="stat-label">Всего сезонов</span>
            </div>
        </div>

        <?php if ( ! empty( $seasons ) ) : ?>
            <div class="seasons-table">
                <div class="seasons-row seasons-header">
                    <div class="seasons-col-id">ID</div>
                    <div class="seasons-col-code">Код</div>
                    <div class="seasons-col-name">Название</div>
                    <div class="seasons-col-dates">Период</div>
                    <div class="seasons-col-active">Активен</div>
                    <div class="seasons-col-action">Действия</div>
                </div>

                <?php foreach ( $seasons as $season ) : ?>
                    <div class="seasons-row">
                        <div class="seasons-col-id">
                            <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 11px;"><?php echo esc_html( $season->id ); ?></code>
                        </div>
                        <div class="seasons-col-code">
                            <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 11px;"><?php echo esc_html( $season->season_id ); ?></code>
                        </div>
                        <div class="seasons-col-name">
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-season-edit&season_id=' . $season->id ); ?>" 
                               class="season-name-link">
                                <?php echo esc_html( $season->season_name ); ?>
                            </a>
                        </div>
                        <div class="seasons-col-dates">
                            <?php
                            if ( $season->start_date && $season->end_date ) {
                                echo esc_html( date( 'd.m.Y', strtotime( $season->start_date ) ) );
                                echo ' — ';
                                echo esc_html( date( 'd.m.Y', strtotime( $season->end_date ) ) );
                            } else {
                                echo '—';
                            }
                            ?>
                        </div>
                        <div class="seasons-col-active">
                            <?php echo $season->is_active ? '<span class="season-active-badge">✓ Активен</span>' : '—'; ?>
                        </div>
                        <div class="seasons-col-action">
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-season-edit&season_id=' . $season->id ); ?>" 
                               class="button button-small" title="Редактировать">
                                ✏️
                            </a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_season&season_id=' . $season->id ), 'arsenal_delete_season_' . $season->id ); ?>" 
                               class="button button-small" title="Удалить"
                               onclick="return confirm('Удалить сезон?');">
                                🗑️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="seasons-pagination">
                    <?php
                    echo paginate_links( array(
                        'base' => add_query_arg( 'paged', '%#%' ),
                        'format' => '',
                        'prev_text' => __( '← Назад' ),
                        'next_text' => __( 'Вперед →' ),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="seasons-row seasons-empty">
                <div class="seasons-empty-message">
                    ℹ️ Сезоны не найдены
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
