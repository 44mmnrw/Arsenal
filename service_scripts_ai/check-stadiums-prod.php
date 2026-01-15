<?php
/**
 * Скрипт для проверки таблицы стадионов на продакшене
 * 
 * Использование: php check-stadiums-prod.php
 * 
 * @package Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
    define( 'WP_USE_THEMES', false );
    require dirname( __FILE__ ) . '/../wp-load.php';
}

global $wpdb;

echo "=== Проверка таблицы стадионов ===" . PHP_EOL;

// Проверка существования таблицы
$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "arsenal_stadiums'" );

if ( ! $table_exists ) {
    echo "❌ Таблица wp_arsenal_stadiums НЕ СУЩЕСТВУЕТ!" . PHP_EOL;
    exit( 1 );
}

echo "✅ Таблица существует" . PHP_EOL;

// Количество стадионов
$count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "arsenal_stadiums" );
echo "📊 Всего стадионов: " . $count . PHP_EOL;

if ( $count == 0 ) {
    echo "⚠️  ВНИМАНИЕ: Таблица пуста! Это причина ошибки при сохранении матча." . PHP_EOL;
    exit( 1 );
}

// Показать стадионы
echo PHP_EOL . "Список стадионов:" . PHP_EOL;
$stadiums = $wpdb->get_results( "SELECT stadium_id, name FROM " . $wpdb->prefix . "arsenal_stadiums ORDER BY stadium_id LIMIT 20" );

foreach ( $stadiums as $stadium ) {
    echo "  - [ID: {$stadium->stadium_id}] {$stadium->name}" . PHP_EOL;
}

if ( $count > 20 ) {
    echo "  ... и еще " . ( $count - 20 ) . " стадионов" . PHP_EOL;
}

echo PHP_EOL . "✅ Таблица стадионов в порядке" . PHP_EOL;
