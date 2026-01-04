<?php
/**
 * Класс управления составами матчей
 *
 * Управление составами команд на матч:
 * - Просмотр составов
 * - Добавление/удаление игроков
 * - Управление капитаном и стартовым составом
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Lineup_Manager {
    
    /**
     * Конструктор
     */
    public function __construct() {
        add_action( 'admin_post_arsenal_save_lineup', array( $this, 'handle_save_lineup' ) );
    }
    
    /**
     * Получить всех игроков команды
     *
     * @param string $team_id ID команды
     * @return array Массив игроков
     */
    public static function get_team_players( $team_id ) {
        global $wpdb;
        
        $players = $wpdb->get_results( 
            "SELECT * FROM {$wpdb->prefix}arsenal_players
             ORDER BY first_name, last_name"
        );
        
        return $players;
    }
    
    /**
     * Добавить игрока в состав матча
     *
     * @param string $match_id ID матча
     * @param string $team_id ID команды
     * @param string $player_id ID игрока
     * @param int    $shirt_number Номер на рубашке
     * @param bool   $is_starting Является ли стартовым
     * @param bool   $is_captain Является ли капитаном
     * @return bool Успешность операции
     */
    public static function add_player_to_lineup( $match_id, $team_id, $player_id, $shirt_number, $is_starting = true, $is_captain = false ) {
        global $wpdb;
        
        // Проверим, нет ли уже этого игрока в составе
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_lineups
             WHERE match_id = %s AND team_id = %s AND player_id = %s",
            $match_id, $team_id, $player_id
        ) );
        
        if ( $exists ) {
            return false;
        }
        
        $result = $wpdb->insert(
            "{$wpdb->prefix}arsenal_match_lineups",
            array(
                'match_id' => $match_id,
                'team_id' => $team_id,
                'player_id' => $player_id,
                'shirt_number' => intval( $shirt_number ),
                'is_starting' => intval( $is_starting ),
                'is_captain' => intval( $is_captain ),
            ),
            array( '%s', '%s', '%s', '%d', '%d', '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Удалить игрока из состава матча
     *
     * @param int $lineup_id ID записи в составе
     * @return bool Успешность операции
     */
    public static function remove_player_from_lineup( $lineup_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            "{$wpdb->prefix}arsenal_match_lineups",
            array( 'id' => intval( $lineup_id ) ),
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Обновить информацию об игроке в составе
     *
     * @param int   $lineup_id ID записи в составе
     * @param array $data Данные для обновления
     * @return bool Успешность операции
     */
    public static function update_lineup_player( $lineup_id, $data ) {
        global $wpdb;
        
        $update_data = array();
        $format = array();
        
        if ( isset( $data['shirt_number'] ) ) {
            $update_data['shirt_number'] = intval( $data['shirt_number'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['is_starting'] ) ) {
            $update_data['is_starting'] = intval( $data['is_starting'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['is_captain'] ) ) {
            $update_data['is_captain'] = intval( $data['is_captain'] );
            $format[] = '%d';
        }
        
        if ( empty( $update_data ) ) {
            return false;
        }
        
        $result = $wpdb->update(
            "{$wpdb->prefix}arsenal_match_lineups",
            $update_data,
            array( 'id' => intval( $lineup_id ) ),
            $format,
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Получить составы матча
     *
     * @param string $match_id ID матча
     * @return array Массив составов
     */
    public static function get_match_lineups( $match_id ) {
        global $wpdb;
        
        $lineups = $wpdb->get_results( $wpdb->prepare(
            "SELECT ml.*, 
             CONCAT_WS(' ', p.first_name, p.last_name) as full_name,
             p.first_name, p.last_name, t.name as team_name
             FROM {$wpdb->prefix}arsenal_match_lineups ml
             LEFT JOIN {$wpdb->prefix}arsenal_players p ON ml.player_id = p.player_id
             LEFT JOIN {$wpdb->prefix}arsenal_teams t ON ml.team_id = t.team_id
             WHERE ml.match_id = %s
             ORDER BY ml.team_id, ml.is_starting DESC, ml.shirt_number",
            $match_id
        ) );
        
        return $lineups;
    }
}
