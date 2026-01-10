<?php
/**
 * Класс управления матчами Arsenal Match Manager
 * 
 * Управление матчами футбольной команды ФК Арсенал Дзержинск:
 * - Просмотр списка матчей
 * - Добавление новых матчей
 * - Редактирование существующих матчей
 * - Удаление матчей
 * - Привязка событий (голы, карточки, замены) и составов
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Match_Manager {
    
    /**
     * Конструктор
     */
    public function __construct() {
        // Инициализируется из основного класса плагина
    }
    
    /**
     * Получить список всех матчей
     *
     * @param int   $paged Номер страницы для пагинации
     * @param int   $per_page Кол-во матчей на странице
     * @param array $filters Массив фильтров (league_id, status, team_id, date_from, date_to)
     * @return array Массив матчей и информация о пагинации
     */
    public static function get_matches( $paged = 1, $per_page = 20, $filters = array() ) {
        global $wpdb;
        
        $offset = ( $paged - 1 ) * $per_page;
        $where = '1=1';
        $params = array();
        
        // Фильтр по турниру
        if ( ! empty( $filters['tournament_id'] ) ) {
            $where .= ' AND m.tournament_id = %s';
            $params[] = sanitize_text_field( $filters['tournament_id'] );
        }
        
        // Фильтр по статусу
        if ( ! empty( $filters['status'] ) ) {
            $where .= ' AND m.status = %s';
            $params[] = sanitize_text_field( $filters['status'] );
        }
        
        // Фильтр по команде (домашняя или гостевая)
        if ( ! empty( $filters['team_id'] ) ) {
            $where .= ' AND (m.home_team_id = %s OR m.away_team_id = %s)';
            $params[] = sanitize_text_field( $filters['team_id'] );
            $params[] = sanitize_text_field( $filters['team_id'] );
        }
        
        // Фильтр по дате (начало периода)
        if ( ! empty( $filters['date_from'] ) ) {
            $where .= ' AND m.match_date >= %s';
            $params[] = sanitize_text_field( $filters['date_from'] );
        }
        
        // Фильтр по дате (конец периода)
        if ( ! empty( $filters['date_to'] ) ) {
            $where .= ' AND m.match_date <= %s';
            $params[] = sanitize_text_field( $filters['date_to'] );
        }
        
        // Фильтр по сезону
        if ( ! empty( $filters['season_id'] ) ) {
            $where .= ' AND m.season_id = %s';
            $params[] = sanitize_text_field( $filters['season_id'] );
        }
        
        // Общее количество матчей
        $count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_matches m WHERE $where";
        $total = $wpdb->get_var( 
            empty( $params ) ? $count_query : $wpdb->prepare( $count_query, ...$params )
        );
        
        // Получение матчей с информацией о командах
        $query = "SELECT m.*, 
                         ht.name AS home_team_name, ht.logo_url AS home_logo,
                         at.name AS away_team_name, at.logo_url AS away_logo
                  FROM {$wpdb->prefix}arsenal_matches m
                  LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
                  LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
                  WHERE $where
                  ORDER BY m.match_date DESC
                  LIMIT %d, %d";
        
        $matches = $wpdb->get_results( 
            $wpdb->prepare( $query, array_merge( $params, array( $offset, $per_page ) ) )
        );
        
        return array(
            'matches' => $matches,
            'total' => intval( $total ),
            'total_pages' => ceil( $total / $per_page ),
            'current_page' => $paged,
            'per_page' => $per_page,
        );
    }
    
    /**
     * Получить матч по ID
     *
     * @param int $match_id ID матча
     * @return object|null Объект матча или null
     */
    public static function get_match( $match_id ) {
        global $wpdb;
        
        $query = "SELECT m.*, 
                         ht.name AS home_team_name, ht.logo_url AS home_logo,
                         at.name AS away_team_name, at.logo_url AS away_logo,
                         s.name AS stadium_name
                  FROM {$wpdb->prefix}arsenal_matches m
                  LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
                  LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
                  LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
                  WHERE m.id = %d";
        
        return $wpdb->get_row( $wpdb->prepare( $query, intval( $match_id ) ) );
    }
    
    /**
     * Создать новый матч
     *
     * @param array $data Данные матча
     * @return int|false ID созданного матча или false при ошибке
     */
    public static function create_match( $data ) {
        global $wpdb;
        
        // Валидация обязательных полей (по схеме БД)
        $required = array( 'status', 'match_date', 'home_team_id', 'away_team_id' );
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return false;
            }
        }
        
        // Если league_id не указан, установить первую доступную лигу
        if ( empty( $data['league_id'] ) ) {
            $leagues = self::get_leagues();
            $league_ids = array_keys( $leagues );
            $data['league_id'] = ! empty( $league_ids ) ? $league_ids[0] : null;
            
            if ( empty( $data['league_id'] ) ) {
                return false; // Нет доступных лиг
            }
        }
        
        // Подготовка данных
        $insert_data = array(
            'match_id' => null, // Будет сгенерирован триггером БД
            'league_id' => ! empty( $data['league_id'] ) ? sanitize_text_field( $data['league_id'] ) : null,
            'season_id' => ! empty( $data['season_id'] ) ? sanitize_text_field( $data['season_id'] ) : null,
            'tournament_id' => sanitize_text_field( $data['tournament_id'] ),
            'match_date' => sanitize_text_field( $data['match_date'] ),
            'match_time' => ! empty( $data['match_time'] ) ? sanitize_text_field( $data['match_time'] ) : null,
            'home_team_id' => sanitize_text_field( $data['home_team_id'] ),
            'away_team_id' => sanitize_text_field( $data['away_team_id'] ),
            'home_score' => isset( $data['home_score'] ) ? intval( $data['home_score'] ) : null,
            'away_score' => isset( $data['away_score'] ) ? intval( $data['away_score'] ) : null,
            'status' => sanitize_text_field( $data['status'] ?? 'NS' ),
            'tour' => isset( $data['tour'] ) ? intval( $data['tour'] ) : null,
            'stadium_id' => ! empty( $data['stadium_id'] ) ? sanitize_text_field( $data['stadium_id'] ) : null,
            'attendance' => isset( $data['attendance'] ) ? intval( $data['attendance'] ) : null,
            'main_referee' => ! empty( $data['main_referee'] ) ? sanitize_text_field( $data['main_referee'] ) : null,
            'assistant_referees_1' => ! empty( $data['assistant_referees_1'] ) ? sanitize_text_field( $data['assistant_referees_1'] ) : null,
            'assistant_referees_2' => ! empty( $data['assistant_referees_2'] ) ? sanitize_text_field( $data['assistant_referees_2'] ) : null,
            'fourth_referee' => ! empty( $data['fourth_referee'] ) ? sanitize_text_field( $data['fourth_referee'] ) : null,
            'referee_inspector' => ! empty( $data['referee_inspector'] ) ? sanitize_text_field( $data['referee_inspector'] ) : null,
            'delegate' => ! empty( $data['delegate'] ) ? sanitize_text_field( $data['delegate'] ) : null,
            'match_report' => ! empty( $data['match_report'] ) ? wp_kses_post( $data['match_report'] ) : null,
        );
        
        // Типы данных для каждого поля
        $format = array(
            '%s', // match_id
            '%s', // league_id
            '%s', // season_id
            '%s', // tournament_id
            '%s', // match_date
            '%s', // match_time
            '%s', // home_team_id
            '%s', // away_team_id
            '%d', // home_score
            '%d', // away_score
            '%s', // status
            '%d', // tour
            '%s', // stadium_id
            '%d', // attendance
            '%s', // main_referee
            '%s', // assistant_referees_1
            '%s', // assistant_referees_2
            '%s', // fourth_referee
            '%s', // referee_inspector
            '%s', // delegate
            '%s', // match_report
        );
        
        $result = $wpdb->insert( "{$wpdb->prefix}arsenal_matches", $insert_data, $format );
        
        if ( ! $result ) {
            error_log( 'Arsenal: Ошибка создания матча - ' . $wpdb->last_error );
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Обновить существующий матч
     *
     * @param int   $match_id ID матча
     * @param array $data Данные для обновления
     * @return bool Успешность операции
     */
    public static function update_match( $match_id, $data ) {
        global $wpdb;
        
        $update_data = array();
        $format = array();
        
        // Обновляем только переданные поля
        if ( isset( $data['tournament_id'] ) ) {
            $update_data['tournament_id'] = sanitize_text_field( $data['tournament_id'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['match_date'] ) ) {
            $update_data['match_date'] = sanitize_text_field( $data['match_date'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['match_time'] ) ) {
            $update_data['match_time'] = sanitize_text_field( $data['match_time'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['home_team_id'] ) ) {
            $update_data['home_team_id'] = sanitize_text_field( $data['home_team_id'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['away_team_id'] ) ) {
            $update_data['away_team_id'] = sanitize_text_field( $data['away_team_id'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['home_score'] ) ) {
            $update_data['home_score'] = intval( $data['home_score'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['away_score'] ) ) {
            $update_data['away_score'] = intval( $data['away_score'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $data['status'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['tour'] ) ) {
            $update_data['tour'] = intval( $data['tour'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['stadium_id'] ) ) {
            $update_data['stadium_id'] = sanitize_text_field( $data['stadium_id'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['attendance'] ) ) {
            $update_data['attendance'] = intval( $data['attendance'] );
            $format[] = '%d';
        }
        
        if ( isset( $data['main_referee'] ) ) {
            $update_data['main_referee'] = sanitize_text_field( $data['main_referee'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['assistant_referees_1'] ) ) {
            $update_data['assistant_referees_1'] = sanitize_text_field( $data['assistant_referees_1'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['assistant_referees_2'] ) ) {
            $update_data['assistant_referees_2'] = sanitize_text_field( $data['assistant_referees_2'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['fourth_referee'] ) ) {
            $update_data['fourth_referee'] = sanitize_text_field( $data['fourth_referee'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['referee_inspector'] ) ) {
            $update_data['referee_inspector'] = sanitize_text_field( $data['referee_inspector'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['delegate'] ) ) {
            $update_data['delegate'] = sanitize_text_field( $data['delegate'] );
            $format[] = '%s';
        }
        
        if ( isset( $data['match_report'] ) ) {
            $update_data['match_report'] = wp_kses_post( $data['match_report'] );
            $format[] = '%s';
        }
        
        if ( empty( $update_data ) ) {
            return false;
        }
        
        $result = $wpdb->update(
            "{$wpdb->prefix}arsenal_matches",
            $update_data,
            array( 'id' => $match_id ),
            $format,
            array( '%d' )
        );
        
        if ( false === $result ) {
            error_log( 'Arsenal: Ошибка обновления матча #' . $match_id . ' - ' . $wpdb->last_error );
            return false;
        }
        
        return true;
    }
    
    /**
     * Удалить матч
     *
     * @param int $match_id ID матча
     * @return bool Успешность операции
     */
    public static function delete_match( $match_id ) {
        global $wpdb;
        
        // Удаляем события матча
        $wpdb->delete( "{$wpdb->prefix}arsenal_match_events", array( 'match_id' => $match_id ), array( '%d' ) );
        
        // Удаляем составы матча
        $wpdb->delete( "{$wpdb->prefix}arsenal_match_lineups", array( 'match_id' => $match_id ), array( '%d' ) );
        
        // Удаляем сам матч
        $result = $wpdb->delete( "{$wpdb->prefix}arsenal_matches", array( 'id' => $match_id ), array( '%d' ) );
        
        if ( false === $result ) {
            error_log( 'Arsenal: Ошибка удаления матча #' . $match_id . ' - ' . $wpdb->last_error );
            return false;
        }
        
        return true;
    }
    
    /**
     * Получить все команды для выпадающего списка
     *
     * @return array Массив команд [team_id => team_name]
     */
    public static function get_teams() {
        global $wpdb;
        
        $teams = $wpdb->get_results( 
            "SELECT team_id, name FROM {$wpdb->prefix}arsenal_teams ORDER BY name"
        );
        
        $result = array();
        foreach ( $teams as $team ) {
            $result[ $team->team_id ] = $team->name;
        }
        
        return $result;
    }
    
    /**
     * Получить все стадионы для выпадающего списка
     *
     * @return array Массив стадионов [stadium_id => stadium_name]
     */
    public static function get_stadiums() {
        global $wpdb;
        
        $stadiums = $wpdb->get_results( 
            "SELECT stadium_id, name FROM {$wpdb->prefix}arsenal_stadiums ORDER BY name"
        );
        
        $result = array();
        foreach ( $stadiums as $stadium ) {
            $result[ $stadium->stadium_id ] = $stadium->name;
        }
        
        return $result;
    }
    
    /**
     * Получить все лиги для выпадающего списка
     *
     * @return array Массив лиг [league_id => league_name]
     */
    public static function get_leagues() {
        global $wpdb;
        
        $leagues = $wpdb->get_results( 
            "SELECT league_id, league_name FROM {$wpdb->prefix}arsenal_leagues ORDER BY league_name"
        );
        
        $result = array();
        foreach ( $leagues as $league ) {
            $result[ $league->league_id ] = $league->league_name;
        }
        
        return $result;
    }
    
    /**
     * Получить все турниры для выпадающего списка
     *
     * @return array Массив турниров [tournament_id => tournament_name]
     */
    public static function get_tournaments() {
        global $wpdb;
        
        $tournaments = $wpdb->get_results( 
            "SELECT tournament_id, name FROM {$wpdb->prefix}arsenal_tournaments ORDER BY name"
        );
        
        $result = array();
        foreach ( $tournaments as $tournament ) {
            $result[ $tournament->tournament_id ] = $tournament->name;
        }
        
        return $result;
    }
    
    /**
     * Получить все сезоны для выпадающего списка
     *
     * @return array Массив сезонов [season_id => season_name]
     */
    public static function get_seasons() {
        global $wpdb;
        
        $seasons = $wpdb->get_results( 
            "SELECT season_id, season_name FROM {$wpdb->prefix}arsenal_seasons ORDER BY start_date DESC"
        );
        
        $result = array();
        foreach ( $seasons as $season ) {
            $result[ $season->season_id ] = $season->season_name;
        }
        
        return $result;
    }
    
    /**
     * Получить все статусы матчей для выпадающего списка
     *
     * @return array Массив статусов [status_id => status_name]
     */
    public static function get_match_statuses() {
        global $wpdb;
        
        $statuses = $wpdb->get_results( 
            "SELECT status_id, match_status FROM {$wpdb->prefix}arsenal_match_statuses ORDER BY match_status"
        );
        
        $result = array();
        foreach ( $statuses as $status ) {
            $result[ $status->status_id ] = $status->match_status;
        }
        
        return $result;
    }
    
    /**
     * Получить составы матча
     *
     * @param string $match_id ID матча
     * @return array Массив составов [team_id => [players...]]
     */
    public static function get_match_lineups( $match_id ) {
        global $wpdb;
        
        $lineups = $wpdb->get_results( $wpdb->prepare(
            "SELECT ml.*, p.first_name, p.last_name, t.name as team_name
             FROM {$wpdb->prefix}arsenal_match_lineups ml
             LEFT JOIN {$wpdb->prefix}arsenal_players p ON ml.player_id = p.player_id
             LEFT JOIN {$wpdb->prefix}arsenal_teams t ON ml.team_id = t.team_id
             WHERE ml.match_id = %s
             ORDER BY ml.team_id, ml.is_starting DESC, ml.shirt_number",
            $match_id
        ) );
        
        return $lineups;
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
            "SELECT me.*, p.first_name, p.last_name, et.event_name
             FROM {$wpdb->prefix}arsenal_match_events me
             LEFT JOIN {$wpdb->prefix}arsenal_players p ON me.player_id = p.player_id
             LEFT JOIN {$wpdb->prefix}arsenal_event_types et ON me.event_type = et.event_type_id
             WHERE me.match_id = %s
             ORDER BY me.minute ASC",
            $match_id
        ) );
        
        return $events;
    }
}

