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

<div class="leagues-wrapper">
    <?php if ( isset( $_GET['message'] ) ) : ?>
        <?php if ( $_GET['message'] === 'created' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Лига успешно создана.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'updated' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Лига успешно обновлена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Лига успешно удалена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'error' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="leagues-header">
        <h1>⚽ Лиги</h1>
        <a href="<?php echo admin_url( 'admin.php?page=arsenal-league-add' ); ?>" class="button button-primary">➕ Добавить лигу</a>
    </div>

    <div class="leagues-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo $total; ?></span>
            <span class="stat-label">Всего лиг</span>
        </div>
    </div>

    <?php if ( ! empty( $leagues ) ) : ?>
        <div class="leagues-table">
            <div class="leagues-row leagues-header">
                <div class="leagues-col-id">ID</div>
                <div class="leagues-col-code">Код</div>
                <div class="leagues-col-name">Название лиги</div>
                <div class="leagues-col-action">✏️</div>
                <div class="leagues-col-action">🗑️</div>
            </div>

            <?php foreach ( $leagues as $league ) : ?>
                <div class="leagues-row">
                    <div class="leagues-col-id"><?php echo esc_html( $league->id ); ?></div>
                    <div class="leagues-col-code"><code><?php echo esc_html( $league->league_id ); ?></code></div>
                    <div class="leagues-col-name">
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-league-edit&league_id=' . $league->id ); ?>" 
                           class="league-name-link"><?php echo esc_html( $league->league_name ); ?></a>
                    </div>
                    <div class="leagues-col-action">
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-league-edit&league_id=' . $league->id ); ?>" 
                           class="button" title="Редактировать">✏️</a>
                    </div>
                    <div class="leagues-col-action">
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_league&league_id=' . $league->id ), 'arsenal_delete_league_' . $league->id ); ?>" 
                           class="button" 
                           onclick="return confirm('Вы уверены, что хотите удалить эту лигу?');" 
                           title="Удалить">🗑️</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="leagues-pagination">
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
        <div class="leagues-empty-message">
            <p>🏆 Лиги не найдены. <a href="<?php echo admin_url( 'admin.php?page=arsenal-league-add' ); ?>">Создайте первую лигу</a></p>
        </div>
    <?php endif; ?>
</div>
