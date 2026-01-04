<?php
/**
 * Добавление/редактирование игрока
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Функция для преобразования абсолютного пути в относительный
function get_relative_path( $url ) {
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
        'photo_url' => ! empty( $_POST['photo_url'] ) ? get_relative_path( $_POST['photo_url'] ) : '',
        'biography' => isset( $_POST['biography'] ) ? wp_kses_post( $_POST['biography'] ) : null
    );
    
    $format = array( '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s' );
    
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
    
    <form method="post" action="">
        <?php wp_nonce_field( 'arsenal_player_edit' ); ?>
        
        <table class="form-table">
            <tr>
                <th colspan="2">
                    <h2>Основная информация</h2>
                </th>
            </tr>
            
            <tr>
                <th><label for="first_name">Имя (латиница)</label></th>
                <td>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo $player ? esc_attr( $player->first_name ) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="last_name">Фамилия (латиница)</label></th>
                <td>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo $player ? esc_attr( $player->last_name ) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th colspan="2">
                    <h2>Номер и позиция</h2>
                </th>
            </tr>
            
            <tr>
                <th><label for="shirt_number">Номер на майке *</label></th>
                <td>
                    <input type="number" id="shirt_number" name="shirt_number" 
                           value="<?php echo $player ? esc_attr( $player->shirt_number ) : ''; ?>" 
                           min="1" max="99" required>
                </td>
            </tr>
            
            <tr>
                <th><label for="position_id">Позиция *</label></th>
                <td>
                    <select id="position_id" name="position_id" required>
                        <option value="">-- Выберите позицию --</option>
                        <?php foreach ( $positions as $pos ): ?>
                            <option value="<?php echo esc_attr( $pos->position_id ); ?>" 
                                    <?php selected( $player ? $player->position_id : '', $pos->position_id ); ?>>
                                <?php echo esc_html( $pos->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <?php if ( ! $is_new && $squad_info ): ?>
            <tr>
                <th><label>Тип состава</label></th>
                <td>
                    <strong><?php echo esc_html( $squad_info->squad_name ); ?></strong>
                    <p class="description">Из последнего контракта</p>
                </td>
            </tr>
            <?php endif; ?>
            
            <tr>
                <th colspan="2">
                    <h2>Персональная информация</h2>
                </th>
            </tr>
            
            <tr>
                <th><label for="birth_date">Дата рождения</label></th>
                <td>
                    <input type="date" id="birth_date" name="birth_date" 
                           value="<?php echo $player ? esc_attr( $player->birth_date ) : ''; ?>">
                    <p class="description">Формат: ГГГГ-ММ-ДД</p>
                </td>
            </tr>
            
            <tr>
                <th><label for="citizenship">Гражданство</label></th>
                <td>
                    <input type="text" id="citizenship" name="citizenship" 
                           value="<?php echo $player ? esc_attr( $player->citizenship ) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th><label for="height">Рост (см)</label></th>
                <td>
                    <input type="number" id="height" name="height" 
                           value="<?php echo $player ? esc_attr( $player->height_cm ) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th><label for="weight">Вес (кг)</label></th>
                <td>
                    <input type="number" id="weight" name="weight" 
                           value="<?php echo $player ? esc_attr( $player->weight_kg ) : ''; ?>">
                </td>
            </tr>
            
            <tr>
                <th colspan="2">
                    <h2>Изображения и ссылки</h2>
                </th>
            </tr>
            
            <tr>
                <th><label for="photo_url">Фото игрока</label></th>
                <td>
                    <input type="hidden" id="photo_url" name="photo_url" 
                           value="<?php echo $player ? esc_attr( $player->photo_url ) : ''; ?>">
                    <button type="button" class="button" id="upload_photo_button">
                        Выбрать из медиабиблиотеки
                    </button>
                    
                    <?php if ( $player && ! empty( $player->photo_url ) ) : ?>
                        <p>
                            <img src="<?php echo esc_url( home_url( $player->photo_url ) ); ?>" 
                                 style="max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;">
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            
            <tr>
                <th colspan="2">
                    <h2>Биография</h2>
                </th>
            </tr>
            
            <tr>
                <th><label for="biography">Биография игрока</label></th>
                <td>
                    <?php
                    $biography_content = $player ? $player->biography : '';
                    wp_editor( $biography_content, 'biography', array(
                        'textarea_name' => 'biography',
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => false
                    ) );
                    ?>
                    <p class="description">Краткая биография игрока. Можно использовать простые HTML теги.</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="arsenal_save_player" class="button button-primary button-large">
                <?php echo $is_new ? 'Добавить игрока' : 'Сохранить изменения'; ?>
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-players' ); ?>" class="button button-large">
                Отмена
            </a>
        </p>
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
        });
        
        mediaUploader.open();
    });
});
</script>


