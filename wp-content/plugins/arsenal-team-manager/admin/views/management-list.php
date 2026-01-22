<?php
/**
 * Список членов руководства
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Получаем все записи из таблицы management
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$position_filter = isset( $_GET['position'] ) ? sanitize_text_field( $_GET['position'] ) : '';

$query = "SELECT * FROM {$wpdb->prefix}arsenal_management WHERE 1=1";
$params = array();

if ( ! empty( $search ) ) {
    $query .= " AND name LIKE %s";
    $params[] = '%' . $wpdb->esc_like( $search ) . '%';
}

if ( ! empty( $position_filter ) ) {
    $query .= " AND position = %s";
    $params[] = $position_filter;
}

$query .= " ORDER BY display_order ASC, position, name ASC";

$management = $params ? $wpdb->get_results( $wpdb->prepare( $query, $params ) ) : $wpdb->get_results( $query );

// Получить список уникальных должностей
$positions = $wpdb->get_col( "SELECT DISTINCT position FROM {$wpdb->prefix}arsenal_management ORDER BY position ASC" );

?>
<div class="wrap">
    <div class="management-list-wrapper">
        <div class="management-list-header">
            <h1>👨‍💼 Руководство</h1>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-management-add' ); ?>" class="button button-primary">
                ➕ Добавить члена руководства
            </a>
        </div>

        <div class="management-list-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo count( $management ); ?></span>
                <span class="stat-label">Всего членов</span>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="management-filters">
            <form method="get" class="management-filters-form">
                <input type="hidden" name="page" value="arsenal-management">
                
                <div class="filter-group">
                    <label for="filter-position">Должность:</label>
                    <select name="position" id="filter-position">
                        <option value="">— Все должности —</option>
                        <?php foreach ( $positions as $pos ): ?>
                            <option value="<?php echo esc_attr( $pos ); ?>" <?php selected( $position_filter, $pos ); ?>>
                                <?php echo esc_html( $pos ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-search">Поиск:</label>
                    <input type="text" name="s" id="filter-search" value="<?php echo esc_attr( $search ); ?>" 
                           placeholder="Имя, фамилия...">
                </div>

                <button type="submit" class="button">Фильтр</button>
                <a href="<?php echo admin_url( 'admin.php?page=arsenal-management' ); ?>" class="button">Очистить</a>
            </form>
        </div>

        <!-- Таблица руководства -->
        <div class="management-list-container">
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="mgmt-col-photo">Фото</th>
                        <th class="mgmt-col-name">ФИО</th>
                        <th class="mgmt-col-position">Должность</th>
                        <th class="mgmt-col-club-type">Тип клуба</th>
                        <th class="mgmt-col-order">Порядок</th>
                        <th class="mgmt-col-description">Описание</th>
                        <th class="mgmt-col-action">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $management ): ?>
                        <?php foreach ( $management as $member ): ?>
                        <tr>
                            <td class="mgmt-col-photo">
                                <?php if ( $member->photo_url ): ?>
                                    <img src="<?php echo esc_url( $member->photo_url ); ?>" 
                                         alt="<?php echo esc_attr( $member->name ); ?>"
                                         class="management-thumbnail">
                                <?php else: ?>
                                    <div class="management-thumbnail-empty">
                                        <span class="dashicons dashicons-admin-users"></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="mgmt-col-name">
                                <a href="<?php echo admin_url( 'admin.php?page=arsenal-management-edit&id=' . $member->id ); ?>" 
                                   class="management-name-link">
                                    <?php echo esc_html( $member->name ); ?>
                                </a>
                            </td>
                            <td class="mgmt-col-position">
                                <span class="position-badge"><?php echo esc_html( $member->position ); ?></span>
                            </td>
                            <td class="mgmt-col-club-type">
                                <span class="club-type-badge">
                                    <?php 
                                    if ( $member->squad_id ) {
                                        $squad = $wpdb->get_row( $wpdb->prepare( "SELECT squad_name FROM {$wpdb->prefix}arsenal_squad WHERE id = %d", $member->squad_id ) );
                                        echo esc_html( $squad ? $squad->squad_name : '—' );
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="mgmt-col-order">
                                <span class="order-number"><?php echo intval( $member->display_order ); ?></span>
                            </td>
                            <td class="mgmt-col-description">
                                <span class="description-text">
                                    <?php echo esc_html( wp_trim_words( $member->description, 15 ) ); ?>
                                </span>
                            </td>
                            <td class="mgmt-col-action">
                                <a href="<?php echo admin_url( 'admin.php?page=arsenal-management-edit&id=' . $member->id ); ?>" 
                                   class="button button-small">Редактировать</a>
                                <button class="button button-small button-link-delete delete-management" 
                                        data-id="<?php echo esc_attr( $member->id ); ?>"
                                        data-name="<?php echo esc_attr( $member->name ); ?>">Удалить</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center; color: #999;">
                                <p>Нет записей. <a href="<?php echo admin_url( 'admin.php?page=arsenal-management-add' ); ?>">Добавить первую</a></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.delete-management').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        if (confirm('Удалить "' + name + '"?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'arsenal_delete_management',
                    nonce: '<?php echo wp_create_nonce( 'arsenal_management_nonce' ); ?>',
                    management_id: id
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Ошибка при удалении');
                }
            });
        }
    });
});
</script>
