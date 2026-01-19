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
        
        $name = sanitize_text_field( $data['name'] );
        
        // Функция для обработки JSON полей
        $sanitize_json = function( $value ) {
            if ( '' === $value || empty( $value ) ) {
                return null;
            }
            
            // Разэкранируем кавычки, которые добавил браузер при POST
            $value = stripslashes( $value );
            
            // Проверяем валидность JSON
            $decoded = json_decode( $value, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                error_log( 'Arsenal: Ошибка JSON декодирования - ' . json_last_error_msg() . ' | Значение: ' . substr( $value, 0, 100 ) );
                return null;
            }
            
            // Возвращаем чистый JSON без экранирования
            return json_encode( $decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        };
        
        $insert_data = array(
            'stadium_id' => sanitize_text_field( $data['stadium_id'] ?? '' ),
            'name' => $name,
            'city' => sanitize_text_field( $data['city'] ?? '' ),
            'capacity' => isset( $data['capacity'] ) ? intval( $data['capacity'] ) : null,
            'open_date' => sanitize_text_field( $data['open_date'] ?? '' ),
            'photo_url' => sanitize_text_field( $data['photo_url'] ?? '' ),
            'description' => isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '',
            'history' => $sanitize_json( $data['history'] ?? '' ),
            'contacts' => $sanitize_json( $data['contacts'] ?? '' ),
            'infrastructure' => $sanitize_json( $data['infrastructure'] ?? '' ),
            'tech_features' => $sanitize_json( $data['tech_features'] ?? '' ),
            'sectors' => $sanitize_json( $data['sectors'] ?? '' ),
            'stat_cards' => $sanitize_json( $data['stat_cards'] ?? '' ),
            'to_get' => $sanitize_json( $data['to_get'] ?? '' ),
            'on_date' => $sanitize_json( $data['on_date'] ?? '' ),
        );
        
        $format = array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );
        
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
        
        // Функция для обработки JSON полей
        $sanitize_json = function( $value ) {
            if ( '' === $value || empty( $value ) ) {
                return null;
            }
            
            // Разэкранируем кавычки, которые добавил браузер при POST
            $value = stripslashes( $value );
            
            // Проверяем валидность JSON
            $decoded = json_decode( $value, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                error_log( 'Arsenal: Ошибка JSON декодирования - ' . json_last_error_msg() . ' | Значение: ' . substr( $value, 0, 100 ) );
                return null;
            }
            
            // Возвращаем чистый JSON без экранирования
            return json_encode( $decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        };
        
        $update_data = array();
        $format = array();
        
        if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
            $update_data['name'] = sanitize_text_field( $data['name'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['city'] ) ) {
            $update_data['city'] = sanitize_text_field( $data['city'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['capacity'] ) && ! empty( $data['capacity'] ) ) {
            $update_data['capacity'] = intval( $data['capacity'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['open_date'] ) ) {
            $update_data['open_date'] = sanitize_text_field( $data['open_date'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['photo_url'] ) ) {
            $update_data['photo_url'] = sanitize_text_field( $data['photo_url'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['description'] ) ) {
            $update_data['description'] = wp_kses_post( $data['description'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['history'] ) ) {
            $update_data['history'] = $sanitize_json( $data['history'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['contacts'] ) ) {
            $update_data['contacts'] = $sanitize_json( $data['contacts'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['infrastructure'] ) ) {
            $update_data['infrastructure'] = $sanitize_json( $data['infrastructure'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['tech_features'] ) ) {
            $update_data['tech_features'] = $sanitize_json( $data['tech_features'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['sectors'] ) ) {
            $update_data['sectors'] = $sanitize_json( $data['sectors'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['stat_cards'] ) ) {
            $update_data['stat_cards'] = $sanitize_json( $data['stat_cards'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['to_get'] ) ) {
            $update_data['to_get'] = $sanitize_json( $data['to_get'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['on_date'] ) ) {
            $update_data['on_date'] = $sanitize_json( $data['on_date'] );
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
