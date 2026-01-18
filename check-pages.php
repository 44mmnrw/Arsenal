<?php
require_once 'wp-load.php';

global $wpdb;

echo "=== ПРОВЕРКА ID СТРАНИЦ ===\n\n";

$pages = array(224, 382);

foreach ($pages as $id) {
	$post = get_post($id);
	if ($post) {
		echo "ID {$id}: {$post->post_title}\n";
		echo "  Slug: {$post->post_name}\n";
		echo "  Template: " . get_page_template_slug($id) . "\n";
		echo "  URL: " . get_permalink($id) . "\n\n";
	}
}

echo "=== ФИЛЬТРЫ ===\n";
foreach ($pages as $id) {
	$dept = get_post_meta($id, '_arsenal_staff_department_filter', true);
	$club = get_post_meta($id, '_arsenal_staff_club_type_filter', true);
	echo "ID {$id}:\n";
	echo "  department_filter: '{$dept}'\n";
	echo "  club_type_filter: '{$club}'\n\n";
}
