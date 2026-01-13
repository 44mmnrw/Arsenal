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
 * Главный класс плагина
 */
class Arsenal_Team_Manager {
    
    private static $instance = null;
    
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
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Инициализация хуков
     */
    private function init_hooks() {
        add_action( 'admin_init', array( $this, 'handle_sponsor_form_submission' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
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
     * Подключение зависимостей
     */
    private function load_dependencies() {
        // Классы управления матчами
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-admin.php';
        
        // Классы управления составами
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-lineup-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-lineup-admin.php';
        
        // Классы управления событиями
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-events-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-match-events-admin.php';
        
        // Классы управления стадионами
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-stadium-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-stadium-admin.php';
        
        // Классы управления сезонами
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-season-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-season-admin.php';
        
        // Классы управления лигами
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-league-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-league-admin.php';
        
        // Классы управления корректировками турнирной таблицы
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-standings-adjustments-manager.php';
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-standings-adjustments-admin.php';
        
        // Классы управления персоналом
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-staff-admin.php';
        
        // Инициализируем админ-интерфейсы
        new Arsenal_Match_Admin();
        new Arsenal_Lineup_Admin();
        new Arsenal_Lineup_Manager();
        new Arsenal_Match_Events_Manager();
        new Arsenal_Match_Events_Admin();
        
        // Инициализируем админ-интерфейс стадионов
        $stadium_admin = new Arsenal_Stadium_Admin();
        $stadium_admin->__init__();
        
        // Инициализируем админ-интерфейс сезонов
        $season_admin = new Arsenal_Season_Admin();
        $season_admin->__init__();
        
        // Инициализируем админ-интерфейс лиг
        $league_admin = new Arsenal_League_Admin();
        $league_admin->__init__();
        
        // Инициализируем админ-интерфейс корректировок
        $adjustments_admin = new Arsenal_Standings_Adjustments_Admin();
        $adjustments_admin->__init__();
        
        // Инициализируем админ-интерфейс персонала
        $staff_admin = new Arsenal_Staff_Admin();
        $staff_admin->init();
    }
    
    /**
     * Добавление меню в админку
     */
    public function add_admin_menu() {
        // Основное меню
        $parent_slug = 'arsenal-team';
        
        // Защита: убедиться, что parent_slug инициализирован
        if ( empty( $parent_slug ) ) {
            error_log( '[Arsenal] Warning: parent_slug is empty in add_admin_menu()' );
            return;
        }
        
        add_menu_page(
            'Арсенал',                          // Заголовок страницы
            'Арсенал',                          // Название пункта меню
            'manage_options',                   // Права доступа
            $parent_slug,                       // Slug
            array( $this, 'render_dashboard' ), // Callback функция
            'dashicons-admin-users',            // Иконка
            30                                  // Позиция в меню
        );
        
        // Подменю: Игроки
        add_submenu_page(
            $parent_slug,                       // Родительский slug
            'Игроки',                           // Заголовок страницы
            'Игроки',                           // Название пункта
            'manage_options',                   // Права
            'arsenal-players',                  // Slug
            array( $this, 'render_players_list' ) // Callback
        );
        
        // Подменю: Команды лиги
        add_submenu_page(
            $parent_slug,
            'Команды лиги',
            'Команды лиги',
            'manage_options',
            'arsenal-teams',
            array( $this, 'render_teams_list' )
        );
        
        // Подменю: Контракты
        add_submenu_page(
            $parent_slug,
            'Контракты',
            'Контракты',
            'manage_options',
            'arsenal-contracts',
            array( $this, 'render_contracts' )
        );
        
        // Подменю: Персонал
        add_submenu_page(
            $parent_slug,
            'Персонал',
            'Персонал',
            'manage_options',
            'arsenal-staff',
            array( $this, 'render_staff_list' )
        );
        
        // Подменю: Стадионы
        add_submenu_page(
            $parent_slug,
            'Стадионы',
            'Стадионы',
            'manage_options',
            'arsenal-stadiums',
            array( $this, 'render_stadiums_list' )
        );
        
        // Подменю: Сезоны
        add_submenu_page(
            $parent_slug,
            'Сезоны',
            'Сезоны',
            'manage_options',
            'arsenal-seasons',
            array( $this, 'render_seasons_list' )
        );
        
        // Подменю: Лиги
        add_submenu_page(
            $parent_slug,
            'Лиги',
            'Лиги',
            'manage_options',
            'arsenal-leagues',
            array( $this, 'render_leagues_list' )
        );
        
        // Подменю: Корректировки турнирной таблицы
        add_submenu_page(
            $parent_slug,
            'Корректировки таблицы',
            'Корректировки таблицы',
            'manage_options',
            'arsenal-adjustments',
            array( $this, 'render_adjustments_list' )
        );
        
        // Подменю: Спонсоры и партнеры
        add_submenu_page(
            $parent_slug,
            'Спонсоры и партнеры',
            'Спонсоры и партнеры',
            'manage_options',
            'arsenal-sponsors',
            array( $this, 'render_sponsors_list' )
        );
        
        // Скрытая страница добавления спонсора (без пункта меню)
        add_submenu_page(
            '',
            'Добавить спонсора',
            'Добавить спонсора',
            'manage_options',
            'arsenal-sponsor-add',
            array( $this, 'render_sponsor_add' )
        );
        
        // Скрытая страница редактирования спонсора (без пункта меню)
        add_submenu_page(
            '',
            'Редактировать спонсора',
            'Редактировать спонсора',
            'manage_options',
            'arsenal-sponsor-edit',
            array( $this, 'render_sponsor_edit' )
        );
        
        // Скрытая страница добавления стадиона (без пункта меню)
        add_submenu_page(
            '',
            'Добавить стадион',
            'Добавить стадион',
            'manage_options',
            'arsenal-stadium-add',
            array( $this, 'render_stadium_add' )
        );
        
        // Скрытая страница редактирования стадиона (без пункта меню)
        add_submenu_page(
            '',
            'Редактировать стадион',
            'Редактировать стадион',
            'manage_options',
            'arsenal-stadium-edit',
            array( $this, 'render_stadium_edit' )
        );
        
        // Скрытая страница добавления сезона (без пункта меню)
        add_submenu_page(
            '',
            'Добавить сезон',
            'Добавить сезон',
            'manage_options',
            'arsenal-season-add',
            array( $this, 'render_season_add' )
        );
        
        // Скрытая страница редактирования сезона (без пункта меню)
        add_submenu_page(
            '',
            'Редактировать сезон',
            'Редактировать сезон',
            'manage_options',
            'arsenal-season-edit',
            array( $this, 'render_season_edit' )
        );
        
        // Скрытая страница добавления лиги (без пункта меню)
        add_submenu_page(
            '',
            'Добавить лигу',
            'Добавить лигу',
            'manage_options',
            'arsenal-league-add',
            array( $this, 'render_league_add' )
        );
        
        // Скрытая страница редактирования лиги (без пункта меню)
        add_submenu_page(
            '',
            'Редактировать лигу',
            'Редактировать лигу',
            'manage_options',
            'arsenal-league-edit',
            array( $this, 'render_league_edit' )
        );
        
        // Скрытая страница добавления корректировки (без пункта меню)
        add_submenu_page(
            '',
            'Добавить корректировку',
            'Добавить корректировку',
            'manage_options',
            'arsenal-adjustment-add',
            array( $this, 'render_adjustment_add' )
        );
        
        // Скрытая страница редактирования корректировки (без пункта меню)
        add_submenu_page(
            '',
            'Редактировать корректировку',
            'Редактировать корректировку',
            'manage_options',
            'arsenal-adjustment-edit',
            array( $this, 'render_adjustment_edit' )
        );
        
        // Скрытые страницы персонала (без пункта меню)
        add_submenu_page(
            '',
            'Добавить сотрудника',
            'Добавить сотрудника',
            'manage_options',
            'arsenal-staff-add',
            array( $this, 'render_staff_add' )
        );
        
        add_submenu_page(
            '',
            'Редактировать сотрудника',
            'Редактировать сотрудника',
            'manage_options',
            'arsenal-staff-edit',
            array( $this, 'render_staff_edit' )
        );
        
        add_submenu_page(
            '',
            'Добавить должность',
            'Добавить должность',
            'manage_options',
            'arsenal-job-title-add',
            array( $this, 'render_job_title_add' )
        );
        
        add_submenu_page(
            '',
            'Редактировать должность',
            'Редактировать должность',
            'manage_options',
            'arsenal-job-title-edit',
            array( $this, 'render_job_title_edit' )
        );
        
        // Скрытая страница добавления матча (без пункта меню)
        add_submenu_page(
            '',
            'Добавить матч',
            'Добавить матч',
            'manage_options',
            'arsenal-match-add',
            array( $this, 'render_match_add' )
        );
        
        // Скрытая страница редактирования матча (без пункта меню)
        add_submenu_page(
            '',
            'Редактировать матч',
            'Редактировать матч',
            'manage_options',
            'arsenal-match-edit',
            array( $this, 'render_match_edit' )
        );
        
        // Скрытая страница событий матча (без пункта меню)
        add_submenu_page(
            '',
            'События матча',
            'События матча',
            'manage_options',
            'arsenal-match-events',
            array( $this, 'render_match_events' )
        );
        
        // Скрытая страница составов матча (без пункта меню)
        add_submenu_page(
            '',
            'Составы матча',
            'Составы матча',
            'manage_options',
            'arsenal-match-lineups',
            array( $this, 'render_match_lineups' )
        );
        
        // Скрытая страница редактирования игрока (без пункта меню)
        add_submenu_page(
            '', // Родитель пусто = скрытая страница
            'Редактировать игрока',
            'Редактировать игрока',
            'manage_options',
            'arsenal-player-edit',
            array( $this, 'render_player_edit' )
        );
        
        // Скрытая страница добавления игрока (без пункта меню)
        add_submenu_page(
            '',
            'Добавить игрока',
            'Добавить игрока',
            'manage_options',
            'arsenal-player-add',
            array( $this, 'render_player_add' )
        );
    }
    
    /**
     * Подключение стилей и скриптов
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
            ARSENAL_TM_VERSION . '.' . time() // Добавляем timestamp для сброса кэша
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
                'nonce' => wp_create_nonce( 'arsenal_admin_nonce' )
            )
        );
        
        // Медиа библиотека для загрузки фото
        wp_enqueue_media();
    }
    
    /**
     * Главная страница (Dashboard)
     */
    public function render_dashboard() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Добавление матча
     */
    public function render_match_add() {
        $match_admin = new Arsenal_Match_Admin();
        $match_admin->render_match_form();
    }
    
    /**
     * Редактирование матча
     */
    public function render_match_edit() {
        $match_admin = new Arsenal_Match_Admin();
        $match_admin->render_match_form();
    }
    
    /**
     * События матча
     */
    public function render_match_events() {
        $events_admin = new Arsenal_Match_Events_Admin();
        $match_id = intval( $_GET['match_id'] ?? 0 );
        $events_admin->render_events_form( $match_id );
    }
    
    /**
     * Составы матча
     */
    public function render_match_lineups() {
        $lineups_admin = new Arsenal_Lineup_Admin();
        $lineups_admin->render_lineups_form();
    }
    
    /**
     * Список игроков
     */
    public function render_players_list() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/players-list.php';
    }
    
    /**
     * Редактирование/добавление игрока
     */
    public function render_player_edit() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/player-form.php';
    }
    
    /**
     * Добавление игрока
     */
    public function render_player_add() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/player-form.php';
    }
    
    /**
     * Список команд лиги
     */
    public function render_teams_list() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/teams-list.php';
    }
    
    /**
     * Управление контрактами
     */
    public function render_contracts() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/contracts.php';
    }
    
    /**
     * Список стадионов
     */
    public function render_stadiums_list() {
        $stadium_admin = new Arsenal_Stadium_Admin();
        $stadium_admin->render_stadiums_list();
    }
    
    /**
     * Добавление стадиона
     */
    public function render_stadium_add() {
        $stadium_admin = new Arsenal_Stadium_Admin();
        $stadium_admin->render_stadium_form();
    }
    
    /**
     * Редактирование стадиона
     */
    public function render_stadium_edit() {
        $stadium_admin = new Arsenal_Stadium_Admin();
        $stadium_admin->render_stadium_form();
    }
    
    /**
     * Список сезонов
     */
    public function render_seasons_list() {
        $season_admin = new Arsenal_Season_Admin();
        $season_admin->render_seasons_list();
    }
    
    /**
     * Добавление сезона
     */
    public function render_season_add() {
        $season_admin = new Arsenal_Season_Admin();
        $season_admin->render_season_form();
    }
    
    /**
     * Редактирование сезона
     */
    public function render_season_edit() {
        $season_admin = new Arsenal_Season_Admin();
        $season_admin->render_season_form();
    }
    
    /**
     * Список лиг
     */
    public function render_leagues_list() {
        $league_admin = new Arsenal_League_Admin();
        $league_admin->render_leagues_list();
    }
    
    /**
     * Добавление лиги
     */
    public function render_league_add() {
        $league_admin = new Arsenal_League_Admin();
        $league_admin->render_league_form();
    }
    
    /**
     * Редактирование лиги
     */
    public function render_league_edit() {
        $league_admin = new Arsenal_League_Admin();
        $league_admin->render_league_form();
    }
    
    /**
     * Список корректировок турнирной таблицы
     */
    public function render_adjustments_list() {
        $adjustments_admin = new Arsenal_Standings_Adjustments_Admin();
        $adjustments_admin->render_adjustments_list();
    }
    
    /**
     * Добавление корректировки
     */
    public function render_adjustment_add() {
        $adjustments_admin = new Arsenal_Standings_Adjustments_Admin();
        $adjustments_admin->render_adjustment_form();
    }
    
    /**
     * Редактирование корректировки
     */
    public function render_adjustment_edit() {
        $adjustments_admin = new Arsenal_Standings_Adjustments_Admin();
        $adjustments_admin->render_adjustment_form();
    }
    
    /**
     * Список персонала
     */
    public function render_staff_list() {
        $staff_admin = new Arsenal_Staff_Admin();
        $staff_admin->render_staff_list();
    }
    
    /**
     * Добавление сотрудника
     */
    public function render_staff_add() {
        $staff_admin = new Arsenal_Staff_Admin();
        $staff_admin->render_staff_add();
    }
    
    /**
     * Редактирование сотрудника
     */
    public function render_staff_edit() {
        $staff_admin = new Arsenal_Staff_Admin();
        $staff_admin->render_staff_edit();
    }
    
    /**
     * Добавление должности
     */
    public function render_job_title_add() {
        $staff_admin = new Arsenal_Staff_Admin();
        $staff_admin->render_job_title_add();
    }
    
    /**
     * Редактирование должности
     */
    public function render_job_title_edit() {
        $staff_admin = new Arsenal_Staff_Admin();
        $staff_admin->render_job_title_edit();
    }
    
    /**
     * Спонсоры и партнеры
     */
    public function render_sponsors_list() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-sponsors-admin.php';
        $sponsors_admin = new Arsenal_Sponsors_Admin();
        $sponsors_admin->render_sponsors_list();
    }
    
    /**
     * Добавление спонсора
     */
    public function render_sponsor_add() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-sponsors-admin.php';
        $sponsors_admin = new Arsenal_Sponsors_Admin();
        $sponsors_admin->render_sponsor_form();
    }
    
    /**
     * Редактирование спонсора
     */
    public function render_sponsor_edit() {
        require_once ARSENAL_TM_PLUGIN_DIR . 'admin/class-arsenal-sponsors-admin.php';
        $sponsors_admin = new Arsenal_Sponsors_Admin();
        $sponsors_admin->render_sponsor_form();
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
    
    global $wpdb;
    
    $team_id = intval( $_POST['team_id'] ?? 0 );
    
    if ( empty( $team_id ) ) {
        wp_send_json_error( array( 'message' => 'Не указан ID команды' ) );
    }
    
    // Получаем тренеров для команды
    // Сначала получаем team_id (HEX строка) по ID команды
    $team_hex_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT team_id FROM wp_arsenal_teams WHERE id = %d",
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
        FROM wp_arsenal_team_coaches tc
        LEFT JOIN wp_arsenal_coaches c ON tc.coach_id = c.coach_id
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
        "SELECT team_id FROM wp_arsenal_teams WHERE id = %d",
        $team_id
    ) );
    
    if ( ! $team_hex_id ) {
        wp_send_json_error( array( 'message' => 'Команда не найдена' ) );
    }
    
    // Проверяем, что тренер существует в БД
    $coach_exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT coach_id FROM wp_arsenal_coaches WHERE coach_id = %s",
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
                "SELECT id, coach_id FROM wp_arsenal_team_coaches 
                 WHERE team_id = %s AND end_date = '0000-00-00'
                 ORDER BY start_date DESC LIMIT 1",
                $team_hex_id
            ) );
            
            // Если есть активный тренер - завершаем его контракт на дату начала нового
            if ( $current_active ) {
                $wpdb->update(
                    'wp_arsenal_team_coaches',
                    array( 'end_date' => $start_date ),
                    array( 'id' => $current_active->id ),
                    array( '%s' ),
                    array( '%d' )
                );
            }
        }
    }
    
    // Добавляем запись в wp_arsenal_team_coaches (coach_id - это уже HEX из выпадающего списка)
    $result = $wpdb->insert(
        'wp_arsenal_team_coaches',
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
    
    global $wpdb;
    
    $team_id = intval( $_POST['team_id'] ?? 0 );
    
    if ( empty( $team_id ) ) {
        wp_send_json_error( array( 'message' => 'Команда не указана' ) );
    }
    
    // Получаем HEX ID команды
    $team_hex_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT team_id FROM wp_arsenal_teams WHERE id = %d",
        $team_id
    ) );
    
    if ( ! $team_hex_id ) {
        wp_send_json_error( array( 'message' => 'Команда не найдена' ) );
    }
    
    // Получаем последнюю запись по дате начала
    $last_coach = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, coach_id, start_date FROM wp_arsenal_team_coaches 
         WHERE team_id = %s
         ORDER BY start_date DESC LIMIT 1",
        $team_hex_id
    ) );
    
    if ( ! $last_coach ) {
        wp_send_json_error( array( 'message' => 'Нет записей для удаления' ) );
    }
    
    // Удаляем последнюю запись
    $result = $wpdb->delete(
        'wp_arsenal_team_coaches',
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
    
    global $wpdb;
    
    $coaches = $wpdb->get_results( "
        SELECT id, name, coach_id
        FROM wp_arsenal_coaches
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
    
    global $wpdb;
    
    $coach_name = sanitize_text_field( $_POST['coach_name'] ?? '' );
    
    if ( empty( $coach_name ) ) {
        wp_send_json_error( array( 'message' => 'Имя тренера не указано' ) );
    }
    
    // Проверяем, не существует ли уже такой тренер
    $existing_coach = $wpdb->get_var( $wpdb->prepare(
        "SELECT coach_id FROM wp_arsenal_coaches WHERE name = %s",
        $coach_name
    ) );
    
    if ( $existing_coach ) {
        wp_send_json_error( array( 'message' => 'Тренер с таким именем уже существует в системе' ) );
    }
    
    // Генерируем новый HEX ID
    $new_coach_hex = bin2hex( random_bytes( 4 ) );
    
    // Создаем нового тренера
    $result = $wpdb->insert(
        'wp_arsenal_coaches',
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
    
    global $wpdb;
    
    $coach_id = sanitize_text_field( $_POST['coach_id'] ?? '' );
    
    if ( empty( $coach_id ) ) {
        wp_send_json_error( array( 'message' => 'ID тренера не указан' ) );
    }
    
    // Проверяем, не используется ли этот тренер в активных контрактах
    $active_contracts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM wp_arsenal_team_coaches 
         WHERE coach_id = %s AND (end_date = '0000-00-00' OR end_date IS NULL OR end_date = '')",
        $coach_id
    ) );
    
    if ( $active_contracts > 0 ) {
        wp_send_json_error( array( 'message' => 'Невозможно удалить тренера - у него есть активные контракты. Завершите их перед удалением.' ) );
    }
    
    // Проверяем, есть ли вообще какие-то контракты этого тренера
    $any_contracts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM wp_arsenal_team_coaches WHERE coach_id = %s",
        $coach_id
    ) );
    
    if ( $any_contracts > 0 ) {
        wp_send_json_error( array( 'message' => 'Невозможно удалить тренера - у него есть контракты в истории. Удалите их перед удалением тренера.' ) );
    }
    
    // Получаем имя тренера перед удалением
    $coach_name = $wpdb->get_var( $wpdb->prepare(
        "SELECT name FROM wp_arsenal_coaches WHERE coach_id = %s",
        $coach_id
    ) );
    
    // Удаляем тренера
    $result = $wpdb->delete(
        'wp_arsenal_coaches',
        array( 'coach_id' => $coach_id ),
        array( '%s' )
    );
    
    if ( ! $result ) {
        wp_send_json_error( array( 'message' => 'Ошибка при удалении тренера из БД' ) );
    }
    
    wp_send_json_success( array( 'message' => 'Тренер "' . $coach_name . '" удален из БД' ) );
}

add_action( 'wp_ajax_arsenal_delete_coach_from_db', 'arsenal_delete_coach_from_db' );