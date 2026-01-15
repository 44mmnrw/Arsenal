<?php
/**
 * Класс: Менеджер турниров
 *
 * Отвечает за работу с таблицей wp_arsenal_tournaments
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Tournament_Manager {
    
    /**
     * Получить все турниры
     *
     * @param int $paged Номер страницы
     * @param int $per_page Кол-во на странице
     * @return array Массив турниров и информация о пагинации
     */
    public static function get_tournaments( $paged = 1, $per_page = 20 ) {
        global $wpdb;
        
        $offset = ( $paged - 1 ) * $per_page;
        
        // Общее количество
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_tournaments" );
        
        // Получить турниры
        $tournaments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_tournaments
                 ORDER BY name ASC
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        return array(
            'tournaments' => $tournaments,
            'total' => $total,
            'total_pages' => ceil( $total / $per_page ),
            'paged' => $paged,
        );
    }
    
    /**
     * Получить турнир по ID
     *
     * @param string $tournament_id ID турнира
     * @return object|null Данные турнира или null
     */
    public static function get_tournament( $tournament_id ) {
        global $wpdb;
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}arsenal_tournaments WHERE tournament_id = %s",
            $tournament_id
        ) );
    }
    
    /**
     * Создать турнир
     *
     * @param array $data Данные турнира
     * @return string|false ID турнира или false
     */
    public static function create_tournament( $data ) {
        global $wpdb;
        
        // Генерируем tournament_id на основе названия турнира
        $tournament_name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
        if ( empty( $tournament_name ) ) {
            return false;
        }
        
        $tournament_id_source = strtoupper( str_replace( ' ', '', substr( $tournament_name, 0, 15 ) ) );
        $tournament_id = strtoupper( substr( md5( $tournament_id_source ), 0, 8 ) );
        
        $description = isset( $data['description'] ) && ! empty( $data['description'] ) 
            ? wp_kses_post( $data['description'] ) 
            : '';
        
        $insert_data = array(
            'tournament_id' => $tournament_id,
            'name' => $tournament_name,
            'description' => $description,
        );
        
        $format = array( '%s', '%s', '%s' );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_tournaments',
            $insert_data,
            $format
        );
        
        if ( $result === false ) {
            error_log( 'Tournament insert error: ' . $wpdb->last_error );
            return false;
        }
        
        return $tournament_id;
    }
    
    /**
     * Обновить турнир
     *
     * @param string $tournament_id ID турнира
     * @param array $data Новые данные
     * @return bool Успешность операции
     */
    public static function update_tournament( $tournament_id, $data ) {
        global $wpdb;
        
        $description = isset( $data['description'] ) && ! empty( $data['description'] ) 
            ? wp_kses_post( $data['description'] ) 
            : '';
        
        $update_data = array(
            'name' => sanitize_text_field( $data['name'] ),
            'description' => $description,
        );
        
        $result = $wpdb->update(
            $wpdb->prefix . 'arsenal_tournaments',
            $update_data,
            array( 'tournament_id' => $tournament_id ),
            array( '%s', '%s' ),
            array( '%s' )
        );
        
        if ( $result === false ) {
            error_log( 'Tournament update error: ' . $wpdb->last_error );
            return false;
        }
        
        return true;
    }
    
    /**
     * Удалить турнир
     *
     * @param string $tournament_id ID турнира
     * @return bool Успешность операции
     */
    public static function delete_tournament( $tournament_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'arsenal_tournaments',
            array( 'tournament_id' => $tournament_id ),
            array( '%s' )
        );
        
        return $result !== false;
    }
}
