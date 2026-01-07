<?php
/**
 * Полная очистка страницы "История" - удаляет контент, ревизии и метаданные
 * 
 * Использование:
 * php clean-history-page-full.php
 * 
 * @package Arsenal
 */

require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

if ( ! function_exists( 'get_the_ID' ) ) {
    die( "❌ Ошибка: WordPress не загружен\n" );
}

class History_Page_Full_Cleaner {

    public static function run() {
        global $wpdb;
        
        echo "\n=== ПОЛНАЯ очистка страницы История ===\n\n";

        // 1. Найти страницу
        $page = get_page_by_title( 'История' );
        if ( ! $page ) {
            $page = get_page_by_path( 'history' );
        }

        if ( ! $page ) {
            echo "❌ Ошибка: Страница 'История' не найдена\n\n";
            return false;
        }

        $page_id = $page->ID;
        echo "✅ Найдена страница: '{$page->post_title}' (ID: {$page_id})\n\n";

        // 2. Удалить все ревизии
        echo "🔄 Удаление всех ревизий...\n";
        $revisions = $wpdb->get_col( $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'revision'",
            $page_id
        ) );
        
        foreach ( $revisions as $revision_id ) {
            wp_delete_post( $revision_id, true );
        }
        echo "✅ Удалено ревизий: " . count( $revisions ) . "\n\n";

        // 3. Очистить весь контент
        echo "🔄 Очистка контента...\n";
        wp_update_post( array(
            'ID'           => $page_id,
            'post_content' => '',
        ) );
        echo "✅ Контент очищен\n\n";

        // 4. Удалить все metaданные блоков (старые некорректные)
        echo "🔄 Удаление старых метаданных...\n";
        $deleted = $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d",
            $page_id
        ) );
        echo "✅ Удалено metaданных: " . $deleted . "\n\n";

        // 5. Очистить кэш
        echo "🔄 Очистка кэша...\n";
        clean_post_cache( $page_id );
        wp_cache_flush();
        echo "✅ Кэш очищен\n\n";

        // 6. Проверить результат
        $clean_page = get_post( $page_id );
        echo "✅ Финальная проверка:\n";
        echo "   - Содержимое: '" . $clean_page->post_content . "'\n";
        echo "   - Статус: " . $clean_page->post_status . "\n";
        echo "   - Тип: " . $clean_page->post_type . "\n\n";

        // 7. Инструкции
        echo "📌 Что дальше:\n";
        echo "   1. Обновите страницу в браузере (Ctrl+Shift+Delete кэш)\n";
        echo "   2. Откройте страницу в админке\n";
        echo "   3. Вставьте паттерны заново через '+ > Patterns'\n";
        echo "   4. Сохраните\n\n";

        echo "=== ПОЛНАЯ очистка завершена! ===\n\n";
        return true;
    }
}

History_Page_Full_Cleaner::run();
