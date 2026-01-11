<?php
/**
 * Data Helpers for ABFF Parser Integration
 * Вспомогательные функции для работы с данными парсера ABFF
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/**
 * Загрузить турнирную таблицу
 */
function arsenal_get_standings() {
    $file = get_template_directory() . '/data/standings.json';
    
    if ( ! file_exists( $file ) ) {
        return null;
    }
    
    $json = @file_get_contents( $file );
    if ( ! $json ) {
        return null;
    }
    return json_decode( $json, true );
}

/**
 * Загрузить информацию о команде
 */
function arsenal_get_team_info() {
    $file = get_template_directory() . '/data/team_info.json';
    
    if ( ! file_exists( $file ) ) {
        return null;
    }
    
    $json = @file_get_contents( $file );
    if ( ! $json ) {
        return null;
    }
    return json_decode( $json, true );
}

/**
 * Загрузить матчи команды
 */
function arsenal_get_fixtures() {
    $file = get_template_directory() . '/data/fixtures.json';
    
    if ( ! file_exists( $file ) ) {
        return null;
    }
    
    $json = @file_get_contents( $file );
    if ( ! $json ) {
        return null;
    }
    return json_decode( $json, true );
}

/**
 * Получить позицию Арсенала в турнирной таблице
 */
function arsenal_get_position() {
    $standings = arsenal_get_standings();
    
    if ( ! $standings || ! isset( $standings['teams'] ) ) {
        return null;
    }
    
    foreach ( $standings['teams'] as $team ) {
        if ( ! empty( $team['name'] ) && strpos( strtolower( $team['name'] ), 'арсенал' ) !== false ) {
            return $team;
        }
    }
    
    return null;
}

/**
 * Получить последние N матчей
 */
function arsenal_get_recent_matches( $limit = 5 ) {
    $fixtures = arsenal_get_fixtures();
    
    if ( ! $fixtures || ! isset( $fixtures['matches'] ) ) {
        return array();
    }
    
    // Фильтруем только сыгранные матчи (с результатом)
    $played = array_filter( $fixtures['matches'], function( $match ) {
        return ! empty( $match['score'] ) && ! empty( $match['date'] );
    });
    
    // Сортируем по дате (последние первыми)
    usort( $played, function( $a, $b ) {
        $date_a = ! empty( $a['date'] ) ? strtotime( $a['date'] ) : 0;
        $date_b = ! empty( $b['date'] ) ? strtotime( $b['date'] ) : 0;
        return $date_b - $date_a;
    });
    
    return array_slice( $played, 0, $limit );
}

/**
 * Получить предстоящие матчи
 */
function arsenal_get_upcoming_matches( $limit = 5 ) {
    $fixtures = arsenal_get_fixtures();
    
    if ( ! $fixtures || ! isset( $fixtures['matches'] ) ) {
        return array();
    }
    
    // Фильтруем матчи без результата
    $upcoming = array_filter( $fixtures['matches'], function( $match ) {
        return empty( $match['score'] ) && ! empty( $match['date'] );
    });
    
    // Сортируем по дате
    usort( $upcoming, function( $a, $b ) {
        $date_a = ! empty( $a['date'] ) ? strtotime( $a['date'] ) : 0;
        $date_b = ! empty( $b['date'] ) ? strtotime( $b['date'] ) : 0;
        return $date_a - $date_b;
    });
    
    return array_slice( $upcoming, 0, $limit );
}
