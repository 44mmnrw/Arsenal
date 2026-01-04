<?php
/**
 * Класс: Менеджер сезонов
 *
 * Отвечает за работу с таблицей wp_arsenal_seasons
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Season_Manager {
    
    /**
     * Получить все сезоны
     *
     * @param int $paged Номер страницы
     * @param int $per_page Кол-во на странице
     * @return array Массив сезонов и информация о пагинации
     */
    public static function get_seasons( $paged = 1, $per_page = 20 ) {
        global $wpdb;
        
        $offset = ( $paged - 1 ) * $per_page;
        
        // Общее количество
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_seasons" );
        
        // Получить сезоны
        $seasons = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_seasons
                 ORDER BY start_date DESC
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        return array(
            'seasons' => $seasons,
            'total' => $total,
            'total_pages' => ceil( $total / $per_page ),
            'paged' => $paged,
        );
    }
    
    /**
     * Получить сезон по ID
     *
     * @param int $season_id ID сезона
     * @return object|null Объект сезона или null
     */
    public static function get_season( $season_id ) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_seasons WHERE id = %d",
                $season_id
            )
        );
    }
    
    /**
     * Создать новый сезон
     *
     * @param array $data Данные сезона
     * @return int|false ID нового сезона или false при ошибке
     */
    public static function create_season( $data ) {
        global $wpdb;
        
        // Генерируем HEX ID для season_id
        $season_id = strtoupper( substr( md5( uniqid( mt_rand(), true ) ), 0, 8 ) );
        
        $insert_data = array(
            'season_id' => $season_id,
            'season_name' => sanitize_text_field( $data['season_name'] ),
            'start_date' => sanitize_text_field( $data['start_date'] ),
            'end_date' => sanitize_text_field( $data['end_date'] ),
            'is_active' => isset( $data['is_active'] ) ? intval( $data['is_active'] ) : 1,
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_seasons',
            $insert_data,
            array( '%s', '%s', '%s', '%s', '%d' )
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Обновить сезон
     *
     * @param int $season_id ID сезона
     * @param array $data Новые данные
     * @return bool Успешность операции
     */
    public static function update_season( $season_id, $data ) {
        global $wpdb;
        
        $update_data = array(
            'season_name' => sanitize_text_field( $data['season_name'] ),
            'start_date' => sanitize_text_field( $data['start_date'] ),
            'end_date' => sanitize_text_field( $data['end_date'] ),
            'is_active' => isset( $data['is_active'] ) ? intval( $data['is_active'] ) : 1,
        );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'arsenal_seasons',
            $update_data,
            array( 'id' => $season_id ),
            array( '%s', '%s', '%s', '%d' ),
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Удалить сезон
     *
     * @param int $season_id ID сезона
     * @return bool Успешность операции
     */
    public static function delete_season( $season_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'arsenal_seasons',
            array( 'id' => $season_id ),
            array( '%d' )
        );
        
        return $result !== false;
    }
}
