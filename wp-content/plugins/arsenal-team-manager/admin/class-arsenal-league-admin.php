<?php
/**
 * Класс: Админ-интерфейс для лиг
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_League_Admin {
    
    /**
     * Инициализация
     */
    public function __init__() {
        add_action( 'admin_post_arsenal_create_league', array( $this, 'handle_create_league' ) );
        add_action( 'admin_post_arsenal_update_league', array( $this, 'handle_update_league' ) );
        add_action( 'admin_post_arsenal_delete_league', array( $this, 'handle_delete_league' ) );
    }
    
    /**
     * Отображение списка лиг
     */
    public function render_leagues_list() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        $result = Arsenal_League_Manager::get_leagues( $paged, 20 );
        
        $leagues = $result['leagues'];
        $total_pages = $result['total_pages'];
        $total = $result['total'];
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/leagues-list.php';
    }
    
    /**
     * Отображение формы добавления/редактирования лиги
     */
    public function render_league_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $league_id = isset( $_GET['league_id'] ) ? intval( $_GET['league_id'] ) : null;
        $league = null;
        $is_edit = false;
        
        if ( $league_id ) {
            $league = Arsenal_League_Manager::get_league( $league_id );
            
            if ( ! $league ) {
                wp_die( 'Лига не найдена.' );
            }
            
            $is_edit = true;
        }
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/league-form.php';
    }
    
    /**
     * Обработка создания лиги
     */
    public function handle_create_league() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_create_league' );
        
        // Получение данных из формы
        $data = array(
            'league_name' => $_POST['league_name'],
        );
        
        // Создание лиги
        $league_id = Arsenal_League_Manager::create_league( $data );
        
        if ( $league_id ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-leagues&message=created' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-league-add&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка обновления лиги
     */
    public function handle_update_league() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_update_league' );
        
        $league_id = intval( $_POST['league_id'] );
        
        // Получение данных из формы
        $data = array(
            'league_name' => $_POST['league_name'],
        );
        
        // Обновление лиги
        $result = Arsenal_League_Manager::update_league( $league_id, $data );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-leagues&message=updated' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-league-edit&league_id=' . $league_id . '&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка удаления лиги
     */
    public function handle_delete_league() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_delete_league_' . $_GET['league_id'] );
        
        $league_id = intval( $_GET['league_id'] );
        
        // Удаление лиги
        $result = Arsenal_League_Manager::delete_league( $league_id );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-leagues&message=deleted' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-leagues&message=error' ) );
        }
        exit;
    }
}
