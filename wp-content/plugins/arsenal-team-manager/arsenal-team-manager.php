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
    }
    
    /**
     * Добавление меню в админку
     */
    public function add_admin_menu() {
        // Основное меню
        add_menu_page(
            'Арсенал',                          // Заголовок страницы
            'Арсенал',                          // Название пункта меню
            'manage_options',                   // Права доступа
            'arsenal-team',                     // Slug
            array( $this, 'render_dashboard' ), // Callback функция
            'dashicons-admin-users',            // Иконка
            30                                  // Позиция в меню
        );
        
        // Подменю: Игроки
        add_submenu_page(
            'arsenal-team',                     // Родительский slug
            'Игроки',                           // Заголовок страницы
            'Игроки',                           // Название пункта
            'manage_options',                   // Права
            'arsenal-players',                  // Slug
            array( $this, 'render_players_list' ) // Callback
        );
        
        // Подменю: Команды лиги
        add_submenu_page(
            'arsenal-team',
            'Команды лиги',
            'Команды лиги',
            'manage_options',
            'arsenal-teams',
            array( $this, 'render_teams_list' )
        );
        
        // Подменю: Контракты
        add_submenu_page(
            'arsenal-team',
            'Контракты',
            'Контракты',
            'manage_options',
            'arsenal-contracts',
            array( $this, 'render_contracts' )
        );
        
        // Подменю: Стадионы
        add_submenu_page(
            'arsenal-team',
            'Стадионы',
            'Стадионы',
            'manage_options',
            'arsenal-stadiums',
            array( $this, 'render_stadiums_list' )
        );
        
        // Подменю: Сезоны
        add_submenu_page(
            'arsenal-team',
            'Сезоны',
            'Сезоны',
            'manage_options',
            'arsenal-seasons',
            array( $this, 'render_seasons_list' )
        );
        
        // Подменю: Лиги
        add_submenu_page(
            'arsenal-team',
            'Лиги',
            'Лиги',
            'manage_options',
            'arsenal-leagues',
            array( $this, 'render_leagues_list' )
        );
        
        // Подменю: Корректировки турнирной таблицы
        add_submenu_page(
            'arsenal-team',
            'Корректировки таблицы',
            'Корректировки таблицы',
            'manage_options',
            'arsenal-adjustments',
            array( $this, 'render_adjustments_list' )
        );
        
        // Скрытая страница добавления стадиона (без пункта меню)
        add_submenu_page(
            null,
            'Добавить стадион',
            'Добавить стадион',
            'manage_options',
            'arsenal-stadium-add',
            array( $this, 'render_stadium_add' )
        );
        
        // Скрытая страница редактирования стадиона (без пункта меню)
        add_submenu_page(
            null,
            'Редактировать стадион',
            'Редактировать стадион',
            'manage_options',
            'arsenal-stadium-edit',
            array( $this, 'render_stadium_edit' )
        );
        
        // Скрытая страница добавления сезона (без пункта меню)
        add_submenu_page(
            null,
            'Добавить сезон',
            'Добавить сезон',
            'manage_options',
            'arsenal-season-add',
            array( $this, 'render_season_add' )
        );
        
        // Скрытая страница редактирования сезона (без пункта меню)
        add_submenu_page(
            null,
            'Редактировать сезон',
            'Редактировать сезон',
            'manage_options',
            'arsenal-season-edit',
            array( $this, 'render_season_edit' )
        );
        
        // Скрытая страница добавления лиги (без пункта меню)
        add_submenu_page(
            null,
            'Добавить лигу',
            'Добавить лигу',
            'manage_options',
            'arsenal-league-add',
            array( $this, 'render_league_add' )
        );
        
        // Скрытая страница редактирования лиги (без пункта меню)
        add_submenu_page(
            null,
            'Редактировать лигу',
            'Редактировать лигу',
            'manage_options',
            'arsenal-league-edit',
            array( $this, 'render_league_edit' )
        );
        
        // Скрытая страница добавления корректировки (без пункта меню)
        add_submenu_page(
            null,
            'Добавить корректировку',
            'Добавить корректировку',
            'manage_options',
            'arsenal-adjustment-add',
            array( $this, 'render_adjustment_add' )
        );
        
        // Скрытая страница редактирования корректировки (без пункта меню)
        add_submenu_page(
            null,
            'Редактировать корректировку',
            'Редактировать корректировку',
            'manage_options',
            'arsenal-adjustment-edit',
            array( $this, 'render_adjustment_edit' )
        );
        
        // Скрытая страница добавления матча (без пункта меню)
        add_submenu_page(
            null,
            'Добавить матч',
            'Добавить матч',
            'manage_options',
            'arsenal-match-add',
            array( $this, 'render_match_add' )
        );
        
        // Скрытая страница редактирования матча (без пункта меню)
        add_submenu_page(
            null,
            'Редактировать матч',
            'Редактировать матч',
            'manage_options',
            'arsenal-match-edit',
            array( $this, 'render_match_edit' )
        );
        
        // Скрытая страница событий матча (без пункта меню)
        add_submenu_page(
            null,
            'События матча',
            'События матча',
            'manage_options',
            'arsenal-match-events',
            array( $this, 'render_match_events' )
        );
        
        // Скрытая страница составов матча (без пункта меню)
        add_submenu_page(
            null,
            'Составы матча',
            'Составы матча',
            'manage_options',
            'arsenal-match-lineups',
            array( $this, 'render_match_lineups' )
        );
        
        // Скрытая страница редактирования игрока (без пункта меню)
        add_submenu_page(
            null, // Родитель null = скрытая страница
            'Редактировать игрока',
            'Редактировать игрока',
            'manage_options',
            'arsenal-player-edit',
            array( $this, 'render_player_edit' )
        );
        
        // Скрытая страница добавления игрока (без пункта меню)
        add_submenu_page(
            null,
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
}

/**
 * Инициализация плагина
 */
function arsenal_team_manager() {
    return Arsenal_Team_Manager::get_instance();
}

// Запуск!
arsenal_team_manager();
