<?php
/**
 * Временный скрипт: сброс "paused_plugins" для Arsenal Team Manager
 * Удали этот файл сразу после использования!
 */

// Загружаем WordPress
define( 'ABSPATH', dirname( dirname( dirname( __DIR__ ) ) ) . '/' );
// Простой прямой путь
$wp_load = realpath( __DIR__ . '/../../..' ) . '/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
    die( 'wp-load.php not found at: ' . $wp_load );
}

require_once $wp_load;

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Access denied. Please log in as admin first: <a href="/wp-admin">Login</a>' );
}

$plugin_slug = 'arsenal-team-manager/arsenal-team-manager.php';

// 1. Удаляем из paused_plugins
$paused = get_option( 'paused_plugins', array() );
$had_paused = isset( $paused[ $plugin_slug ] );
if ( $had_paused ) {
    unset( $paused[ $plugin_slug ] );
    update_option( 'paused_plugins', $paused );
    echo '<p style="color:green">✓ Удалён из paused_plugins</p>';
} else {
    echo '<p>— В paused_plugins плагина нет</p>';
}

// 2. Удаляем recovery mode transient
delete_transient( 'wp_paused_plugins' );
delete_option( '_site_transient_update_plugins' );

// 3. Показываем текущий статус
$active = get_option( 'active_plugins', array() );
$is_active = in_array( $plugin_slug, $active );
echo '<p>Активен сейчас: ' . ( $is_active ? '<b style="color:green">ДА</b>' : '<b style="color:red">НЕТ</b>' ) . '</p>';

// 4. Пробуем активировать
if ( ! $is_active ) {
    $result = activate_plugin( $plugin_slug );
    if ( is_wp_error( $result ) ) {
        echo '<p style="color:red">Ошибка активации: ' . esc_html( $result->get_error_message() ) . '</p>';
    } else {
        echo '<p style="color:green">✓ Плагин активирован!</p>';
    }
}

echo '<p><b>Готово. <a href="/wp-admin/plugins.php">Перейти к плагинам</a> | <a href="?delete=1">Удалить этот файл</a></b></p>';

// Самоудаление
if ( isset( $_GET['delete'] ) ) {
    unlink( __FILE__ );
    echo '<p>Файл удалён.</p>';
}
