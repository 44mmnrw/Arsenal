<?php
/**
 * Скрипт очистки старых метаполей (ACF/Pods)
 *
 * Удаляет все остатки старой системы управления полями из страницы "История"
 * 
 * Использование:
 * - Запустить из WordPress: php service_scripts_ai/cleanup-old-fields.php
 * - Или через wp-cli: wp eval 'include("service_scripts_ai/cleanup-old-fields.php");'
 *
 * @package Arsenal
 * @since 1.0.0
 */

// Безопасность
if ( ! function_exists( 'get_page_by_title' ) ) {
    die( '❌ WordPress не загружен' );
}

// Список полей для удаления (старые поля ACF/Pods)
$meta_keys_to_delete = array(
    'best_place',                   // Было: best_place_field
    'best_results_block_title',     // Было: best_results_block_title_field
    'biggest_win',                  // Было: biggest_win_field
    'history_intro',                // Было: history_intro_field
    'intro_title',                  // Было: intro_title_field
    'timeline_title',               // Было: timeline_title_field
    'records_title',                // Было: records_title_field
    'titles_block_title',           // Было: titles_block_title_field
    'stat_cards',                   // Было: Pods repeater field
    '_pods_stat_cards',             // Было: Pods meta field
    'timeline_events',              // Было: timeline_events repeater
    'achievements_titles',          // Было: achievements_titles repeater
    'main_blocks',                  // Было: main_blocks repeater
    'stat_card_1_title',            // Было: ACF workaround field #1
    'stat_card_1_value',
    'stat_card_2_title',
    'stat_card_2_value',
    'stat_card_3_title',
    'stat_card_3_value',
    'stat_card_4_title',
    'stat_card_4_value',
    'stat_card_5_title',
    'stat_card_5_value',
    'stat_card_6_title',
    'stat_card_6_value',
);

// Найти страницу "История"
$page = get_page_by_title( 'История' );
if ( ! $page ) {
    $page = get_page_by_path( 'history' );
}

if ( ! $page ) {
    echo "❌ Ошибка: Страница 'История' не найдена\n";
    echo "   Создайте страницу с названием 'История' или slug 'history'\n";
    exit;
}

$page_id = $page->ID;

echo "====================================================\n";
echo "🗑️  ОЧИСТКА СТАРЫХ МЕТАПОЛЕЙ\n";
echo "====================================================\n\n";

echo "📄 Найдена страница: '{$page->post_title}' (ID: {$page_id})\n";
echo "📋 Полей для удаления: " . count( $meta_keys_to_delete ) . "\n\n";

// Получить все метаполя страницы для проверки
global $wpdb;
$existing_metas = $wpdb->get_col( $wpdb->prepare(
    "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d",
    $page_id
) );

// Фильтровать только те поля, которые нужно удалить
$metas_to_delete = array_intersect( $meta_keys_to_delete, $existing_metas );

if ( empty( $metas_to_delete ) ) {
    echo "✅ Старых полей не найдено!\n";
    echo "   Страница уже чистая.\n\n";
    exit;
}

echo "🔍 Найдено полей для удаления: " . count( $metas_to_delete ) . "\n";
echo "───────────────────────────────────────────────────\n";

// Удалить каждое поле
$deleted_count = 0;
foreach ( $metas_to_delete as $meta_key ) {
    $deleted = delete_post_meta( $page_id, $meta_key );
    
    if ( $deleted ) {
        echo "✅ Удалено: {$meta_key}\n";
        $deleted_count++;
    } else {
        echo "⚠️  Не удалось удалить: {$meta_key}\n";
    }
}

echo "───────────────────────────────────────────────────\n\n";

// Проверка результатов
$remaining_metas = $wpdb->get_col( $wpdb->prepare(
    "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d",
    $page_id
) );

echo "📊 РЕЗУЛЬТАТЫ:\n";
echo "───────────────────────────────────────────────────\n";
echo "✅ Удалено полей: {$deleted_count}\n";
echo "📦 Осталось других полей: " . count( $remaining_metas ) . "\n";

if ( $remaining_metas ) {
    echo "\n📋 Оставшиеся поля:\n";
    foreach ( $remaining_metas as $meta ) {
        // Пропустить системные поля WordPress
        if ( ! in_array( $meta, array( '_edit_lock', '_edit_last' ), true ) ) {
            echo "  • {$meta}\n";
        }
    }
}

echo "\n====================================================\n";
echo "✅ ОЧИСТКА ЗАВЕРШЕНА!\n";
echo "====================================================\n\n";

echo "💡 Помните:\n";
echo "   • Новая система использует JSON в '_arsenal_stat_cards_json'\n";
echo "   • Старые поля больше не используются\n";
echo "   • Все данные теперь хранятся в JSON формате\n\n";

echo "🧪 Проверка работы:\n";
echo "   1. Перейти в admin: Pages → История\n";
echo "   2. Должна быть видна только секция 'Статистические карточки'\n";
echo "   3. Запустить: php service_scripts_ai/test-stat-cards-manager.php\n\n";
