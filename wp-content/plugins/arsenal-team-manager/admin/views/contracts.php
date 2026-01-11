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
<div class="contracts-wrapper">
    <div class="contracts-header">
        <h1>📋 Контракты</h1>
        <button onclick="openAddModal()" class="button button-primary">
            ➕ Добавить контракт
        </button>
    </div>

    <?php if ( ! empty( $message ) ): ?>
        <div class="notice notice-success is-dismissible" style="margin: 20px 0;">
            <p><?php echo esc_html( $message ); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ( ! empty( $error ) ): ?>
        <div class="notice notice-error is-dismissible" style="margin: 20px 0;">
            <p><?php echo esc_html( $error ); ?></p>
        </div>
    <?php endif; ?>

    <div class="contracts-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo count( $contracts ); ?></span>
            <span class="stat-label">Всего контрактов</span>
        </div>
    </div>

    <div class="contracts-table">
            <div class="contracts-row contracts-header">
                <div class="contracts-col-photo">Фото</div>
                <div class="contracts-col-player">Игрок</div>
                <div class="contracts-col-number">№ контракта</div>
                <div class="contracts-col-start">Начало</div>
                <div class="contracts-col-end">Конец</div>
                <div class="contracts-col-action">Действия</div>
            </div>

            <?php if ( $contracts ): ?>
                <?php foreach ( $contracts as $contract ): ?>
                <div class="contracts-row">
                    <div class="contracts-col-photo">
                        <?php if ( $contract->photo_url ): ?>
                            <img src="<?php echo esc_url( $contract->photo_url ); ?>" 
                                 alt="<?php echo esc_attr( $contract->first_name . ' ' . $contract->last_name ); ?>"
                                 class="contract-thumbnail">
                        <?php else: ?>
                            <div class="contract-thumbnail-empty">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="contracts-col-player">
                        <a href="#" onclick="openEditModal(<?php echo esc_attr( json_encode( $contract ) ); ?>); return false;" 
                           class="contract-player-link">
                            <?php echo esc_html( ( $contract->first_name ?? '' ) . ' ' . ( $contract->last_name ?? '' ) ); ?>
                        </a>
                    </div>
                    <div class="contracts-col-number">
                        <a href="#" onclick="openEditModal(<?php echo esc_attr( json_encode( $contract ) ); ?>); return false;" 
                           class="contract-player-link">
                            <?php echo esc_html( $contract->contract_number ); ?>
                        </a>
                    </div>
                    <div class="contracts-col-start">
                        <?php echo esc_html( wp_date( 'd.m.Y', strtotime( $contract->contract_start ?? '' ) ) ); ?>
                    </div>
                    <div class="contracts-col-end">
                        <?php echo esc_html( wp_date( 'd.m.Y', strtotime( $contract->contract_end ?? '' ) ) ); ?>
                    </div>
                    <div class="contracts-col-action">
                        <form method="post" action="" style="display: inline;">
                            <?php wp_nonce_field( 'arsenal_contract_action' ); ?>
                            <input type="hidden" name="action" value="delete_contract">
                            <input type="hidden" name="contract_number" value="<?php echo esc_attr( $contract->contract_number ); ?>">
                            <button type="submit" class="button button-small" 
                                    onclick="return confirm('Удалить контракт?')" title="Удалить контракт">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="contracts-row contracts-empty">
                    <div class="contracts-empty-message">
                        ℹ️ Контрактов не найдено
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления контракта -->
<div id="addContractModal" class="arsenal-modal">
    <div class="arsenal-modal-content">
        <div class="arsenal-modal-header">
            <h2>📋 Добавить контракт</h2>
            <button type="button" class="arsenal-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        
        <form method="post" action="" class="arsenal-modal-form">
            <?php wp_nonce_field( 'arsenal_contract_action' ); ?>
            <input type="hidden" name="action" value="add_contract">
            
            <div class="form-group">
                <label for="add-player-id">👤 Игрок<span class="required">*</span></label>
                <select name="player_id" id="add-player-id" required>
                    <option value="">-- Выберите игрока --</option>
                    <?php foreach ( $players as $player ): ?>
                        <option value="<?php echo esc_attr( $player->player_id ); ?>">
                            <?php echo esc_html( ( $player->last_name ?? '' ) . ' ' . ( $player->first_name ?? '' ) ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="add-contract-number">📄 Номер контракта<span class="required">*</span></label>
                <input type="text" name="contract_number" id="add-contract-number" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="add-contract-start">📅 Дата начала<span class="required">*</span></label>
                    <input type="date" name="contract_start" id="add-contract-start" required>
                </div>

                <div class="form-group">
                    <label for="add-contract-end">📅 Дата окончания<span class="required">*</span></label>
                    <input type="date" name="contract_end" id="add-contract-end" required>
                </div>
            </div>

            <div class="form-group">
                <label for="add-squad-id">👕 Состав<span class="required">*</span></label>
                <select name="squad_id" id="add-squad-id" required>
                    <option value="">-- Выберите состав --</option>
                    <?php foreach ( $squads as $squad ): ?>
                        <option value="<?php echo esc_attr( $squad->squad_id ); ?>">
                            <?php echo esc_html( $squad->squad_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="arsenal-modal-actions">
                <button type="button" onclick="closeAddModal()" class="button">Отмена</button>
                <button type="submit" class="button button-primary">✓ Добавить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования контракта -->
<div id="editContractModal" class="arsenal-modal">
    <div class="arsenal-modal-content">
        <div class="arsenal-modal-header">
            <h2>📋 Редактировать контракт</h2>
            <button type="button" class="arsenal-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        
        <form method="post" action="" class="arsenal-modal-form">
            <?php wp_nonce_field( 'arsenal_contract_action' ); ?>
            <input type="hidden" name="action" value="edit_contract">
            <input type="hidden" name="contract_db_id" id="contract_db_id" value="">
            
            <div class="form-group">
                <label for="edit-player-name">👤 Игрок</label>
                <input type="text" id="edit-player-name" readonly>
            </div>

            <div class="form-group">
                <label for="edit-contract-number">📄 Номер контракта<span class="required">*</span></label>
                <input type="text" name="edit_contract_number" id="edit-contract-number" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit-contract-start">📅 Дата начала<span class="required">*</span></label>
                    <input type="date" name="edit_contract_start" id="edit-contract-start" required>
                </div>

                <div class="form-group">
                    <label for="edit-contract-end">📅 Дата окончания<span class="required">*</span></label>
                    <input type="date" name="edit_contract_end" id="edit-contract-end" required>
                </div>
            </div>

            <div class="form-group">
                <label for="edit-squad-id">👕 Состав<span class="required">*</span></label>
                <select name="edit_squad_id" id="edit-squad-id" required>
                    <option value="">-- Выберите состав --</option>
                    <?php foreach ( $squads as $squad ): ?>
                        <option value="<?php echo esc_attr( $squad->squad_id ); ?>">
                            <?php echo esc_html( $squad->squad_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="arsenal-modal-actions">
                <button type="button" onclick="closeEditModal()" class="button">Отмена</button>
                <button type="submit" class="button button-primary">✓ Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addContractModal').classList.add('active');
}

function closeAddModal() {
    document.getElementById('addContractModal').classList.remove('active');
    // Clear form
    document.getElementById('add-player-id').value = '';
    document.getElementById('add-contract-number').value = '';
    document.getElementById('add-contract-start').value = '';
    document.getElementById('add-contract-end').value = '';
    document.getElementById('add-squad-id').value = '';
}

function openEditModal(contract) {
    document.getElementById('contract_db_id').value = contract.id;
    document.getElementById('edit-player-name').value = (contract.first_name || '') + ' ' + (contract.last_name || '');
    document.getElementById('edit-contract-number').value = contract.contract_number;
    document.getElementById('edit-contract-start').value = contract.contract_start;
    document.getElementById('edit-contract-end').value = contract.contract_end;
    document.getElementById('edit-squad-id').value = contract.squad_id;
    document.getElementById('editContractModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editContractModal').classList.remove('active');
}

// Close modals when clicking outside
window.onclick = function(event) {
    var addModal = document.getElementById('addContractModal');
    var editModal = document.getElementById('editContractModal');
    
    if (event.target === addModal) {
        addModal.classList.remove('active');
    }
    if (event.target === editModal) {
        editModal.classList.remove('active');
    }
}
</script>
