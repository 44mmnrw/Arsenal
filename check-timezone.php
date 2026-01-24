<?php
require 'wp-load.php';

echo "⏰ Текущее время WordPress: " . date_i18n('Y-m-d H:i:s') . "\n";
echo "⏰ Системное время PHP: " . date('Y-m-d H:i:s') . "\n";
echo "⏰ Таймзона WordPress: " . get_option('timezone_string') . "\n";
echo "⏰ GMT Offset: " . get_option('gmt_offset') . " часов\n\n";

// Проверим как обрабатывается время из БД
global $wpdb;

$sample_time = $wpdb->get_var("SELECT CONCAT(match_date, ' ', match_time) FROM {$wpdb->prefix}arsenal_matches WHERE match_time IS NOT NULL LIMIT 1");

echo "📊 Время из БД (сырое): " . $sample_time . "\n";

$ts = strtotime($sample_time);
echo "📊 strtotime() результат: " . date('Y-m-d H:i:s', $ts) . "\n";
echo "📊 date_i18n() результат: " . date_i18n('Y-m-d H:i:s', $ts) . "\n";

if (function_exists('wp_date')) {
	echo "📊 wp_date() результат: " . wp_date('Y-m-d H:i:s', $ts) . "\n";
}
?>
