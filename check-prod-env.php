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
foreach ( $plugins as $plugin => $data ) {
	if ( is_plugin_active( $plugin ) ) {
		echo "✓ " . $data['Name'] . "\n";
	}
}

echo "\n=== Пользователи с правами publish_pages ===\n";
global $wpdb;
$users = $wpdb->get_results( "SELECT u.ID, u.user_login FROM {$wpdb->users} u LIMIT 10" );
foreach ( $users as $user ) {
	$u = get_user_by( 'id', $user->ID );
	$can_publish = user_can( $u, 'publish_pages' ) ? 'ДА' : 'НЕТ';
	$role = implode( ', ', $u->roles );
	echo "{$user->user_login} ({$role}) - publish_pages: {$can_publish}\n";
}

echo "\n=== Тестовая страница ===\n";
$test_page = get_page_by_title( 'Тест', OBJECT, 'page' );
if ( $test_page ) {
	echo "ID: {$test_page->ID}\n";
	echo "Status: {$test_page->post_status}\n";
}
