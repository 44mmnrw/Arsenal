<?php
require_once __DIR__ . '/wp-load.php';

$history = Arsenal_History_Manager::get_history();

if ( ! empty( $history['scale'] ) ) {
	$scale = is_string( $history['scale'] ) ? json_decode( $history['scale'], true ) : $history['scale'];
	echo "Timeline data found:\n";
	if ( is_array( $scale ) ) {
		echo "Count: " . count( $scale ) . " entries\n";
		foreach ( $scale as $i => $entry ) {
			echo "  Entry " . $i . ": Year " . ( $entry['year'] ?? 'N/A' ) . " - " . ( $entry['text'] ?? 'N/A' ) . "\n";
		}
	} else {
		echo "Scale is not an array after parsing\n";
	}
} else {
	echo "No timeline data in scale field\n";
}
?>
