<?php
/**
 * Arsenal Player Stats Corrections Manager
 * 
 * Управление корректировками статистики игроков
 * Включает функции для добавления, обновления и применения корректировок
 *
 * @package Arsenal_Team_Manager
 * @since 1.0.0
 */

// Запрет прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Класс для управления корректировками статистики игроков
 */
class Arsenal_Player_Stats_Corrections {
    
    private static $instance = null;
    private $table_name = '';
    private $wpdb = null;
    
    /**
     * Singleton
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор
     */
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'arsenal_player_stats_corrections';
    }
    
    /**
     * Создание таблицы корректировок
     */
    public function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            correction_id VARCHAR(8) NOT NULL UNIQUE COMMENT 'Уникальный ID корректировки (8 hex символов)',
            player_id VARCHAR(16) NOT NULL COMMENT 'ID игрока',
            tournament_id VARCHAR(8) NOT NULL COMMENT 'ID турнира',
            season_id VARCHAR(20) NULL COMMENT 'ID сезона (опционально)',
            
            minutes_played_delta INT DEFAULT 0 COMMENT 'Корректировка минут за сезон',
            matches_played_delta INT DEFAULT 0 COMMENT 'Корректировка матчей сыграно',
            goals_delta INT DEFAULT 0 COMMENT 'Корректировка голов забито',
            goals_conceded_delta INT DEFAULT 0 COMMENT 'Корректировка голов пропущено',
            assists_delta INT DEFAULT 0 COMMENT 'Корректировка ассистов',
            yellow_cards_delta INT DEFAULT 0 COMMENT 'Корректировка жёлтых карточек',
            red_cards_delta INT DEFAULT 0 COMMENT 'Корректировка красных карточек',
            
            correction_reason LONGTEXT COMMENT 'Причина корректировки',
            is_applied TINYINT(1) DEFAULT 0 COMMENT 'Применена ли корректировка',
            applied_at DATETIME NULL COMMENT 'Дата применения',
            applied_by INT UNSIGNED NULL COMMENT 'ID пользователя, применившего корректировку',
            
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            KEY idx_player_id (player_id),
            KEY idx_tournament_id (tournament_id),
            KEY idx_season_id (season_id),
            KEY idx_is_applied (is_applied),
            KEY idx_created_at (created_at),
            KEY idx_player_tournament (player_id, tournament_id)
        ) $charset_collate COMMENT='Таблица для хранения корректировок статистики игроков';";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        
        return true;
    }
    
    /**
     * Добавить новую корректировку
     *
     * @param string $player_id ID игрока
     * @param string $tournament_id ID турнира
     * @param array $deltas Массив с изменениями статистики
     * @param string $reason Причина корректировки
     * @param string $season_id ID сезона (опционально)
     *
     * @return string|false correction_id (hex строка) или false при ошибке
     */
    public function add_correction( $player_id, $tournament_id, $deltas, $reason = '', $season_id = null ) {
        
        if ( empty( $player_id ) || empty( $tournament_id ) ) {
            return false;
        }
        
        $correction_id = bin2hex( random_bytes( 4 ) );  // 8 символов hex (4 байта)
        
        $data = array(
            'correction_id'         => $correction_id,
            'player_id'             => sanitize_text_field( $player_id ),
            'tournament_id'         => sanitize_text_field( $tournament_id ),
            'season_id'             => $season_id ? sanitize_text_field( $season_id ) : null,
            'minutes_played_delta'  => isset( $deltas['minutes_played'] ) ? intval( $deltas['minutes_played'] ) : 0,
            'matches_played_delta'  => isset( $deltas['matches_played'] ) ? intval( $deltas['matches_played'] ) : 0,
            'goals_delta'           => isset( $deltas['goals'] ) ? intval( $deltas['goals'] ) : 0,
            'goals_conceded_delta'  => isset( $deltas['goals_conceded'] ) ? intval( $deltas['goals_conceded'] ) : 0,
            'assists_delta'         => isset( $deltas['assists'] ) ? intval( $deltas['assists'] ) : 0,
            'yellow_cards_delta'    => isset( $deltas['yellow_cards'] ) ? intval( $deltas['yellow_cards'] ) : 0,
            'red_cards_delta'       => isset( $deltas['red_cards'] ) ? intval( $deltas['red_cards'] ) : 0,
            'correction_reason'     => sanitize_textarea_field( $reason ),
            'created_at'            => current_time( 'mysql' ),
        );
        
        $format = array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s' );
        
        if ( $this->wpdb->insert( $this->table_name, $data, $format ) ) {
            // Возвращаем сгенерированный correction_id (hex строку), а не числовой insert_id
            return $correction_id;
        }
        
        return false;
    }
    
    /**
     * Получить корректировку по ID
     *
     * @param int $id ID корректировки
     *
     * @return object|null Объект с данными корректировки или null
     */
    public function get_correction( $id ) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
    }
    
    /**
     * Получить все корректировки для игрока и турнира
     *
     * @param string $player_id ID игрока
     * @param string $tournament_id ID турнира
     * @param bool $applied_only Только примененные корректировки
     *
     * @return array Массив корректировок
     */
    public function get_corrections( $player_id, $tournament_id, $applied_only = false ) {
        $query = "SELECT * FROM {$this->table_name} WHERE player_id = %s AND tournament_id = %s";
        $params = array( $player_id, $tournament_id );
        
        if ( $applied_only ) {
            $query .= " AND is_applied = 1";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        return $this->wpdb->get_results(
            $this->wpdb->prepare( $query, $params )
        );
    }
    
    /**
     * Обновить корректировку
     *
     * @param int $id ID корректировки
     * @param array $data Данные для обновления
     *
     * @return bool
     */
    public function update_correction( $id, $data ) {
        $update_data = array();
        $update_format = array();
        
        if ( isset( $data['minutes_played_delta'] ) ) {
            $update_data['minutes_played_delta'] = intval( $data['minutes_played_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['matches_played_delta'] ) ) {
            $update_data['matches_played_delta'] = intval( $data['matches_played_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['goals_delta'] ) ) {
            $update_data['goals_delta'] = intval( $data['goals_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['goals_conceded_delta'] ) ) {
            $update_data['goals_conceded_delta'] = intval( $data['goals_conceded_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['assists_delta'] ) ) {
            $update_data['assists_delta'] = intval( $data['assists_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['yellow_cards_delta'] ) ) {
            $update_data['yellow_cards_delta'] = intval( $data['yellow_cards_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['red_cards_delta'] ) ) {
            $update_data['red_cards_delta'] = intval( $data['red_cards_delta'] );
            $update_format[] = '%d';
        }
        if ( isset( $data['correction_reason'] ) ) {
            $update_data['correction_reason'] = sanitize_textarea_field( $data['correction_reason'] );
            $update_format[] = '%s';
        }
        if ( isset( $data['is_applied'] ) ) {
            $update_data['is_applied'] = intval( $data['is_applied'] );
            $update_format[] = '%d';
        }
        
        if ( empty( $update_data ) ) {
            return false;
        }
        
        $update_format[] = '%d';
        
        return $this->wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $id ),
            $update_format,
            array( '%d' )
        ) !== false;
    }
    
    /**
     * Удалить корректировку
     *
     * @param int $id ID корректировки
     *
     * @return bool
     */
    public function delete_correction( $id ) {
        return $this->wpdb->delete(
            $this->table_name,
            array( 'id' => $id ),
            array( '%d' )
        ) !== false;
    }
    
    /**
     * Удалить корректировку по correction_id (hex строка)
     *
     * @param string $correction_id Уникальный ID корректировки (hex строка 8 символов)
     *
     * @return bool
     */
    public function delete_correction_by_hex_id( $correction_id ) {
        return $this->wpdb->delete(
            $this->table_name,
            array( 'correction_id' => sanitize_text_field( $correction_id ) ),
            array( '%s' )
        ) !== false;
    }
    
    /**
     * Применить корректировку к статистике
     * 
     * TODO: Реализовать применение корректировок к таблице player_stats
     * 
     * @param string $correction_id Уникальный ID корректировки (hex строка)
     *
     * @return bool
     */
    public function apply_correction( $correction_id ) {
        // Ищем по correction_id (hex строка), а не по id (число)
        $correction = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE correction_id = %s",
                sanitize_text_field( $correction_id )
            )
        );
        
        if ( ! $correction ) {
            return false;
        }
        
        // TODO: Реализовать логику применения корректировок
        // Здесь нужно будет обновить статистику в соответствующей таблице
        
        return $this->update_correction( $correction->id, array(
            'is_applied' => 1,
            'applied_at' => current_time( 'mysql' ),
            'applied_by' => get_current_user_id(),
        ) );
    }
    
    /**
     * Получить неприменённые корректировки
     *
     * @param string $player_id ID игрока (опционально)
     *
     * @return array Массив неприменённых корректировок
     */
    public function get_unapplied_corrections( $player_id = null ) {
        $query = "SELECT * FROM {$this->table_name} WHERE is_applied = 0";
        $params = array();
        
        if ( $player_id ) {
            $query .= " AND player_id = %s";
            $params[] = $player_id;
        }
        
        $query .= " ORDER BY created_at DESC";
        
        if ( empty( $params ) ) {
            return $this->wpdb->get_results( $query );
        } else {
            return $this->wpdb->get_results(
                $this->wpdb->prepare( $query, $params )
            );
        }
    }
    
    /**
     * Получить все корректировки для админ-таблицы
     *
     * @param int $limit Количество записей
     * @param int $offset Смещение
     * @param array $filters Фильтры (player_id, tournament_id, is_applied)
     *
     * @return array Массив корректировок
     */
    public function get_corrections_for_admin( $limit = 50, $offset = 0, $filters = array() ) {
        $query = "SELECT * FROM {$this->table_name} WHERE 1=1";
        $params = array();
        
        if ( ! empty( $filters['player_id'] ) ) {
            $query .= " AND player_id = %s";
            $params[] = $filters['player_id'];
        }
        
        if ( ! empty( $filters['tournament_id'] ) ) {
            $query .= " AND tournament_id = %s";
            $params[] = $filters['tournament_id'];
        }
        
        if ( isset( $filters['is_applied'] ) && $filters['is_applied'] !== '' ) {
            $query .= " AND is_applied = %d";
            $params[] = intval( $filters['is_applied'] );
        }
        
        $query .= " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->wpdb->get_results(
            $this->wpdb->prepare( $query, $params )
        );
    }
    
    /**
     * Получить общее количество корректировок
     *
     * @param array $filters Фильтры
     *
     * @return int
     */
    public function get_corrections_count( $filters = array() ) {
        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1";
        $params = array();
        
        if ( ! empty( $filters['player_id'] ) ) {
            $query .= " AND player_id = %s";
            $params[] = $filters['player_id'];
        }
        
        if ( ! empty( $filters['tournament_id'] ) ) {
            $query .= " AND tournament_id = %s";
            $params[] = $filters['tournament_id'];
        }
        
        if ( isset( $filters['is_applied'] ) && $filters['is_applied'] !== '' ) {
            $query .= " AND is_applied = %d";
            $params[] = intval( $filters['is_applied'] );
        }
        
        return intval( $this->wpdb->get_var(
            empty( $params ) ? $query : $this->wpdb->prepare( $query, $params )
        ) );
    }
}
