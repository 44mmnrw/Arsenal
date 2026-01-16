<?php
/**
 * Скрипт миграции: обновление tournament_id для матчей
 * Обновляем 450D6F35 → 71CFDAA6 (основная лига)
 * Обновляем A87D2B20 → E4DE8DC0 (если нужно)
 */

require 'wp-load.php';

global $wpdb;

echo "=== Миграция tournament_id ===\n\n";

// Проверяем текущее состояние
echo "ДО МИГРАЦИИ:\n";
$before = $wpdb->get_results(
    "SELECT tournament_id, COUNT(*) as count FROM {$wpdb->prefix}arsenal_matches GROUP BY tournament_id"
);
foreach ($before as $row) {
    echo "  {$row->tournament_id}: {$row->count} матчей\n";
}

// Обновляем tournament_id
echo "\n\nВыполняем обновление:\n";

$result1 = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->prefix}arsenal_matches SET tournament_id = %s WHERE tournament_id = %s",
        '71CFDAA6',
        '450D6F35'
    )
);

echo "✓ Обновлено 450D6F35 → 71CFDAA6: $result1 матчей\n";

$result2 = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->prefix}arsenal_matches SET tournament_id = %s WHERE tournament_id = %s",
        'E4DE8DC0',
        'A87D2B20'
    )
);

echo "✓ Обновлено A87D2B20 → E4DE8DC0: $result2 матчей\n";

// Проверяем результат
echo "\n\nПОСЛЕ МИГРАЦИИ:\n";
$after = $wpdb->get_results(
    "SELECT tournament_id, COUNT(*) as count FROM {$wpdb->prefix}arsenal_matches GROUP BY tournament_id ORDER BY tournament_id"
);
foreach ($after as $row) {
    echo "  {$row->tournament_id}: {$row->count} матчей\n";
}

echo "\n✓ Миграция завершена!\n";
