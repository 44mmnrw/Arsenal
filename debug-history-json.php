<?php
/**
 * Скрипт отладки JSON в истории
 */

// Загружаем WordPress
require_once __DIR__ . '/wp-load.php';

echo "=== ОТЛАДКА JSON В ИСТОРИИ ===\n\n";

// Получаем текущие данные
$history = Arsenal_History_Manager::get_history();

echo "1. Текущие данные в wp_options:\n";
echo "title: " . $history['title'] . "\n";
echo "description: " . substr($history['description'], 0, 50) . "...\n";
echo "scale тип: " . gettype($history['scale']) . "\n";
echo "records тип: " . gettype($history['records']) . "\n";
echo "records: " . print_r($history['records'], true) . "\n";
echo "achievements тип: " . gettype($history['achievements']) . "\n";
echo "achievements: " . print_r($history['achievements'], true) . "\n";

echo "\n\n2. Попытка сохранить тестовый JSON:\n";

$test_data = array(
    'title'        => 'История клуба',
    'description'  => '<p>Тест описание</p>',
    'scale'        => '[]',
    'title_second' => 'Рекорды и достижения',
    'records'      => json_encode([
        [
            'title' => 'Титулы',
            'items' => [
                'Чемпион Первой лиги 2021',
                'Чемпион Первой лиги 2023'
            ]
        ]
    ], JSON_UNESCAPED_UNICODE),
    'achievements' => json_encode([
        [
            'title' => 'Награды',
            'items' => [
                'Лучший клуб года',
                'Лучшая молодежь'
            ]
        ]
    ], JSON_UNESCAPED_UNICODE),
    'title_third'      => 'Домашние стадионы',
    'additional_cards' => '[]',
);

echo "Отправляем данные:\n";
echo "records (JSON строка): " . $test_data['records'] . "\n";
echo "achievements (JSON строка): " . $test_data['achievements'] . "\n";

// Сохраняем
$result = Arsenal_History_Manager::save_history($test_data);
echo "\nРезультат save_history: " . ($result ? 'TRUE' : 'FALSE') . "\n";

// Проверяем, что сохранилось
$history_after = Arsenal_History_Manager::get_history();

echo "\n\n3. Данные ПОСЛЕ сохранения:\n";
echo "records тип: " . gettype($history_after['records']) . "\n";
echo "records содержимое:\n";
echo print_r($history_after['records'], true);

echo "\nachievements тип: " . gettype($history_after['achievements']) . "\n";
echo "achievements содержимое:\n";
echo print_r($history_after['achievements'], true);

// Проверим сырые данные в wp_options
echo "\n\n4. Сырые JSON в wp_options:\n";
$raw_json = get_option('arsenal_history_data');
echo "Сырая строка:\n";
echo $raw_json . "\n";

$decoded = json_decode($raw_json, true);
echo "\nDecoded records:\n";
echo print_r($decoded['records'], true);

echo "\nDecoded achievements:\n";
echo print_r($decoded['achievements'], true);

echo "\n\n=== КОНЕЦ ОТЛАДКИ ===\n";
