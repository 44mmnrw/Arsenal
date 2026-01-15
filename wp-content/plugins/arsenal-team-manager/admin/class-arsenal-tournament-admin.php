<?php
/**
 * Класс: Админ-интерфейс для турниров
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Tournament_Admin {
    
    /**
     * Инициализация
     */
    public function __init__() {
        add_action( 'admin_post_arsenal_create_tournament', array( $this, 'handle_create_tournament' ) );
        add_action( 'admin_post_arsenal_update_tournament', array( $this, 'handle_update_tournament' ) );
        add_action( 'admin_post_arsenal_delete_tournament', array( $this, 'handle_delete_tournament' ) );
    }
    
    /**
     * Отображение списка турниров
     */
    public function render_tournaments_list() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        $result = Arsenal_Tournament_Manager::get_tournaments( $paged, 20 );
        
        $tournaments = $result['tournaments'];
        $total_pages = $result['total_pages'];
        $total = $result['total'];
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/tournaments-list.php';
    }
    
    /**
     * Отображение формы добавления/редактирования турнира
     */
    public function render_tournament_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $tournament_id = isset( $_GET['tournament_id'] ) ? sanitize_text_field( $_GET['tournament_id'] ) : null;
        $tournament = null;
        $is_edit = false;
        
        if ( $tournament_id ) {
            $tournament = Arsenal_Tournament_Manager::get_tournament( $tournament_id );
            
            if ( ! $tournament ) {
                wp_die( 'Турнир не найден.' );
            }
            
            $is_edit = true;
        }
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/tournament-form.php';
    }
    
    /**
     * Обработка создания турнира
     */
    public function handle_create_tournament() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_create_tournament' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        // Получение и валидация данных из формы
        if ( ! isset( $_POST['name'] ) || empty( $_POST['name'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournament-add&message=error&error_text=' . urlencode( 'Название турнира обязательно.' ) ) );
            exit;
        }
        
        $data = array(
            'name' => sanitize_text_field( $_POST['name'] ),
            'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
        );
        
        // Создание турнира
        $tournament_id = Arsenal_Tournament_Manager::create_tournament( $data );
        
        if ( $tournament_id ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=created' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournament-add&message=error&error_text=' . urlencode( 'Не удалось создать турнир. Попробуйте ещё раз.' ) ) );
        }
        exit;
    }
    
    /**
     * Обработка обновления турнира
     */
    public function handle_update_tournament() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_update_tournament' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        // Валидация tournament_id
        if ( ! isset( $_POST['tournament_id'] ) || empty( $_POST['tournament_id'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=error' ) );
            exit;
        }
        
        $tournament_id = sanitize_text_field( $_POST['tournament_id'] );
        
        // Валидация названия
        if ( ! isset( $_POST['name'] ) || empty( $_POST['name'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournament-edit&tournament_id=' . esc_attr( $tournament_id ) . '&message=error' ) );
            exit;
        }
        
        // Получение данных из формы
        $data = array(
            'name' => sanitize_text_field( $_POST['name'] ),
            'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
        );
        
        // Обновление турнира
        $result = Arsenal_Tournament_Manager::update_tournament( $tournament_id, $data );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=updated' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournament-edit&tournament_id=' . esc_attr( $tournament_id ) . '&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка удаления турнира
     */
    public function handle_delete_tournament() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'arsenal_delete_tournament' ) ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=error' ) );
            exit;
        }
        
        // Валидация tournament_id
        if ( ! isset( $_GET['tournament_id'] ) || empty( $_GET['tournament_id'] ) ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=error' ) );
            exit;
        }
        
        $tournament_id = sanitize_text_field( $_GET['tournament_id'] );
        
        // Удаление турнира
        $result = Arsenal_Tournament_Manager::delete_tournament( $tournament_id );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=deleted' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-tournaments&message=error' ) );
        }
        exit;
    }
}
