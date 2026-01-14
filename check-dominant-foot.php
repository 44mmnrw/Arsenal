<?php
require 'wp-load.php';
global $wpdb;

$values = $wpdb->get_col("SELECT DISTINCT dominant_foot FROM wp_arsenal_players WHERE dominant_foot IS NOT NULL AND dominant_foot != '' ORDER BY dominant_foot");

echo "Реальные значения в БД:\n";
if($values) {
  foreach($values as $v) {
    echo "- " . $v . "\n";
  }
} else {
  echo "Нет данных\n";
}
