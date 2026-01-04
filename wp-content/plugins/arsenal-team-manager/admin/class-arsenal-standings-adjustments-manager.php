<?php
/**
 * Класс: Менеджер корректировок турнирной таблицы
 *
 * Отвечает за работу с таблицей wp_arsenal_standings_adjustments
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Standings_Adjustments_Manager {
    
    /**
     * Получить все корректировки
     *
     * @param int $paged Номер страницы
     * @param int $per_page Кол-во на странице
     * @return array Массив корректировок и информация о пагинации
     */
    public static function get_adjustments( $paged = 1, $per_page = 20 ) {
        global $wpdb;
        
        $offset = ( $paged - 1 ) * $per_page;
        
        // Общее количество
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_standings_adjustments" );
        
        // Получить корректировки с JOIN к связанным таблицам
        $adjustments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*, 
                        t.name AS team_name,
                        s.season_name,
                        u.display_name AS applied_by_name
                 FROM {$wpdb->prefix}arsenal_standings_adjustments a
                 LEFT JOIN {$wpdb->prefix}arsenal_teams t ON a.team_id = t.team_id
                 LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON a.season_id = s.season_id
                 LEFT JOIN {$wpdb->prefix}users u ON a.applied_by = u.ID
                 ORDER BY a.applied_date DESC
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        return array(
            'adjustments' => $adjustments,
            'total' => $total,
            'total_pages' => ceil( $total / $per_page ),
            'paged' => $paged,
        );
    }
    
    /**
     * Получить корректировку по ID
     *
     * @param int $adjustment_id ID корректировки
     * @return object|null Объект корректировки или null
     */
    public static function get_adjustment( $adjustment_id ) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_standings_adjustments WHERE id = %d",
                $adjustment_id
            )
        );
    }
    
    /**
     * Создать новую корректировку
     *
     * @param array $data Данные корректировки
     * @return int|false ID новой корректировки или false при ошибке
     */
    public static function create_adjustment( $data ) {
        global $wpdb;
        
        $insert_data = array(
            'tournament_id' => sanitize_text_field( $data['tournament_id'] ),
            'season_id' => sanitize_text_field( $data['season_id'] ),
            'team_id' => sanitize_text_field( $data['team_id'] ),
            'adjustment_points' => intval( $data['adjustment_points'] ),
            'comment' => wp_kses_post( $data['comment'] ),
            'applied_date' => sanitize_text_field( $data['applied_date'] ),
            'applied_by' => get_current_user_id(),
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_standings_adjustments',
            $insert_data,
            array( '%s', '%s', '%s', '%d', '%s', '%s', '%d' )
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Обновить корректировку
     *
     * @param int $adjustment_id ID корректировки
     * @param array $data Новые данные
     * @return bool Успешность операции
     */
    public static function update_adjustment( $adjustment_id, $data ) {
        global $wpdb;
        
        $update_data = array(
            'tournament_id' => sanitize_text_field( $data['tournament_id'] ),
            'season_id' => sanitize_text_field( $data['season_id'] ),
            'team_id' => sanitize_text_field( $data['team_id'] ),
            'adjustment_points' => intval( $data['adjustment_points'] ),
            'comment' => wp_kses_post( $data['comment'] ),
            'applied_date' => sanitize_text_field( $data['applied_date'] ),
        );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'arsenal_standings_adjustments',
            $update_data,
            array( 'id' => $adjustment_id ),
            array( '%s', '%s', '%s', '%d', '%s', '%s' ),
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Удалить корректировку
     *
     * @param int $adjustment_id ID корректировки
     * @return bool Успешность операции
     */
    public static function delete_adjustment( $adjustment_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'arsenal_standings_adjustments',
            array( 'id' => $adjustment_id ),
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Получить список команд
     *
     * @return array Массив команд [team_id => name]
     */
    public static function get_teams() {
        global $wpdb;
        
        $teams = $wpdb->get_results(
            "SELECT team_id, name FROM {$wpdb->prefix}arsenal_teams ORDER BY name ASC"
        );
        
        $result = array();
        foreach ( $teams as $team ) {
            $result[ $team->team_id ] = $team->name;
        }
        
        return $result;
    }
}
