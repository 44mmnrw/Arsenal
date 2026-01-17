<?php
/**
 * Arsenal History Admin
 * 
 * Админ-интерфейс для управления историей клуба (одна форма, post_meta)
 * 
 * @package Arsenal_Team_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_History_Admin {
    
    /**
     * Инициализация
     */
    public function __init__() {
        // Используем admin_init для обработки формы (более надежно)
        add_action( 'admin_init', array( $this, 'handle_save_history' ) );
    }
    
    /**
     * Отображение формы управления историей
     */
    public function render_history_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение истории
        $history = Arsenal_History_Manager::get_history();
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/history-form.php';
    }
    
    /**
     * Обработка сохранения истории (срабатывает на admin_init)
     */
    public function handle_save_history() {
        // Проверяем, была ли отправлена наша форма
        if ( ! isset( $_POST['action'] ) || 'arsenal_save_history' !== $_POST['action'] ) {
            return;
        }
        
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_save_history' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        // Подготовка данных
        $data = array(
            'title'            => isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : 'История клуба',
            'description'      => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
            'scale'            => isset( $_POST['scale'] ) ? wp_unslash( $_POST['scale'] ) : '[]',
            'title_second'     => isset( $_POST['title_second'] ) ? sanitize_text_field( $_POST['title_second'] ) : 'Рекорды и достижения',
            'records'          => isset( $_POST['records'] ) ? wp_unslash( $_POST['records'] ) : '[]',
            'achievements'     => isset( $_POST['achievements'] ) ? wp_unslash( $_POST['achievements'] ) : '[]',
            'title_third'      => isset( $_POST['title_third'] ) ? sanitize_text_field( $_POST['title_third'] ) : 'Домашние стадионы',
            'additional_cards' => isset( $_POST['additional_cards'] ) ? wp_unslash( $_POST['additional_cards'] ) : '[]',
        );
        
        // Сохранение
        $saved = Arsenal_History_Manager::save_history( $data );
        
        if ( $saved ) {
            // Добавляем message параметр к текущей странице
            add_action( 'admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e( 'История успешно сохранена!', 'arsenal-team-manager' ); ?></p>
                </div>
                <?php
            });
        } else {
            add_action( 'admin_notices', function() {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e( 'Произошла ошибка при сохранении.', 'arsenal-team-manager' ); ?></p>
                </div>
                <?php
            });
        }
    }
}
