<?php
/**
 * Класс: Админ-интерфейс для корректировок турнирной таблицы
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Standings_Adjustments_Admin {
    
    /**
     * Инициализация
     */
    public function __init__() {
        add_action( 'admin_post_arsenal_create_adjustment', array( $this, 'handle_create_adjustment' ) );
        add_action( 'admin_post_arsenal_update_adjustment', array( $this, 'handle_update_adjustment' ) );
        add_action( 'admin_post_arsenal_delete_adjustment', array( $this, 'handle_delete_adjustment' ) );
    }
    
    /**
     * Отображение списка корректировок
     */
    public function render_adjustments_list() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        $result = Arsenal_Standings_Adjustments_Manager::get_adjustments( $paged, 20 );
        
        $adjustments = $result['adjustments'];
        $total_pages = $result['total_pages'];
        $total = $result['total'];
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/adjustments-list.php';
    }
    
    /**
     * Отображение формы добавления/редактирования корректировки
     */
    public function render_adjustment_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение данных
        $adjustment_id = isset( $_GET['adjustment_id'] ) ? intval( $_GET['adjustment_id'] ) : null;
        $adjustment = null;
        $is_edit = false;
        
        if ( $adjustment_id ) {
            $adjustment = Arsenal_Standings_Adjustments_Manager::get_adjustment( $adjustment_id );
            
            if ( ! $adjustment ) {
                wp_die( 'Корректировка не найдена.' );
            }
            
            $is_edit = true;
        }
        
        // Получить справочники
        $teams = Arsenal_Standings_Adjustments_Manager::get_teams();
        $seasons = Arsenal_Season_Manager::get_seasons( 1, 999 );
        $tournaments = Arsenal_Match_Manager::get_tournaments();
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/adjustment-form.php';
    }
    
    /**
     * Обработка создания корректировки
     */
    public function handle_create_adjustment() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_create_adjustment' );
        
        // Получение данных из формы
        $data = array(
            'tournament_id' => $_POST['tournament_id'],
            'season_id' => $_POST['season_id'],
            'team_id' => $_POST['team_id'],
            'adjustment_points' => $_POST['adjustment_points'],
            'comment' => $_POST['comment'],
            'applied_date' => $_POST['applied_date'],
        );
        
        // Создание корректировки
        $adjustment_id = Arsenal_Standings_Adjustments_Manager::create_adjustment( $data );
        
        if ( $adjustment_id ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-adjustments&message=created' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-adjustment-add&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка обновления корректировки
     */
    public function handle_update_adjustment() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_update_adjustment' );
        
        $adjustment_id = intval( $_POST['adjustment_id'] );
        
        // Получение данных из формы
        $data = array(
            'tournament_id' => $_POST['tournament_id'],
            'season_id' => $_POST['season_id'],
            'team_id' => $_POST['team_id'],
            'adjustment_points' => $_POST['adjustment_points'],
            'comment' => $_POST['comment'],
            'applied_date' => $_POST['applied_date'],
        );
        
        // Обновление корректировки
        $result = Arsenal_Standings_Adjustments_Manager::update_adjustment( $adjustment_id, $data );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-adjustments&message=updated' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-adjustment-edit&adjustment_id=' . $adjustment_id . '&message=error' ) );
        }
        exit;
    }
    
    /**
     * Обработка удаления корректировки
     */
    public function handle_delete_adjustment() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для выполнения этого действия.' );
        }
        
        // Проверка nonce
        check_admin_referer( 'arsenal_delete_adjustment_' . $_GET['adjustment_id'] );
        
        $adjustment_id = intval( $_GET['adjustment_id'] );
        
        // Удаление корректировки
        $result = Arsenal_Standings_Adjustments_Manager::delete_adjustment( $adjustment_id );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-adjustments&message=deleted' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-adjustments&message=error' ) );
        }
        exit;
    }
}
