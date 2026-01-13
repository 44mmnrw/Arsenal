<?php
/**
 * Скрипт создания страницы "Спонсоры и партнеры"
 */

require 'wp-load.php';

// Проверка существования страницы используя WP_Query
$page_query = new WP_Query( array(
	'post_type'      => 'page',
	'title'          => 'Спонсоры и партнеры',
	'posts_per_page' => 1,
) );

$page = ! empty( $page_query->posts ) ? $page_query->posts[0] : null;

if ( $page ) {
    echo "✓ Страница уже существует: ID " . $page->ID . "\n";
    echo "  URL: " . get_permalink( $page->ID ) . "\n";
    exit;
}

// Создаем страницу
$page_id = wp_insert_post( array(
    'post_type'      => 'page',
    'post_title'     => 'Спонсоры и партнеры',
    'post_name'      => 'sponsors',
    'post_status'    => 'publish',
    'post_content'   => 'Наши спонсоры и партнеры',
    'page_template'  => 'templates/page-sponsors.php',
    'post_author'    => 1,
) );

if ( is_wp_error( $page_id ) ) {
    echo "✗ Ошибка: " . $page_id->get_error_message() . "\n";
    exit;
}

// Проверяем, что класс Arsenal_Sponsors подключен
if ( ! class_exists( 'Arsenal_Sponsors' ) ) {
    require_once get_template_directory() . '/inc/class-arsenal-sponsors.php';
}

// Проверяем количество спонсоров в БД
global $wpdb;
$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_sponsors" );

echo "✓ Страница 'Спонсоры и партнеры' создана успешно!\n";
echo "  ID страницы: " . $page_id . "\n";
echo "  URL: " . get_permalink( $page_id ) . "\n";
echo "  Шаблон: templates/page-sponsors.php\n";
echo "  Спонсоров в БД: " . $count . "\n";

// Проверяем наличие класса
if ( class_exists( 'Arsenal_Sponsors' ) ) {
    echo "  Класс Arsenal_Sponsors: ✓ подключен\n";
} else {
    echo "  Класс Arsenal_Sponsors: ✗ НЕ подключен!\n";
}

echo "\nГотово! Страница доступна по адресу: " . get_permalink( $page_id ) . "\n";
