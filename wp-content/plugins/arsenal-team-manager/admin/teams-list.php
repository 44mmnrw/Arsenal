<?php
/**
 * Управление командами лиги - загрузка логотипов
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Обработка добавления новой команды
if ( isset( $_POST['add_team'] ) && check_admin_referer( 'arsenal_add_team' ) ) {
    $team_name = sanitize_text_field( $_POST['team_name'] );
    $team_id = sanitize_text_field( $_POST['team_id'] );
    
    // Проверяем, что team_id не пустой
    if ( empty( $team_id ) ) {
        echo '<div class="notice notice-error is-dismissible"><p>Ошибка: необходимо указать ID команды.</p></div>';
    } else {
        // Генерируем хеш team_id если пустой
        if ( empty( $team_id ) ) {
            $team_id = substr( md5( $team_name ), 0, 8 );
        }
        
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'arsenal_teams',
            array(
                'team_id' => $team_id,
                'name' => $team_name,
            ),
            array( '%s', '%s' )
        );
        
        if ( $inserted ) {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Команда добавлена!</strong> ID: ' . esc_html( $team_id ) . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Ошибка при добавлении команды: ' . esc_html( $wpdb->last_error ) . '</p></div>';
        }
    }
}

// Обработка удаления команды
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['team_id'] ) ) {
    check_admin_referer( 'delete_team_' . $_GET['team_id'] );
    
    $team_id = intval( $_GET['team_id'] );
    
    $deleted = $wpdb->delete( 
        $wpdb->prefix . 'arsenal_teams', 
        array( 'id' => $team_id ), 
        array( '%d' ) 
    );
    
    if ( $deleted ) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Команда удалена!</strong></p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Ошибка при удалении команды.</p></div>';
    }
}

// Обработка сохранения логотипа
if ( isset( $_POST['save_team_logo'] ) && check_admin_referer( 'arsenal_save_team_logo' ) ) {
    $team_id = intval( $_POST['team_id'] );
    $logo_url = esc_url_raw( $_POST['logo_url'] );
    
    $updated = $wpdb->update(
        'wp_arsenal_teams',
        array( 'logo_url' => $logo_url ),
        array( 'id' => $team_id ),
        array( '%s' ),
        array( '%d' )
    );
    
    if ( $updated !== false ) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Логотип сохранён!</strong></p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Ошибка при сохранении логотипа.</p></div>';
    }
}

// Получаем все лиги для селектора
$leagues = $wpdb->get_results( "SELECT id, league_name FROM wp_arsenal_leagues ORDER BY league_name" );

// Получаем все команды лиги
$teams = $wpdb->get_results( "
    SELECT 
        id,
        team_id,
        name,
        logo_url,
        created_at,
        updated_at
    FROM wp_arsenal_teams
    ORDER BY name ASC
" );

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-shield" style="font-size: 32px; width: 32px; height: 32px; vertical-align: middle;"></span>
        Команды лиги
    </h1>
    <a href="#" class="page-title-action" id="add-team-btn">Добавить команду</a>
    <hr class="wp-header-end">
    
    <p style="margin-top: 20px;">
        Всего команд в лиге: <strong><?php echo count( $teams ); ?></strong>
        <br>
        <small style="color: #666;">Загрузите логотипы для каждой команды, чтобы они отображались на сайте</small>
    </p>
    
    <!-- Модальное окно добавления команды -->
    <div id="add-team-modal" style="display: none;">
        <div class="postbox" style="max-width: 600px; margin: 20px auto;">
            <div style="padding: 20px;">
                <h2 style="margin-top: 0;">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                    Добавить новую команду
                </h2>
                
                <form method="post" id="add-team-form">
                    <?php wp_nonce_field( 'arsenal_add_team' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="team_name">Название команды <span style="color: #d63638;">*</span></label></th>
                            <td>
                                <input type="text" 
                                       id="team_name" 
                                       name="team_name" 
                                       class="regular-text" 
                                       required
                                       placeholder="Например: Динамо Минск">
                                <p class="description">Полное название команды</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="team_id">ID команды <span style="color: #d63638;">*</span></label></th>
                            <td>
                                <input type="text" 
                                       id="team_id" 
                                       name="team_id" 
                                       maxlength="8"
                                       required
                                       placeholder="Уникальный ID">
                                <p class="description">8-символьный уникальный идентификатор (хеш)</p>
                            </td>
                        </tr>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" name="add_team" class="button button-primary button-large">
                            <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                            Добавить команду
                        </button>
                        <button type="button" class="button button-large" id="cancel-add-team">
                            Отмена
                        </button>
                    </p>
                </form>
            </div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; margin-top: 20px;">
        <?php foreach ( $teams as $team ): ?>
            <div class="postbox arsenal-team-card" style="padding: 0; overflow: hidden; min-width: 160px; box-sizing: border-box;">
                <!-- Заголовок -->
                <div style="background: #f5f5f5; 
                            padding: 8px 10px; 
                            border-bottom: 1px solid #ddd;">
                    <h3 style="margin: 0; color: #333; font-size: 13px; line-height: 1.3;">
                        <?php echo esc_html( $team->name ); ?>
                    </h3>
                </div>
                
                <!-- Логотип -->
                <div style="padding: 10px; text-align: center; background: #fff; height: 90px; display: flex; align-items: center; justify-content: center;">
                    <?php if ( $team->logo_url ): ?>
                        <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border: 1px solid #e0e0e0; border-radius: 4px; padding: 5px; box-sizing: border-box; background: #fafafa;">
                            <img id="team-logo-preview-<?php echo $team->id; ?>" 
                                 src="<?php echo esc_url( $team->logo_url ); ?>" 
                                 style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block;">
                        </div>
                    <?php else: ?>
                        <div id="team-logo-preview-<?php echo $team->id; ?>" style="color: #ccc; font-size: 11px;">
                            <span class="dashicons dashicons-format-image" style="font-size: 40px; width: 40px; height: 40px;"></span>
                            <br><small>Нет лого</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Форма загрузки -->
                <div style="padding: 8px; background: #fafafa; border-top: 1px solid #ddd;">
                    <form method="post" style="margin: 0;">
                        <?php wp_nonce_field( 'arsenal_save_team_logo' ); ?>
                        <input type="hidden" name="team_id" value="<?php echo $team->id; ?>">
                        <input type="hidden" 
                               name="logo_url" 
                               id="team-logo-url-<?php echo $team->id; ?>" 
                               value="<?php echo esc_attr( $team->logo_url ); ?>">
                        
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <button type="button" 
                                    class="button arsenal-team-btn arsenal-team-btn-upload upload-logo-btn" 
                                    data-team-id="<?php echo $team->id; ?>"
                                    style="padding: 4px 6px !important; font-size: 11px !important; height: 24px !important; line-height: 1.2 !important;">
                                <span class="dashicons dashicons-upload" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                Загрузить
                            </button>
                            
                            <button type="submit" 
                                    name="save_team_logo" 
                                    class="button arsenal-team-btn arsenal-team-btn-save"
                                    style="padding: 4px 6px !important; font-size: 11px !important; height: 24px !important; line-height: 1.2 !important;">
                                <span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                Сохранить
                            </button>
                        </div>
                        
                        <?php if ( $team->logo_url ): ?>
                            <button type="button" 
                                    class="button arsenal-team-btn arsenal-team-btn-delete remove-logo-btn" 
                                    data-team-id="<?php echo $team->id; ?>"
                                    style="margin-top: 5px; padding: 4px 6px !important; font-size: 11px !important; height: 24px !important; line-height: 1.2 !important;">
                                <span class="dashicons dashicons-no" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                Удалить лого
                            </button>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Кнопка удаления команды -->
                    <div style="margin-top: 5px; padding-top: 5px; border-top: 1px solid #ddd;">
                        <a href="<?php echo wp_nonce_url( 
                            admin_url( 'admin.php?page=arsenal-teams&action=delete&team_id=' . $team->id ), 
                            'delete_team_' . $team->id 
                        ); ?>" 
                           class="button arsenal-team-btn arsenal-team-btn-delete"
                           style="padding: 4px 6px !important; font-size: 11px !important; height: 24px !important; line-height: 1.2 !important; display: inline-block; text-decoration: none;"
                           onclick="return confirm('Удалить команду <?php echo esc_js( $team->name ); ?>?');">
                            <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span>
                            Удалить
                        </a>
                    </div>
                    
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var mediaUploader;
    
    // Показать форму добавления команды
    $('#add-team-btn').on('click', function(e) {
        e.preventDefault();
        $('#add-team-modal').slideDown();
        $('html, body').animate({
            scrollTop: $('#add-team-modal').offset().top - 50
        }, 500);
    });
    
    // Отменить добавление команды
    $('#cancel-add-team').on('click', function() {
        $('#add-team-modal').slideUp();
        $('#add-team-form')[0].reset();
    });
    
    // Загрузка логотипа
    $('.upload-logo-btn').on('click', function(e) {
        e.preventDefault();
        
        var teamId = $(this).data('team-id');
        var button = $(this);
        
        // Создаём новый медиа загрузчик для каждого клика
        var teamMediaUploader = wp.media({
            title: 'Выберите логотип команды',
            button: {
                text: 'Использовать это изображение'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // Обработка выбора изображения
        teamMediaUploader.on('select', function() {
            var attachment = teamMediaUploader.state().get('selection').first().toJSON();
            
            // Обновляем скрытое поле с URL
            $('#team-logo-url-' + teamId).val(attachment.url);
            
            // Находим контейнер превью
            var previewContainer = $('#team-logo-preview-' + teamId).parent();
            
            // Обновляем превью
            previewContainer.html(
                '<div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border: 1px solid #e0e0e0; border-radius: 4px; padding: 5px; box-sizing: border-box; background: #fafafa;">' +
                '<img id="team-logo-preview-' + teamId + '" src="' + attachment.url + '" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block;">' +
                '</div>'
            );
            
            // Находим форму и проверяем наличие кнопки удаления
            var form = button.closest('form');
            var existingDeleteBtn = form.find('.remove-logo-btn[data-team-id="' + teamId + '"]');
            
            // Если кнопки удаления нет, добавляем
            if (existingDeleteBtn.length === 0) {
                button.parent().after(
                    '<button type="button" ' +
                    'class="button arsenal-team-btn arsenal-team-btn-delete remove-logo-btn" ' +
                    'data-team-id="' + teamId + '" ' +
                    'style="margin-top: 5px; padding: 4px 6px !important; font-size: 11px !important; height: 24px !important; line-height: 1.2 !important;">' +
                    '<span class="dashicons dashicons-no" style="font-size: 14px; width: 14px; height: 14px;"></span> ' +
                    'Удалить лого' +
                    '</button>'
                );
            }
        });
        
        teamMediaUploader.open();
    });
    
    // Удаление логотипа
    $(document).on('click', '.remove-logo-btn', function(e) {
        e.preventDefault();
        
        if (!confirm('Удалить логотип команды?')) {
            return;
        }
        
        var teamId = $(this).data('team-id');
        var deleteBtn = $(this);
        
        // Очищаем скрытое поле URL
        $('#team-logo-url-' + teamId).val('');
        
        // Находим контейнер превью и очищаем
        var previewContainer = $('#team-logo-preview-' + teamId).parent();
        previewContainer.html(
            '<div id="team-logo-preview-' + teamId + '" style="color: #ccc; font-size: 11px;">' +
            '<span class="dashicons dashicons-format-image" style="font-size: 40px; width: 40px; height: 40px;"></span>' +
            '<br><small>Нет лого</small>' +
            '</div>'
        );
        
        // Удаляем кнопку удаления
        deleteBtn.remove();
    });
});
</script>


