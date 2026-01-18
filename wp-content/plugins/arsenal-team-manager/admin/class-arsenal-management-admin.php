<?php
/**
 * Arsenal Management Admin Class
 * 
 * Управление админ-интерфейсом для руководства
 * 
 * @package Arsenal_Team_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Management_Admin {

    /**
     * Конструктор
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Инициализация
     */
    public function init() {
        add_action( 'wp_ajax_arsenal_delete_management', array( $this, 'delete_management_ajax' ) );
    }

    /**
     * AJAX удаление члена руководства
     */
    public function delete_management_ajax() {
        check_ajax_referer( 'arsenal_management_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $management_id = isset( $_POST['management_id'] ) ? intval( $_POST['management_id'] ) : 0;

        if ( ! $management_id ) {
            wp_send_json_error( 'ID не указан' );
        }

        global $wpdb;
        $result = $wpdb->delete( $wpdb->prefix . 'arsenal_management', array( 'id' => $management_id ), array( '%d' ) );

        if ( $result ) {
            wp_send_json_success( 'Запись удалена' );
        } else {
            wp_send_json_error( 'Ошибка удаления' );
        }
    }

    /**
     * Вывести список руководства
     */
    public function render_management_list() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/management-list.php';
    }

    /**
     * Вывести форму добавления/редактирования
     */
    public function render_management_form() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/management-form.php';
    }
}
