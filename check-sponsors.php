<?php
require 'wp-load.php';

global $wpdb;

echo "=== Проверка таблицы wp_arsenal_sponsors ===\n\n";

// Спонсоры
$sponsors = $wpdb->get_results( "SELECT id, name, type, is_active FROM {$wpdb->prefix}arsenal_sponsors ORDER BY id" );

echo "Спонсоры в БД (" . count( $sponsors ) . "):\n";
foreach ( $sponsors as $s ) {
    $status = $s->is_active ? '✓' : '✗';
    echo "  ID {$s->id}: {$s->name} ({$s->type}) - {$status}\n";
}

echo "\n=== Проверка страницы WordPress ===\n\n";

// Проверяем страницу используя WP_Query
$page_query = new WP_Query( array(
	'post_type'      => 'page',
	'title'          => 'Спонсоры и партнеры',
	'posts_per_page' => 1,
) );

$page = ! empty( $page_query->posts ) ? $page_query->posts[0] : null;

if ( $page ) {
    echo "✓ Страница найдена!\n";
    echo "  ID: {$page->ID}\n";
    echo "  URL: " . get_permalink( $page->ID ) . "\n";
    echo "  Slug: {$page->post_name}\n";
} else {
    echo "✗ Страница НЕ найдена\n";
}

echo "\n=== Проверка класса Arsenal_Sponsors ===\n\n";

if ( class_exists( 'Arsenal_Sponsors' ) ) {
    echo "✓ Класс Arsenal_Sponsors загружен\n";
} else {
    echo "✗ Класс Arsenal_Sponsors НЕ загружен\n";
    echo "  Подключаю вручную...\n";
    require_once get_template_directory() . '/inc/class-arsenal-sponsors.php';
    if ( class_exists( 'Arsenal_Sponsors' ) ) {
        echo "  ✓ Класс успешно подключен\n";
    }
}

echo "\n=== Проверка методов класса ===\n\n";

// Попробуем вызвать методы
$general = Arsenal_Sponsors::get_general_sponsor();
$partners = Arsenal_Sponsors::get_partners();
$all = Arsenal_Sponsors::get_sponsors();

echo "Общих спонсоров: " . ( $general ? 1 : 0 ) . "\n";
echo "Партнеров: " . count( $partners ) . "\n";
echo "Всего спонсоров: " . count( $all ) . "\n";

echo "\n✓ ВСЕ ПРОВЕРКИ ЗАВЕРШЕНЫ\n";
