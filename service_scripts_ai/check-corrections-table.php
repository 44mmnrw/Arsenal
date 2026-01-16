<?php
/**
 * Проверка таблицы корректировок статистики игроков
 * 
 * Скрипт для диагностики таблицы wp_arsenal_player_stats_corrections
 * Запуск: php check-corrections-table.php
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Загружаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

global $wpdb;

$table_name = $wpdb->prefix . 'arsenal_player_stats_corrections';

echo "\n=== ПРОВЕРКА ТАБЛИЦЫ КОРРЕКТИРОВОК ===\n\n";

// 1. Проверяем существование таблицы
$table_exists = $wpdb->get_var( 
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '" . $table_name . "'" 
);

if ( ! $table_exists ) {
    echo "❌ Таблица " . $table_name . " не существует!\n";
    echo "Попытаемся создать...\n\n";
    
    // Подключаем класс
    require_once ABSPATH . 'wp-content/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections.php';
    $manager = Arsenal_Player_Stats_Corrections::get_instance();
    $manager->create_table();
    
    echo "✓ Таблица создана\n\n";
} else {
    echo "✓ Таблица существует\n\n";
}

// 2. Проверяем структуру таблицы
echo "Структура таблицы:\n";
$columns = $wpdb->get_results( "DESCRIBE " . $table_name );
foreach ( $columns as $col ) {
    echo "  - " . $col->Field . " (" . $col->Type . ")\n";
}
echo "\n";

// 3. Проверяем количество записей
$count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $table_name );
echo "Всего записей: " . $count . "\n\n";

// 4. Если записи есть, выводим последние
if ( $count > 0 ) {
    echo "Последние записи:\n";
    $results = $wpdb->get_results( "SELECT * FROM " . $table_name . " ORDER BY created_at DESC LIMIT 5" );
    
    foreach ( $results as $row ) {
        echo "\n  ID: " . $row->id . "\n";
        echo "  Correction ID: " . $row->correction_id . "\n";
        echo "  Player ID: " . $row->player_id . "\n";
        echo "  Tournament ID: " . $row->tournament_id . "\n";
        echo "  Goals Δ: " . $row->goals_delta . "\n";
        echo "  Is Applied: " . ( $row->is_applied ? '✓ Да' : '✗ Нет' ) . "\n";
        echo "  Created: " . $row->created_at . "\n";
    }
    echo "\n";
}

// 5. Проверяем индексы
echo "Индексы:\n";
$indexes = $wpdb->get_results( "SHOW INDEX FROM " . $table_name );
foreach ( $indexes as $idx ) {
    echo "  - " . $idx->Key_name . " (" . $idx->Column_name . ")\n";
}

echo "\n=== КОНЕЦ ПРОВЕРКИ ===\n\n";
