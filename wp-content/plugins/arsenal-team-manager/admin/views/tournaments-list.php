<?php
/**
 * Шаблон: Список турниров
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tournaments-wrapper">
    <?php if ( isset( $_GET['message'] ) ) : ?>
        <?php if ( $_GET['message'] === 'created' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Турнир успешно создан.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'updated' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Турнир успешно обновлён.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Турнир успешно удалён.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'error' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="tournaments-header">
        <h1>Турниры</h1>
        <a href="<?php echo admin_url( 'admin.php?page=arsenal-tournament-add' ); ?>" class="button button-primary">+ Добавить турнир</a>
    </div>

    <div class="tournaments-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo $total; ?></span>
            <span class="stat-label">Всего турниров</span>
        </div>
    </div>

    <?php if ( ! empty( $tournaments ) ) : ?>
        <div class="tournaments-table">
            <div class="tournaments-row tournaments-header">
                <div class="tournaments-col-id">ID</div>
                <div class="tournaments-col-name">Название турнира</div>
                <div class="tournaments-col-description">Описание</div>
                <div class="tournaments-col-action">✏️</div>
                <div class="tournaments-col-action">🗑️</div>
            </div>

            <?php foreach ( $tournaments as $tournament ) : ?>
                <div class="tournaments-row">
                    <div class="tournaments-col-id"><code><?php echo esc_html( $tournament->tournament_id ); ?></code></div>
                    <div class="tournaments-col-name">
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-tournament-edit&tournament_id=' . esc_attr( $tournament->tournament_id ) ); ?>" 
                           class="tournament-name-link"><?php echo esc_html( $tournament->name ); ?></a>
                    </div>
                    <div class="tournaments-col-description">
                        <?php 
                        if ( $tournament->description ) {
                            echo esc_html( wp_trim_words( $tournament->description, 10 ) );
                        } else {
                            echo '<em style="color: #999;">Нет описания</em>';
                        }
                        ?>
                    </div>
                    <div class="tournaments-col-action">
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-tournament-edit&tournament_id=' . esc_attr( $tournament->tournament_id ) ); ?>" 
                           class="button" title="Редактировать">✏️</a>
                    </div>
                    <div class="tournaments-col-action">
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_tournament&tournament_id=' . esc_attr( $tournament->tournament_id ) ), 'arsenal_delete_tournament_' . $tournament->tournament_id ); ?>" 
                           class="button" 
                           onclick="return confirm('Вы уверены, что хотите удалить этот турнир?');" 
                           title="Удалить">🗑️</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="tournaments-pagination">
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
        <div class="tournaments-empty-message">
            <p>Турниры не найдены. <a href="<?php echo admin_url( 'admin.php?page=arsenal-tournament-add' ); ?>">Создайте первый турнир</a></p>
        </div>
    <?php endif; ?>
</div>
