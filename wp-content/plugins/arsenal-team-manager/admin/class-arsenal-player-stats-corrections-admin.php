<?php
/**
 * Arsenal Player Stats Corrections Admin
 * 
 * Админ-интерфейс для управления корректировками статистики игроков
 *
 * @package Arsenal_Team_Manager
 * @since 1.0.0
 */

// Запрет прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Класс для админ-интерфейса корректировок статистики
 */
class Arsenal_Player_Stats_Corrections_Admin {
    
    private static $instance = null;
    private $corrections_manager = null;
    
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
    public function __construct() {
        $this->corrections_manager = Arsenal_Player_Stats_Corrections::get_instance();
    }
    
    /**
     * Инициализация
     */
    public function init() {
        add_action( 'admin_init', array( $this, 'create_table' ) );
        add_action( 'wp_ajax_arsenal_save_correction', array( $this, 'handle_save_correction' ) );
        add_action( 'wp_ajax_arsenal_delete_correction', array( $this, 'handle_delete_correction' ) );
        add_action( 'wp_ajax_arsenal_apply_correction', array( $this, 'handle_apply_correction' ) );
    }
    
    /**
     * Создание таблицы при активации плагина
     */
    public function create_table() {
        $this->corrections_manager->create_table();
    }
    
    /**
     * Отобразить список корректировок
     */
    public function render_page() {
        global $wpdb;
        
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещён' );
        }
        
        // Получить фильтры из GET параметров
        $filters = array();
        if ( ! empty( $_GET['player_id'] ) ) {
            $filters['player_id'] = sanitize_text_field( $_GET['player_id'] );
        }
        if ( ! empty( $_GET['tournament_id'] ) ) {
            $filters['tournament_id'] = sanitize_text_field( $_GET['tournament_id'] );
        }
        if ( isset( $_GET['is_applied'] ) && $_GET['is_applied'] !== '' ) {
            $filters['is_applied'] = intval( $_GET['is_applied'] );
        }
        
        // Получить всех игроков (без ограничений)
        $players = $wpdb->get_results( "SELECT DISTINCT player_id, full_name FROM {$wpdb->prefix}arsenal_players ORDER BY full_name" );
        
        // Получить все турниры
        $tournaments = $wpdb->get_results( "SELECT DISTINCT tournament_id, name FROM {$wpdb->prefix}arsenal_tournaments ORDER BY name" );
        
        // Получить все сезоны
        $seasons = $wpdb->get_results( "SELECT DISTINCT season_id, season_name FROM {$wpdb->prefix}arsenal_seasons ORDER BY season_name DESC" );
        
        // Получить корректировки
        $limit = 50;
        $offset = isset( $_GET['paged'] ) ? ( intval( $_GET['paged'] ) - 1 ) * $limit : 0;
        
        $corrections = $this->corrections_manager->get_corrections_for_admin( $limit, $offset, $filters );
        $total_count = $this->corrections_manager->get_corrections_count( $filters );
        
        // Получить счетчики для статистики
        $unapplied_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_player_stats_corrections WHERE is_applied = 0" );
        $applied_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_player_stats_corrections WHERE is_applied = 1" );
        
        $total_pages = ceil( $total_count / $limit );
        $current_page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        
        // Подключить view файл
        include_once dirname( __FILE__ ) . '/views/player-stats-corrections-list.php';
    }
    
    /**
     * Обработка AJAX запроса сохранения корректировки
     */
    public function handle_save_correction() {
        // Проверка nonce с обработкой ошибки
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_correction_nonce' ) ) {
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Доступ запрещён' );
        }
        
        $player_id = sanitize_text_field( $_POST['player_id'] ?? '' );
        $tournament_id = sanitize_text_field( $_POST['tournament_id'] ?? '' );
        $season_id = sanitize_text_field( $_POST['season_id'] ?? '' );
        
        if ( empty( $player_id ) || empty( $tournament_id ) ) {
            wp_send_json_error( 'Игрок и турнир обязательны' );
        }
        
        $deltas = array(
            'minutes_played' => intval( $_POST['minutes_played_delta'] ?? 0 ),
            'matches_played' => intval( $_POST['matches_played_delta'] ?? 0 ),
            'goals' => intval( $_POST['goals_delta'] ?? 0 ),
            'goals_conceded' => intval( $_POST['goals_conceded_delta'] ?? 0 ),
            'assists' => intval( $_POST['assists_delta'] ?? 0 ),
            'yellow_cards' => intval( $_POST['yellow_cards_delta'] ?? 0 ),
            'red_cards' => intval( $_POST['red_cards_delta'] ?? 0 ),
        );
        
        $reason = sanitize_textarea_field( $_POST['correction_reason'] ?? '' );
        
        try {
            $correction_id = $this->corrections_manager->add_correction(
                $player_id,
                $tournament_id,
                $deltas,
                $reason,
                $season_id ?: null
            );
            
            if ( $correction_id ) {
                wp_send_json_success( array( 'correction_id' => $correction_id ) );
            } else {
                wp_send_json_error( 'Ошибка при сохранении корректировки (добавление не удалось)' );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( 'Исключение: ' . $e->getMessage() );
        } catch ( Throwable $t ) {
            wp_send_json_error( 'Ошибка: ' . $t->getMessage() );
        }
    }
    
    /**
     * Обработка AJAX запроса удаления корректировки
     */
    public function handle_delete_correction() {
        // Проверка nonce с обработкой ошибки
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_correction_nonce' ) ) {
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Доступ запрещён' );
        }
        
        $correction_id = sanitize_text_field( $_POST['correction_id'] ?? '' );
        
        if ( empty( $correction_id ) ) {
            wp_send_json_error( 'correction_id не предоставлен' );
        }
        
        try {
            // Удаляем по correction_id (hex строка)
            if ( $this->corrections_manager->delete_correction_by_hex_id( $correction_id ) ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( 'Ошибка при удалении корректировки' );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( 'Исключение: ' . $e->getMessage() );
        }
    }
    
    /**
     * Обработка AJAX запроса применения корректировки
     */
    public function handle_apply_correction() {
        // Проверка nonce с обработкой ошибки
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_correction_nonce' ) ) {
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Доступ запрещён' );
        }
        
        $correction_id = sanitize_text_field( $_POST['correction_id'] ?? '' );
        
        if ( empty( $correction_id ) ) {
            wp_send_json_error( 'correction_id не предоставлен' );
        }
        
        try {
            if ( $this->corrections_manager->apply_correction( $correction_id ) ) {
                wp_send_json_success();
            } else {
                wp_send_json_error( 'Ошибка при применении корректировки' );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( 'Исключение: ' . $e->getMessage() );
        }
    }
}
