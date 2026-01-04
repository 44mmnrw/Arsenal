<?php
/**
 * Shortcode для отображения матча
 * 
 * Использование:
 * [match_details team_id="813F7502" date="2025-03-13"]
 * ИЛИ
 * [match_details team_name="Арсенал" date="2025-03-13"]
 *
 * @package Arsenal
 */

if ( ! function_exists( 'arsenal_match_details_shortcode' ) ) {
    function arsenal_match_details_shortcode( $atts ) {
        // Атрибуты shortcode'а
        $atts = shortcode_atts( array(
            'team_id'   => '',
            'team_name' => '',
            'date'      => '',
        ), $atts, 'match_details' );

        // Устанавливаем глобальные GET параметры для шаблона
        if ( ! empty( $atts['team_id'] ) ) {
            $_GET['team_id'] = $atts['team_id'];
        }
        if ( ! empty( $atts['team_name'] ) ) {
            $_GET['team_name'] = $atts['team_name'];
        }
        if ( ! empty( $atts['date'] ) ) {
            $_GET['date'] = $atts['date'];
        }

        // Получаем шаблон в буфер обмена
        ob_start();
        get_template_part( 'template-parts/blocks/match-details' );
        $output = ob_get_clean();

        return $output;
    }

    // Регистрируем shortcode
    add_shortcode( 'match_details', 'arsenal_match_details_shortcode' );
}

/**
 * Shortcode для отображения турнира
 * 
 * Использование:
 * [tournament]
 * ИЛИ
 * [tournament season_id="5B2ABC0C"]
 *
 * @package Arsenal
 */
if ( ! function_exists( 'arsenal_tournament_shortcode' ) ) {
    function arsenal_tournament_shortcode( $atts ) {
        // Атрибуты shortcode'а
        $atts = shortcode_atts( array(
            'season_id' => '',
        ), $atts, 'tournament' );

        // Устанавливаем глобальные GET параметры для шаблона
        if ( ! empty( $atts['season_id'] ) ) {
            $_GET['season_id'] = $atts['season_id'];
        }

        // Получаем шаблон в буфер обмена
        ob_start();
        get_template_part( 'template-parts/blocks/tournament-bracket' );
        $output = ob_get_clean();

        return $output;
    }

    // Регистрируем shortcode
    add_shortcode( 'tournament', 'arsenal_tournament_shortcode' );
}
