<?php
/**
 * Класс: Менеджер стадионов
 *
 * Отвечает за работу с таблицей wp_arsenal_stadiums
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Stadium_Manager {
    
    /**
     * Получить все стадионы
     *
     * @param int $paged Номер страницы
     * @param int $per_page Кол-во на странице
     * @return array Массив стадионов и информация о пагинации
     */
    public static function get_stadiums( $paged = 1, $per_page = 20 ) {
        global $wpdb;
        
        $offset = ( $paged - 1 ) * $per_page;
        
        // Общее количество
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_stadiums" );
        
        // Получить стадионы
        $stadiums = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_stadiums ORDER BY name ASC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        
        return array(
            'stadiums' => $stadiums,
            'total' => $total,
            'total_pages' => ceil( $total / $per_page ),
            'paged' => $paged,
        );
    }
    
    /**
     * Получить стадион по ID
     *
     * @param int $stadium_id ID стадиона
     * @return object|null Данные стадиона или null
     */
    public static function get_stadium( $stadium_id ) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}arsenal_stadiums WHERE id = %d",
                intval( $stadium_id )
            )
        );
    }
    
    /**
     * Создать новый стадион
     *
     * @param array $data Данные стадиона
     * @return int|false ID нового стадиона или false при ошибке
     */
    public static function create_stadium( $data ) {
        global $wpdb;
        
        // Валидация
        if ( empty( $data['name'] ) ) {
            return false;
        }
        
        $insert_data = array(
            'stadium_id' => sanitize_text_field( $data['stadium_id'] ?? '' ),
            'name' => sanitize_text_field( $data['name'] ),
            'city' => sanitize_text_field( $data['city'] ?? '' ),
            'capacity' => isset( $data['capacity'] ) ? intval( $data['capacity'] ) : null,
            'photo_url' => sanitize_text_field( $data['photo_url'] ?? '' ),
        );
        
        $format = array( '%s', '%s', '%s', '%d', '%s' );
        
        $result = $wpdb->insert( "{$wpdb->prefix}arsenal_stadiums", $insert_data, $format );
        
        if ( ! $result ) {
            error_log( 'Arsenal: Ошибка создания стадиона - ' . $wpdb->last_error );
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Обновить стадион
     *
     * @param int $stadium_id ID стадиона
     * @param array $data Новые данные
     * @return bool Успешность операции
     */
    public static function update_stadium( $stadium_id, $data ) {
        global $wpdb;
        
        $update_data = array();
        $format = array();
        
        if ( isset( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['city'] ) ) {
            $update_data['city'] = sanitize_text_field( $data['city'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['capacity'] ) ) {
            $update_data['capacity'] = intval( $data['capacity'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['photo_url'] ) ) {
            $update_data['photo_url'] = sanitize_text_field( $data['photo_url'] );
            $format[] = '%s';
        }
        
        if ( empty( $update_data ) ) {
            return false;
        }
        
        $result = $wpdb->update(
            "{$wpdb->prefix}arsenal_stadiums",
            $update_data,
            array( 'id' => intval( $stadium_id ) ),
            $format,
            array( '%d' )
        );
        
        if ( false === $result ) {
            error_log( 'Arsenal: Ошибка обновления стадиона #' . $stadium_id . ' - ' . $wpdb->last_error );
            return false;
        }
        
        return true;
    }
    
    /**
     * Удалить стадион
     *
     * @param int $stadium_id ID стадиона
     * @return bool Успешность операции
     */
    public static function delete_stadium( $stadium_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            "{$wpdb->prefix}arsenal_stadiums",
            array( 'id' => intval( $stadium_id ) ),
            array( '%d' )
        );
        
        if ( ! $result ) {
            error_log( 'Arsenal: Ошибка удаления стадиона #' . $stadium_id . ' - ' . $wpdb->last_error );
            return false;
        }
        
        return true;
    }
}
