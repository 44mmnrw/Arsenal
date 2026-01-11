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
<div class="teams-wrapper">
    <div class="teams-header">
        <h1>🏆 Команды лиги</h1>
        <button type="button" class="button button-primary" id="add-team-btn">
            + Добавить команду
        </button>
    </div>

    <div class="teams-stats">
        <span>Всего команд: <strong><?php echo count( $teams ); ?></strong></span>
        <small>Загрузите логотипы для каждой команды</small>
    </div>
    
    <!-- Модальное окно добавления команды -->
    <div id="add-team-modal" class="teams-modal" style="display: none;">
        <div class="teams-modal-content">
            <div class="teams-modal-header">
                <h2>Добавить новую команду</h2>
                <button type="button" class="teams-modal-close" id="cancel-add-team">&times;</button>
            </div>
            
            <form method="post" id="add-team-form" class="teams-form">
                <?php wp_nonce_field( 'arsenal_add_team' ); ?>
                
                <div class="teams-form-group">
                    <label for="team_name">Название команды <span class="teams-required">*</span></label>
                    <input type="text" 
                           id="team_name" 
                           name="team_name" 
                           class="teams-form-input" 
                           required
                           placeholder="Например: Динамо Минск">
                    <p class="teams-form-help">Полное название команды</p>
                </div>
                
                <div class="teams-form-group">
                    <label for="team_id">ID команды <span class="teams-required">*</span></label>
                    <input type="text" 
                           id="team_id" 
                           name="team_id" 
                           maxlength="8"
                           class="teams-form-input"
                           required
                           placeholder="Уникальный ID">
                    <p class="teams-form-help">8-символьный уникальный идентификатор (хеш)</p>
                </div>
                
                <div class="teams-form-actions">
                    <button type="submit" name="add_team" class="button button-primary">
                        + Добавить команду
                    </button>
                    <button type="button" class="button" id="cancel-modal">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="teams-grid">
        <?php foreach ( $teams as $team ): ?>
            <div class="teams-card">
                <!-- Заголовок -->
                <div class="teams-card-header">
                    <h3><?php echo esc_html( $team->name ); ?></h3>
                </div>
                
                <!-- Логотип -->
                <div class="teams-card-logo">
                    <?php if ( $team->logo_url ): ?>
                        <img id="team-logo-preview-<?php echo $team->id; ?>" 
                             src="<?php echo esc_url( $team->logo_url ); ?>" 
                             alt="<?php echo esc_attr( $team->name ); ?>">
                    <?php else: ?>
                        <div id="team-logo-preview-<?php echo $team->id; ?>" class="teams-no-logo">
                            <span class="dashicons dashicons-format-image"></span>
                            <small>Нет лого</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Кнопки -->
                <div class="teams-card-buttons">
                    <button type="button" 
                            class="teams-btn-upload upload-logo-btn" 
                            data-team-id="<?php echo $team->id; ?>"
                            title="Загрузить логотип">
                        <span class="dashicons dashicons-upload"></span>
                        Загрузить
                    </button>
                    
                    <button type="button" 
                            class="teams-btn-delete remove-logo-btn" 
                            data-team-id="<?php echo $team->id; ?>"
                            title="Удалить логотип"
                            style="display: <?php echo $team->logo_url ? 'block' : 'none'; ?>">
                        <span class="dashicons dashicons-no"></span>
                        Удалить лого
                    </button>
                    
                    <button type="button" 
                            class="teams-btn-delete delete-team-btn" 
                            data-team-id="<?php echo $team->id; ?>"
                            data-team-name="<?php echo esc_attr( $team->name ); ?>"
                            data-nonce="<?php echo wp_create_nonce( 'delete_team_' . $team->id ); ?>"
                            title="Удалить команду">
                        <span class="dashicons dashicons-trash"></span>
                        Удалить команду
                    </button>
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
        $('#add-team-modal').fadeIn(200);
    });
    
    // Отменить добавление команды
    $('#cancel-add-team, #cancel-modal').on('click', function(e) {
        e.preventDefault();
        $('#add-team-modal').fadeOut(200);
        $('#add-team-form')[0].reset();
    });
    
    // Закрыть модаль при клике вне
    $('#add-team-modal').on('click', function(e) {
        if ($(e.target).is('#add-team-modal')) {
            $('#add-team-modal').fadeOut(200);
            $('#add-team-form')[0].reset();
        }
    });
    
    // Загрузка логотипа - ТОЧНО КАК В WORDPRESS
    $('.upload-logo-btn').on('click', function(e) {
        e.preventDefault();
        
        var teamId = $(this).data('team-id');
        var button = $(this);
        
        // Сохраняем team_id для обработчика
        window.arsenalTeamId = teamId;
        window.arsenalButton = button;
        
        // Переопределяем обработчик ОДИН РАЗ перед открытием
        wp.media.editor.send.attachment = function(props, attachment) {
            var teamId = window.arsenalTeamId;
            var button = window.arsenalButton;
            
            var fullUrl = attachment.url;
            
            // Конвертируем в относительный URL
            var relativeUrl = fullUrl;
            var homeUrl = '<?php echo home_url(); ?>';
            if (relativeUrl.indexOf(homeUrl) === 0) {
                relativeUrl = relativeUrl.substring(homeUrl.length);
                if (relativeUrl.charAt(0) !== '/') {
                    relativeUrl = '/' + relativeUrl;
                }
            }
            
            // Обновляем превью
            $('#team-logo-preview-' + teamId).parent().html(
                '<img id="team-logo-preview-' + teamId + '" src="' + fullUrl + '" alt="Team Logo">'
            );
            
            // Показываем кнопку удаления
            button.closest('.teams-card').find('.remove-logo-btn[data-team-id="' + teamId + '"]').show();
            
            // AJAX сохранение
            $.post(arsenal_ajax.ajax_url, {
                action: 'arsenal_save_team_logo',
                team_id: teamId,
                logo_url: relativeUrl,
                nonce: arsenal_ajax.save_team_logo_nonce
            });
        };
        
        // Открываем галерею как в записях
        wp.media.editor.open(button);
    });
    
    // Удаление логотипа
    $(document).on('click', '.remove-logo-btn', function(e) {
        e.preventDefault();
        
        if (!confirm('Удалить логотип команды?')) {
            return;
        }
        
        var teamId = $(this).data('team-id');
        var deleteBtn = $(this);
        
        // Находим контейнер превью и очищаем
        var previewContainer = $('#team-logo-preview-' + teamId).parent();
        previewContainer.html(
            '<div id="team-logo-preview-' + teamId + '" class="teams-no-logo">' +
            '<span class="dashicons dashicons-format-image"></span>' +
            '<small>Нет лого</small>' +
            '</div>'
        );
        
        // Скрываем кнопку удаления
        deleteBtn.hide();
        
        // AJAX сохранение пустого URL
        $.post(arsenal_ajax.ajax_url, {
            action: 'arsenal_save_team_logo',
            team_id: teamId,
            logo_url: '',
            nonce: arsenal_ajax.save_team_logo_nonce
        });
    });
    
    // Удаление команды
    $(document).on('click', '.delete-team-btn', function(e) {
        e.preventDefault();
        
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');
        var nonce = $(this).data('nonce');
        
        if (!confirm('Удалить команду ' + teamName + '?')) {
            return;
        }
        
        // Перенаправляем на страницу удаления
        window.location.href = '<?php echo admin_url('admin.php?page=arsenal-teams&action=delete&team_id='); ?>' + teamId + '&_wpnonce=' + nonce;
    });
});
</script>
