<?php
require 'wp-load.php';

echo "=== СТРАНИЦЫ ===\n";
$pages = get_pages();
foreach($pages as $p) {
	echo $p->ID . ': ' . $p->post_title . " (" . $p->post_status . ")\n";
}

echo "\n=== МЕНЮ ===\n";
$menus = wp_get_nav_menus();
foreach($menus as $menu) {
	echo "Menu ID: " . $menu->term_id . " - " . $menu->name . "\n";
	$items = wp_get_nav_menu_items($menu->term_id);
	if($items) {
		foreach($items as $item) {
			echo "  - " . $item->title . " (Object: " . $item->object . ", ID: " . $item->object_id . ")\n";
		}
	}
}

echo "\n=== ТАБЛИЦЫ БД ===\n";
global $wpdb;
$tables = $wpdb->get_results("SHOW TABLES LIKE 'wp_arsenal%'");
foreach($tables as $t) {
	$key = 'Tables_in_' . DB_NAME;
	echo $t->$key . "\n";
}
