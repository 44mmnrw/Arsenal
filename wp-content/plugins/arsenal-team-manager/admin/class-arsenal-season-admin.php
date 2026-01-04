<?php
/**
 * Класс: Админ-интерфейс для сезонов
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Season_Admin {
    
    /**
     * Инициализация
     */
    public function __init__() {
        add_action( 'admin_post_arsenal_create_season', array( $this, 'handle_create_season' ) );
        add_action( 'admin_post_arsenal_update_season', array( $this, 'handle_update_season' ) );
        add_action( 'admin_post_arsenal_delete_season', array( $this, 'handle_delete_season' ) );
    }
    
    /**
     * Отображение списка сезонов
     */
    public function render_seasons_list() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        $result = Arsenal_Season_Manager::get_seasons( $paged, 20 );
        
        $seasons = $result['seasons'];
        $total_pages = $result['total_pages'];
        $total = $result['total'];
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/seasons-list.php';
    }
    
    /**
     * Отображение формы добавления/редактирования сезона
     */
    public function render_season_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $season_id = isset( $_GET['season_id'] ) ? intval( $_GET['season_id'] ) : null;
        $season = null;
        $is_edit = false;
        
        if ( $season_id ) {
            $season = Arsenal_Season_Manager::get_season( $season_id );
            
            if ( ! $season ) {
                wp_die( 'Сезон не найден.' );
            }
            
            $is_edit = true;
        }
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/season-form.php';
    }
    
    /**
     * Обработка создания сезона
     */
    public function handle_create_season() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_create_season' );
        
        // Получение данных из формы
        $data = array(
            'season_name' => $_POST['season_name'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'is_active' => isset( $_POST['is_active'] ) ? 1 : 0,
        );
        
        // Создание сезона
        $season_id = Arsenal_Season_Manager::create_season( $data );
        
        if ( $season_id ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-seasons&message=created' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-season-add&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка обновления сезона
     */
    public function handle_update_season() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_update_season' );
        
        $season_id = intval( $_POST['season_id'] );
        
        // Получение данных из формы
        $data = array(
            'season_name' => $_POST['season_name'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'is_active' => isset( $_POST['is_active'] ) ? 1 : 0,
        );
        
        // Обновление сезона
        $result = Arsenal_Season_Manager::update_season( $season_id, $data );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-seasons&message=updated' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-season-edit&season_id=' . $season_id . '&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка удаления сезона
     */
    public function handle_delete_season() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_delete_season_' . $_GET['season_id'] );
        
        $season_id = intval( $_GET['season_id'] );
        
        // Удаление сезона
        $result = Arsenal_Season_Manager::delete_season( $season_id );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-seasons&message=deleted' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-seasons&message=error' ) );
        }
        exit;
    }
}
