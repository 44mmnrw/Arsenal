<?php
/**
 * Arsenal History Manager
 * 
 * Управление данными истории клуба через wp_options
 * 
 * @package Arsenal_Team_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_History_Manager {
    
    /**
     * Option ключ для истории
     */
    const HISTORY_OPTION_KEY = 'arsenal_history_data';
    
    /**
     * Получить singleton экземпляр
     * 
     * @return Arsenal_History_Manager
     */
    public static function get_instance() {
        static $instance = null;
        
        if ( null === $instance ) {
            $instance = new self();
        }
        
        return $instance;
    }
    
    /**
     * Получить историю с БД
     * 
     * @return array
     */
    public static function get_history() {
        // Получаем из wp_options
        $data = get_option( self::HISTORY_OPTION_KEY );
        
        if ( empty( $data ) ) {
            return self::get_default_history();
        }
        
        // Если это строка JSON, парсим
        if ( is_string( $data ) ) {
            $data = json_decode( $data, true );
        }
        
        return is_array( $data ) ? $data : self::get_default_history();
    }
    
    /**
     * Сохранить историю в БД
     * 
     * @param array $data История
     * @return bool
     */
    public static function save_history( $data ) {
        // Валидация данных
        $data = self::validate_history_data( $data );
        
        // Сохраняем как JSON в wp_options
        return update_option( 
            self::HISTORY_OPTION_KEY, 
            json_encode( $data, JSON_UNESCAPED_UNICODE )
        );
    }
    
    /**
     * История по умолчанию
     * 
     * @return array
     */
    private static function get_default_history() {
        return array(
            'title'            => 'История клуба',
            'description'      => '',
            'scale'            => array(),
            'title_second'     => 'Рекорды и достижения',
            'records'          => array(),
            'achievements'     => array(),
            'title_third'      => 'Домашние стадионы',
            'additional_cards' => array(),
        );
    }
    
    /**
     * Валидировать данные истории
     * 
     * @param array $data История
     * @return array
     */
    private static function validate_history_data( $data ) {
        return array(
            'title'            => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : 'История клуба',
            'description'      => isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '',
            'scale'            => isset( $data['scale'] ) ? self::validate_json_field( $data['scale'] ) : array(),
            'title_second'     => isset( $data['title_second'] ) ? sanitize_text_field( $data['title_second'] ) : 'Рекорды и достижения',
            'records'          => isset( $data['records'] ) ? self::validate_json_field( $data['records'] ) : array(),
            'achievements'     => isset( $data['achievements'] ) ? self::validate_json_field( $data['achievements'] ) : array(),
            'title_third'      => isset( $data['title_third'] ) ? sanitize_text_field( $data['title_third'] ) : 'Домашние стадионы',
            'additional_cards' => isset( $data['additional_cards'] ) ? self::validate_json_field( $data['additional_cards'] ) : array(),
        );
    }
    
    /**
     * Валидировать JSON поле
     * 
     * @param mixed $field Значение поля
     * @return array
     */
    private static function validate_json_field( $field ) {
        if ( is_array( $field ) ) {
            return $field;
        }
        
        if ( is_string( $field ) ) {
            $decoded = json_decode( $field, true );
            return is_array( $decoded ) ? $decoded : array();
        }
        
        return array();
    }
}
