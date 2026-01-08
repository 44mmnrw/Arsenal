<?php
/**
 * Класс для управления админ-интерфейсом матчей
 *
 * Регистрация меню, страниц редактирования и отображение списка матчей
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Match_Admin {
    
    /**
     * Конструктор
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 15 );
        add_action( 'admin_post_arsenal_create_match', array( $this, 'handle_create_match' ) );
        add_action( 'admin_post_arsenal_update_match', array( $this, 'handle_update_match' ) );
        add_action( 'admin_post_arsenal_delete_match', array( $this, 'handle_delete_match' ) );
    }
    
    /**
     * Добавление меню матчей в админку
     */
    public function add_admin_menu() {
        // Подменю: Матчи
        add_submenu_page(
            'arsenal-team',                          // Родительский slug
            'Матчи',                                 // Заголовок страницы
            'Матчи',                                 // Название пункта
            'manage_options',                        // Права
            'arsenal-matches',                       // Slug
            array( $this, 'render_matches_list' )   // Callback
        );
        
        // Скрытая страница редактирования матча регистрируется в главном классе плагина
        // чтобы избежать дублирования
    }
    
    /**
     * Отображение списка матчей
     */
    public function render_matches_list() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение параметров пагинации и фильтра
        $paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        $per_page = 20;
        
        // Фильтры
        $filters = array();
        if ( ! empty( $_GET['filter_team'] ) ) {
            $filters['team_id'] = sanitize_text_field( $_GET['filter_team'] );
        }
        if ( ! empty( $_GET['filter_status'] ) ) {
            $filters['status'] = sanitize_text_field( $_GET['filter_status'] );
        }
        if ( ! empty( $_GET['filter_tournament'] ) ) {
            $filters['tournament_id'] = sanitize_text_field( $_GET['filter_tournament'] );
        }
        if ( ! empty( $_GET['filter_season'] ) ) {
            $filters['season_id'] = sanitize_text_field( $_GET['filter_season'] );
        }
        
        // Получение данных
        $result = Arsenal_Match_Manager::get_matches( $paged, $per_page, $filters );
        $matches = $result['matches'];
        $total_pages = $result['total_pages'];
        $total = $result['total'];
        
        // Получение справочников для фильтров
        $teams = Arsenal_Match_Manager::get_teams();
        $tournaments = Arsenal_Match_Manager::get_tournaments();
        $statuses = Arsenal_Match_Manager::get_match_statuses();
        $seasons = Arsenal_Match_Manager::get_seasons();
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/matches-list.php';
    }
    
    /**
     * Отображение формы добавления/редактирования матча
     */
    public function render_match_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        $match = null;
        $is_edit = false;
        
        // Если редактируем матч
        if ( isset( $_GET['match_id'] ) ) {
            $match_id = intval( $_GET['match_id'] );
            $match = Arsenal_Match_Manager::get_match( $match_id );
            $is_edit = true;
            
            if ( ! $match ) {
                wp_die( 'Матч не найден.' );
            }
        }
        
        // Получение справочников
        $teams = Arsenal_Match_Manager::get_teams();
        $stadiums = Arsenal_Match_Manager::get_stadiums();
        $tournaments = Arsenal_Match_Manager::get_tournaments();
        $statuses = Arsenal_Match_Manager::get_match_statuses();
        $seasons = Arsenal_Season_Manager::get_seasons( 1, 999 );
        
        // Получение составов и событий матча (если редактируем)
        $lineups = $is_edit ? Arsenal_Match_Manager::get_match_lineups( $match->match_id ) : array();
        $events = $is_edit ? Arsenal_Match_Manager::get_match_events( $match->match_id ) : array();
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/match-form.php';
    }
    
    /**
     * Обработка создания матча
     */
    public function handle_create_match() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['arsenal_match_nonce'] ) || 
             ! wp_verify_nonce( $_POST['arsenal_match_nonce'], 'arsenal_match_form' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        // Подготовка данных
        $data = array(
            'tournament_id' => sanitize_text_field( $_POST['tournament_id'] ?? '' ),
            'match_date' => sanitize_text_field( $_POST['match_date'] ?? '' ),
            'match_time' => sanitize_text_field( $_POST['match_time'] ?? '' ),
            'home_team_id' => sanitize_text_field( $_POST['home_team_id'] ?? '' ),
            'away_team_id' => sanitize_text_field( $_POST['away_team_id'] ?? '' ),
            'home_score' => sanitize_text_field( $_POST['home_score'] ?? '' ),
            'away_score' => sanitize_text_field( $_POST['away_score'] ?? '' ),
            'status' => sanitize_text_field( $_POST['status'] ?? 'NS' ),
            'tour' => sanitize_text_field( $_POST['tour'] ?? '' ),
            'stadium_id' => sanitize_text_field( $_POST['stadium_id'] ?? '' ),
            'attendance' => sanitize_text_field( $_POST['attendance'] ?? '' ),
            'main_referee' => sanitize_text_field( $_POST['main_referee'] ?? '' ),
            'assistant_referees_1' => sanitize_text_field( $_POST['assistant_referees_1'] ?? '' ),
            'assistant_referees_2' => sanitize_text_field( $_POST['assistant_referees_2'] ?? '' ),
            'fourth_referee' => sanitize_text_field( $_POST['fourth_referee'] ?? '' ),
            'referee_inspector' => sanitize_text_field( $_POST['referee_inspector'] ?? '' ),
            'delegate' => sanitize_text_field( $_POST['delegate'] ?? '' ),
            'match_report' => wp_kses_post( $_POST['match_report'] ?? '' ),
        );
        
        // Создание матча
        $match_id = Arsenal_Match_Manager::create_match( $data );
        
        if ( $match_id ) {
            wp_safe_remote_post( admin_url( 'admin.php?page=arsenal-matches' ), array(
                'blocking' => false,
            ) );
            wp_redirect( admin_url( 'admin.php?page=arsenal-matches&success=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-match-add&error=1' ) );
        }
        
        exit;
    }
    
    /**
     * Обработка обновления матча
     */
    public function handle_update_match() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['arsenal_match_nonce'] ) || 
             ! wp_verify_nonce( $_POST['arsenal_match_nonce'], 'arsenal_match_form' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $match_id = intval( $_POST['match_id'] ?? 0 );
        if ( ! $match_id ) {
            wp_die( 'ID матча не указан.' );
        }
        
        // Подготовка данных
        $data = array(
            'tournament_id' => sanitize_text_field( $_POST['tournament_id'] ?? '' ),
            'season_id' => sanitize_text_field( $_POST['season_id'] ?? '' ),
            'match_date' => sanitize_text_field( $_POST['match_date'] ?? '' ),
            'match_time' => sanitize_text_field( $_POST['match_time'] ?? '' ),
            'home_team_id' => sanitize_text_field( $_POST['home_team_id'] ?? '' ),
            'away_team_id' => sanitize_text_field( $_POST['away_team_id'] ?? '' ),
            'home_score' => sanitize_text_field( $_POST['home_score'] ?? '' ),
            'away_score' => sanitize_text_field( $_POST['away_score'] ?? '' ),
            'status' => sanitize_text_field( $_POST['status'] ?? 'NS' ),
            'tour' => sanitize_text_field( $_POST['tour'] ?? '' ),
            'stadium_id' => sanitize_text_field( $_POST['stadium_id'] ?? '' ),
            'attendance' => sanitize_text_field( $_POST['attendance'] ?? '' ),
            'main_referee' => sanitize_text_field( $_POST['main_referee'] ?? '' ),
            'assistant_referees_1' => sanitize_text_field( $_POST['assistant_referees_1'] ?? '' ),
            'assistant_referees_2' => sanitize_text_field( $_POST['assistant_referees_2'] ?? '' ),
            'fourth_referee' => sanitize_text_field( $_POST['fourth_referee'] ?? '' ),
            'referee_inspector' => sanitize_text_field( $_POST['referee_inspector'] ?? '' ),
            'delegate' => sanitize_text_field( $_POST['delegate'] ?? '' ),
            'match_report' => wp_kses_post( $_POST['match_report'] ?? '' ),
        );
        
        // Обновление матча
        $result = Arsenal_Match_Manager::update_match( $match_id, $data );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-matches&success=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match_id . '&error=1' ) );
        }
        
        exit;
    }
    
    /**
     * Обработка удаления матча
     */
    public function handle_delete_match() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_GET['_wpnonce'] ) || 
             ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_match_' . intval( $_GET['match_id'] ) ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $match_id = intval( $_GET['match_id'] ?? 0 );
        if ( ! $match_id ) {
            wp_die( 'ID матча не указан.' );
        }
        
        // Удаление матча
        $result = Arsenal_Match_Manager::delete_match( $match_id );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-matches&deleted=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-matches&error=1' ) );
        }
        
        exit;
    }
}
