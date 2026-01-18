<?php
require_once 'wp-load.php';

global $wpdb;

echo "=== ВСЕ СТРАНИЦЫ С ШАБЛОНОМ page-staff-grid.php ===\n\n";

$pages = $wpdb->get_results(
	"SELECT p.ID, p.post_title, p.post_name, pm.meta_value as template
	FROM {$wpdb->prefix}posts p
	LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_page_template'
	WHERE p.post_type = 'page' AND pm.meta_value = 'templates/page-staff-grid.php'
	ORDER BY p.ID"
);

if ($pages) {
	foreach ($pages as $page) {
		echo "ID: {$page->ID}\n";
		echo "  Название: {$page->post_title}\n";
		echo "  URL: http://arsenal.test/{$page->post_name}/\n";
		
		$dept = get_post_meta($page->ID, '_arsenal_staff_department_filter', true);
		$club = get_post_meta($page->ID, '_arsenal_staff_club_type_filter', true);
		
		echo "  Фильтры:\n";
		echo "    - department_id: " . ($dept ? $dept : '(пусто)') . "\n";
		echo "    - club_type: " . ($club ? $club : '(пусто)') . "\n";
		echo "\n";
	}
} else {
	echo "Нет страниц с этим шаблоном!\n";
}
