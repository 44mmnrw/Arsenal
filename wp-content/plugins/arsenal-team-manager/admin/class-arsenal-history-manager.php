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
            json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
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
            'description'      => isset( $data['description'] ) ? self::sanitize_rich_text( $data['description'] ) : '',
            'scale'            => isset( $data['scale'] ) ? self::validate_json_field( $data['scale'] ) : array(),
            'title_second'     => isset( $data['title_second'] ) ? sanitize_text_field( $data['title_second'] ) : 'Рекорды и достижения',
            'records'          => isset( $data['records'] ) ? self::validate_json_field( $data['records'] ) : array(),
            'achievements'     => isset( $data['achievements'] ) ? self::validate_json_field( $data['achievements'] ) : array(),
            'title_third'      => isset( $data['title_third'] ) ? sanitize_text_field( $data['title_third'] ) : 'Домашние стадионы',
            'additional_cards' => isset( $data['additional_cards'] ) ? self::validate_json_field( $data['additional_cards'] ) : array(),
        );
    }
    
    /**
     * Санитизировать текст с HTML форматированием
     * Разрешаем основные теги форматирования
     * 
     * @param string $text Текст с HTML
     * @return string
     */
    private static function sanitize_rich_text( $text ) {
        // Сначала применяем wpautop для преобразования переносов строк в <p> теги
        $text = wpautop( $text );
        
        $allowed_html = array(
            'p'      => array( 'class' => array(), 'style' => array() ),
            'br'     => array(),
            'strong' => array( 'class' => array(), 'style' => array() ),
            'b'      => array( 'class' => array(), 'style' => array() ),
            'em'     => array( 'class' => array(), 'style' => array() ),
            'i'      => array( 'class' => array(), 'style' => array() ),
            'u'      => array( 'class' => array(), 'style' => array() ),
            'a'      => array( 'href' => array(), 'title' => array(), 'class' => array(), 'style' => array() ),
            'ul'     => array( 'class' => array(), 'style' => array() ),
            'ol'     => array( 'class' => array(), 'style' => array() ),
            'li'     => array( 'class' => array(), 'style' => array() ),
            'h1'     => array( 'class' => array(), 'style' => array() ),
            'h2'     => array( 'class' => array(), 'style' => array() ),
            'h3'     => array( 'class' => array(), 'style' => array() ),
            'h4'     => array( 'class' => array(), 'style' => array() ),
            'h5'     => array( 'class' => array(), 'style' => array() ),
            'h6'     => array( 'class' => array(), 'style' => array() ),
            'blockquote' => array( 'class' => array(), 'style' => array() ),
            'code'   => array( 'class' => array(), 'style' => array() ),
            'pre'    => array( 'class' => array(), 'style' => array() ),
            'span'   => array( 'class' => array(), 'style' => array() ),
            'div'    => array( 'class' => array(), 'style' => array() ),
        );
        return wp_kses( $text, $allowed_html );
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
