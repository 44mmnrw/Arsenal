<?php
/**
 * Проверка страницы Standings в WordPress
 */

require 'wp-load.php';

// Получаем страницу по названию шаблона
$pages = get_pages( array(
    'meta_key' => '_wp_page_template',
    'meta_value' => 'page-standings.php'
) );

if ( ! empty( $pages ) ) {
    echo "Найдены страницы со шаблоном page-standings.php:\n";
    foreach ( $pages as $page ) {
        echo "  - ID: {$page->ID}, Title: {$page->post_title}, URL: " . get_permalink( $page->ID ) . "\n";
    }
} else {
    echo "Страницы со шаблоном page-standings.php не найдены\n";
}

// Попробуем найти любую страницу с названием "Турнирная таблица"
$posts = get_posts( array(
    'post_type' => 'page',
    's' => 'Турнирная'
) );

if ( ! empty( $posts ) ) {
    echo "\nНайдены страницы со словом 'Турнирная':\n";
    foreach ( $posts as $post ) {
        echo "  - ID: {$post->ID}, Title: {$post->post_title}, Template: " . get_post_meta( $post->ID, '_wp_page_template', true ) . "\n";
    }
}

// Проверим прямой URL по слагу
$page = get_page_by_path( 'standings', OBJECT, 'page' );
if ( $page ) {
    echo "\nСтраница по пути 'standings' найдена:\n";
    echo "  - ID: {$page->ID}, Title: {$page->post_title}\n";
    echo "  - URL: " . get_permalink( $page->ID ) . "\n";
    echo "  - Template: " . get_post_meta( $page->ID, '_wp_page_template', true ) . "\n";
} else {
    echo "\nСтраница по пути 'standings' НЕ найдена\n";
}
