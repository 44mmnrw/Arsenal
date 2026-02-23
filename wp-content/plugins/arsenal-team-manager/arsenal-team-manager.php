<?php
/**
 * Plugin Name: Arsenal Team Manager
 * Plugin URI: https://github.com/44mmnrw/Arsenal
 * Description: Управление командой ФК Арсенал Дзержинск - игроки, статистика, составы
 * Version: 1.0.0
 * Author: Arsenal Dev Team
 * Text Domain: arsenal-team-manager
 * Domain Path: /languages
 */

// Запрет прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Константы плагина
define( 'ARSENAL_TM_VERSION', '1.0.0' );
define( 'ARSENAL_TM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ARSENAL_TM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ARSENAL_MATCH_MANAGER_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Подключение установщика плагина
 */
require_once ARSENAL_TM_PLUGIN_DIR . 'inc/class-installer.php';

/**
 * Главный класс плагина
 *
 * Отвечает за:
 *   - загрузку зависимостей и инициализацию admin-классов (load_dependencies)
 *   - регистрацию глобальных хуков (init_hooks)
 *   - подключение стилей/скриптов (enqueue_admin_assets)
 *   - обработку формы спонсора (handle_sponsor_form_submission)
 *
 * Регистрация меню и render-коллбэки вынесены в Arsenal_Menu_Manager.
 */
class Arsenal_Team_Manager {

    private static $instance = null;

    // Все admin-инстансы хранятся как свойства класса.
    // public — доступ нужен Arsenal_Menu_Manager.
    public $match_admin;
    public $tournament_admin;
    public $lineup_admin;
    public $match_events_admin;
    public $stadium_admin;
    public $season_admin;
    public $league_admin;
    public $adjustments_admin;
    public $staff_admin;
    public $management_admin;
    public $corrections_admin;
    public $sponsors_admin;
    public $db_import_admin;

    /**
     * Singleton
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Конструктор
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Инициализация хуков (кроме admin_menu — он в Arsenal_Menu_Manager)
     */
    private function init_hooks() {
        add_action( 'admin_init', array( $this, 'handle_sponsor_form_submission' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'after_setup_theme', array( $this, 'register_image_sizes' ) );
    }
    
    /**
     * Регистрация кастомных размеров изображений
     */
    public function register_image_sizes() {
        // Размер для логотипов команд (миниатюра)
        add_image_size( 'team-logo-thumb', 120, 120, false ); // 120×120 для админки
        add_image_size( 'team-logo-small', 60, 60, false );   // 60×60 для карточек
        add_image_size( 'team-logo-medium', 200, 200, false ); // 200×200 для фронтенда
    }
    
    /**
     * Загрузка зависимостей и инициализация всех admin-классов.
     * Инстансы сохраняются как свойства для повторного использования.
     */
    private function load_dependencies() {
        // ── Require ───────────────────────────────────────────────────────────
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-lineup-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-lineup-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-events-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-events-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-stadium-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-stadium-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-season-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-season-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-tournament-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-tournament-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-league-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-league-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-standings-adjustments-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-standings-adjustments-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-staff-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-squad-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-management-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-player-stats-corrections.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-player-stats-corrections-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-sponsors-admin.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-db-import-admin.php';

        // ── Инициализация инстансов ───────────────────────────────────────────

        // Матчи
        $this->match_admin        = new Arsenal_Match_Admin();
        $this->lineup_admin       = new Arsenal_Lineup_Admin();
        $this->match_events_admin = new Arsenal_Match_Events_Admin();

        // Само-регистрирующиеся через конструктор (хранить не нужно)
        new Arsenal_Lineup_Manager();
        new Arsenal_Match_Events_Manager();

        // Стадионы, сезоны, турниры, лиги, корректировки — требуют __init__()
        $this->stadium_admin     = new Arsenal_Stadium_Admin();
        $this->stadium_admin->__init__();

        $this->season_admin      = new Arsenal_Season_Admin();
        $this->season_admin->__init__();

        $this->tournament_admin  = new Arsenal_Tournament_Admin();
        $this->tournament_admin->__init__();

        $this->league_admin      = new Arsenal_League_Admin();
        $this->league_admin->__init__();

        $this->adjustments_admin = new Arsenal_Standings_Adjustments_Admin();
        $this->adjustments_admin->__init__();

        // Персонал (init не в конструкторе)
        $this->staff_admin       = new Arsenal_Staff_Admin();
        $this->staff_admin->init();

        // Руководство (конструктор сам вызывает init — повторный вызов не нужен)
        $this->management_admin  = new Arsenal_Management_Admin();

        // Корректировки статистики (init не в конструкторе)
        $this->corrections_admin = new Arsenal_Player_Stats_Corrections_Admin();
        $this->corrections_admin->init();

        // Спонсоры
        $this->sponsors_admin    = new Arsenal_Sponsors_Admin();

        // Импорт данных БД (одноразовый)
        $this->db_import_admin   = new Arsenal_Db_Import_Admin();

        // ── Регистрация меню через выделенный класс ───────────────────────────
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-menu-manager.php';
        $menu_manager = new Arsenal_Menu_Manager( $this );
        add_action( 'admin_menu', array( $menu_manager, 'register' ) );
    }

    /**
     * Подключение стилей и скриптов для страниц плагина
     */
    public function enqueue_admin_assets( $hook ) {
        // Подключаем только на наших страницах
        if ( strpos( $hook, 'arsenal-' ) === false ) {
            return;
        }
        
        // Подключаем стили с версией для сброса кэша
        wp_enqueue_style(
            'arsenal-admin',
            ARSENAL_TM_PLUGIN_URL . 'admin/assets/css/admin.css',
            array(),
            filemtime( ARSENAL_TM_PLUGIN_DIR . 'admin/assets/css/admin.css' )
        );

        // Стили страницы редактирования стадиона
        wp_enqueue_style(
            'arsenal-stadium-form',
            ARSENAL_TM_PLUGIN_URL . 'admin/assets/css/stadium-form.css',
            array( 'arsenal-admin' ),
            filemtime( ARSENAL_TM_PLUGIN_DIR . 'admin/assets/css/stadium-form.css' )
        );
        
        wp_enqueue_script(
            'arsenal-admin',
            ARSENAL_TM_PLUGIN_URL . 'admin/assets/js/admin.js',
            array( 'jquery' ),
            ARSENAL_TM_VERSION,
            true
        );
        
        // Локализация скрипта с нонсом для AJAX
        wp_localize_script(
            'arsenal-admin',
            'arsenal_ajax',
            array(
                'save_team_logo_nonce' => wp_create_nonce( 'arsenal_save_team_logo_ajax' ),
                'get_team_coaches_nonce' => wp_create_nonce( 'arsenal_get_team_coaches' ),
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'arsenal_admin_nonce' ),
                'home_url' => home_url( '/' ),
            )
        );
        
        // Подключение JavaScript для корректировок статистики
        wp_enqueue_script(
            'arsenal-player-stats-corrections',
            ARSENAL_TM_PLUGIN_URL . 'admin/assets/js/player-stats-corrections.js',
            array(),
            ARSENAL_TM_VERSION,
            true
        );
        
        // Медиа библиотека для загрузки фото
        wp_enqueue_media();
    }
    
    /**
     * Обработка отправки формы спонсора (admin_init - ПЕРЕД выводом контента!)
     */
    public function handle_sponsor_form_submission() {
        // Проверяем, была ли отправлена форма спонсора
        if ( ! isset( $_POST['save_sponsor'] ) ) {
            return;
        }

        // Только на нужных страницах
        if ( ! isset( $_GET['page'] ) || ( $_GET['page'] !== 'arsenal-sponsor-add' && $_GET['page'] !== 'arsenal-sponsor-edit' ) ) {
            return;
        }

        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }

        // Verify nonce
        if ( ! isset( $_POST['sponsor_nonce'] ) || ! wp_verify_nonce( $_POST['sponsor_nonce'], 'save_sponsor' ) ) {
            wp_die( 'Ошибка проверки безопасности.' );
        }

        require_once get_template_directory() . '/inc/classes/class-arsenal-sponsors.php';

        $sponsor_data = array(
            'name'        => sanitize_text_field( $_POST['sponsor_name'] ?? '' ),
            'description' => wp_kses_post( $_POST['sponsor_description'] ?? '' ),
            'type'        => sanitize_text_field( $_POST['sponsor_type'] ?? 'partner' ),
            'industry'    => sanitize_text_field( $_POST['sponsor_industry'] ?? '' ),
            'logo_url'    => esc_url_raw( $_POST['sponsor_logo_url'] ?? '' ),
            'website_url' => esc_url_raw( $_POST['sponsor_website_url'] ?? '' ),
            'is_active'   => isset( $_POST['sponsor_is_active'] ) ? 1 : 0,
            'order_index' => absint( $_POST['sponsor_order_index'] ?? 0 ),
        );

        if ( empty( $sponsor_data['name'] ) ) {
            wp_die( 'Пожалуйста, заполните название спонсора.' );
        }

        // Проверяем - это редактирование или создание?
        $is_edit = isset( $_GET['id'] ) ? true : false;
        
        // Если выбран тип "Генеральный спонсор" - переводим предыдущего в партнеры
        if ( 'general_sponsor' === $sponsor_data['type'] ) {
            global $wpdb;
            $current_id = $is_edit ? absint( $_GET['id'] ) : 0;
            
            // Найти существующего генерального спонсора (кроме текущего при редактировании)
            $existing_general = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}arsenal_sponsors 
                     WHERE type = 'general_sponsor' AND id != %d",
                    $current_id
                )
            );
            
            if ( $existing_general ) {
                // Переводим его в партнеры
                $wpdb->update(
                    "{$wpdb->prefix}arsenal_sponsors",
                    array( 'type' => 'partner' ),
                    array( 'id' => $existing_general ),
                    array( '%s' ),
                    array( '%d' )
                );
                set_transient( 'arsenal_previous_general_changed', 1, 10 );
            }
        }
        
        if ( $is_edit ) {
            $sponsor_id = absint( $_GET['id'] );
            Arsenal_Sponsors::update_sponsor( $sponsor_id, $sponsor_data );
        } else {
            Arsenal_Sponsors::create_sponsor( $sponsor_data );
        }

        // РЕДИРЕКТ ПРОИСХОДИТ ДО ВСЕГО ОСТАЛЬНОГО ВЫВОДА!
        wp_safe_redirect( add_query_arg( 'success', 1, admin_url( 'admin.php?page=arsenal-sponsors' ) ) );
        exit;
    }
}

/**
 * Инициализация плагина
 */
function arsenal_team_manager() {
    return Arsenal_Team_Manager::get_instance();
}

// Запуск!
arsenal_team_manager();

/**
 * AJAX handler для сохранения логотипа команды
 */
add_action( 'wp_ajax_arsenal_save_team_logo', 'arsenal_ajax_save_team_logo' );

function arsenal_ajax_save_team_logo() {
    // Проверяем nonce
    check_ajax_referer( 'arsenal_save_team_logo_ajax', 'nonce' );

    // Проверяем права доступа
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет доступа' ) );
    }
    
    // Получаем данные
    $team_id = intval( $_POST['team_id'] ?? 0 );
    $logo_url = esc_url_raw( $_POST['logo_url'] ?? '' );
    
    if ( !$team_id ) {
        wp_send_json_error( array( 'message' => 'Team ID отсутствует' ) );
    }
    
    global $wpdb;
    
    // Обновляем логотип команды
    $updated = $wpdb->update(
        $wpdb->prefix . 'arsenal_teams',
        array( 'logo_url' => $logo_url ),
        array( 'id' => $team_id ),
        array( '%s' ),
        array( '%d' )
    );
    
    if ( $updated !== false ) {
        wp_send_json_success( array( 'message' => 'Логотип сохранён' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Ошибка при сохранении логотипа' ) );
    }
}
/**
 * AJAX обработчик для получения тренеров команды
 */
function arsenal_get_team_coaches() {
    check_ajax_referer( 'arsenal_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    global $wpdb;
    
    $team_id = intval( $_POST['team_id'] ?? 0 );
    
    if ( empty( $team_id ) ) {
        wp_send_json_error( array( 'message' => 'Не указан ID команды' ) );
    }
    
    // Получаем тренеров для команды
    // Сначала получаем team_id (HEX строка) по ID команды
    $team_hex_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE id = %d",
        $team_id
    ) );

    if ( ! $team_hex_id ) {
        wp_send_json_success( array( 'data' => array() ) );
        return;
    }

    // Теперь получаем тренеров по HEX ID
    $coaches = $wpdb->get_results( $wpdb->prepare( "
        SELECT
            c.name as coach_name,
            tc.start_date,
            tc.end_date
        FROM {$wpdb->prefix}arsenal_team_coaches tc
        LEFT JOIN {$wpdb->prefix}arsenal_coaches c ON tc.coach_id = c.coach_id
        WHERE tc.team_id = %s
        ORDER BY tc.start_date DESC
    ", $team_hex_id ) );
    
    if ( empty( $coaches ) ) {
        wp_send_json_success( array( 'data' => array() ) );
        return;
    }
    
    // Объединяем последовательные периоды одного тренера
    usort( $coaches, function( $a, $b ) {
        return strtotime( $a->start_date ) - strtotime( $b->start_date );
    });
    
    $merged = array();
    $current_coach = null;
    $current_start = null;
    $current_end = null;
    
    foreach ( $coaches as $coach ) {
        if ( $current_coach === null ) {
            $current_coach = $coach->coach_name;
            $current_start = $coach->start_date;
            $current_end = $coach->end_date;
        } elseif ( $current_coach === $coach->coach_name ) {
            if ( strtotime( $coach->start_date ) < strtotime( $current_start ) ) {
                $current_start = $coach->start_date;
            }
            if ( $current_end === '0000-00-00' || $current_end === null ) {
                // Текущий период активен
            } elseif ( $coach->end_date === '0000-00-00' || $coach->end_date === null ) {
                $current_end = $coach->end_date;
            } elseif ( strtotime( $coach->end_date ) > strtotime( $current_end ) ) {
                $current_end = $coach->end_date;
            }
        } else {
            $merged[] = (object) array(
                'coach_name' => $current_coach,
                'start_date' => $current_start,
                'end_date' => $current_end
            );
            $current_coach = $coach->coach_name;
            $current_start = $coach->start_date;
            $current_end = $coach->end_date;
        }
    }
    
    if ( $current_coach !== null ) {
        $merged[] = (object) array(
            'coach_name' => $current_coach,
            'start_date' => $current_start,
            'end_date' => $current_end
        );
    }
    
    // Сортируем по дате начала (новые сверху)
    usort( $merged, function( $a, $b ) {
        return strtotime( $b->start_date ) - strtotime( $a->start_date );
    });
    
    // Преобразуем объекты в массивы для JSON
    $coaches_array = array();
    foreach ( $merged as $coach ) {
        $coaches_array[] = array(
            'coach_name' => $coach->coach_name,
            'start_date' => $coach->start_date,
            'end_date' => $coach->end_date
        );
    }
    
    wp_send_json_success( $coaches_array );
}

add_action( 'wp_ajax_arsenal_get_team_coaches', 'arsenal_get_team_coaches' );

/**
 * AJAX handler для добавления тренера из списка (ТОЛЬКО ВЫБОР, БЕЗ СОЗДАНИЯ)
 */
function arsenal_add_coach() {
    check_ajax_referer( 'arsenal_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    global $wpdb;
    
    $team_id = intval( $_POST['team_id'] ?? 0 );
    $coach_id = sanitize_text_field( $_POST['coach_id'] ?? '' ); // HEX coach_id из dropdown
    $start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
    $end_date = sanitize_text_field( $_POST['end_date'] ?? '' );
    
    if ( empty( $team_id ) || empty( $coach_id ) || empty( $start_date ) ) {
        wp_send_json_error( array( 'message' => 'Не заполнены обязательные поля' ) );
    }
    
    // Получаем HEX ID команды
    $team_hex_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE id = %d",
        $team_id
    ) );

    if ( ! $team_hex_id ) {
        wp_send_json_error( array( 'message' => 'Команда не найдена' ) );
    }

    // Проверяем, что тренер существует в БД
    $coach_exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT coach_id FROM {$wpdb->prefix}arsenal_coaches WHERE coach_id = %s",
        $coach_id
    ) );
    
    if ( ! $coach_exists ) {
        wp_send_json_error( array( 'message' => 'Тренер не найден в системе' ) );
    }
    
    // Если дата окончания не указана, используем 0000-00-00 (активный контракт)
    if ( empty( $end_date ) ) {
        $end_date = '0000-00-00';
        
        // Если новый тренер с активным контрактом (без конечной даты),
        // то завершаем контракт у предыдущего активного тренера этой команды
        if ( $end_date === '0000-00-00' ) {
            // Получаем текущего активного тренера (если есть)
            $current_active = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, coach_id FROM {$wpdb->prefix}arsenal_team_coaches
                 WHERE team_id = %s AND end_date = '0000-00-00'
                 ORDER BY start_date DESC LIMIT 1",
                $team_hex_id
            ) );

            // Если есть активный тренер - завершаем его контракт на дату начала нового
            if ( $current_active ) {
                $wpdb->update(
                    $wpdb->prefix . 'arsenal_team_coaches',
                    array( 'end_date' => $start_date ),
                    array( 'id' => $current_active->id ),
                    array( '%s' ),
                    array( '%d' )
                );
            }
        }
    }
    
    // Добавляем запись в arsenal_team_coaches (coach_id - это уже HEX из выпадающего списка)
    $result = $wpdb->insert(
        $wpdb->prefix . 'arsenal_team_coaches',
        array(
            'team_id' => $team_hex_id,
            'coach_id' => $coach_id,
            'start_date' => $start_date,
            'end_date' => $end_date
        ),
        array( '%s', '%s', '%s', '%s' )
    );
    
    if ( ! $result ) {
        wp_send_json_error( array( 'message' => 'Ошибка при добавлении тренера в БД' ) );
    }
    
    wp_send_json_success( array( 'message' => 'Тренер добавлен успешно' ) );
}

add_action( 'wp_ajax_arsenal_add_coach', 'arsenal_add_coach' );

/**
 * AJAX handler для удаления последней записи о тренере команды
 */
function arsenal_delete_last_coach() {
    check_ajax_referer( 'arsenal_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    global $wpdb;
    
    $team_id = intval( $_POST['team_id'] ?? 0 );
    
    if ( empty( $team_id ) ) {
        wp_send_json_error( array( 'message' => 'Команда не указана' ) );
    }
    
    // Получаем HEX ID команды
    $team_hex_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE id = %d",
        $team_id
    ) );

    if ( ! $team_hex_id ) {
        wp_send_json_error( array( 'message' => 'Команда не найдена' ) );
    }

    // Получаем последнюю запись по дате начала
    $last_coach = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, coach_id, start_date FROM {$wpdb->prefix}arsenal_team_coaches
         WHERE team_id = %s
         ORDER BY start_date DESC LIMIT 1",
        $team_hex_id
    ) );
    
    if ( ! $last_coach ) {
        wp_send_json_error( array( 'message' => 'Нет записей для удаления' ) );
    }
    
    // Удаляем последнюю запись
    $result = $wpdb->delete(
        $wpdb->prefix . 'arsenal_team_coaches',
        array( 'id' => $last_coach->id ),
        array( '%d' )
    );
    
    if ( ! $result ) {
        wp_send_json_error( array( 'message' => 'Ошибка при удалении записи' ) );
    }
    
    wp_send_json_success( array( 'message' => 'Запись удалена успешно' ) );
}

add_action( 'wp_ajax_arsenal_delete_last_coach', 'arsenal_delete_last_coach' );

/**
 * AJAX handler для получения списка всех тренеров
 */
function arsenal_get_all_coaches() {
    check_ajax_referer( 'arsenal_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    global $wpdb;
    
    $coaches = $wpdb->get_results( "
        SELECT id, name, coach_id
        FROM {$wpdb->prefix}arsenal_coaches
        ORDER BY name ASC
    " );
    
    if ( empty( $coaches ) ) {
        wp_send_json_success( array() );
    }
    
    wp_send_json_success( $coaches );
}

add_action( 'wp_ajax_arsenal_get_all_coaches', 'arsenal_get_all_coaches' );

/**
 * AJAX handler для создания нового тренера
 */
function arsenal_create_new_coach() {
    check_ajax_referer( 'arsenal_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    global $wpdb;
    
    $coach_name = sanitize_text_field( $_POST['coach_name'] ?? '' );
    
    if ( empty( $coach_name ) ) {
        wp_send_json_error( array( 'message' => 'Имя тренера не указано' ) );
    }
    
    // Проверяем, не существует ли уже такой тренер
    $existing_coach = $wpdb->get_var( $wpdb->prepare(
        "SELECT coach_id FROM {$wpdb->prefix}arsenal_coaches WHERE name = %s",
        $coach_name
    ) );
    
    if ( $existing_coach ) {
        wp_send_json_error( array( 'message' => 'Тренер с таким именем уже существует в системе' ) );
    }
    
    // Генерируем новый HEX ID
    $new_coach_hex = bin2hex( random_bytes( 4 ) );
    
    // Создаем нового тренера
    $result = $wpdb->insert(
        $wpdb->prefix . 'arsenal_coaches',
        array(
            'coach_id' => $new_coach_hex,
            'name' => $coach_name
        ),
        array( '%s', '%s' )
    );
    
    if ( ! $result ) {
        wp_send_json_error( array( 'message' => 'Ошибка при добавлении тренера в БД' ) );
    }
    
    wp_send_json_success( array( 'message' => 'Тренер добавлен успешно', 'coach_id' => $new_coach_hex ) );
}

add_action( 'wp_ajax_arsenal_create_new_coach', 'arsenal_create_new_coach' );

/**
 * AJAX handler для удаления тренера из БД
 */
function arsenal_delete_coach_from_db() {
    check_ajax_referer( 'arsenal_admin_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    global $wpdb;
    
    $coach_id = sanitize_text_field( $_POST['coach_id'] ?? '' );
    
    if ( empty( $coach_id ) ) {
        wp_send_json_error( array( 'message' => 'ID тренера не указан' ) );
    }
    
    // Проверяем, не используется ли этот тренер в активных контрактах
    $active_contracts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_team_coaches
         WHERE coach_id = %s AND (end_date = '0000-00-00' OR end_date IS NULL OR end_date = '')",
        $coach_id
    ) );

    if ( $active_contracts > 0 ) {
        wp_send_json_error( array( 'message' => 'Невозможно удалить тренера - у него есть активные контракты. Завершите их перед удалением.' ) );
    }

    // Проверяем, есть ли вообще какие-то контракты этого тренера
    $any_contracts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_team_coaches WHERE coach_id = %s",
        $coach_id
    ) );

    if ( $any_contracts > 0 ) {
        wp_send_json_error( array( 'message' => 'Невозможно удалить тренера - у него есть контракты в истории. Удалите их перед удалением тренера.' ) );
    }

    // Получаем имя тренера перед удалением
    $coach_name = $wpdb->get_var( $wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}arsenal_coaches WHERE coach_id = %s",
        $coach_id
    ) );

    // Удаляем тренера
    $result = $wpdb->delete(
        $wpdb->prefix . 'arsenal_coaches',
        array( 'coach_id' => $coach_id ),
        array( '%s' )
    );
    
    if ( ! $result ) {
        wp_send_json_error( array( 'message' => 'Ошибка при удалении тренера из БД' ) );
    }
    
    wp_send_json_success( array( 'message' => 'Тренер "' . $coach_name . '" удален из БД' ) );
}

/**
 * AJAX: Получить department_id для должности
 */
function arsenal_get_job_title_department() {
    check_ajax_referer( 'arsenal_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }

    $job_title_id = isset( $_POST['job_title_id'] ) ? intval( $_POST['job_title_id'] ) : 0;
    
    if ( ! $job_title_id ) {
        wp_send_json_error( array( 'message' => 'ID должности не указан' ) );
    }
    
    require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';
    
    $job_title = Arsenal_Staff_Manager::get_job_title( $job_title_id );
    
    if ( ! $job_title ) {
        wp_send_json_error( array( 'message' => 'Должность не найдена' ) );
    }
    
    wp_send_json_success( array(
        'department_id' => $job_title->department_id,
        'job_title_name' => $job_title->job_title_name,
    ) );
}

add_action( 'wp_ajax_arsenal_get_job_title_department', 'arsenal_get_job_title_department' );

add_action( 'wp_ajax_arsenal_delete_coach_from_db', 'arsenal_delete_coach_from_db' );

/**
 * AJAX: Добавить новый отдел
 */
function arsenal_add_department() {
    check_ajax_referer( 'arsenal_add_department', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }
    
    $department_name = sanitize_text_field( $_POST['department_name'] ?? '' );
    $description = wp_kses_post( $_POST['description'] ?? '' );
    
    if ( empty( $department_name ) ) {
        wp_send_json_error( array( 'message' => 'Название отдела обязательно' ) );
    }
    
    require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';
    
    $department_id = Arsenal_Staff_Manager::add_department( $department_name, $description );
    
    if ( ! $department_id ) {
        wp_send_json_error( array( 'message' => 'Ошибка при добавлении отдела в БД' ) );
    }
    
    wp_send_json_success( array( 
        'message' => 'Отдел добавлен успешно',
        'department_id' => $department_id,
        'department_name' => $department_name
    ) );
}

add_action( 'wp_ajax_arsenal_add_department', 'arsenal_add_department' );

/**
 * AJAX: Получить данные отдела
 */
function arsenal_get_department() {
    check_ajax_referer( 'arsenal_get_department', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }
    
    $department_id = intval( $_POST['department_id'] ?? 0 );
    
    if ( ! $department_id ) {
        wp_send_json_error( array( 'message' => 'ID отдела не указан' ) );
    }
    
    require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';
    
    $department = Arsenal_Staff_Manager::get_department( $department_id );
    
    if ( ! $department ) {
        wp_send_json_error( array( 'message' => 'Отдел не найден' ) );
    }
    
    wp_send_json_success( array(
        'id' => $department->id,
        'department_name' => $department->department_name,
        'description' => $department->description
    ) );
}

add_action( 'wp_ajax_arsenal_get_department', 'arsenal_get_department' );

/**
 * AJAX: Обновить отдел
 */
function arsenal_update_department() {
    check_ajax_referer( 'arsenal_update_department', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Нет прав доступа' ) );
    }
    
    $department_id = intval( $_POST['department_id'] ?? 0 );
    $department_name = sanitize_text_field( $_POST['department_name'] ?? '' );
    $description = wp_kses_post( $_POST['description'] ?? '' );
    
    if ( ! $department_id ) {
        wp_send_json_error( array( 'message' => 'ID отдела не указан' ) );
    }
    
    if ( empty( $department_name ) ) {
        wp_send_json_error( array( 'message' => 'Название отдела обязательно' ) );
    }
    
    require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';
    
    $result = Arsenal_Staff_Manager::update_department( $department_id, array(
        'department_name' => $department_name,
        'description' => $description
    ) );
    
    if ( ! $result ) {
        wp_send_json_error( array( 'message' => 'Ошибка при обновлении отдела в БД' ) );
    }
    
    wp_send_json_success( array(
        'message' => 'Отдел обновлен успешно',
        'department_id' => $department_id,
        'department_name' => $department_name
    ) );
}

add_action( 'wp_ajax_arsenal_update_department', 'arsenal_update_department' );