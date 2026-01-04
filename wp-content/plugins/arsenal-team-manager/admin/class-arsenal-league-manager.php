<?php
/**
 * Класс: Менеджер лиг
 *
 * Отвечает за работу с таблицей wp_arsenal_leagues
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_League_Manager {
    
    /**
     * Получить все лиги
     *
     * @param int $paged Номер страницы
     * @param int $per_page Кол-во на странице
     * @return array Массив лиг и информация о пагинации
     */
    public static function get_leagues( $paged = 1, $per_page = 20 ) {
        global $wpdb;
        
        $offset = ( $paged - 1 ) * $per_page;
        
        // Общее количество
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_leagues" );
        
        // Получить лиги
        $leagues = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_leagues
                 ORDER BY league_name ASC
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        return array(
            'leagues' => $leagues,
            'total' => $total,
            'total_pages' => ceil( $total / $per_page ),
            'paged' => $paged,
        );
    }
    
    /**
     * Получить лигу по ID
     *
     * @param int $league_id ID лиги
     * @return object|null Объект лиги или null
     */
    public static function get_league( $league_id ) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_leagues WHERE id = %d",
                $league_id
            )
        );
    }
    
    /**
     * Создать новую лигу
     *
     * @param array $data Данные лиги
     * @return int|false ID новой лиги или false при ошибке
     */
    public static function create_league( $data ) {
        global $wpdb;
        
        // Генерируем HEX ID для league_id
        $league_id = strtoupper( substr( md5( uniqid( mt_rand(), true ) ), 0, 8 ) );
        
        $insert_data = array(
            'league_id' => $league_id,
            'league_name' => sanitize_text_field( $data['league_name'] ),
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_leagues',
            $insert_data,
            array( '%s', '%s' )
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Обновить лигу
     *
     * @param int $league_id ID лиги
     * @param array $data Новые данные
     * @return bool Успешность операции
     */
    public static function update_league( $league_id, $data ) {
        global $wpdb;
        
        $update_data = array(
            'league_name' => sanitize_text_field( $data['league_name'] ),
        );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'arsenal_leagues',
            $update_data,
            array( 'id' => $league_id ),
            array( '%s' ),
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Удалить лигу
     *
     * @param int $league_id ID лиги
     * @return bool Успешность операции
     */
    public static function delete_league( $league_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'arsenal_leagues',
            array( 'id' => $league_id ),
            array( '%d' )
        );
        
        return $result !== false;
    }
}
