<?php
require 'wp-load.php';
global $wpdb;

$staff = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}arsenal_staff" );
echo "Total staff: " . count( $staff ) . "\n\n";

if ( ! empty( $staff ) ) {
	foreach ( $staff as $p ) {
		echo "ID: {$p->id}\n";
		echo "  Name: {$p->first_name} {$p->second_name}\n";
		echo "  Job Title ID: {$p->job_title_id}\n";
		echo "  Photo: {$p->photo_url}\n";
		echo "\n";
	}
} else {
	echo "No staff found\n";
}

echo "\n=== Job Titles ===\n";
$jt = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}arsenal_staff_job_titles" );
echo "Job titles: " . count( $jt ) . "\n";
foreach ( $jt as $j ) {
	echo "  ID: {$j->id}, job_title_name: '{$j->job_title_name}'\n";
}

echo "\n=== JOIN Test with job_title_name ===\n";
$test = $wpdb->get_results( 
	"SELECT s.*, jt.job_title_name as job_title, CONCAT(s.first_name, ' ', s.second_name) as full_name
	FROM {$wpdb->prefix}arsenal_staff s
	LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id" 
);
echo "JOIN Result: " . count( $test ) . "\n";
if ( ! empty( $test ) ) {
	foreach ( $test as $p ) {
		echo "  - {$p->full_name} (job_id: {$p->job_title_id}, job_title_name: '{$p->job_title}')\n";
	}
}
