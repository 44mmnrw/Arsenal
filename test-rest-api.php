<?php
require 'wp-load.php';

// Проверить что endpoint регистрируется
if ( function_exists( 'rest_url' ) ) {
	echo 'REST URL: ' . rest_url( 'arsenal/v1/seasons-by-tournament' ) . "\n";
}

// Проверить функцию напрямую
echo "\nПроверка функции arsenal_get_seasons_by_tournament():\n";

class MockRequest {
	public function get_param( $param ) {
		return '';
	}
}

$result = arsenal_get_seasons_by_tournament( new MockRequest() );
echo json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
