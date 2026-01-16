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
        error_log( '[Arsenal Corrections SAVE] === AJAX handler called ===' );
        error_log( '[Arsenal Corrections SAVE] $_POST содержит: ' . json_encode( $_POST ) );
        
        // Проверка nonce с обработкой ошибки
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_correction_nonce' ) ) {
            error_log( '[Arsenal Corrections SAVE] ❌ Nonce verification failed' );
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }
        error_log( '[Arsenal Corrections SAVE] ✓ Nonce проверен успешно' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            error_log( '[Arsenal Corrections SAVE] ❌ Permission denied' );
            wp_send_json_error( 'Доступ запрещён' );
        }
        error_log( '[Arsenal Corrections SAVE] ✓ Права проверены' );
        
        $player_id = sanitize_text_field( $_POST['player_id'] ?? '' );
        $tournament_id = sanitize_text_field( $_POST['tournament_id'] ?? '' );
        $season_id = sanitize_text_field( $_POST['season_id'] ?? '' );
        
        error_log( '[Arsenal Corrections SAVE] player_id: ' . $player_id );
        error_log( '[Arsenal Corrections SAVE] tournament_id: ' . $tournament_id );
        error_log( '[Arsenal Corrections SAVE] season_id: ' . $season_id );
        
        if ( empty( $player_id ) || empty( $tournament_id ) ) {
            error_log( '[Arsenal Corrections SAVE] ❌ ERROR: Missing player_id or tournament_id' );
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
        
        error_log( '[Arsenal Corrections SAVE] Deltas: ' . json_encode( $deltas ) );
        error_log( '[Arsenal Corrections SAVE] Reason: ' . $reason );
        
        try {
            error_log( '[Arsenal Corrections SAVE] Вызываю add_correction()...' );
            
            $correction_id = $this->corrections_manager->add_correction(
                $player_id,
                $tournament_id,
                $deltas,
                $reason,
                $season_id ?: null
            );
            
            error_log( '[Arsenal Corrections SAVE] add_correction() вернул: ' . var_export( $correction_id, true ) );
            
            if ( $correction_id ) {
                error_log( '[Arsenal Corrections SAVE] ✓ SUCCESS! correction_id: ' . $correction_id );
                wp_send_json_success( array( 'correction_id' => $correction_id ) );
            } else {
                error_log( '[Arsenal Corrections SAVE] ❌ ERROR: add_correction() вернул false или пусто' );
                error_log( '[Arsenal Corrections SAVE] wpdb->last_error: ' . $this->corrections_manager->wpdb->last_error );
                error_log( '[Arsenal Corrections SAVE] wpdb->last_query: ' . $this->corrections_manager->wpdb->last_query );
                wp_send_json_error( 'Ошибка при сохранении корректировки (добавление не удалось)' );
            }
        } catch ( Exception $e ) {
            error_log( '[Arsenal Corrections SAVE] ❌ EXCEPTION: ' . $e->getMessage() );
            error_log( '[Arsenal Corrections SAVE] Stack trace: ' . $e->getTraceAsString() );
            wp_send_json_error( 'Исключение: ' . $e->getMessage() );
        } catch ( Throwable $t ) {
            error_log( '[Arsenal Corrections SAVE] ❌ THROWABLE: ' . $t->getMessage() );
            error_log( '[Arsenal Corrections SAVE] Stack trace: ' . $t->getTraceAsString() );
            wp_send_json_error( 'Ошибка: ' . $t->getMessage() );
        }
    }
    
    /**
     * Обработка AJAX запроса удаления корректировки
     */
    public function handle_delete_correction() {
        error_log( '[Arsenal Corrections Delete] === AJAX handler called ===' );
        
        // Проверка nonce с обработкой ошибки
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_correction_nonce' ) ) {
            error_log( '[Arsenal Corrections Delete] ❌ Nonce verification failed' );
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }
        error_log( '[Arsenal Corrections Delete] ✓ Nonce проверен успешно' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            error_log( '[Arsenal Corrections Delete] ❌ Permission denied' );
            wp_send_json_error( 'Доступ запрещён' );
        }
        error_log( '[Arsenal Corrections Delete] ✓ Права проверены' );
        
        $correction_id = sanitize_text_field( $_POST['correction_id'] ?? '' );
        error_log( '[Arsenal Corrections Delete] correction_id: ' . $correction_id );
        
        if ( empty( $correction_id ) ) {
            error_log( '[Arsenal Corrections Delete] ❌ ERROR: correction_id is empty' );
            wp_send_json_error( 'correction_id не предоставлен' );
        }
        
        try {
            error_log( '[Arsenal Corrections Delete] Attempting to delete...' );
            
            // Удаляем по correction_id (hex строка)
            if ( $this->corrections_manager->delete_correction_by_hex_id( $correction_id ) ) {
                error_log( '[Arsenal Corrections Delete] ✓ SUCCESS! Correction deleted: ' . $correction_id );
                wp_send_json_success();
            } else {
                error_log( '[Arsenal Corrections Delete] ❌ ERROR: delete_correction_by_hex_id returned false' );
                wp_send_json_error( 'Ошибка при удалении корректировки' );
            }
        } catch ( Exception $e ) {
            error_log( '[Arsenal Corrections Delete] ❌ EXCEPTION: ' . $e->getMessage() );
            error_log( '[Arsenal Corrections Delete] Stack trace: ' . $e->getTraceAsString() );
            wp_send_json_error( 'Исключение: ' . $e->getMessage() );
        }
    }
    
    /**
     * Обработка AJAX запроса применения корректировки
     */
    public function handle_apply_correction() {
        error_log( '[Arsenal Corrections Apply] === AJAX handler called ===' );
        
        // Проверка nonce с обработкой ошибки
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'arsenal_correction_nonce' ) ) {
            error_log( '[Arsenal Corrections Apply] ❌ Nonce verification failed' );
            wp_send_json_error( 'Ошибка безопасности (nonce)' );
        }
        error_log( '[Arsenal Corrections Apply] ✓ Nonce проверен успешно' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            error_log( '[Arsenal Corrections Apply] ❌ Permission denied' );
            wp_send_json_error( 'Доступ запрещён' );
        }
        error_log( '[Arsenal Corrections Apply] ✓ Права проверены' );
        
        $correction_id = sanitize_text_field( $_POST['correction_id'] ?? '' );
        error_log( '[Arsenal Corrections Apply] correction_id: ' . $correction_id );
        
        if ( empty( $correction_id ) ) {
            error_log( '[Arsenal Corrections Apply] ❌ ERROR: correction_id is empty' );
            wp_send_json_error( 'correction_id не предоставлен' );
        }
        
        try {
            error_log( '[Arsenal Corrections Apply] Attempting to apply...' );
            
            if ( $this->corrections_manager->apply_correction( $correction_id ) ) {
                error_log( '[Arsenal Corrections Apply] ✓ SUCCESS! Correction applied: ' . $correction_id );
                wp_send_json_success();
            } else {
                error_log( '[Arsenal Corrections Apply] ❌ ERROR: apply_correction returned false' );
                wp_send_json_error( 'Ошибка при применении корректировки' );
            }
        } catch ( Exception $e ) {
            error_log( '[Arsenal Corrections Apply] ❌ EXCEPTION: ' . $e->getMessage() );
            error_log( '[Arsenal Corrections Apply] Stack trace: ' . $e->getTraceAsString() );
            wp_send_json_error( 'Исключение: ' . $e->getMessage() );
        }
    }
}
