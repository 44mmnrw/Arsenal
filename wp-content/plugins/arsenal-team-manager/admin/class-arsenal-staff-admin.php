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
        add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
    }

    /**
     * Обработка отправки формы сотрудника
     */
    public function handle_form_submission() {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return;
        }

        if ( ! isset( $_POST['save_staff'] ) ) {
            return;
        }

        check_admin_referer( 'arsenal_staff_nonce' );

        require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

        $staff_id = isset( $_POST['staff_id'] ) ? intval( $_POST['staff_id'] ) : 0;
        $is_edit = $staff_id > 0;

        // Обработка achievements
        $achievements_input = sanitize_textarea_field( $_POST['achievements'] ?? '' );
        $achievements_array = array_filter( array_map( 'trim', explode( "\n", $achievements_input ) ) );
        $achievements_json = ! empty( $achievements_array ) ? wp_json_encode( $achievements_array ) : null;

        // Обработка career_positions
        $career_input = sanitize_textarea_field( $_POST['career_positions_text'] ?? '' );
        $career_positions = array();
        if ( ! empty( $career_input ) ) {
            $lines = array_filter( array_map( 'trim', explode( "\n", $career_input ) ) );
            foreach ( $lines as $line ) {
                $parts = array_map( 'trim', explode( '|', $line ) );
                if ( count( $parts ) >= 1 && ! empty( $parts[0] ) ) {
                    $career_positions[] = array(
                        'title' => $parts[0] ?? '',
                        'organization' => $parts[1] ?? '',
                        'experience' => $parts[2] ?? '',
                    );
                }
            }
        }
        $career_positions_json = ! empty( $career_positions ) ? wp_json_encode( $career_positions ) : null;

        $data = array(
            'first_name' => sanitize_text_field( $_POST['first_name'] ?? '' ),
            'second_name' => sanitize_text_field( $_POST['second_name'] ?? '' ),
            'job_title_id' => ! empty( $_POST['job_title_id'] ) ? intval( $_POST['job_title_id'] ) : null,
            'department_id' => ! empty( $_POST['department_id'] ) ? intval( $_POST['department_id'] ) : null,
            'club_type' => sanitize_text_field( $_POST['club_type'] ?? 'Основной клуб' ),
            'birth_date' => ! empty( $_POST['birth_date'] ) ? sanitize_text_field( $_POST['birth_date'] ) : null,
            'contract_start' => ! empty( $_POST['contract_start'] ) ? sanitize_text_field( $_POST['contract_start'] ) : null,
            'contract_end' => ! empty( $_POST['contract_end'] ) ? sanitize_text_field( $_POST['contract_end'] ) : null,
            'phone' => sanitize_text_field( $_POST['phone'] ?? '' ),
            'email' => sanitize_email( $_POST['email'] ?? '' ),
            'photo_url' => ! empty( $_POST['photo_url'] ) ? esc_url_raw( $_POST['photo_url'] ) : '',
            'bio' => sanitize_textarea_field( $_POST['bio'] ?? '' ),
            'experience' => ! empty( $_POST['experience'] ) ? intval( $_POST['experience'] ) : null,
            'citizenship' => sanitize_text_field( $_POST['citizenship'] ?? '' ),
            'interesting_fact' => sanitize_textarea_field( $_POST['interesting_fact'] ?? '' ),
            'achievements' => $achievements_json,
            'career_positions' => $career_positions_json,
        );

        if ( $is_edit ) {
            Arsenal_Staff_Manager::update_staff( $staff_id, $data );
        } else {
            Arsenal_Staff_Manager::add_staff( $data );
        }

        wp_redirect( admin_url( 'admin.php?page=arsenal-staff' ) );
        exit;
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
