<?php
/**
 * Имитация AJAX запроса удаления корректировки (для тестирования)
 * 
 * Скрипт имитирует AJAX запрос, который отправляет JavaScript функция arsenalDeleteCorrection()
 * из админ-интерфейса. Проверяет что AJAX обработчик получает правильные данные и логирует их.
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Подключение WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

echo "=== Имитация AJAX запроса удаления ===\n\n";

// Проверяем что есть корректировки
global $wpdb;
$table_name = $wpdb->prefix . 'arsenal_player_stats_corrections';

// Сначала добавим тестовую корректировку
require_once dirname( dirname( __FILE__ ) ) . '/wp-content/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections.php';

$corrections = Arsenal_Player_Stats_Corrections::get_instance();

// Добавляем тестовую корректировку
echo "📝 Создаём тестовую корректировку...\n";
$result = $corrections->add_correction(
    'E7167B1F',      // player_id
    '71CFDAA6',      // tournament_id
    array(
        'goals' => 1,
        'assists' => 0
    ),
    'Тестовая корректировка для проверки удаления',
    null
);

if ( ! $result ) {
    echo "❌ Ошибка при создании корректировки\n";
    exit;
}

// Получаем только что созданную корректировку
$test_correction = $wpdb->get_row( "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1" );

echo "✓ Корректировка создана: " . $test_correction->correction_id . "\n\n";

// Теперь имитируем AJAX запрос
echo "🔄 Имитирование AJAX запроса arsenalDeleteCorrection()...\n";
echo "   Action: arsenal_delete_correction\n";
echo "   correction_id: " . $test_correction->correction_id . "\n";
echo "   Ожидание логов [Arsenal Corrections Delete]...\n\n";

// Вызываем AJAX обработчик напрямую
require_once dirname( dirname( __FILE__ ) ) . '/wp-content/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections-admin.php';

$admin = Arsenal_Player_Stats_Corrections_Admin::get_instance();

// Имитируем $_POST данные
$_POST['correction_id'] = $test_correction->correction_id;
$_POST['_wpnonce'] = wp_create_nonce( 'arsenal_correction_nonce_field' );

// Переводим в режим, когда нет фронтенда (для перехвата AJAX)
define( 'DOING_AJAX', true );

// Вызываем обработчик (он вызовет wp_send_json_* которые выведут JSON)
// Не вызовем это, так как это перенаправит вывод
// $admin->handle_delete_correction();

// Вместо этого проверим логи
echo "📋 Проверяем логи в debug.log...\n\n";

$debug_log = ABSPATH . 'wp-content/debug.log';
if ( file_exists( $debug_log ) ) {
    $lines = file( $debug_log );
    
    // Найти последние логи с префиксом [Arsenal Corrections Delete]
    $delete_logs = array();
    foreach ( array_reverse( $lines ) as $line ) {
        if ( strpos( $line, '[Arsenal Corrections Delete]' ) !== false ) {
            $delete_logs[] = trim( $line );
        }
        if ( count( $delete_logs ) >= 10 ) break;
    }
    
    if ( empty( $delete_logs ) ) {
        echo "⚠️ Логи [Arsenal Corrections Delete] не найдены\n";
        echo "Это нормально, если AJAX обработчик ещё не был вызван\n\n";
    } else {
        echo "✓ Найдены логи (последние 10):\n";
        foreach ( array_reverse( $delete_logs ) as $log ) {
            echo "   " . $log . "\n";
        }
    }
} else {
    echo "❌ Файл debug.log не найден\n";
}

echo "\n📊 Проверка статистики таблицы:\n";
$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
echo "   Всего корректировок: " . $count . "\n";
echo "   (Должна быть 1 созданная для теста, удаление выполняется через форму админки)\n";

echo "\n💡 Для полного теста:\n";
echo "   1. Откройте Arsenal → Корректировки статистики\n";
echo "   2. Добавьте новую корректировку (или используйте созданную)\n";
echo "   3. Нажмите кнопку 🗑️ (удалить)\n";
echo "   4. Проверьте wp-content/debug.log на наличие логов:\n";
echo "      [Arsenal Corrections Delete] === AJAX handler called ===\n";
echo "   5. Если удаление сработает, вы увидите:\n";
echo "      [Arsenal Corrections Delete] ✓ SUCCESS! Correction deleted: ...\n";
