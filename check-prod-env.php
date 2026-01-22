<?php
/**
 * Проверка окружения на продакшене
 */
require 'wp-load.php';

echo "=== Версия WordPress ===\n";
echo "WP: " . get_bloginfo('version') . "\n";
echo "PHP: " . PHP_VERSION . "\n\n";

echo "=== Активные плагины ===\n";
$plugins = get_plugins();
$active_count = 0;
foreach ( $plugins as $plugin => $data ) {
	if ( is_plugin_active( $plugin ) ) {
		echo "✓ " . $data['Name'] . "\n";
		$active_count++;
	}
}
if ( $active_count === 0 ) {
	echo "(нет активных плагинов)\n";
}

echo "\n=== Проверка статуса страниц ===\n";
global $wpdb;
$pages = $wpdb->get_results( 
	$wpdb->prepare( "SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_type = %s LIMIT 10", 'page' )
);
if ( ! empty( $pages ) ) {
	foreach ( $pages as $page ) {
		echo "{$page->ID}: {$page->post_title} - {$page->post_status}\n";
	}
} else {
	echo "(нет страниц)\n";
}

echo "\n";

