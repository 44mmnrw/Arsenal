<?php
/**
 * Arsenal Staff Manager Admin Class
 * 
 * Управление админ-интерфейсом для персонала
 * 
 * @package Arsenal_Team_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Staff_Admin {

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
        add_action( 'wp_ajax_arsenal_delete_staff', array( $this, 'delete_staff_ajax' ) );
        add_action( 'wp_ajax_arsenal_delete_job_title', array( $this, 'delete_job_title_ajax' ) );
    }

    /**
     * AJAX удаление сотрудника
     */
    public function delete_staff_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $staff_id = isset( $_POST['staff_id'] ) ? intval( $_POST['staff_id'] ) : 0;

        if ( ! $staff_id ) {
            wp_send_json_error( 'ID сотрудника не указан' );
        }

        require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

        $result = Arsenal_Staff_Manager::delete_staff( $staff_id );

        if ( $result ) {
            wp_send_json_success( 'Сотрудник удален' );
        } else {
            wp_send_json_error( 'Ошибка удаления' );
        }
    }

    /**
     * AJAX удаление должности
     */
    public function delete_job_title_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $job_title_id = isset( $_POST['job_title_id'] ) ? intval( $_POST['job_title_id'] ) : 0;

        if ( ! $job_title_id ) {
            wp_send_json_error( 'ID должности не указан' );
        }

        require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

        $result = Arsenal_Staff_Manager::delete_job_title( $job_title_id );

        if ( $result ) {
            wp_send_json_success( 'Должность удалена' );
        } else {
            wp_send_json_error( 'Ошибка удаления' );
        }
    }

    /**
     * Вывести список персонала
     */
    public function render_staff_list() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/staff-list.php';
    }

    /**
     * Вывести форму добавления сотрудника
     */
    public function render_staff_add() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/staff-form.php';
    }

    /**
     * Вывести форму редактирования сотрудника
     */
    public function render_staff_edit() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/staff-form.php';
    }

    /**
     * Вывести список должностей
     */
    public function render_job_titles_list() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/job-titles-list.php';
    }

    /**
     * Вывести форму добавления должности
     */
    public function render_job_title_add() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/job-title-form.php';
    }

    /**
     * Вывести форму редактирования должности
     */
    public function render_job_title_edit() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/views/job-title-form.php';
    }
}
