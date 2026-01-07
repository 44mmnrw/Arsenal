<?php
/**
 * Удаление старых произвольных полей (meta) из страницы истории
 * 
 * Скрипт для удаления meta полей которые больше не используются
 */

// Загружаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

// ID страницы "История клуба" (замени на нужный ID)
$page_id = (int) $_GET['page_id'] ?? 0;

if ( ! $page_id ) {
    die( 'Укажи page_id в URL: ?page_id=123' );
}

// Проверяем что это админ
if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Доступ запрещен' );
}

// Список meta ключей для удаления
$meta_keys_to_delete = array(
    'best_place',
    'best_results_block_title',
    'biggest_win',
    'history_intro',
    'intro_title',
    'records_title',
    'stadiums_title',
    'stat_card_1_title',
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
    'timeline_events',
    'achievements_titles',
    'main_blocks',
    'stat_cards',
    'titles_block_title',
    'top_division_since',
);

$deleted_count = 0;

foreach ( $meta_keys_to_delete as $meta_key ) {
    $deleted = delete_post_meta( $page_id, $meta_key );
    if ( $deleted ) {
        $deleted_count++;
        echo "✓ Удалено: <strong>$meta_key</strong><br>";
    } else {
        echo "- Не найдено: <em>$meta_key</em><br>";
    }
}

echo "<hr>";
echo "<strong>Итого удалено полей: $deleted_count</strong><br>";
echo "<a href='post.php?post=" . $page_id . "&action=edit'>← Вернуться на страницу редактирования</a>";
