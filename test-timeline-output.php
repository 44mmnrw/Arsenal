<?php
require_once __DIR__ . '/wp-load.php';

// Включаем вывод
ob_start();

// Вызываем функцию
if ( function_exists( 'arsenal_display_timeline' ) ) {
	arsenal_display_timeline();
	$output = ob_get_clean();
	echo "Output length: " . strlen( $output ) . " chars\n";
	echo "First 500 chars:\n";
	echo substr( $output, 0, 500 ) . "\n";
	echo "\nLast 200 chars:\n";
	echo substr( $output, -200 ) . "\n";
} else {
	ob_end_clean();
	echo "Function arsenal_display_timeline not found\n";
}
?>
