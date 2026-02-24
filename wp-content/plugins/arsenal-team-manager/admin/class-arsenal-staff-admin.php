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
     * Обеспечить наличие колонки squad_id в таблице отделов.
     *
     * @return bool
     */
    private function ensure_department_squad_column() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'arsenal_staff_department';
        $has_squad_id_column = (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM {$table_name} LIKE %s",
                'squad_id'
            )
        );

        if ( $has_squad_id_column ) {
            return true;
        }

        $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN squad_id VARCHAR(64) NULL" );

        $has_squad_id_column = (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SHOW COLUMNS FROM {$table_name} LIKE %s",
                'squad_id'
            )
        );

        if ( $has_squad_id_column ) {
            $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_squad_id (squad_id)" );
        }

        return $has_squad_id_column;
    }

    /**
     * Обеспечить корректные индексы отделов для работы по составам.
     *
     * Старые схемы могли иметь UNIQUE(department_name), что блокирует
     * одинаковые названия отделов в разных составах.
     *
     * @return void
     */
    private function ensure_department_indexes() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'arsenal_staff_department';

        $indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
        if ( empty( $indexes ) ) {
            return;
        }

        $unique_department_indexes = array();
        $composite_unique_exists = false;

        foreach ( $indexes as $index_row ) {
            if ( (int) $index_row->Non_unique !== 0 ) {
                continue;
            }

            if ( 'PRIMARY' === $index_row->Key_name ) {
                continue;
            }

            if ( 'uniq_department_squad' === $index_row->Key_name || 'department_name_squad_id' === $index_row->Key_name ) {
                $composite_unique_exists = true;
            }

            if ( 'department_name' === $index_row->Column_name ) {
                $unique_department_indexes[ $index_row->Key_name ] = true;
            }
        }

        // Удаляем старые уникальные индексы только на department_name.
        if ( ! empty( $unique_department_indexes ) ) {
            foreach ( array_keys( $unique_department_indexes ) as $index_name ) {
                if ( 'uniq_department_squad' === $index_name || 'department_name_squad_id' === $index_name ) {
                    continue;
                }

                $wpdb->query( "ALTER TABLE {$table_name} DROP INDEX {$index_name}" );
            }
        }

        if ( ! $composite_unique_exists ) {
            $wpdb->query( "ALTER TABLE {$table_name} ADD UNIQUE INDEX uniq_department_squad (department_name, squad_id)" );
        }
    }

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
        add_action( 'wp_ajax_arsenal_add_job_title', array( $this, 'add_job_title_ajax' ) );
        add_action( 'wp_ajax_arsenal_add_department', array( $this, 'add_department_ajax' ) );
        add_action( 'wp_ajax_arsenal_delete_department', array( $this, 'delete_department_ajax' ) );
        add_action( 'wp_ajax_arsenal_update_department_name', array( $this, 'update_department_name_ajax' ) );
        add_action( 'wp_ajax_arsenal_get_department_jobs', array( $this, 'get_department_jobs_ajax' ) );
        add_action( 'wp_ajax_arsenal_get_departments_by_squad', array( $this, 'get_departments_by_squad_ajax' ) );
        add_action( 'wp_ajax_arsenal_get_jobs_by_department', array( $this, 'get_jobs_by_department_ajax' ) );
        add_action( 'wp_ajax_arsenal_get_job_title_data', array( $this, 'get_job_title_data_ajax' ) );
        add_action( 'wp_ajax_arsenal_update_job_title', array( $this, 'update_job_title_ajax' ) );
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
            'squad_id' => ! empty( $_POST['squad_id'] ) ? intval( $_POST['squad_id'] ) : null,
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
            'sort_order' => ! empty( $_POST['sort_order'] ) ? intval( $_POST['sort_order'] ) : 0,
            'achievements' => $achievements_json,
            'career_positions' => $career_positions_json,
        );

        // ✅ ВАЛИДАЦИЯ - проверяем связи между должностью, отделом и квадом
        if ( $data['job_title_id'] && $data['department_id'] ) {
            global $wpdb;
            
            // Проверить что должность принадлежит отделу
            $job_check = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff_job_titles 
                 WHERE id = %d AND department_id = %d",
                $data['job_title_id'],
                $data['department_id']
            ) );

            if ( ! $job_check ) {
                wp_die( 'Ошибка: выбранная должность не принадлежит этому отделу!' );
            }

            // Проверить что отдел принадлежит составу
            if ( $data['squad_id'] ) {
                $dept_check = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff_department d
                     WHERE d.id = %d AND (
                        d.squad_id = %d OR d.squad_id IN (
                            SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d
                        )
                     )",
                    $data['department_id'],
                    $data['squad_id'],
                    $data['squad_id']
                ) );

                if ( ! $dept_check ) {
                    wp_die( 'Ошибка: выбранный отдел не принадлежит этому составу!' );
                }
            }
        }

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

    /**
     * AJAX добавление отдела
     */
    public function add_department_ajax() {
        $nonce = sanitize_text_field( $_POST['nonce'] ?? '' );

        if ( ! $nonce || ( ! wp_verify_nonce( $nonce, 'arsenal_staff_nonce' ) && ! wp_verify_nonce( $nonce, 'arsenal_add_department' ) ) ) {
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

        // Поддержка обеих схем: hex squad_id и numeric id состава.
        $squad_id = sanitize_text_field( $_POST['squad_id'] ?? '' );
        $squad_numeric_id = absint( $_POST['squad_numeric_id'] ?? 0 );
        $department_name = sanitize_text_field( $_POST['department_name'] ?? '' );

        if ( empty( $department_name ) ) {
            wp_send_json_error( 'Название отдела не может быть пустым' );
        }

        global $wpdb;

        $squad_hex_by_numeric = '';
        if ( $squad_numeric_id > 0 ) {
            $squad_hex_by_numeric = (string) $wpdb->get_var( $wpdb->prepare(
                "SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d",
                $squad_numeric_id
            ) );
        }

        $squad_ref = '';
        if ( ! empty( $squad_id ) ) {
            $squad_ref = $squad_id;
        } elseif ( ! empty( $squad_hex_by_numeric ) ) {
            $squad_ref = $squad_hex_by_numeric;
        } elseif ( $squad_numeric_id > 0 ) {
            $squad_ref = (string) $squad_numeric_id;
        }

        $table_name = $wpdb->prefix . 'arsenal_staff_department';

        if ( empty( $squad_ref ) ) {
            wp_send_json_error( 'ID состава не указан' );
        }

        if ( ! $this->ensure_department_squad_column() ) {
            wp_send_json_error( 'Не удалось обновить структуру таблицы отделов (squad_id)' );
        }

        $this->ensure_department_indexes();

        // Проверить есть ли уже отдел с таким названием для этого состава
        $squad_match_conditions = array();
        $squad_match_values = array();

        if ( ! empty( $squad_ref ) ) {
            $squad_match_conditions[] = 'squad_id = %s';
            $squad_match_values[] = $squad_ref;
        }

        if ( $squad_numeric_id > 0 ) {
            $squad_match_conditions[] = 'squad_id = %d';
            $squad_match_values[] = $squad_numeric_id;
        }

        if ( ! empty( $squad_hex_by_numeric ) && $squad_hex_by_numeric !== $squad_ref ) {
            $squad_match_conditions[] = 'squad_id = %s';
            $squad_match_values[] = $squad_hex_by_numeric;
        }

        if ( empty( $squad_match_conditions ) ) {
            wp_send_json_error( 'ID состава не указан' );
        }

        $existing_query = "SELECT id FROM {$wpdb->prefix}arsenal_staff_department
            WHERE department_name = %s
            AND ( " . implode( ' OR ', $squad_match_conditions ) . " )
            LIMIT 1";

        $existing_query_params = array_merge( array( $department_name ), $squad_match_values );
        $existing = $wpdb->get_var( $wpdb->prepare( $existing_query, $existing_query_params ) );

        if ( $existing ) {
            wp_send_json_error( 'Отдел с таким названием уже существует' );
        }

        // Проверить есть ли отдел с этим названием в другом составе
        // Если есть - просто обновить его squad_id, иначе создать новый
        $existing_other = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}arsenal_staff_department 
             WHERE department_name = %s 
             LIMIT 1",
            $department_name
        ));

        if ( $existing_other ) {
            // Отдел существует в другом составе - клонируем его для этого состава
            // Сначала получаем его данные
            $dept_data = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_staff_department WHERE id = %d",
                $existing_other
            ));

            // Legacy-случай: отдел был глобальным (без привязки), просто привязываем к текущему составу.
            if ( isset( $dept_data->squad_id ) && ( null === $dept_data->squad_id || '' === (string) $dept_data->squad_id ) ) {
                $updated = $wpdb->update(
                    $wpdb->prefix . 'arsenal_staff_department',
                    array( 'squad_id' => $squad_ref ),
                    array( 'id' => $existing_other ),
                    array( '%s' ),
                    array( '%d' )
                );

                if ( false !== $updated ) {
                    wp_send_json_success( 'Отдел добавлен' );
                }
            }

            // Вставляем новую запись для этого состава
            $result = $wpdb->insert(
                $wpdb->prefix . 'arsenal_staff_department',
                array(
                    'department_name' => $dept_data->department_name,
                    'description' => $dept_data->description,
                    'sort_order' => $dept_data->sort_order,
                    'squad_id' => $squad_ref,
                ),
                array( '%s', '%s', '%d', '%s' )
            );

            if ( $result ) {
                wp_send_json_success( 'Отдел добавлен' );
            } else {
                $db_error = ! empty( $wpdb->last_error ) ? ' (' . $wpdb->last_error . ')' : '';
                wp_send_json_error( 'Ошибка при добавлении отдела' . $db_error );
            }
        } else {
            // Отдела нет ни в каком составе - создаём новый
            $result = Arsenal_Staff_Manager::add_department( $department_name, '', 0 );

            if ( $result ) {
                // Обновляем squad_id (VARCHAR строка, не число!)
                $wpdb->update(
                    $wpdb->prefix . 'arsenal_staff_department',
                    array( 'squad_id' => $squad_ref ),
                    array( 'id' => $result ),
                    array( '%s' ),
                    array( '%d' )
                );
                wp_send_json_success( 'Отдел добавлен' );
            } else {
                $db_error = ! empty( $wpdb->last_error ) ? ' (' . $wpdb->last_error . ')' : '';
                wp_send_json_error( 'Ошибка при добавлении отдела' . $db_error );
            }
        }
    }

    /**
     * AJAX удаление отдела
     */
    public function delete_department_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

        $squad_id = sanitize_text_field( $_POST['squad_id'] ?? '' );
        $department_id = intval( $_POST['department_id'] ?? 0 );

        if ( ! $department_id ) {
            wp_send_json_error( 'ID отдела не указан' );
        }

        $result = Arsenal_Staff_Manager::delete_department( $department_id );

        if ( $result ) {
            wp_send_json_success( 'Отдел удален' );
        } else {
            wp_send_json_error( 'Ошибка при удалении отдела' );
        }
    }

    /**
     * AJAX обновление названия отдела
     */
    public function update_department_name_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $department_id = intval( $_POST['department_id'] ?? 0 );
        $department_name = sanitize_text_field( $_POST['department_name'] ?? '' );

        if ( ! $department_id || ! $department_name ) {
            wp_send_json_error( 'Параметры не указаны' );
        }

        global $wpdb;

        $result = $wpdb->update(
            "{$wpdb->prefix}arsenal_staff_department",
            array( 'department_name' => $department_name ),
            array( 'id' => $department_id ),
            array( '%s' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            wp_send_json_success( 'Отдел обновлен' );
        } else {
            wp_send_json_error( 'Ошибка при обновлении отдела' );
        }
    }

    /**
     * AJAX получение должностей отдела
     */
    public function get_department_jobs_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        $department_id = intval( $_POST['department_id'] ?? 0 );

        if ( ! $department_id ) {
            wp_send_json_error( 'ID отдела не указан' );
        }

        global $wpdb;

        $jobs = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, job_title_name FROM {$wpdb->prefix}arsenal_staff_job_titles 
             WHERE department_id = %d 
             ORDER BY sort_order ASC, job_title_name ASC",
            $department_id
        ) );

        wp_send_json_success( $jobs );
    }

    /**
     * AJAX получение отделов по кведу
     */
    public function get_departments_by_squad_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        global $wpdb;

        if ( ! $this->ensure_department_squad_column() ) {
            wp_send_json_error( 'Не удалось обновить структуру таблицы отделов (squad_id)' );
        }

        $this->ensure_department_indexes();

        $squad_id_raw = sanitize_text_field( $_POST['squad_id'] ?? '' );
        $squad_numeric_id = absint( $_POST['squad_numeric_id'] ?? 0 );

        if ( 0 === $squad_numeric_id && is_numeric( $squad_id_raw ) ) {
            $squad_numeric_id = (int) $squad_id_raw;
        }

        $squad_hex_by_numeric = '';
        if ( $squad_numeric_id > 0 ) {
            $squad_hex_by_numeric = (string) $wpdb->get_var( $wpdb->prepare(
                "SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d",
                $squad_numeric_id
            ) );
        }

        if ( empty( $squad_id_raw ) && empty( $squad_hex_by_numeric ) && $squad_numeric_id <= 0 ) {
            wp_send_json_error( 'ID состава не указан' );
        }

        $where_clauses = array();
        $where_values = array();

        if ( ! empty( $squad_id_raw ) ) {
            $where_clauses[] = 'd.squad_id = %s';
            $where_values[] = $squad_id_raw;
        }

        if ( $squad_numeric_id > 0 ) {
            $where_clauses[] = 'd.squad_id = %d';
            $where_values[] = $squad_numeric_id;

            $where_clauses[] = "d.squad_id IN ( SELECT squad_id FROM {$wpdb->prefix}arsenal_squad WHERE id = %d )";
            $where_values[] = $squad_numeric_id;
        }

        if ( ! empty( $squad_hex_by_numeric ) && $squad_hex_by_numeric !== $squad_id_raw ) {
            $where_clauses[] = 'd.squad_id = %s';
            $where_values[] = $squad_hex_by_numeric;
        }

        if ( empty( $where_clauses ) ) {
            wp_send_json_success( array() );
        }

        $departments_query = "SELECT DISTINCT d.id, d.department_name
             FROM {$wpdb->prefix}arsenal_staff_department d
             WHERE " . implode( ' OR ', $where_clauses ) . "
             ORDER BY d.sort_order ASC, d.department_name ASC";

        $departments = $wpdb->get_results( $wpdb->prepare( $departments_query, $where_values ) );

        wp_send_json_success( $departments );
    }

    /**
     * AJAX получение должностей по отделу
     */
    public function get_jobs_by_department_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        $department_id = intval( $_POST['department_id'] ?? 0 );

        if ( ! $department_id ) {
            wp_send_json_error( 'ID отдела не указан' );
        }

        global $wpdb;

        // Получаем должности для этого отдела
        $jobs = $wpdb->get_results( $wpdb->prepare(
            "SELECT j.id, j.job_title_name 
             FROM {$wpdb->prefix}arsenal_staff_job_titles j
             WHERE j.department_id = %d
             ORDER BY j.sort_order ASC, j.job_title_name ASC",
            $department_id
        ) );

        wp_send_json_success( $jobs );
    }

    /**
     * AJAX добавление должности
     */
    public function add_job_title_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $department_id = intval( $_POST['department_id'] ?? 0 );
        $job_title_name = sanitize_text_field( $_POST['job_title_name'] ?? '' );
        $job_title_name_plural = sanitize_text_field( $_POST['job_title_name_plural'] ?? '' );

        if ( ! $department_id ) {
            wp_send_json_error( 'ID отдела не указан' );
        }

        if ( empty( $job_title_name ) ) {
            wp_send_json_error( 'Название должности не может быть пустым' );
        }

        // Добавляем должность с множественным числом
        global $wpdb;
        $table_name = $wpdb->prefix . 'arsenal_staff_job_titles';
        
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'department_id' => $department_id,
                'job_title_name' => $job_title_name,
                'job_title_name_plural' => $job_title_name_plural,
                'sort_order' => 0
            ),
            array( '%d', '%s', '%s', '%d' )
        );

        if ( $inserted ) {
            wp_send_json_success( array( 
                'id' => $wpdb->insert_id, 
                'job_title_name' => $job_title_name,
                'job_title_name_plural' => $job_title_name_plural
            ) );
        } else {
            wp_send_json_error( 'Ошибка при добавлении должности: ' . $wpdb->last_error );
        }
    }

    /**
     * AJAX получение данных должности для редактирования
     */
    public function get_job_title_data_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $job_title_id = intval( $_POST['job_title_id'] ?? 0 );

        if ( ! $job_title_id ) {
            wp_send_json_error( 'ID должности не указан' );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'arsenal_staff_job_titles';
        
        $job_title = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, job_title_name, job_title_name_plural FROM {$table_name} WHERE id = %d",
            $job_title_id
        ));

        if ( $job_title ) {
            wp_send_json_success( (array) $job_title );
        } else {
            wp_send_json_error( 'Должность не найдена' );
        }
    }

    /**
     * AJAX обновление должности
     */
    public function update_job_title_ajax() {
        check_ajax_referer( 'arsenal_staff_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        $job_title_id = intval( $_POST['job_title_id'] ?? 0 );
        $job_title_name = sanitize_text_field( $_POST['job_title_name'] ?? '' );
        $job_title_name_plural = sanitize_text_field( $_POST['job_title_name_plural'] ?? '' );

        if ( ! $job_title_id ) {
            wp_send_json_error( 'ID должности не указан' );
        }

        if ( empty( $job_title_name ) ) {
            wp_send_json_error( 'Название должности не может быть пустым' );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'arsenal_staff_job_titles';
        
        $updated = $wpdb->update(
            $table_name,
            array(
                'job_title_name' => $job_title_name,
                'job_title_name_plural' => $job_title_name_plural
            ),
            array( 'id' => $job_title_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( $updated !== false ) {
            wp_send_json_success( array( 
                'id' => $job_title_id, 
                'job_title_name' => $job_title_name,
                'job_title_name_plural' => $job_title_name_plural
            ) );
        } else {
            wp_send_json_error( 'Ошибка при обновлении должности: ' . $wpdb->last_error );
        }
    }
}
