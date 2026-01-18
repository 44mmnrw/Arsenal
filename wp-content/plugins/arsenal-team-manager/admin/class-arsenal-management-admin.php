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
        add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
    }

    /**
     * Обработка отправки формы с ранних хуков
     */
    public function handle_form_submission() {
        // Проверяем что мы на нужной странице и метод POST
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return;
        }

        if ( ! isset( $_POST['save_management'] ) ) {
            return;
        }

        check_admin_referer( 'arsenal_management_nonce' );

        global $wpdb;

        $id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $is_edit = $id > 0;

        $data = array(
            'name' => sanitize_text_field( $_POST['name'] ?? '' ),
            'position' => sanitize_text_field( $_POST['position'] ?? '' ),
            'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
            'photo_url' => ! empty( $_POST['photo_url'] ) ? esc_url_raw( $_POST['photo_url'] ) : '',
            'display_order' => intval( $_POST['display_order'] ?? 0 ),
            'club_type' => sanitize_text_field( $_POST['club_type'] ?? 'Основной клуб' ),
        );

        // Валидация
        if ( empty( $data['name'] ) ) {
            return;
        }
        if ( empty( $data['position'] ) ) {
            return;
        }

        if ( $is_edit ) {
            $result = $wpdb->update(
                $wpdb->prefix . 'arsenal_management',
                $data,
                array( 'id' => $id ),
                array( '%s', '%s', '%s', '%s', '%d', '%s' ),
                array( '%d' )
            );
        } else {
            $result = $wpdb->insert(
                $wpdb->prefix . 'arsenal_management',
                $data,
                array( '%s', '%s', '%s', '%s', '%d', '%s' )
            );
        }

        // Если успешно - редирект
        if ( $result !== false ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-management' ) );
            exit;
        }
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
