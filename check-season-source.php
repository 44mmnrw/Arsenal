<?php
require 'wp-load.php';
global $wpdb;

// Найти страницу с шаблоном "Турнирная таблица"
$page = $wpdb->get_row(
    "SELECT p.ID, p.post_title, pm.meta_value as template
     FROM {$wpdb->prefix}posts p
     LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_page_template'
     WHERE p.post_type = 'page' 
     AND pm.meta_value LIKE '%standings%'
     LIMIT 1"
);

if ($page) {
    echo "Страница найдена: " . $page->post_title . " (ID: " . $page->ID . ")\n";
    echo "Шаблон: " . $page->template . "\n\n";
    
    // Проверяем все custom fields этой страницы
    $meta = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value 
         FROM {$wpdb->prefix}postmeta 
         WHERE post_id = %d
         ORDER BY meta_key",
        $page->ID
    ));
    
    echo "Custom Fields:\n";
    foreach ($meta as $m) {
        if (strpos($m->meta_key, 'season') !== false || strpos($m->meta_key, 'year') !== false) {
            echo "  " . $m->meta_key . " = " . $m->meta_value . "\n";
        }
    }
} else {
    echo "Страница с шаблоном standings не найдена\n";
}

// Проверим структуру таблицы сезонов
echo "\n=== Сезоны в БД ===\n";
$seasons = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}arsenal_seasons ORDER BY year DESC");
foreach ($seasons as $s) {
    $cols = get_object_vars($s);
    foreach ($cols as $key => $val) {
        echo "$key = $val, ";
    }
    echo "\n";
}
