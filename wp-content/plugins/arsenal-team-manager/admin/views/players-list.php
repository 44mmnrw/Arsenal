<?php
/**
 * Список игроков команды
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

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
        c.contract_number,
        c.contract_start,
        c.contract_end,
        pos.name as position_name
    FROM wp_arsenal_players p
    INNER JOIN wp_arsenal_team_contracts c ON p.player_id = c.player_id
    LEFT JOIN wp_arsenal_positions pos ON p.position_id = pos.position_id
    WHERE c.contract_start <= %s 
        AND c.contract_end >= %s
    ORDER BY p.last_name, p.first_name",
    $current_date,
    $current_date
) );

?>
<div class="wrap">
    <div class="players-list-wrapper">
        <div class="players-list-header">
            <h1>👥 Игроки команды</h1>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-player-add' ); ?>" class="button button-primary">
                ➕ Добавить игрока
            </a>
        </div>

        <div class="players-list-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo count( $players ); ?></span>
                <span class="stat-label">Всего игроков</span>
            </div>
        </div>

        <div class="players-list-container">
            <div class="players-table">
                <div class="players-row players-header">
                    <div class="players-col-photo">Фото</div>
                    <div class="players-col-name">Имя</div>
                    <div class="players-col-position">Позиция</div>
                    <div class="players-col-number">№ контракта</div>
                    <div class="players-col-start">Начало</div>
                    <div class="players-col-end">Конец</div>
                    <div class="players-col-action">Действие</div>
                </div>

                <?php if ( $players ): ?>
                    <?php foreach ( $players as $player ): ?>
                    <div class="players-row">
                        <div class="players-col-photo">
                            <?php if ( $player->photo_url ): ?>
                                <img src="<?php echo esc_url( $player->photo_url ); ?>" 
                                     alt="<?php echo esc_attr( $player->first_name . ' ' . $player->last_name ); ?>"
                                     class="player-thumbnail">
                            <?php else: ?>
                                <div class="player-thumbnail-empty">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="players-col-name">
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-player-edit&player_id=' . $player->player_id ); ?>" 
                               class="player-name-link">
                                <?php echo esc_html( ( $player->first_name ?? '' ) . ' ' . ( $player->last_name ?? '' ) ); ?>
                            </a>
                        </div>
                        <div class="players-col-position">
                            <span class="player-position-badge"><?php echo esc_html( $player->position_name ?? '—' ); ?></span>
                        </div>
                        <div class="players-col-number">
                            <?php echo esc_html( $player->contract_number ?? '—' ); ?>
                        </div>
                        <div class="players-col-start">
                            <?php echo esc_html( wp_date( 'd.m.Y', strtotime( $player->contract_start ?? '' ) ) ); ?>
                        </div>
                        <div class="players-col-end">
                            <?php echo esc_html( wp_date( 'd.m.Y', strtotime( $player->contract_end ?? '' ) ) ); ?>
                        </div>
                        <div class="players-col-action">
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-player-edit&player_id=' . $player->player_id ); ?>" 
                               class="button button-small">
                                ✏️ Редактировать
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="players-row players-empty">
                        <div class="players-empty-message">
                            ℹ️ Нет игроков с активными контрактами на текущую дату
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
