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

// Подготавливаем данные о тренерах для каждой команды
$team_coaches = array();
if ( ! empty( $teams ) ) {
    $team_coaches = $wpdb->get_results( "
        SELECT 
            tc.team_id,
            c.name as coach_name,
            tc.start_date,
            tc.end_date
        FROM wp_arsenal_team_coaches tc
        LEFT JOIN wp_arsenal_coaches c ON tc.coach_id = c.coach_id
        WHERE tc.team_id IN (" . implode( ',', array_map( function( $t ) { return "'" . $t->team_id . "'"; }, $teams ) ) . ")
        ORDER BY tc.start_date DESC
    " );
    
    // Организуем тренеров по team_id и объединяем последовательные периоды
    $coaches_by_team = array();
    foreach ( $team_coaches as $coach ) {
        if ( ! isset( $coaches_by_team[ $coach->team_id ] ) ) {
            $coaches_by_team[ $coach->team_id ] = array();
        }
        $coaches_by_team[ $coach->team_id ][] = $coach;
    }
    
    // Объединяем последовательные периоды одного тренера
    foreach ( $coaches_by_team as $team_id => &$coaches ) {
        // Сначала сортируем по дате начала (в прямом порядке - от старых к новым)
        usort( $coaches, function( $a, $b ) {
            return strtotime( $a->start_date ) - strtotime( $b->start_date );
        });
        
        // Группируем по имени и объединяем последовательные периоды
        $merged = array();
        $current_coach = null;
        $current_start = null;
        $current_end = null;
        
        foreach ( $coaches as $coach ) {
            if ( $current_coach === null ) {
                // Первый тренер
                $current_coach = $coach->coach_name;
                $current_start = $coach->start_date;
                $current_end = $coach->end_date;
            } elseif ( $current_coach === $coach->coach_name ) {
                // Тот же тренер - объединяем периоды
                // Берем более раннюю дату начала
                if ( strtotime( $coach->start_date ) < strtotime( $current_start ) ) {
                    $current_start = $coach->start_date;
                }
                // Берем более позднюю дату конца
                // Если хотя бы один период активен (end_date = '0000-00-00' или null), результат активен
                if ( $current_end === '0000-00-00' || $current_end === null ) {
                    // Текущий период активен - оставляем как есть (по настоящее время)
                } elseif ( $coach->end_date === '0000-00-00' || $coach->end_date === null ) {
                    // Новый период активен - переходим на него
                    $current_end = $coach->end_date;
                } elseif ( strtotime( $coach->end_date ) > strtotime( $current_end ) ) {
                    // Обе даты конкретные - берем более позднюю
                    $current_end = $coach->end_date;
                }
            } else {
                // Новый тренер - сохраняем предыдущего
                $merged[] = (object) array(
                    'coach_name' => $current_coach,
                    'start_date' => $current_start,
                    'end_date' => $current_end
                );
                $current_coach = $coach->coach_name;
                $current_start = $coach->start_date;
                $current_end = $coach->end_date;
            }
        }
        
        // Добавляем последнего тренера
        if ( $current_coach !== null ) {
            $merged[] = (object) array(
                'coach_name' => $current_coach,
                'start_date' => $current_start,
                'end_date' => $current_end
            );
        }
        
        // Переопределяем массив объединёнными данными, сортируем по дате начала (новые сверху)
        usort( $merged, function( $a, $b ) {
            return strtotime( $b->start_date ) - strtotime( $a->start_date );
        });
        $coaches = $merged;
    }
    unset( $coaches );
    
    $team_coaches = $coaches_by_team;
}

?>
<div class="teams-wrapper">
    <div class="teams-header">
        <h1>🏆 Команды лиги</h1>
        <button type="button" class="button button-primary" id="add-team-btn">
            + Добавить команду
        </button>
    </div>

    <div class="teams-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo count( $teams ); ?></span>
            <span class="stat-label">Всего команд</span>
        </div>
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

                <!-- Информация о тренерах -->
                <div class="teams-card-coaches" style="margin: 8px 0;">
                    <button type="button" 
                            class="teams-btn-coaches view-coaches-btn" 
                            data-team-id="<?php echo $team->id; ?>"
                            data-team-name="<?php echo esc_attr( $team->name ); ?>"
                            title="Показать информацию о тренерах">
                        <span class="dashicons dashicons-groups"></span>
                        Тренеры
                    </button>
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

<!-- Модальное окно с информацией о тренерах -->
<div id="coaches-modal" class="arsenal-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9998;">
    <div class="arsenal-modal-content" style="width: 50%; max-width: 900px; background: white; border-radius: 5px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); max-height: 85vh; overflow-y: auto; margin: auto;">
        <div class="arsenal-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 20px; border-bottom: 1px solid #eee;">
            <h2 id="coaches-modal-title" style="margin: 0; font-size: 18px; color: #0a0a0a;">Тренеры</h2>
            <button type="button" id="close-coaches-modal" class="arsenal-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999; padding: 0; line-height: 1;">×</button>
        </div>
        <div class="arsenal-modal-body" style="padding: 20px;">
            <input type="hidden" id="coaches-modal-team-id" value="">
            
            <!-- Список тренеров -->
            <div id="coaches-modal-content" style="min-height: 100px; margin-bottom: 20px;">
                <!-- Содержимое со списком тренеров -->
            </div>
            
            <!-- Форма добавления тренера -->
            <div style="border-top: 1px solid #eee; padding-top: 20px;">
                <h3 style="margin: 0 0 15px 0; font-size: 14px; color: #0a0a0a;">Добавить тренера</h3>
                <form id="add-coach-form" style="display: grid; gap: 12px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label for="coach-name-select" style="font-size: 14px; color: #333; font-weight: 600;">Имя тренера *</label>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <select id="coach-name-select" required style="flex: 1; min-width: 300px; padding: 2px 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px; box-sizing: border-box; height: 40px; line-height: 36px; vertical-align: top;">
                                <option value="">-- Выберите тренера --</option>
                                <!-- Опции загружаются через JavaScript -->
                            </select>
                            <button type="button" id="add-new-coach-btn" class="button button-secondary" style="font-size: 14px; padding: 0 18px; height: 40px; white-space: nowrap; display: flex; align-items: center; justify-content: center;">+ Новый</button>
                            <button type="button" id="delete-coach-btn" class="button button-secondary" style="font-size: 14px; padding: 0 18px; height: 40px; white-space: nowrap; display: none; align-items: center; justify-content: center;" title="Удалить тренера">✕ Удалить</button>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label for="coach-start-date" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666; font-weight: 600;">Дата начала *</label>
                            <input type="date" id="coach-start-date" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label for="coach-end-date" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666; font-weight: 600;">Дата окончания</label>
                            <input type="date" id="coach-end-date" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; box-sizing: border-box;">
                            <small style="display: block; margin-top: 4px; color: #999;">Оставить пусто = по настоящее время</small>
                        </div>
                    </div>
                    <button type="submit" class="button button-primary" style="width: 100%; margin-top: 5px;">Добавить тренера</button>
                </form>
                <div id="coach-form-message" style="margin-top: 10px; padding: 10px; border-radius: 3px; display: none; font-size: 13px; border: 1px solid #ddd;"></div>
            </div>
        </div>
        <div class="arsenal-modal-footer" style="text-align: right; padding: 15px 20px; border-top: 1px solid #eee;">
            <button type="button" id="cancel-coaches-modal" class="button button-secondary">Закрыть</button>
        </div>
    </div>
</div>

<!-- Модаль для добавления нового тренера -->
<div id="new-coach-modal" class="arsenal-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: none;">
    <div class="arsenal-modal-content" style="background: white; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); width: 90%; max-width: 550px; max-height: 90vh; overflow-y: auto; margin: 50px auto;">
        <div class="arsenal-modal-header" style="padding: 20px; border-bottom: 1px solid #eee; font-size: 16px; font-weight: 600; color: #0a0a0a;">
            Добавить нового тренера
        </div>
        <div class="arsenal-modal-body" style="padding: 20px;">
            <form id="new-coach-form" style="display: grid; gap: 12px;">
                <div>
                    <label for="new-coach-name" style="display: block; margin-bottom: 5px; font-size: 12px; color: #666; font-weight: 600;">Имя тренера *</label>
                    <input type="text" id="new-coach-name" required placeholder="ФИО тренера" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; box-sizing: border-box;">
                </div>
                <button type="submit" class="button button-primary" style="width: 100%;">Добавить тренера</button>
            </form>
            <div id="new-coach-form-message" style="margin-top: 10px; padding: 10px; border-radius: 3px; display: none; font-size: 13px;"></div>
        </div>
        <div class="arsenal-modal-footer" style="text-align: right; padding: 15px 20px; border-top: 1px solid #eee;">
            <button type="button" id="close-new-coach-modal" class="button button-secondary">Закрыть</button>
        </div>
    </div>
</div>

<style>
#coaches-modal {
    display: none !important;
}

#coaches-modal.active {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

#new-coach-modal.active {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

#coach-form-message.notice-success {
    background: #d4edda;
    border-color: #c3e6cb !important;
    color: #155724;
}

#coach-form-message.notice-error {
    background: #f8d7da;
    border-color: #f5c6cb !important;
    color: #721c24;
}

#new-coach-form-message.notice-success {
    background: #d4edda;
    border-color: #c3e6cb !important;
    color: #155724;
}

#new-coach-form-message.notice-error {
    background: #f8d7da;
    border-color: #f5c6cb !important;
    color: #721c24;
}
</style>
