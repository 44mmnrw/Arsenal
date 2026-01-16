<?php
/**
 * Тест логирования в WordPress
 * 
 * Проверяет, что WP_DEBUG включен и логи работают
 */

require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

echo "\n=== ТЕСТ ЛОГИРОВАНИЯ ===\n\n";

// Проверяем настройки WP_DEBUG
echo "WP_DEBUG: " . ( defined( 'WP_DEBUG' ) && WP_DEBUG ? "✓ ВКЛ" : "❌ ВЫКЛ" ) . "\n";
echo "WP_DEBUG_LOG: " . ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? "✓ ВКЛ" : "❌ ВЫКЛ" ) . "\n";
echo "WP_DEBUG_DISPLAY: " . ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ? "✓ ВКЛ" : "❌ ВЫКЛ" ) . "\n\n";

// Получаем путь к файлу логов
$log_file = WP_CONTENT_DIR . '/debug.log';
echo "Путь к файлу логов: " . $log_file . "\n";
echo "Существует: " . ( file_exists( $log_file ) ? "✓ Да" : "❌ Нет" ) . "\n";
echo "Доступ для записи: " . ( is_writable( dirname( $log_file ) ) ? "✓ Да" : "❌ Нет" ) . "\n\n";

// Пишем тестовый лог
echo "Пишем тестовый лог...\n";
error_log( '[TEST] Это тестовое сообщение из проверки логирования - ' . current_time( 'mysql' ) );

// Проверяем, что лог был записан
if ( file_exists( $log_file ) ) {
    $content = file_get_contents( $log_file );
    $lines = explode( "\n", trim( $content ) );
    $last_line = end( $lines );
    
    echo "✓ Логи работают!\n";
    echo "Последняя строка лога: " . substr( $last_line, 0, 80 ) . "...\n\n";
} else {
    echo "❌ Файл логов не создался! Проверьте права доступа.\n\n";
}

// Пишем логи для AJAX обработчика
echo "Пишем тестовые логи для корректировок...\n";
error_log( '[Arsenal Corrections] TEST - player_id: E7167B1F, tournament_id: 71CFDAA6' );
error_log( '[Arsenal Corrections] TEST - Success: correction_id 12345678' );

echo "✓ Тестовые логи записаны\n";
echo "\nПроверьте содержимое: c:\\laragon\\www\\arsenal\\wp-content\\debug.log\n";
echo "\n=== КОНЕЦ ТЕСТА ===\n\n";
