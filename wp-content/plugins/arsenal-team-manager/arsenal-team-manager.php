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
                'ajax_url' => admin_url( 'admin-ajax.php' )
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

        require_once get_template_directory() . '/inc/class-arsenal-sponsors.php';

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
    // Логируем входящие данные
    error_log('AJAX save_team_logo called: ' . json_encode($_POST));
    
    // Проверяем права доступа
    if ( ! current_user_can( 'manage_options' ) ) {
        error_log('User does not have manage_options capability');
        wp_send_json_error( array( 'message' => 'Нет доступа' ) );
    }
    
    // Получаем данные
    $team_id = intval( $_POST['team_id'] ?? 0 );
    $logo_url = esc_url_raw( $_POST['logo_url'] ?? '' );
    
    error_log('Processing team_id=' . $team_id . ', logo_url=' . $logo_url);
    
    if ( !$team_id ) {
        error_log('Team ID is empty');
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
    
    error_log('Update result: ' . $updated);
    
    if ( $updated !== false ) {
        error_log('Logo saved successfully');
        wp_send_json_success( array( 'message' => 'Логотип сохранён' ) );
    } else {
        error_log('Update failed: ' . $wpdb->last_error);
        wp_send_json_error( array( 'message' => 'Ошибка при сохранении логотипа' ) );
    }
}
