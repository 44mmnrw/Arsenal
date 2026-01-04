<?php
/**
 * Класс управления событиями матчей
 *
 * Управление событиями матча (голы, карточки, замены):
 * - Просмотр событий
 * - Добавление/удаление событий
 * - Редактирование событий
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Match_Events_Manager {
    
    /**
     * Конструктор
     */
    public function __construct() {
        add_action( 'admin_post_arsenal_update_events', array( $this, 'handle_update_events' ) );
    }
    
    /**
     * Получить типы событий
     *
     * @return array Массив типов событий
     */
    public static function get_event_types() {
        global $wpdb;
        
        $types = $wpdb->get_results( 
            "SELECT * FROM {$wpdb->prefix}arsenal_event_types ORDER BY event_name"
        );
        
        $result = array();
        foreach ( $types as $type ) {
            // Используем русское название, если существует, иначе английское
            $name = ! empty( $type->event_name_ru ) ? $type->event_name_ru : $type->event_name;
            $result[ $type->event_type_id ] = $name;
        }
        
        return $result;
    }
    
    /**
     * Добавить событие матча
     *
     * @param string $match_id ID матча
     * @param string $player_id ID игрока
     * @param string $event_type_id ID типа события
     * @param int    $minute Минута события
     * @return bool Успешность операции
     */
    public static function add_event( $match_id, $player_id, $event_type_id, $minute ) {
        global $wpdb;
        
        $result = $wpdb->insert(
            "{$wpdb->prefix}arsenal_match_events",
            array(
                'match_id' => $match_id,
                'player_id' => $player_id,
                'event_type' => $event_type_id,
                'minute' => intval( $minute ),
            ),
            array( '%s', '%s', '%s', '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Удалить событие матча
     *
     * @param int $event_id ID события
     * @return bool Успешность операции
     */
    public static function delete_event( $event_id ) {
        global $wpdb;
        
        $result = $wpdb->delete(
            "{$wpdb->prefix}arsenal_match_events",
            array( 'id' => intval( $event_id ) ),
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Обновить событие матча
     *
     * @param int   $event_id ID события
     * @param array $data Данные для обновления
     * @return bool Успешность операции
     */
    public static function update_event( $event_id, $data ) {
        global $wpdb;
        
        $update_data = array();
        $format = array();
        
        if ( isset( $data['minute'] ) ) {
            $update_data['minute'] = intval( $data['minute'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['event_type'] ) ) {
            $update_data['event_type'] = sanitize_text_field( $data['event_type'] );
            $format[] = '%s';
        }
        
        if ( empty( $update_data ) ) {
            return false;
        }
        
        $result = $wpdb->update(
            "{$wpdb->prefix}arsenal_match_events",
            $update_data,
            array( 'id' => intval( $event_id ) ),
            $format,
            array( '%d' )
        );
        
        return $result !== false;
    }
    
    /**
     * Получить события матча
     *
     * @param string $match_id ID матча
     * @return array Массив событий матча
     */
    public static function get_match_events( $match_id ) {
        global $wpdb;
        
        $events = $wpdb->get_results( $wpdb->prepare(
            "SELECT me.*, p.full_name, 
                    COALESCE(et.event_name_ru, et.event_name) as event_name
             FROM {$wpdb->prefix}arsenal_match_events me
             LEFT JOIN {$wpdb->prefix}arsenal_players p ON me.player_id = p.player_id
             LEFT JOIN {$wpdb->prefix}arsenal_event_types et ON me.event_type = et.event_type_id
             WHERE me.match_id = %s
             ORDER BY me.minute ASC",
            $match_id
        ) );
        
        return $events;
    }
    
    /**
     * Обработка обновления событий
     */
    public function handle_update_events() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['arsenal_events_nonce'] ) || 
             ! wp_verify_nonce( $_POST['arsenal_events_nonce'], 'arsenal_events_form' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $match_id = intval( $_POST['match_id'] ?? 0 );
        if ( ! $match_id ) {
            wp_die( 'ID матча не указан.' );
        }
        
        // Получение матча
        $match = Arsenal_Match_Manager::get_match( $match_id );
        if ( ! $match ) {
            wp_die( 'Матч не найден.' );
        }
        
        // Обновление событий
        if ( isset( $_POST['events'] ) && is_array( $_POST['events'] ) ) {
            foreach ( $_POST['events'] as $event_id => $data ) {
                $event_id = intval( $event_id );
                
                $update_data = array(
                    'minute' => intval( $data['minute'] ?? 0 ),
                    'event_type' => sanitize_text_field( $data['event_type'] ?? '' ),
                );
                
                self::update_event( $event_id, $update_data );
            }
        }
        
        // Добавление новых событий
        if ( isset( $_POST['new_events'] ) && is_array( $_POST['new_events'] ) ) {
            foreach ( $_POST['new_events'] as $event_data ) {
                if ( ! empty( $event_data['player_id'] ) && ! empty( $event_data['event_type'] ) ) {
                    self::add_event(
                        $match->match_id,
                        sanitize_text_field( $event_data['player_id'] ),
                        sanitize_text_field( $event_data['event_type'] ),
                        intval( $event_data['minute'] ?? 0 )
                    );
                }
            }
        }
        
        wp_redirect( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id . '&success=1' ) );
        exit;
    }
}
