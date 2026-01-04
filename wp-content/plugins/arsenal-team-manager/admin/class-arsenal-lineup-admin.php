<?php
/**
 * Класс для управления админ-интерфейсом составов матчей
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Lineup_Admin {
    
    /**
     * Конструктор
     */
    public function __construct() {
        // Страница already registered in main plugin class, so we don't need to register it again
        add_action( 'admin_post_arsenal_update_lineup', array( $this, 'handle_update_lineup' ) );
        add_action( 'admin_post_arsenal_delete_lineup_player', array( $this, 'handle_delete_lineup_player' ) );
    }
    
    /**
     * Отображение формы редактирования составов
     */
    public function render_lineups_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        $match_id = isset( $_GET['match_id'] ) ? sanitize_text_field( $_GET['match_id'] ) : '';
        if ( ! $match_id ) {
            wp_die( 'ID матча не указан.' );
        }
        
        // Получение матча
        $match = $GLOBALS['wpdb']->get_row( $GLOBALS['wpdb']->prepare(
            "SELECT m.*, 
                    ht.name AS home_team_name, 
                    at.name AS away_team_name
             FROM {$GLOBALS['wpdb']->prefix}arsenal_matches m
             LEFT JOIN {$GLOBALS['wpdb']->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
             LEFT JOIN {$GLOBALS['wpdb']->prefix}arsenal_teams at ON m.away_team_id = at.team_id
             WHERE m.match_id = %s",
            $match_id
        ) );
        
        if ( ! $match ) {
            wp_die( 'Матч не найден.' );
        }
        
        // Получение команд
        $teams = $GLOBALS['wpdb']->get_results( $GLOBALS['wpdb']->prepare(
            "SELECT * FROM {$GLOBALS['wpdb']->prefix}arsenal_teams 
             WHERE team_id = %s OR team_id = %s",
            $match->home_team_id,
            $match->away_team_id
        ) );
        
        // Получение составов
        $lineups = Arsenal_Lineup_Manager::get_match_lineups( $match_id );
        
        // Проверить что lineups не пусто
        if ( empty( $lineups ) ) {
            $lineups = array();
        } else {
            // Убедиться что все данные валидны
            foreach ( $lineups as $key => $lineup ) {
                if ( empty( $lineup->first_name ) || empty( $lineup->last_name ) ) {
                    $lineup->first_name = $lineup->first_name ?? 'Неизвестно';
                    $lineup->last_name = $lineup->last_name ?? '';
                }
            }
        }
        
        // Получение всех игроков для добавления в состав
        $all_players = Arsenal_Lineup_Manager::get_team_players( '' );
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/lineups-form.php';
    }
    
    /**
     * Обработка обновления составов
     */
    public function handle_update_lineup() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['arsenal_lineups_nonce'] ) || 
             ! wp_verify_nonce( $_POST['arsenal_lineups_nonce'], 'arsenal_lineups_form' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $match_id = intval( $_POST['match_id'] ?? 0 );
        if ( ! $match_id ) {
            wp_die( 'ID матча не указан.' );
        }
        
        // Получение матча по числовому ID
        $match = Arsenal_Match_Manager::get_match( $match_id );
        if ( ! $match ) {
            wp_die( 'Матч не найден.' );
        }
        
        // Обновление составов
        if ( isset( $_POST['lineups'] ) && is_array( $_POST['lineups'] ) ) {
            foreach ( $_POST['lineups'] as $lineup_id => $data ) {
                $lineup_id = intval( $lineup_id );
                
                $update_data = array(
                    'shirt_number' => intval( $data['shirt_number'] ?? 0 ),
                    'is_starting' => intval( isset( $data['is_starting'] ) ? 1 : 0 ),
                    'is_captain' => intval( isset( $data['is_captain'] ) ? 1 : 0 ),
                );
                
                Arsenal_Lineup_Manager::update_lineup_player( $lineup_id, $update_data );
            }
        }
        
        // Добавление новых игроков
        if ( isset( $_POST['new_players'] ) && is_array( $_POST['new_players'] ) ) {
            foreach ( $_POST['new_players'] as $player_data ) {
                if ( ! empty( $player_data['player_id'] ) ) {
                    Arsenal_Lineup_Manager::add_player_to_lineup(
                        $match->match_id,
                        sanitize_text_field( $player_data['team_id'] ),
                        sanitize_text_field( $player_data['player_id'] ),
                        intval( $player_data['shirt_number'] ?? 0 ),
                        intval( isset( $player_data['is_starting'] ) ? 1 : 0 ),
                        intval( isset( $player_data['is_captain'] ) ? 1 : 0 )
                    );
                }
            }
        }
        
        wp_redirect( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id . '&success=1' ) );
        exit;
    }
    
    /**
     * Обработка удаления игрока из состава
     */
    public function handle_delete_lineup_player() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_GET['_wpnonce'] ) || 
             ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_lineup_' . intval( $_GET['lineup_id'] ?? 0 ) ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $lineup_id = intval( $_GET['lineup_id'] ?? 0 );
        $match_id = intval( $_GET['match_id'] ?? 0 );
        
        if ( ! $lineup_id || ! $match_id ) {
            wp_die( 'Необходимые параметры не указаны.' );
        }
        
        // Удаление игрока
        Arsenal_Lineup_Manager::remove_player_from_lineup( $lineup_id );
        
        wp_redirect( admin_url( 'admin.php?page=arsenal-match-lineups&match_id=' . base64_encode( '' ) . '&success=1' ) );
        exit;
    }
}

/**
 * Получить составы матча (дублирование метода из Match Manager для удобства)
 */
function Arsenal_Lineup_Manager_get_match_lineups( $match_id ) {
    global $wpdb;
    
    $lineups = $wpdb->get_results( $wpdb->prepare(
        "SELECT ml.*, p.first_name, p.last_name, t.name as team_name
         FROM {$wpdb->prefix}arsenal_match_lineups ml
         LEFT JOIN {$wpdb->prefix}arsenal_players p ON ml.player_id = p.player_id
         LEFT JOIN {$wpdb->prefix}arsenal_teams t ON ml.team_id = t.team_id
         WHERE ml.match_id = %s
         ORDER BY ml.team_id, ml.is_starting DESC, ml.shirt_number",
        $match_id
    ) );
    
    return $lineups;
}
