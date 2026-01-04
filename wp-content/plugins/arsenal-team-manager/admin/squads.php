<?php
/**
 * Страница управления составами по сезонам
 * Использует таблицы wp_arsenal_team_season_squads и wp_arsenal_squad_status
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Обработка действий
$message = '';
$error = '';

if ( isset( $_POST['action'] ) && check_admin_referer( 'arsenal_squad_action' ) ) {
    $action = $_POST['action'];
    
    if ( $action === 'add_player' ) {
        $player_id = sanitize_text_field( $_POST['player_id'] );
        $season_id = sanitize_text_field( $_POST['season_id'] );
        $status = sanitize_text_field( $_POST['status'] );
        
        // Генерируем уникальный squad_id (MD5)
        $squad_id = substr( md5( $season_id . $player_id . time() ), 0, 8 );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_team_season_squads',
            array(
                'squad_id' => $squad_id,
                'team_season_id' => $season_id,
                'player_id' => $player_id,
                'status' => $status,
                'is_active' => 1
            ),
            array( '%s', '%s', '%s', '%s', '%d' )
        );
        
        if ( $result ) {
            $message = 'Игрок добавлен в состав';
        } else {
            $error = 'Ошибка при добавлении: ' . $wpdb->last_error;
        }
    }
    
    if ( $action === 'remove_player' ) {
        $squad_entry_id = intval( $_POST['squad_entry_id'] );
        $wpdb->delete( 
            $wpdb->prefix . 'arsenal_team_season_squads', 
            array( 'id' => $squad_entry_id ), 
            array( '%d' ) 
        );
        $message = 'Игрок удалён из состава';
    }
    
    if ( $action === 'toggle_status' ) {
        $squad_entry_id = intval( $_POST['squad_entry_id'] );
        $new_status = sanitize_text_field( $_POST['new_status'] );
        
        $wpdb->update(
            $wpdb->prefix . 'arsenal_team_season_squads',
            array( 'status' => $new_status ),
            array( 'id' => $squad_entry_id ),
            array( '%s' ),
            array( '%d' )
        );
        
        $message = 'Статус обновлён';
    }
}

// Получаем список сезонов
$seasons = $wpdb->get_results( "SELECT season_id, year FROM {$wpdb->prefix}arsenal_seasons ORDER BY year DESC" );

// Текущий выбранный сезон
$current_season_id = isset( $_GET['season_id'] ) ? sanitize_text_field( $_GET['season_id'] ) : ( $seasons[0]->season_id ?? null );

if ( ! $current_season_id && ! empty( $seasons ) ) {
    $current_season_id = $seasons[0]->season_id;
}

// Получаем составы для выбранного сезона
$squads = array();
if ( $current_season_id ) {
    $squads = $wpdb->get_results( $wpdb->prepare(
        "SELECT 
            s.id,
            s.squad_id,
            s.player_id,
            s.status,
            s.debut_date,
            s.contract_end,
            s.is_active,
            p.full_name_ru,
            p.photo_url,
            pos.position_name,
            season.year
        FROM {$wpdb->prefix}arsenal_team_season_squads s
        LEFT JOIN {$wpdb->prefix}arsenal_players p ON s.player_id = p.player_id
        LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
        LEFT JOIN {$wpdb->prefix}arsenal_seasons season ON s.team_season_id = season.season_id
        WHERE s.team_season_id = %s
        ORDER BY s.status, pos.position_name, p.full_name_ru",
        $current_season_id
    ) );
}

// Разделяем на основной и дублирующий состав
$main_squad = array_filter( $squads, function( $item ) { return $item->status === 'Основной'; } );
$reserve_squad = array_filter( $squads, function( $item ) { return $item->status === 'Дублирующий'; } );

// Получаем ID команды Арсенал
$arsenal_team_id = $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}arsenal_teams WHERE is_arsenal = 1 LIMIT 1" );

// Получаем всех игроков Арсенала для добавления
$all_players = array();
if ( $arsenal_team_id ) {
    $all_players = $wpdb->get_results( $wpdb->prepare(
        "SELECT DISTINCT
            p.player_id,
            p.full_name_ru,
            pos.position_name
        FROM {$wpdb->prefix}arsenal_players p
        LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
        WHERE p.is_active = 1
        ORDER BY pos.position_name, p.full_name_ru",
        $arsenal_team_id
    ) );
}

// ID игроков уже в составе
$squad_player_ids = array_column( $squads, 'player_id' );
$available_players = array_filter( $all_players, function( $p ) use ( $squad_player_ids ) {
    return ! in_array( $p->player_id, $squad_player_ids );
} );

?>

<div class="wrap arsenal-admin">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups"></span>
        Составы по сезонам
    </h1>
    
    <?php if ( $message ): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ( $error ): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $error ); ?></p>
        </div>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <!-- Выбор сезона -->
    <div class="arsenal-filters" style="margin: 20px 0;">
        <label for="season-select" style="font-weight: 600; margin-right: 10px;">Сезон:</label>
        <select id="season-select" onchange="location.href='?page=arsenal-squads&season_id=' + this.value" style="padding: 5px 10px;">
            <?php foreach ( $seasons as $season ): ?>
                <option value="<?php echo esc_attr( $season->season_id ); ?>" <?php selected( $season->season_id, $current_season_id ); ?>>
                    <?php echo esc_html( $season->season_name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="button" class="button button-primary" style="margin-left: 20px;" onclick="document.getElementById('add-player-form').style.display='block'">
            + Добавить игрока
        </button>
    </div>
    
    <!-- Форма добавления игрока -->
    <div id="add-player-form" style="display: none; background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
        <h3>Добавить игрока в состав</h3>
        <form method="post">
            <?php wp_nonce_field( 'arsenal_squad_action' ); ?>
            <input type="hidden" name="action" value="add_player">
            <input type="hidden" name="season_id" value="<?php echo esc_attr( $current_season_id ); ?>">
            
            <table class="form-table">
                <tr>
                    <th><label for="player_id">Игрок</label></th>
                    <td>
                        <select name="player_id" id="player_id" required style="min-width: 300px;">
                            <option value="">-- Выберите игрока --</option>
                            <?php foreach ( $available_players as $player ): ?>
                                <option value="<?php echo esc_attr( $player->player_id ); ?>">
                                    <?php echo esc_html( $player->full_name_ru ); ?> 
                                    (<?php echo esc_html( $player->position_name ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="status">Статус в составе</label></th>
                    <td>
                        <select name="status" id="status" required>
                            <option value="Основной">Основной состав</option>
                            <option value="Дублирующий">Дублирующий состав</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">Добавить</button>
                <button type="button" class="button" onclick="document.getElementById('add-player-form').style.display='none'">Отмена</button>
            </p>
        </form>
    </div>
    
    <!-- Основной состав -->
    <div class="arsenal-squad-section" style="margin-top: 30px;">
        <h2>Основной состав (<?php echo count( $main_squad ); ?> игроков)</h2>
        
        <?php if ( $main_squad ): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Фото</th>
                        <th>Имя</th>
                        <th style="width: 120px;">Позиция</th>
                        <th style="width: 100px;">Дебют</th>
                        <th style="width: 100px;">Контракт до</th>
                        <th style="width: 150px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $main_squad as $item ): ?>
                        <tr>
                            <td>
                                <?php if ( $item->photo_url ): ?>
                                    <?php
                                    // Преобразуем относительный путь в полный URL если нужно
                                    $photo_url = $item->photo_url;
                                    if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                                        $photo_url = home_url( $photo_url );
                                    }
                                    ?>
                                    <img src="<?php echo esc_url( $photo_url ); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #ddd; border-radius: 4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html( $item->full_name_ru ); ?></strong></td>
                            <td><?php echo esc_html( $item->position_name ?? 'Неизвестно' ); ?></td>
                            <td><?php echo $item->debut_date ? date_i18n( 'd.m.Y', strtotime( $item->debut_date ) ) : '—'; ?></td>
                            <td><?php echo $item->contract_end ? date_i18n( 'd.m.Y', strtotime( $item->contract_end ) ) : '—'; ?></td>
                            <td>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Переместить игрока в дублирующий состав?')">
                                    <?php wp_nonce_field( 'arsenal_squad_action' ); ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="squad_entry_id" value="<?php echo intval( $item->id ); ?>">
                                    <input type="hidden" name="new_status" value="Дублирующий">
                                    <button type="submit" class="button button-small">В дубль</button>
                                </form>
                                <form method="post" style="display: inline; margin-left: 5px;" onsubmit="return confirm('Удалить игрока из состава?')">
                                    <?php wp_nonce_field( 'arsenal_squad_action' ); ?>
                                    <input type="hidden" name="action" value="remove_player">
                                    <input type="hidden" name="roster_id" value="<?php echo intval( $item->id ); ?>">
                                    <button type="submit" class="button button-small button-link-delete">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">Нет игроков в основном составе</p>
        <?php endif; ?>
    </div>
    
    <!-- Дублирующий состав -->
    <div class="arsenal-squad-section" style="margin-top: 30px;">
        <h2>Дублирующий состав (<?php echo count( $reserve_squad ); ?> игроков)</h2>
        
        <?php if ( $reserve_squad ): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Фото</th>
                        <th>Имя</th>
                        <th style="width: 120px;">Позиция</th>
                        <th style="width: 100px;">Дебют</th>
                        <th style="width: 100px;">Контракт до</th>
                        <th style="width: 150px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $reserve_squad as $item ): ?>
                        <tr>
                            <td>
                                <?php if ( $item->photo_url ): ?>
                                    <img src="<?php echo esc_url( $item->photo_url ); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #ddd; border-radius: 4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html( $item->full_name_ru ); ?></strong></td>
                            <td><?php echo esc_html( $item->position_name ?? 'Неизвестно' ); ?></td>
                            <td><?php echo $item->debut_date ? date_i18n( 'd.m.Y', strtotime( $item->debut_date ) ) : '—'; ?></td>
                            <td><?php echo $item->contract_end ? date_i18n( 'd.m.Y', strtotime( $item->contract_end ) ) : '—'; ?></td>
                            <td>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Переместить игрока в основной состав?')">
                                    <?php wp_nonce_field( 'arsenal_squad_action' ); ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="squad_entry_id" value="<?php echo intval( $item->id ); ?>">
                                    <input type="hidden" name="new_status" value="Основной">
                                    <button type="submit" class="button button-small">В основу</button>
                                </form>
                                <form method="post" style="display: inline; margin-left: 5px;" onsubmit="return confirm('Удалить игрока из состава?')">
                                    <?php wp_nonce_field( 'arsenal_squad_action' ); ?>
                                    <input type="hidden" name="action" value="remove_player">
                                    <input type="hidden" name="squad_entry_id" value="<?php echo intval( $item->id ); ?>">
                                    <button type="submit" class="button button-small button-link-delete">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #666; font-style: italic;">Нет игроков в дублирующем составе</p>
        <?php endif; ?>
    </div>
</div>
