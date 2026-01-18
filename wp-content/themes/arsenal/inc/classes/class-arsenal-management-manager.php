<?php
/**
 * Arsenal Management Manager Class
 * 
 * Управление руководством клуба
 * 
 * @package Arsenal_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Management_Manager {

    /**
     * Получить все записи руководства
     *
     * @param array $args Аргументы запроса
     * @return array Массив записей
     */
    public static function get_management( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'search' => '',
            'position' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => -1,
            'offset' => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        $query = "SELECT * FROM {$wpdb->prefix}arsenal_management WHERE 1=1";
        $params = array();

        if ( ! empty( $args['search'] ) ) {
            $query .= " AND name LIKE %s";
            $params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }

        if ( ! empty( $args['position'] ) ) {
            $query .= " AND position = %s";
            $params[] = $args['position'];
        }

        // Сортировка
        if ( in_array( $args['orderby'], array( 'name', 'position', 'created_at', 'id' ) ) ) {
            $query .= " ORDER BY " . esc_sql( $args['orderby'] );
        } else {
            $query .= " ORDER BY name";
        }

        $query .= strtoupper( $args['order'] ) === 'DESC' ? " DESC" : " ASC";

        // Лимит
        if ( (int) $args['limit'] > 0 ) {
            $query .= " LIMIT %d";
            $params[] = (int) $args['limit'];

            if ( (int) $args['offset'] > 0 ) {
                $query .= " OFFSET %d";
                $params[] = (int) $args['offset'];
            }
        }

        if ( $params ) {
            return $wpdb->get_results( $wpdb->prepare( $query, $params ) );
        } else {
            return $wpdb->get_results( $query );
        }
    }

    /**
     * Получить запись по ID
     *
     * @param int $id ID записи
     * @return object|null Объект или null
     */
    public static function get_management_member( $id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}arsenal_management WHERE id = %d",
            (int) $id
        ) );
    }

    /**
     * Добавить новую запись
     *
     * @param array $data Данные
     * @return int|false ID или false
     */
    public static function add_management( $data ) {
        global $wpdb;

        $defaults = array(
            'name' => '',
            'position' => '',
            'description' => '',
            'photo_url' => '',
        );

        $data = wp_parse_args( $data, $defaults );

        if ( empty( $data['name'] ) ) {
            return false;
        }

        $insert = array(
            'name' => sanitize_text_field( $data['name'] ),
            'position' => sanitize_text_field( $data['position'] ),
            'description' => sanitize_textarea_field( $data['description'] ),
            'photo_url' => esc_url_raw( $data['photo_url'] ),
            'created_at' => current_time( 'mysql' ),
        );

        $format = array( '%s', '%s', '%s', '%s', '%s' );

        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_management',
            $insert,
            $format
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Обновить запись
     *
     * @param int $id ID записи
     * @param array $data Данные
     * @return int|false Количество обновленных строк или false
     */
    public static function update_management( $id, $data ) {
        global $wpdb;

        $defaults = array(
            'name' => '',
            'position' => '',
            'description' => '',
            'photo_url' => '',
        );

        $data = wp_parse_args( $data, $defaults );

        $update = array(
            'name' => sanitize_text_field( $data['name'] ),
            'position' => sanitize_text_field( $data['position'] ),
            'description' => sanitize_textarea_field( $data['description'] ),
            'photo_url' => esc_url_raw( $data['photo_url'] ),
            'updated_at' => current_time( 'mysql' ),
        );

        $format = array( '%s', '%s', '%s', '%s', '%s' );
        $where = array( 'id' => (int) $id );
        $where_format = array( '%d' );

        return $wpdb->update(
            $wpdb->prefix . 'arsenal_management',
            $update,
            $where,
            $format,
            $where_format
        );
    }

    /**
     * Удалить запись
     *
     * @param int $id ID записи
     * @return int|false Количество удаленных строк или false
     */
    public static function delete_management( $id ) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'arsenal_management',
            array( 'id' => (int) $id ),
            array( '%d' )
        );
    }

    /**
     * Получить уникальные должности
     *
     * @return array Массив должностей
     */
    public static function get_positions() {
        global $wpdb;

        return $wpdb->get_col(
            "SELECT DISTINCT position FROM {$wpdb->prefix}arsenal_management ORDER BY position ASC"
        );
    }

    /**
     * Получить количество записей
     *
     * @return int Количество
     */
    public static function count_management() {
        global $wpdb;

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_management"
        );
    }

    /**
     * Получить записи по должности
     *
     * @param string $position Должность
     * @return array Массив записей
     */
    public static function get_by_position( $position ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}arsenal_management WHERE position = %s ORDER BY name ASC",
            $position
        ) );
    }
}
