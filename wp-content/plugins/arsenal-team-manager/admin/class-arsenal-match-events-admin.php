<?php
/**
 * Админ-интерфейс для событий матчей
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Match_Events_Admin {
    
    /**
     * Конструктор
     */
    public function __construct() {
        add_action( 'admin_post_arsenal_delete_event', array( $this, 'handle_delete_event' ) );
    }
    
    /**
     * Вывести форму событий матча
     *
     * @param int $match_id ID матча
     */
    public function render_events_form( $match_id ) {
        // Получение матча
        $match = Arsenal_Match_Manager::get_match( $match_id );
        if ( ! $match ) {
            wp_die( 'Матч не найден.' );
        }
        
        // Получение данных для формы
        $events = Arsenal_Match_Events_Manager::get_match_events( $match->match_id );
        $lineups = Arsenal_Lineup_Manager::get_match_lineups( $match->match_id );
        $event_types = Arsenal_Match_Events_Manager::get_event_types();
        
        // Получение игроков для каждой команды
        $home_players = array();
        $away_players = array();
        
        foreach ( $lineups as $lineup ) {
            $player_name = ! empty( $lineup->full_name ) ? $lineup->full_name : '';
            $shirt_number = ! empty( $lineup->shirt_number ) ? $lineup->shirt_number : '?';
            $display_name = trim( $player_name . ' (№' . $shirt_number . ')' );
            
            if ( $lineup->team_id === $match->home_team_id ) {
                $home_players[ $lineup->player_id ] = $display_name;
            } else {
                $away_players[ $lineup->player_id ] = $display_name;
            }
        }
        
        // Сортировка игроков по фамилии
        asort( $home_players );
        asort( $away_players );
        
        // Включение шаблона
        $template = ARSENAL_MATCH_MANAGER_DIR . 'admin/views/events-form.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }
    
    /**
     * Обработка удаления события
     */
    public function handle_delete_event() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_GET['_wpnonce'] ) || 
             ! wp_verify_nonce( $_GET['_wpnonce'], 'arsenal_delete_event_' . ( $_GET['event_id'] ?? '' ) ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $event_id = intval( $_GET['event_id'] ?? 0 );
        if ( ! $event_id ) {
            wp_die( 'ID события не указан.' );
        }
        
        // Получение события для нахождения матча
        global $wpdb;
        $event = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}arsenal_match_events WHERE id = %d",
            $event_id
        ) );
        
        if ( ! $event ) {
            wp_die( 'Событие не найдено.' );
        }
        
        // Удаление события
        Arsenal_Match_Events_Manager::delete_event( $event_id );
        
        // Редирект обратно на форму матча
        wp_redirect( admin_url( 'admin.php?page=arsenal-match-events&match_id=' . $event->match_id . '&deleted=1' ) );
        exit;
    }
}
