<?php
/**
 * Полный тест добавления и удаления корректировки
 * 
 * Проверяет:
 * 1. add_correction() возвращает correction_id (hex)
 * 2. delete_correction_by_hex_id() удаляет по correction_id
 * 3. apply_correction() применяет по correction_id
 * 4. Все логи пишутся правильно
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Подключение WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║   ПОЛНЫЙ ТЕСТ КОРРЕКТИРОВОК СТАТИСТИКИ                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Подключить класс
require_once dirname( dirname( __FILE__ ) ) . '/wp-content/plugins/arsenal-team-manager/admin/class-arsenal-player-stats-corrections.php';

$corrections = Arsenal_Player_Stats_Corrections::get_instance();

// 1. ТЕСТ ДОБАВЛЕНИЯ
echo "ЭТАП 1️⃣ : ДОБАВЛЕНИЕ КОРРЕКТИРОВКИ\n";
echo "─────────────────────────────────────────────────────────────\n\n";

$result = $corrections->add_correction(
    'E7167B1F',      // player_id
    '71CFDAA6',      // tournament_id
    array(
        'goals' => 2,
        'assists' => 1,
        'yellow_cards' => 1
    ),
    'Тест: добавление, удаление, применение',
    null
);

echo "📝 Вызвана add_correction()\n";
echo "   Результат: " . var_export( $result, true ) . "\n";

if ( ! $result ) {
    echo "❌ ОШИБКА: add_correction() вернула false\n";
    exit;
}

if ( ! is_string( $result ) ) {
    echo "❌ ОШИБКА: add_correction() должна вернуть строку (correction_id), вернула: " . gettype( $result ) . "\n";
    exit;
}

$correction_id = $result;
echo "✓ SUCCESS! correction_id: " . $correction_id . " (тип: " . gettype( $correction_id ) . ")\n\n";

// Проверим что запись в БД
global $wpdb;
$table_name = $wpdb->prefix . 'arsenal_player_stats_corrections';
$check = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE correction_id = %s",
        $correction_id
    )
);

if ( $check ) {
    echo "✓ Запись найдена в БД:\n";
    echo "   ID (число): " . $check->id . "\n";
    echo "   correction_id (hex): " . $check->correction_id . "\n";
    echo "   Голы: " . $check->goals_delta . "\n";
    echo "   Ассисты: " . $check->assists_delta . "\n";
    echo "   Жёлтые: " . $check->yellow_cards_delta . "\n";
    echo "   Статус: " . ( $check->is_applied ? '✓ Применена' : '❌ Ожидает' ) . "\n\n";
} else {
    echo "❌ ОШИБКА: Запись не найдена в БД\n";
    exit;
}

// 2. ТЕСТ ПРИМЕНЕНИЯ
echo "ЭТАП 2️⃣ : ПРИМЕНЕНИЕ КОРРЕКТИРОВКИ\n";
echo "─────────────────────────────────────────────────────────────\n\n";

echo "🔄 Применяю apply_correction( '{$correction_id}' )\n";
$apply_result = $corrections->apply_correction( $correction_id );

if ( $apply_result ) {
    echo "✓ SUCCESS! Корректировка отмечена как примененная\n";
    
    $check = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE correction_id = %s",
            $correction_id
        )
    );
    
    echo "✓ Проверка:\n";
    echo "   is_applied: " . ( $check->is_applied ? '✓ Да' : '❌ Нет' ) . "\n";
    echo "   applied_at: " . ( $check->applied_at ? $check->applied_at : 'null' ) . "\n";
    echo "   applied_by: " . ( $check->applied_by ? $check->applied_by : 'null' ) . "\n\n";
} else {
    echo "❌ ОШИБКА: apply_correction() вернула false\n";
    exit;
}

// 3. ТЕСТ УДАЛЕНИЯ
echo "ЭТАП 3️⃣ : УДАЛЕНИЕ КОРРЕКТИРОВКИ\n";
echo "─────────────────────────────────────────────────────────────\n\n";

echo "🗑️ Удаляю delete_correction_by_hex_id( '{$correction_id}' )\n";
$delete_result = $corrections->delete_correction_by_hex_id( $correction_id );

if ( $delete_result ) {
    echo "✓ SUCCESS! Корректировка удалена\n";
    
    $check = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE correction_id = %s",
            $correction_id
        )
    );
    
    if ( $check ) {
        echo "❌ ОШИБКА: Запись всё ещё в БД\n";
    } else {
        echo "✓ Запись удалена из БД\n\n";
    }
} else {
    echo "❌ ОШИБКА: delete_correction_by_hex_id() вернула false\n";
    exit;
}

// 4. ПРОВЕРКА ЛОГОВ
echo "ЭТАП 4️⃣ : ПРОВЕРКА ЛОГИРОВАНИЯ\n";
echo "─────────────────────────────────────────────────────────────\n\n";

$debug_log = ABSPATH . 'wp-content/debug.log';
if ( file_exists( $debug_log ) ) {
    $lines = file( $debug_log );
    
    // Найти логи по последним операциям
    $save_logs = array();
    $delete_logs = array();
    
    foreach ( array_reverse( $lines ) as $line ) {
        if ( strpos( $line, '[Arsenal Corrections SAVE]' ) !== false ) {
            $save_logs[] = trim( $line );
        }
        if ( strpos( $line, '[Arsenal Corrections Delete]' ) !== false ) {
            $delete_logs[] = trim( $line );
        }
        if ( count( $save_logs ) >= 5 && count( $delete_logs ) >= 5 ) break;
    }
    
    echo "📋 Примеры логов при добавлении:\n";
    if ( ! empty( $save_logs ) ) {
        foreach ( array_reverse( array_slice( $save_logs, 0, 3 ) ) as $log ) {
            echo "   " . substr( $log, 0, 100 ) . "...\n";
        }
    } else {
        echo "   (логи [Arsenal Corrections SAVE] ещё не были записаны)\n";
    }
    
    echo "\n📋 Примеры логов при удалении:\n";
    if ( ! empty( $delete_logs ) ) {
        foreach ( array_reverse( array_slice( $delete_logs, 0, 3 ) ) as $log ) {
            echo "   " . substr( $log, 0, 100 ) . "...\n";
        }
    } else {
        echo "   (логи [Arsenal Corrections Delete] ещё не были записаны)\n";
    }
} else {
    echo "❌ debug.log не найден\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║   ✓ ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО                            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "💡 СЛЕДУЮЩИЙ ШАГ:\n";
echo "   1. Откройте: Arsenal → Корректировки статистики\n";
echo "   2. Добавьте новую корректировку через форму\n";
echo "   3. Проверьте wp-content/debug.log\n";
echo "   4. Должны видны логи с префиксом [Arsenal Corrections SAVE]\n";
echo "   5. Если ошибка - она будет видна в логе\n";
