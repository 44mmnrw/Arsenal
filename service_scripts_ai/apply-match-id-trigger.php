<?php
/**
 * Скрипт для применения SQL триггера автогенерации match_id в БД
 * 
 * Использование: php apply-match-id-trigger.php
 * 
 * Триггер автоматически генерирует уникальный match_id при вставке новой записи
 * Формат: MATCH_[unix_timestamp]_[random_hex]
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Загрузить WordPress
require_once __DIR__ . '/../../wp-load.php';

global $wpdb;

echo "=== Применение триггера автогенерации match_id ===\n\n";

// SQL триггер
$trigger_sql = "
DROP TRIGGER IF EXISTS `trg_generate_match_id`;

DELIMITER $$

CREATE TRIGGER `trg_generate_match_id`
BEFORE INSERT ON `wp_arsenal_matches`
FOR EACH ROW
BEGIN
    IF NEW.match_id IS NULL THEN
        SET NEW.match_id = CONCAT('MATCH_', UNIX_TIMESTAMP(NOW()), '_', LOWER(HEX(RAND() * 0xFFFFFF)));
    END IF;
END$$

DELIMITER ;
";

// Разделить триггер на отдельные команды
$trigger_commands = array(
    "DROP TRIGGER IF EXISTS `trg_generate_match_id`",
    "CREATE TRIGGER `trg_generate_match_id`
BEFORE INSERT ON `wp_arsenal_matches`
FOR EACH ROW
BEGIN
    IF NEW.match_id IS NULL THEN
        SET NEW.match_id = CONCAT('MATCH_', UNIX_TIMESTAMP(NOW()), '_', LOWER(HEX(RAND() * 0xFFFFFF)));
    END IF;
END"
);

// Применить каждую команду
$success = true;
foreach ( $trigger_commands as $index => $command ) {
    $result = $wpdb->query( $command );
    
    if ( $result === false ) {
        echo "❌ Ошибка при выполнении команды " . ( $index + 1 ) . ":\n";
        echo "   Команда: " . trim( $command ) . "\n";
        echo "   Ошибка: " . $wpdb->last_error . "\n\n";
        $success = false;
    } else {
        echo "✅ Команда " . ( $index + 1 ) . " выполнена успешно\n";
    }
}

if ( $success ) {
    echo "\n✅ Триггер успешно применен!\n";
    echo "   Триггер будет автоматически генерировать match_id при вставке новых матчей.\n";
    echo "   Формат ID: MATCH_[unix_timestamp]_[random_hex]\n";
    exit( 0 );
} else {
    echo "\n❌ Ошибка при применении триггера\n";
    echo "   Пожалуйста, проверьте логи и попробуйте снова.\n";
    exit( 1 );
}
