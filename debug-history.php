<?php
/**
 * Скрипт отладки сохранения истории
 */

// Загружаем WordPress
require_once __DIR__ . '/wp-load.php';

echo "=== ОТЛАДКА ИСТОРИИ КЛУБА ===\n\n";

// 1. Проверяем, есть ли данные в wp_options
$history_data = get_option('arsenal_history_data');
echo "1. Данные в wp_options (arsenal_history_data):\n";
if ($history_data) {
    echo "✅ Данные ЕСТЬ:\n";
    echo print_r(json_decode($history_data, true), true);
} else {
    echo "❌ Данные ОТСУТСТВУЮТ\n";
}

echo "\n\n2. Проверяем класс Arsenal_History_Manager:\n";
if (class_exists('Arsenal_History_Manager')) {
    echo "✅ Класс найден\n";
    $history = Arsenal_History_Manager::get_history();
    echo "Результат get_history():\n";
    echo print_r($history, true);
} else {
    echo "❌ Класс не найден\n";
}

echo "\n\n3. Проверяем класс Arsenal_History_Admin:\n";
if (class_exists('Arsenal_History_Admin')) {
    echo "✅ Класс найден\n";
} else {
    echo "❌ Класс не найден\n";
}

echo "\n\n4. Проверяем текущего пользователя:\n";
$user = wp_get_current_user();
echo "ID: " . $user->ID . "\n";
echo "Логин: " . $user->user_login . "\n";
echo "Может ли manage_options: " . (current_user_can('manage_options') ? 'ДА' : 'НЕТ') . "\n";

echo "\n\n5. Попытка сохранить тестовые данные:\n";
$test_data = array(
    'title' => 'Тест ' . date('Y-m-d H:i:s'),
    'description' => 'Тестовое описание',
    'scale' => array(),
    'title_second' => 'Рекорды и достижения',
    'records' => array(),
    'achievements' => array(),
    'title_third' => 'Домашние стадионы',
    'additional_cards' => array(),
);

$result = Arsenal_History_Manager::save_history($test_data);
echo "Результат save_history: " . ($result ? 'TRUE' : 'FALSE') . "\n";

// Перепроверяем
$history_data = get_option('arsenal_history_data');
echo "Данные после сохранения:\n";
echo print_r(json_decode($history_data, true), true);

echo "\n\n=== КОНЕЦ ОТЛАДКИ ===\n";
