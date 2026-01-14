<?php
/**
 * Добавление/редактирование игрока
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Функция для преобразования абсолютного пути в относительный
function get_relative_path( $url ) {
    if ( ! $url ) {
        return '';
    }
    $home_url = home_url();
    if ( strpos( $url, $home_url ) === 0 ) {
        return substr( $url, strlen( $home_url ) );
    }
    return $url;
}

$player_id = isset( $_GET['player_id'] ) ? sanitize_text_field( $_GET['player_id'] ) : '';
$is_new = empty( $player_id );

// Обработка сохранения
if ( isset( $_POST['arsenal_save_player'] ) ) {
    check_admin_referer( 'arsenal_player_edit' );
    
    $data = array(
        'first_name' => sanitize_text_field( $_POST['first_name'] ),
        'last_name' => sanitize_text_field( $_POST['last_name'] ),
        'shirt_number' => sanitize_text_field( $_POST['shirt_number'] ),
        'position_id' => sanitize_text_field( $_POST['position_id'] ),
        'birth_date' => sanitize_text_field( $_POST['birth_date'] ),
        'citizenship' => sanitize_text_field( $_POST['citizenship'] ),
        'height_cm' => ! empty( $_POST['height'] ) ? intval( $_POST['height'] ) : 0,
        'weight_kg' => ! empty( $_POST['weight'] ) ? intval( $_POST['weight'] ) : 0,
        'dominant_foot' => sanitize_text_field( $_POST['dominant_foot'] ?? '' ),
        'photo_url' => ! empty( $_POST['photo_url'] ) ? get_relative_path( $_POST['photo_url'] ) : '',
        'biography' => isset( $_POST['biography'] ) ? wp_kses_post( $_POST['biography'] ) : null
    );
    
    $format = array( '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' );
    
    if ( $is_new ) {
        // Для новых игроков добавляем player_id
        $data['player_id'] = sanitize_text_field( $_POST['player_id'] ?? '' );
        $data = array_merge( array( 'team_id' => 915703 ), $data );
        array_unshift( $format, '%d' );
        
        $wpdb->insert( 'wp_arsenal_players', $data, $format );
        $player_id = $wpdb->insert_id;
        echo '<div class="notice notice-success"><p>Игрок добавлен!</p></div>';
    } else {
        // При редактировании НЕ меняем player_id
        $result = $wpdb->update( 'wp_arsenal_players', $data, array( 'player_id' => $player_id ), $format, array( '%s' ) );
        
        if ( $result === false ) {
            echo '<div class="notice notice-error"><p>❌ Ошибка при сохранении: ' . esc_html( $wpdb->last_error ) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>✅ Данные сохранены! (обновлено строк: ' . $result . ')</p></div>';
        }
    }
}

// Получаем данные игрока
$player = null;
if ( ! $is_new ) {
    $player = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wp_arsenal_players WHERE player_id = %s", $player_id ) );
    
    if ( ! $player ) {
        echo '<div class="notice notice-error"><p>Игрок не найден.</p></div>';
        return;
    }
}

// Список позиций
$positions = $wpdb->get_results( "SELECT position_id, name FROM wp_arsenal_positions ORDER BY id" );

// Получаем тип состава из последнего контракта (если существует)
$squad_info = null;
if ( ! $is_new ) {
    $squad_info = $wpdb->get_row( $wpdb->prepare(
        "SELECT tc.squad_id, sq.squad_name 
         FROM {$wpdb->prefix}arsenal_team_contracts tc
         LEFT JOIN {$wpdb->prefix}arsenal_squad sq ON tc.squad_id = sq.squad_id
         WHERE tc.player_id = %s
         ORDER BY tc.contract_start DESC
         LIMIT 1",
        $player->player_id
    ) );
}

?>
<div class="wrap">
    <h1><?php echo $is_new ? 'Добавить игрока' : 'Редактировать игрока'; ?></h1>
    
    <form method="post" action="" class="player-form">
        <?php wp_nonce_field( 'arsenal_player_edit' ); ?>
        
        <div class="player-form-wrapper">
            <!-- ЛЕВАЯ КОЛОНКА -->
            <div class="player-form-left">
                <!-- Основная информация -->
                <div class="player-form-section">
                    <h3>👤 Основная информация</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="first_name">Имя</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo $player ? esc_attr( $player->first_name ) : ''; ?>" 
                                   class="regular-text">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Фамилия</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo $player ? esc_attr( $player->last_name ) : ''; ?>" 
                                   class="regular-text">
                        </div>
                    </div>
                </div>
                
                <!-- Номер и позиция -->
                <div class="player-form-section">
                    <h3>⚽ Номер и позиция</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shirt_number">Номер на майке *</label>
                            <input type="number" id="shirt_number" name="shirt_number" 
                                   value="<?php echo $player ? esc_attr( $player->shirt_number ) : ''; ?>" 
                                   min="1" max="99" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="position_id">Позиция *</label>
                            <select id="position_id" name="position_id" required>
                                <option value="">-- Выберите --</option>
                                <?php foreach ( $positions as $pos ): ?>
                                    <option value="<?php echo esc_attr( $pos->position_id ); ?>" 
                                            <?php selected( $player ? $player->position_id : '', $pos->position_id ); ?>>
                                        <?php echo esc_html( $pos->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <?php if ( ! $is_new && $squad_info ): ?>
                    <div class="form-group">
                        <label>Тип состава</label>
                        <div style="padding: 8px 10px; background: #e7f3ff; border: 1px solid #0073aa; border-radius: 4px; color: #0073aa;">
                            <strong><?php echo esc_html( $squad_info->squad_name ); ?></strong>
                            <p class="description">Из последнего контракта</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Персональная информация -->
                <div class="player-form-section">
                    <h3>📋 Персональная информация</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="birth_date">Дата рождения</label>
                            <input type="date" id="birth_date" name="birth_date" 
                                   value="<?php echo $player ? esc_attr( $player->birth_date ) : ''; ?>">
                            <p class="description">Формат: ДД-ММ-ГГГГ</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="citizenship">Гражданство</label>
                            <input type="text" id="citizenship" name="citizenship" 
                                   value="<?php echo $player ? esc_attr( $player->citizenship ) : ''; ?>" 
                                   class="regular-text">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="height">Рост (см)</label>
                            <input type="number" id="height" name="height" 
                                   value="<?php echo $player ? esc_attr( $player->height_cm ) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="weight">Вес (кг)</label>
                            <input type="number" id="weight" name="weight" 
                                   value="<?php echo $player ? esc_attr( $player->weight_kg ) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="dominant_foot">Ведущая нога</label>
                            <select id="dominant_foot" name="dominant_foot">
                                <option value="">-- Не указано --</option>
                                <option value="левая" <?php echo $player && $player->dominant_foot === 'левая' ? 'selected' : ''; ?>>левая</option>
                                <option value="правая" <?php echo $player && $player->dominant_foot === 'правая' ? 'selected' : ''; ?>>правая</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Биография -->
                <div class="player-form-section">
                    <h3>📝 Биография</h3>
                    
                    <div class="form-group">
                        <label for="biography">Биография игрока</label>
                        <?php
                        $biography_content = $player ? ( $player->biography ?? '' ) : '';
                        wp_editor( $biography_content, 'biography', array(
                            'textarea_name' => 'biography',
                            'textarea_rows' => 8,
                            'media_buttons' => false,
                            'teeny' => true,
                            'quicktags' => false
                        ) );
                        ?>
                        <p class="description">Краткая биография игрока</p>
                    </div>
                </div>
            </div>
            
            <!-- ПРАВАЯ КОЛОНКА -->
            <div class="player-form-right">
                <!-- Фото игрока -->
                <div class="player-form-section">
                    <h3>🖼️ Фото игрока</h3>
                    
                    <div class="player-photo-box">
                        <div id="photo_preview">
                            <?php if ( $player && ! empty( $player->photo_url ) ) : ?>
                                <img src="<?php echo esc_url( home_url( $player->photo_url ) ); ?>" 
                                     alt="Фото игрока">
                            <?php else: ?>
                                <p style="color: #999; padding: 40px 10px;">Нет фото</p>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" id="photo_url" name="photo_url" 
                               value="<?php echo $player ? esc_attr( $player->photo_url ) : ''; ?>">
                        
                        <button type="button" class="button button-primary" id="upload_photo_button">
                            📷 Выбрать фото
                        </button>
                        
                        <?php if ( $player && ! empty( $player->photo_url ) ) : ?>
                        <button type="button" class="button" id="remove_photo_button" style="margin-top: 8px; color: #c00;">
                            🗑️ Удалить
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Кнопки действия -->
        <div class="player-form-buttons">
            <button type="submit" name="arsenal_save_player" class="button button-primary button-large">
                <?php echo $is_new ? '✅ Добавить игрока' : '💾 Сохранить изменения'; ?>
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-players' ); ?>" class="button button-large">
                ❌ Отмена
            </a>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Загрузка фото через Media Library
    $('#upload_photo_button').on('click', function(e) {
        e.preventDefault();
        
        var mediaUploader = wp.media({
            title: 'Выберите фото игрока',
            button: {
                text: 'Использовать это фото'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#photo_url').val(attachment.url);
            
            // Обновляем превью
            var previewHtml = '<img src="' + attachment.url + '" alt="Фото игрока">';
            $('#photo_preview').html(previewHtml);
            
            // Добавляем кнопку удаления, если её нет
            if ($('#remove_photo_button').length === 0) {
                $('#upload_photo_button').after('<button type="button" class="button" id="remove_photo_button" style="margin-top: 8px; color: #c00;">🗑️ Удалить</button>');
                addRemovePhotoHandler();
            }
        });
        
        mediaUploader.open();
    });
    
    // Удаление фото
    function addRemovePhotoHandler() {
        $('#remove_photo_button').on('click', function(e) {
            e.preventDefault();
            $('#photo_url').val('');
            $('#photo_preview').html('<p style="color: #999; padding: 40px 10px;">Нет фото</p>');
            $(this).remove();
        });
    }
    
    // Инициализируем удаление фото, если кнопка уже есть
    if ($('#remove_photo_button').length > 0) {
        addRemovePhotoHandler();
    }
});
</script>
