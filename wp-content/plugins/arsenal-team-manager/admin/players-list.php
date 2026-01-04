<?php
/**
 * Список игроков команды
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Удаление полностью отключено из списка игроков
// Управление игроками только через страницу редактирования

// Получаем все доступные сезоны
$seasons = $wpdb->get_results( "SELECT id, season_name FROM wp_arsenal_seasons ORDER BY id DESC" );

// Определяем выбранный сезон (текущий или из GET параметра)
$selected_season_id = isset( $_GET['season_id'] ) ? intval( $_GET['season_id'] ) : null;
if ( ! $selected_season_id && ! empty( $seasons ) ) {
    // По умолчанию выбираем первый сезон из списка
    $selected_season_id = $seasons[0]->id;
}

// Получаем текущую дату
$current_date = current_time( 'Y-m-d' );

// Получаем игроков с активными контрактами
$players = $wpdb->get_results( $wpdb->prepare(
    "SELECT 
        p.*,
        c.contract_id,
        c.contract_start,
        c.contract_end
    FROM wp_arsenal_players p
    INNER JOIN wp_arsenal_team_contracts c ON p.player_id = c.player_id
    WHERE c.contract_start <= %s 
        AND c.contract_end >= %s
    ORDER BY p.last_name, p.first_name",
    $current_date,
    $current_date
) );

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        Игроки команды
    </h1>
    <a href="<?php echo admin_url( 'admin.php?page=arsenal-player-add' ); ?>" class="page-title-action">
        Добавить игрока
    </a>
    <hr class="wp-header-end">
    
    <p>Всего игроков: <strong><?php echo count( $players ); ?></strong></p>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 50px;">Фото</th>
                <th>Имя</th>
                <th style="width: 120px;">№ контракта</th>
                <th style="width: 120px;">Дата начала</th>
                <th style="width: 120px;">Дата конца</th>
                <th style="width: 120px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $players ): ?>
                <?php foreach ( $players as $player ): ?>
                <tr>
                    <td>
                        <?php if ( $player->photo_url ): ?>
                            <img src="<?php echo esc_url( $player->photo_url ); ?>" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <span class="dashicons dashicons-admin-users" style="font-size: 40px; color: #ccc;"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong>
                            <?php echo esc_html( ( $player->first_name ?? '' ) . ' ' . ( $player->last_name ?? '' ) ); ?>
                        </strong>
                    </td>
                    <td><?php echo esc_html( $player->contract_id ); ?></td>
                    <td><?php echo esc_html( wp_date( 'd.m.Y', strtotime( $player->contract_start ?? '' ) ) ); ?></td>
                    <td><?php echo esc_html( wp_date( 'd.m.Y', strtotime( $player->contract_end ?? '' ) ) ); ?></td>
                    <td>
                        <a href="<?php echo admin_url( 'admin.php?page=arsenal-player-edit&player_id=' . $player->player_id ); ?>" 
                           class="button button-small">
                            Редактировать
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                        Нет игроков с активными контрактами на текущую дату
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>
