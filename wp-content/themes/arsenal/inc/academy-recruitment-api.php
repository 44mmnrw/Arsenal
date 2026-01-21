<?php
/**
 * Academy Recruitment REST API
 * Асинхронная загрузка данных для страницы набора в академию
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Регистрация REST API маршрутов
 */
add_action( 'rest_api_init', function() {
	register_rest_route( 'arsenal/v1', '/academy-recruitment/(?P<page_id>\d+)', array(
		'methods'              => 'GET',
		'callback'             => 'arsenal_get_academy_recruitment_data',
		'permission_callback'  => '__return_true',
		'args'                 => array(
			'page_id' => array(
				'validate_callback' => function( $param ) {
					return is_numeric( $param );
				},
			),
		),
	) );
} );

/**
 * Callback для получения данных набора в академию
 */
function arsenal_get_academy_recruitment_data( $request ) {
	$page_id = $request['page_id'];
	
	// Подключить менеджер
	require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';
	
	$data = Arsenal_Academy_Recruitment_Manager::get_page_data( $page_id );
	
	if ( ! $data ) {
		$data = Arsenal_Academy_Recruitment_Manager::get_default_data();
	}
	
	return rest_ensure_response( $data );
}
