<?php
/**
 * Управление контрактами игроков
 * Использует таблицу wp_arsenal_team_contracts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Обработка действий
$message = '';
$error = '';

if ( isset( $_POST['action'] ) && check_admin_referer( 'arsenal_contract_action' ) ) {
    $action = $_POST['action'];
    
    if ( $action === 'add_contract' ) {
        $player_id = sanitize_text_field( $_POST['player_id'] );
        $contract_number = sanitize_text_field( $_POST['contract_number'] );
        $contract_start = sanitize_text_field( $_POST['contract_start'] );
        $contract_end = sanitize_text_field( $_POST['contract_end'] );
        $squad_id = sanitize_text_field( $_POST['squad_id'] );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_team_contracts',
            array(
                'player_id' => $player_id,
                'contract_number' => $contract_number,
                'contract_start' => $contract_start,
                'contract_end' => $contract_end,
                'squad_id' => $squad_id,
                'created_at' => current_time( 'mysql' )
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s' )
        );
        
        if ( $result ) {
            $message = 'Контракт добавлен';
        } else {
            $error = 'Ошибка при добавлении контракта: ' . esc_html( $wpdb->last_error );
        }
    }
    
    if ( $action === 'delete_contract' ) {
        // For delete, we need to find the contract by contract_number
        $contract_number = sanitize_text_field( $_POST['contract_number'] );
        $contract = $wpdb->get_row( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}arsenal_team_contracts WHERE contract_number = %s LIMIT 1",
            $contract_number
        ) );
        
        if ( $contract ) {
            $wpdb->delete( 
                $wpdb->prefix . 'arsenal_team_contracts', 
                array( 'id' => $contract->id ),
                array( '%d' )
            );
            $message = 'Контракт удалён';
        }
    }
    
    if ( $action === 'edit_contract' ) {
        $contract_db_id = intval( $_POST['contract_db_id'] );
        $contract_number = sanitize_text_field( $_POST['edit_contract_number'] );
        $contract_start = sanitize_text_field( $_POST['edit_contract_start'] );
        $contract_end = sanitize_text_field( $_POST['edit_contract_end'] );
        $squad_id = sanitize_text_field( $_POST['edit_squad_id'] );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'arsenal_team_contracts',
            array(
                'contract_number' => $contract_number,
                'contract_start' => $contract_start,
                'contract_end' => $contract_end,
                'squad_id' => $squad_id
            ),
            array( 'id' => $contract_db_id ),
            array( '%s', '%s', '%s', '%s' ),
            array( '%d' )
        );
        
        if ( $result !== false ) {
            $message = 'Контракт обновлён';
        } else {
            $error = 'Ошибка при обновлении: ' . esc_html( $wpdb->last_error );
        }
    }
}

// Получить все контракты с информацией об игроках
$contracts = $wpdb->get_results( "
    SELECT 
        c.id,
        c.player_id,
        c.contract_number,
        c.contract_start,
        c.contract_end,
        c.squad_id,
        c.created_at,
        p.first_name,
        p.last_name,
        p.photo_url
    FROM wp_arsenal_team_contracts c
    LEFT JOIN wp_arsenal_players p ON c.player_id = p.player_id
    ORDER BY c.created_at DESC
" );

// Получить список доступных игроков
$players = $wpdb->get_results( "
    SELECT player_id, first_name, last_name 
    FROM wp_arsenal_players 
    ORDER BY last_name, first_name
" );

// Получить список составов
$squads = $wpdb->get_results( "
    SELECT squad_id, squad_name 
    FROM wp_arsenal_squad 
    ORDER BY squad_name
" );

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Контракты</h1>
    <hr class="wp-header-end">
    
    <?php if ( ! empty( $message ) ): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ( ! empty( $error ) ): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $error ); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Кнопка Добавить контракт -->
    <div style="margin-bottom: 20px;">
        <button onclick="openAddModal()" class="button button-primary button-large">
            <span class="dashicons dashicons-plus-alt" style="vertical-align: middle; margin-right: 5px;"></span>
            Добавить контракт
        </button>
    </div>
    
    <!-- Таблица контрактов -->
    <div class="postbox">
        <h2>Все контракты</h2>
        <p>Всего контрактов: <strong><?php echo count( $contracts ); ?></strong></p>
        
        <?php if ( $contracts ): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40px;">Фото</th>
                        <th>Игрок</th>
                        <th style="width: 150px;">Номер контракта</th>
                        <th style="width: 120px;">Дата начала</th>
                        <th style="width: 120px;">Дата окончания</th>
                        <th style="width: 120px;">Добавлен</th>
                        <th style="width: 100px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $contracts as $contract ): ?>
                        <tr>
                            <td>
                                <?php if ( $contract->photo_url ): ?>
                                    <?php
                                    // Преобразуем относительный путь в полный URL если нужно
                                    $photo_url = $contract->photo_url;
                                    if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                                        $photo_url = home_url( $photo_url );
                                    }
                                    ?>
                                    <img src="<?php echo esc_url( $photo_url ); ?>" 
                                         alt="Фото" style="max-width: 40px; max-height: 40px; border-radius: 4px;">
                                <?php else: ?>
                                    <span style="color: #999;">Нет фото</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" onclick="openEditModal(<?php echo esc_attr( json_encode( $contract ) ); ?>); return false;" 
                                   style="color: #0073aa; text-decoration: underline; cursor: pointer;">
                                    <strong>
                                        <?php echo esc_html( ( $contract->first_name ?? '' ) . ' ' . ( $contract->last_name ?? '' ) ); ?>
                                    </strong>
                                </a>
                            </td>
                            <td>
                                <a href="#" onclick="openEditModal(<?php echo esc_attr( json_encode( $contract ) ); ?>); return false;" 
                                   style="color: #0073aa; text-decoration: underline; cursor: pointer;">
                                    <?php echo esc_html( $contract->contract_number ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $contract->contract_start ); ?></td>
                            <td><?php echo esc_html( $contract->contract_end ); ?></td>
                            <td><?php echo esc_html( wp_date( 'd.m.Y H:i', strtotime( $contract->created_at ?? '' ) ) ); ?></td>
                            <td>
                                <form method="post" action="" style="display: inline;">
                                    <?php wp_nonce_field( 'arsenal_contract_action' ); ?>
                                    <input type="hidden" name="action" value="delete_contract">
                                    <input type="hidden" name="contract_number" value="<?php echo esc_attr( $contract->contract_number ); ?>">
                                    <button type="submit" class="button button-small button-danger" 
                                            onclick="return confirm('Удалить контракт?')">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #999;">Контрактов не найдено</p>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно для добавления контракта -->
<div id="addContractModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div style="background-color: #fff; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <span onclick="closeAddModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 style="margin-top: 0;">Добавить контракт</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'arsenal_contract_action' ); ?>
            <input type="hidden" name="action" value="add_contract">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="add-player-id">Игрок</label></th>
                    <td>
                        <select name="player_id" id="add-player-id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Выберите игрока --</option>
                            <?php foreach ( $players as $player ): ?>
                                <option value="<?php echo esc_attr( $player->player_id ); ?>">
                                    <?php echo esc_html( ( $player->last_name ?? '' ) . ' ' . ( $player->first_name ?? '' ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-contract-number">Номер контракта</label></th>
                    <td>
                        <input type="text" name="contract_number" id="add-contract-number" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-contract-start">Дата начала</label></th>
                    <td>
                        <input type="date" name="contract_start" id="add-contract-start" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-contract-end">Дата окончания</label></th>
                    <td>
                        <input type="date" name="contract_end" id="add-contract-end" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="add-squad-id">Состав</label></th>
                    <td>
                        <select name="squad_id" id="add-squad-id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Выберите состав --</option>
                            <?php foreach ( $squads as $squad ): ?>
                                <option value="<?php echo esc_attr( $squad->squad_id ); ?>">
                                    <?php echo esc_html( $squad->squad_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div style="margin-top: 20px; text-align: right;">
                <button type="button" onclick="closeAddModal()" class="button" style="margin-right: 10px;">Отмена</button>
                <button type="submit" class="button button-primary">Добавить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования контракта -->
<div id="editContractModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div style="background-color: #fff; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <span onclick="closeEditModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 style="margin-top: 0;">Редактировать контракт</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'arsenal_contract_action' ); ?>
            <input type="hidden" name="action" value="edit_contract">
            <input type="hidden" name="contract_db_id" id="contract_db_id" value="">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="edit-player-id">Игрок</label></th>
                    <td>
                        <input type="text" id="edit-player-name" readonly style="width: 100%; padding: 8px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="edit-contract-number">Номер контракта</label></th>
                    <td>
                        <input type="text" name="edit_contract_number" id="edit-contract-number" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="edit-contract-start">Дата начала</label></th>
                    <td>
                        <input type="date" name="edit_contract_start" id="edit-contract-start" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="edit-contract-end">Дата окончания</label></th>
                    <td>
                        <input type="date" name="edit_contract_end" id="edit-contract-end" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="edit-squad-id">Состав</label></th>
                    <td>
                        <select name="edit_squad_id" id="edit-squad-id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Выберите состав --</option>
                            <?php foreach ( $squads as $squad ): ?>
                                <option value="<?php echo esc_attr( $squad->squad_id ); ?>">
                                    <?php echo esc_html( $squad->squad_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div style="margin-top: 20px; text-align: right;">
                <button type="button" onclick="closeEditModal()" class="button" style="margin-right: 10px;">Отмена</button>
                <button type="submit" class="button button-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addContractModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addContractModal').style.display = 'none';
    document.getElementById('add-player-id').value = '';
    document.getElementById('add-contract-number').value = '';
    document.getElementById('add-contract-start').value = '';
    document.getElementById('add-contract-end').value = '';
    document.getElementById('add-squad-id').value = '';
}

function openEditModal(contract) {
    document.getElementById('contract_db_id').value = contract.id;
    document.getElementById('edit-player-name').value = contract.first_name + ' ' + contract.last_name;
    document.getElementById('edit-contract-number').value = contract.contract_number;
    document.getElementById('edit-contract-start').value = contract.contract_start;
    document.getElementById('edit-contract-end').value = contract.contract_end;
    document.getElementById('edit-squad-id').value = contract.squad_id;
    document.getElementById('editContractModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editContractModal').style.display = 'none';
}

// Закрыть модальные окна при клике вне их
window.onclick = function(event) {
    var addModal = document.getElementById('addContractModal');
    var editModal = document.getElementById('editContractModal');
    if (event.target == addModal) {
        addModal.style.display = 'none';
    }
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}
</script>

