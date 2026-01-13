<?php
/**
 * Функции для работы со страницей матча
 * 
 * Содержит все SQL запросы и бизнес-логику для отображения данных матча
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Получить матч по дате и team_id
 *
 * @param string $match_date Дата матча в формате YYYY-MM-DD
 * @param string $team_id    ID команды (HEX строка)
 * @return object|null       Объект матча или null
 */
function arsenal_get_match_by_date_and_team( $match_date, $team_id ) {
    global $wpdb;

    $query = $wpdb->prepare(
        "SELECT m.id, m.match_id, m.match_date, m.match_time, m.home_team_id, m.away_team_id, m.home_score, m.away_score,
                m.status, m.tour, m.attendance, m.main_referee, m.assistant_referees_1, m.assistant_referees_2, m.fourth_referee, m.referee_inspector, m.delegate, m.stadium_id, m.league_id, m.match_report,
                ht.name AS home_team_name, ht.logo_url AS home_logo,
                at.name AS away_team_name, at.logo_url AS away_logo,
                s.name AS stadium_name
         FROM {$wpdb->prefix}arsenal_matches m
         LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
         LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
         LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
         WHERE m.match_date = %s
         AND (m.home_team_id = %s OR m.away_team_id = %s)
         AND m.home_score IS NOT NULL
         AND m.away_score IS NOT NULL
         LIMIT 1",
        $match_date,
        $team_id,
        $team_id
    );

    return $wpdb->get_row( $query );
}

/**
 * Получить тренера команды на дату матча
 *
 * @param string $team_id    ID команды (HEX строка)
 * @param string $match_date Дата матча в формате YYYY-MM-DD
 * @return object|null       Объект с полем name или null
 */
function arsenal_get_team_coach( $team_id, $match_date ) {
    global $wpdb;

    return $wpdb->get_row(
        $wpdb->prepare(
            "SELECT c.name FROM {$wpdb->prefix}arsenal_team_coaches tc
             LEFT JOIN {$wpdb->prefix}arsenal_coaches c ON tc.coach_id = c.coach_id
             WHERE tc.team_id = %s 
             AND tc.start_date <= %s 
             AND (tc.end_date = '0000-00-00' OR tc.end_date >= %s)
             ORDER BY tc.start_date DESC LIMIT 1",
            $team_id,
            $match_date,
            $match_date
        )
    );
}

/**
 * Получить события матча
 *
 * @param string $match_id ID матча
 * @return array           Массив объектов событий
 */
function arsenal_get_match_events( $match_id ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT me.*, p.full_name AS player_name, et.event_name, ml.team_id AS event_team_id
             FROM {$wpdb->prefix}arsenal_match_events me
             LEFT JOIN {$wpdb->prefix}arsenal_players p ON me.player_id = p.player_id
             LEFT JOIN {$wpdb->prefix}arsenal_event_types et ON me.event_type = et.event_type_id
             LEFT JOIN {$wpdb->prefix}arsenal_match_lineups ml ON me.player_id = ml.player_id AND me.match_id = ml.match_id
             WHERE me.match_id = %s
             ORDER BY me.minute ASC, me.id ASC",
            $match_id
        )
    );
}

/**
 * Получить составы матча со всеми данными
 *
 * @param string $match_id ID матча
 * @return array           Массив объектов составов с информацией о профилях игроков
 */
function arsenal_get_match_lineups( $match_id ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ml.*, p.full_name, p.player_id, pos.name AS position_name,
                    CASE WHEN EXISTS (
                        SELECT 1 FROM {$wpdb->prefix}arsenal_team_contracts 
                        WHERE player_id = ml.player_id LIMIT 1
                    ) THEN 1 ELSE 0 END AS has_profile
             FROM {$wpdb->prefix}arsenal_match_lineups ml
             LEFT JOIN {$wpdb->prefix}arsenal_players p ON ml.player_id = p.player_id
             LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
             WHERE ml.match_id = %s
             ORDER BY ml.is_starting DESC, ml.shirt_number ASC",
            $match_id
        )
    );
}

/**
 * Разделить составы по командам и типам (стартовые/запасные)
 *
 * @param array  $lineups     Массив составов
 * @param string $home_team_id ID домашней команды
 * @return array              Ассоциативный массив с ключами home_starting, home_subs, away_starting, away_subs
 */
function arsenal_organize_lineups( $lineups, $home_team_id ) {
    $home_starting = [];
    $home_subs = [];
    $away_starting = [];
    $away_subs = [];

    foreach ( $lineups as $lineup ) {
        if ( $lineup->team_id === $home_team_id ) {
            if ( $lineup->is_starting ) {
                $home_starting[] = $lineup;
            } else {
                $home_subs[] = $lineup;
            }
        } else {
            if ( $lineup->is_starting ) {
                $away_starting[] = $lineup;
            } else {
                $away_subs[] = $lineup;
            }
        }
    }

    // Группируем основной состав по позициям для домашней команды
    $home_starting_by_position = arsenal_group_by_position( $home_starting );
    // Группируем основной состав по позициям для гостевой команды
    $away_starting_by_position = arsenal_group_by_position( $away_starting );

    return [
        'home_starting' => $home_starting,
        'home_starting_by_position' => $home_starting_by_position,
        'home_subs'     => $home_subs,
        'away_starting' => $away_starting,
        'away_starting_by_position' => $away_starting_by_position,
        'away_subs'     => $away_subs,
    ];
}

/**
 * Группирует игроков по позициям
 *
 * @param array $players Массив объектов игроков
 * @return array         Ассоциативный массив вида ['Позиция' => [игроки]]
 */
function arsenal_group_by_position( $players ) {
    $grouped = [];
    $position_order = [
        'Вратарь' => 0,
        'Защитник' => 1,
        'Полузащитник' => 2,
        'Нападающий' => 3,
    ];

    foreach ( $players as $player ) {
        $position = $player->position_name ? $player->position_name : 'Неизвестная позиция';
        if ( ! isset( $grouped[ $position ] ) ) {
            $grouped[ $position ] = [];
        }
        $grouped[ $position ][] = $player;
    }

    // Сортируем позиции по определённому порядку
    uksort( $grouped, function( $a, $b ) use ( $position_order ) {
        $order_a = $position_order[ $a ] ?? 999;
        $order_b = $position_order[ $b ] ?? 999;
        return $order_a <=> $order_b;
    } );

    return $grouped;
}

/**
 * Получить информацию о стадионе по ID
 *
 * @param string|int $stadium_id ID стадиона
 * @return object|null           Объект стадиона с полями (id, name, city, photo_url) или null
 */
function arsenal_get_stadium_by_id( $stadium_id ) {
    global $wpdb;

    if ( ! $stadium_id ) {
        return null;
    }

    $query = $wpdb->prepare(
        "SELECT id, name, city, photo_url FROM {$wpdb->prefix}arsenal_stadiums WHERE stadium_id = %s LIMIT 1",
        $stadium_id
    );

    return $wpdb->get_row( $query );
}

/**
 * Получить URL на страницу игрока если у него есть контракт с клубом
 * 
 * @param string $player_id ID игрока
 * @return string|null      URL на страницу игрока или null если контракта нет
 */
function arsenal_get_player_url_if_has_contract( $player_id ) {
	global $wpdb;
	
	if ( empty( $player_id ) ) {
		return null;
	}
	
	// Проверяем наличие контракта в wp_arsenal_team_contracts
	$has_contract = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_team_contracts WHERE player_id = %s LIMIT 1",
		$player_id
	) );
	
	if ( ! $has_contract ) {
		return null;
	}
	
	// Если контракт есть, возвращаем URL на страницу игрока
	return arsenal_get_player_url( $player_id );
}
