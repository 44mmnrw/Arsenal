<?php
/**
 * Скрипт тестирования менеджера статистических карточек
 * 
 * Использование:
 * - Добавить в WordPress и запустить один раз
 * - Или запустить из консоли PHP
 * 
 * @package Arsenal
 */

// Для локального тестирования (если скрипт вне WordPress)
// require_once( 'wp-load.php' );

// Проверка WordPress
if ( ! function_exists( 'get_the_ID' ) ) {
    die( 'WordPress не загружен. Пожалуйста, запустите скрипт из корня сайта.' );
}

// Подключение менеджера
require_once get_template_directory() . '/inc/class-stat-cards-manager.php';

/**
 * Тестирование менеджера карточек
 */
class Stat_Cards_Test {

    public static function run() {
        echo "=== Тест менеджера статистических карточек ===\n\n";

        // 1. Найти страницу Historia
        $page = get_page_by_title( 'История' );
        if ( ! $page ) {
            $page = get_page_by_path( 'history' );
        }

        if ( ! $page ) {
            echo "❌ Ошибка: Страница 'История' не найдена\n";
            echo "   Создайте страницу с названием 'История' или slug 'history'\n";
            return;
        }

        $page_id = $page->ID;
        echo "✅ Найдена страница: '{$page->post_title}' (ID: {$page_id})\n\n";

        // 2. Очистить карточки (для чистого теста)
        echo "📝 Очистка старых карточек...\n";
        Arsenal_Stat_Cards_Manager::clear_all( $page_id );
        echo "✅ Очистка завершена\n\n";

        // 3. Добавить тестовые карточки
        echo "📝 Добавление тестовых карточек...\n";
        Arsenal_Stat_Cards_Manager::add_card( '15', 'Побед в сезоне', $page_id );
        Arsenal_Stat_Cards_Manager::add_card( '3', 'Голов за сезон', $page_id );
        Arsenal_Stat_Cards_Manager::add_card( '42', 'Матчей сыграно', $page_id );
        Arsenal_Stat_Cards_Manager::add_card( '10 место', 'Финальное место в чемпионате', $page_id );
        echo "✅ Добавлено 4 карточки\n\n";

        // 4. Получить карточки
        echo "📝 Получение карточек...\n";
        $cards = Arsenal_Stat_Cards_Manager::get_cards( $page_id );
        echo "✅ Получено карточек: " . count( $cards ) . "\n\n";

        // 5. Вывести карточки
        echo "📋 Список карточек:\n";
        foreach ( $cards as $index => $card ) {
            echo sprintf(
                "  [%d] %s = %s\n",
                $index,
                $card['stat_title'],
                $card['stat_value']
            );
        }
        echo "\n";

        // 6. Обновить карточку
        echo "📝 Обновление карточки #0...\n";
        Arsenal_Stat_Cards_Manager::update_card( 0, '16', 'Побед в сезоне (обновлено)', $page_id );
        $updated_cards = Arsenal_Stat_Cards_Manager::get_cards( $page_id );
        echo "✅ Обновлено. Новое значение: " . $updated_cards[0]['stat_title'] . "\n\n";

        // 7. Удалить карточку
        echo "📝 Удаление карточки #1...\n";
        Arsenal_Stat_Cards_Manager::delete_card( 1, $page_id );
        $final_cards = Arsenal_Stat_Cards_Manager::get_cards( $page_id );
        echo "✅ Удалено. Осталось карточек: " . count( $final_cards ) . "\n\n";

        // 8. Вывести итоговый список
        echo "📋 Итоговый список карточек:\n";
        foreach ( $final_cards as $index => $card ) {
            echo sprintf(
                "  [%d] %s = %s\n",
                $index,
                $card['stat_title'],
                $card['stat_value']
            );
        }
        echo "\n";

        // 9. Проверить сохранение в БД
        echo "📝 Проверка сохранения в БД...\n";
        global $wpdb;
        $meta = $wpdb->get_var( $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} 
             WHERE post_id = %d AND meta_key = '_arsenal_stat_cards_json' LIMIT 1",
            $page_id
        ) );

        if ( $meta ) {
            echo "✅ Данные сохранены в БД\n";
            echo "   Размер JSON: " . strlen( $meta ) . " байт\n";
            echo "   Содержимое: " . substr( $meta, 0, 100 ) . "...\n";
        } else {
            echo "❌ Данные НЕ найдены в БД\n";
        }

        echo "\n=== Тест завершен успешно! ===\n";
    }
}

// Запустить тест
Stat_Cards_Test::run();
