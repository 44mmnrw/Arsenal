<?php
require 'wp-load.php';
global $wpdb;

echo "=== Checking wp_arsenal_staff ===\n\n";

$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff" );
echo "Staff count: $count\n\n";

if ( $count > 0 ) {
	$data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}arsenal_staff LIMIT 3" );
	echo "Sample data:\n";
	foreach ( $data as $row ) {
		echo "ID: {$row->id}, Name: {$row->name}, Job Title ID: {$row->job_title_id}\n";
	}
} else {
	echo "No staff data found\n";
}

echo "\n=== Checking pages with coaches template ===\n\n";

$pages = $wpdb->get_results( 
	"SELECT ID, post_title, post_name FROM {$wpdb->prefix}posts 
	 WHERE post_type = 'page' AND post_name LIKE '%coach%'" 
);

if ( ! empty( $pages ) ) {
	foreach ( $pages as $page ) {
		echo "Page ID: {$page->ID}, Title: {$page->post_title}, Slug: {$page->post_name}\n";
	}
} else {
	echo "No coach-related pages found\n";
	echo "\nAvailable pages:\n";
	$all_pages = $wpdb->get_results( 
		"SELECT ID, post_title, post_name FROM {$wpdb->prefix}posts WHERE post_type = 'page' LIMIT 10" 
	);
	foreach ( $all_pages as $page ) {
		echo "  - {$page->post_title} ({$page->post_name})\n";
	}
}
