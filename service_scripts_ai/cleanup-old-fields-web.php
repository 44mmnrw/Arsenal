<?php
/**
 * Веб-версия скрипта очистки старых метаполей
 * 
 * Запуск в браузере:
 * http://arsenal.test/service_scripts_ai/cleanup-old-fields-web.php?action=cleanup&nonce=YOUR_NONCE
 *
 * @package Arsenal
 */

// Загрузить WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

// Проверка прав доступа
if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
    wp_die( '❌ Доступ запрещен. Необходимо быть администратором.' );
}

// Проверка действия
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

if ( $action !== 'cleanup' ) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Очистка старых метаполей</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                margin: 40px;
                background: #f1f1f1;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                max-width: 600px;
            }
            h1 { color: #23282d; }
            .button {
                background: #0073aa;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-size: 14px;
                text-decoration: none;
                display: inline-block;
            }
            .button:hover { background: #005a87; }
            .warning {
                background: #fff8e5;
                border-left: 4px solid #ffb900;
                padding: 15px;
                margin: 20px 0;
            }
            code {
                background: #f5f5f5;
                padding: 2px 5px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🗑️ Очистка старых метаполей</h1>
            
            <p>Этот скрипт удалит все остатки старой системы управления полями (ACF/Pods) из страницы "История".</p>
            
            <div class="warning">
                ⚠️ <strong>ВНИМАНИЕ!</strong><br>
                Эта операция необратима. Убедитесь что вы сделали резервную копию БД перед запуском.
            </div>
            
            <h3>Будут удалены следующие поля:</h3>
            <ul>
                <li>best_place</li>
                <li>best_results_block_title</li>
                <li>biggest_win</li>
                <li>history_intro</li>
                <li>intro_title</li>
                <li>timeline_title</li>
                <li>records_title</li>
                <li>titles_block_title</li>
                <li>stat_cards и другие repeater поля</li>
                <li>stat_card_1_title, stat_card_1_value ... stat_card_6 (ACF workaround)</li>
            </ul>
            
            <p><strong>Новая система использует:</strong> <code>_arsenal_stat_cards_json</code> (это будет сохранено)</p>
            
            <a href="?action=cleanup" class="button" onclick="return confirm('Вы уверены? Эта операция необратима!');">
                ✅ ЗАПУСТИТЬ ОЧИСТКУ
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ЗАПУСК ОЧИСТКИ
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Результаты очистки</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 40px;
            background: #f1f1f1;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 700px;
        }
        h1 { color: #23282d; }
        .log {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            overflow: auto;
            max-height: 500px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Результаты очистки</h1>
        <div class="log">
<?php

// Список полей для удаления
$meta_keys_to_delete = array(
    'best_place',
    'best_results_block_title',
    'biggest_win',
    'history_intro',
    'intro_title',
    'timeline_title',
    'records_title',
    'titles_block_title',
    'top_division_since',
    'stat_cards',
    '_pods_stat_cards',
    'timeline_events',
    'achievements_titles',
    'main_blocks',
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
);

echo "====================================================<br>";
echo "<strong>🗑️  ОЧИСТКА СТАРЫХ МЕТАПОЛЕЙ</strong><br>";
echo "====================================================<br><br>";

// Найти страницу
$page = get_page_by_title( 'История' );
if ( ! $page ) {
    $page = get_page_by_path( 'history' );
}

if ( ! $page ) {
    echo '<span class="error">❌ Ошибка: Страница "История" не найдена</span><br>';
    exit;
}

$page_id = $page->ID;

echo '📄 Найдена страница: "<strong>' . esc_html( $page->post_title ) . '</strong>" (ID: ' . $page_id . ')<br>';
echo '📋 Полей для удаления: ' . count( $meta_keys_to_delete ) . '<br><br>';

// Получить существующие метаполя
global $wpdb;
$existing_metas = $wpdb->get_col( $wpdb->prepare(
    "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d",
    $page_id
) );

$metas_to_delete = array_intersect( $meta_keys_to_delete, $existing_metas );

if ( empty( $metas_to_delete ) ) {
    echo '<span class="success">✅ Старых полей не найдено!</span><br>';
    echo 'Страница уже чистая.<br><br>';
} else {
    echo '🔍 Найдено полей для удаления: ' . count( $metas_to_delete ) . '<br>';
    echo '───────────────────────────────────────────────────<br>';

    $deleted_count = 0;
    foreach ( $metas_to_delete as $meta_key ) {
        $deleted = delete_post_meta( $page_id, $meta_key );
        
        if ( $deleted ) {
            echo '<span class="success">✅ Удалено:</span> ' . esc_html( $meta_key ) . '<br>';
            $deleted_count++;
        } else {
            echo '<span class="warning">⚠️  Не удалось удалить:</span> ' . esc_html( $meta_key ) . '<br>';
        }
    }

    echo '───────────────────────────────────────────────────<br><br>';

    echo '📊 <strong>РЕЗУЛЬТАТЫ:</strong><br>';
    echo '───────────────────────────────────────────────────<br>';
    echo '<span class="success">✅ Удалено полей: ' . $deleted_count . '</span><br>';
}

// Проверка оставшихся полей
$remaining_metas = $wpdb->get_col( $wpdb->prepare(
    "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_key",
    $page_id
) );

echo '📦 Осталось других полей: ' . count( $remaining_metas ) . '<br>';

if ( $remaining_metas ) {
    echo '<br>📋 Оставшиеся поля:<br>';
    foreach ( $remaining_metas as $meta ) {
        if ( ! in_array( $meta, array( '_edit_lock', '_edit_last' ), true ) ) {
            echo '  • ' . esc_html( $meta ) . '<br>';
        }
    }
}

// Проверка новой системы
$json_data = get_post_meta( $page_id, '_arsenal_stat_cards_json', true );
echo '<br>───────────────────────────────────────────────────<br>';
if ( $json_data ) {
    $cards = json_decode( $json_data, true );
    echo '<span class="success">✅ Новая система работает!</span><br>';
    echo '   JSON карточек: ' . count( $cards ) . ' шт.<br>';
} else {
    echo '<span class="warning">ℹ️  Карточек еще не добавлено</span><br>';
    echo '   Добавьте их через админ-панель<br>';
}

echo '<br>====================================================<br>';
echo '<strong>✅ ОЧИСТКА ЗАВЕРШЕНА!</strong><br>';
echo '====================================================<br><br>';

echo '💡 <strong>Что дальше:</strong><br>';
echo '   1. Перейти в admin → Pages → История<br>';
echo '   2. Должна быть видна только секция "Статистические карточки"<br>';
echo '   3. Добавить карточки если нужны<br><br>';

?>
        </div>
        <a href="http://arsenal.test/wp-admin/post.php?post=<?php echo $page_id; ?>&action=edit" class="button">
            ✅ Перейти к странице История в админке
        </a>
    </div>
</body>
</html>
<?php
