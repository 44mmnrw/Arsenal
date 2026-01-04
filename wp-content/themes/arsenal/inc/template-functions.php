<?php
/**
 * Вспомогательные функции темы
 *
 * @package Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Вывод мета-информации о записи
 */
if ( ! function_exists( 'arsenal_posted_on' ) ) {
	function arsenal_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		
		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf( '<span class="posted-on">%s</span>', $time_string );
	}
}

/**
 * Вывод автора записи
 */
if ( ! function_exists( 'arsenal_posted_by' ) ) {
	function arsenal_posted_by() {
		printf(
			'<span class="byline">%s <a href="%s">%s</a></span>',
			esc_html__( 'Автор:', 'arsenal' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		);
	}
}

/**
 * Настройка excerpt
 */
if ( ! function_exists( 'arsenal_excerpt_length' ) ) {
	function arsenal_excerpt_length( $length ) {
		return 30;
	}
}
add_filter( 'excerpt_length', 'arsenal_excerpt_length' );

if ( ! function_exists( 'arsenal_excerpt_more' ) ) {
	function arsenal_excerpt_more( $more ) {
		return '...';
	}
}
add_filter( 'excerpt_more', 'arsenal_excerpt_more' );

/**
 * ═══════════════════════════════════════════════════════════════
 * ФУНКЦИИ ДЛЯ СТРАНИЦЫ СТАТИСТИКИ ИГРОКА
 * 
 * ВАЖНО: Функции для работы с игроками определены в inc/player-functions.php
 * которые используют правильную структуру новой БД Arsenal (wp_arsenal_*)
 * 
 * Используйте:
 * - arsenal_get_player_data($player_id)
 * - arsenal_get_player_seasons($player_id)
 * - arsenal_get_player_stats($player_id)
 * - arsenal_get_player_events($player_id, $year)
 * - arsenal_get_player_position($position_id)
 * ═══════════════════════════════════════════════════════════════
 */

