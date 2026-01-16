<?php
/**
 * Тестирование удаления корректировки
 * 
 * Скрипт для проверки функции delete_correction_by_hex_id()
 * Использует реальную hex correction_id (строку из 8 символов)
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Подключение WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

echo "=== Тест удаления корректировки ===\n\n";

// Подключить класс
require_once dirname( dirname( __FILE__ ) ) . '/wp-content/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections.php';

$corrections = Arsenal_Player_Stats_Corrections::get_instance();

// Получить первую неприменённую корректировку для тестирования
global $wpdb;
$table_name = $wpdb->prefix . 'arsenal_player_stats_corrections';

$test_correction = $wpdb->get_row( "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1" );

if ( ! $test_correction ) {
    echo "❌ Нет корректировок для тестирования\n";
    exit;
}

echo "📋 Найденная корректировка:\n";
echo "   ID (число): " . $test_correction->id . "\n";
echo "   Correction ID (hex): " . $test_correction->correction_id . "\n";
echo "   Игрок: " . $test_correction->player_id . "\n";
echo "   Турнир: " . $test_correction->tournament_id . "\n";
echo "   Голы: " . $test_correction->goals_delta . "\n";
echo "   Статус: " . ( $test_correction->is_applied ? 'Применена' : 'Ожидает применения' ) . "\n\n";

// Тест удаления по correction_id
echo "🔄 Попытка удалить по correction_id (hex)...\n";
$result = $corrections->delete_correction_by_hex_id( $test_correction->correction_id );

if ( $result ) {
    echo "✓ SUCCESS! Корректировка удалена\n";
    
    // Проверка что запись удалена
    $check = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE correction_id = %s",
            $test_correction->correction_id
        )
    );
    
    if ( ! $check ) {
        echo "✓ Подтверждено: запись удалена из базы\n";
    } else {
        echo "❌ ОШИБКА: запись всё ещё в базе!\n";
    }
} else {
    echo "❌ ОШИБКА: удаление не удалось\n";
    echo "   Последняя ошибка MySQL: " . $wpdb->last_error . "\n";
}

echo "\n📊 Статистика таблицы:\n";
$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
echo "   Всего корректировок: " . $count . "\n";

$unapplied = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE is_applied = 0" );
echo "   Ожидают применения: " . $unapplied . "\n";

$applied = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE is_applied = 1" );
echo "   Применено: " . $applied . "\n";
