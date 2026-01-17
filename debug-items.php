<?php
/**
 * Скрипт отладки сохранения items
 */

require_once __DIR__ . '/wp-load.php';

echo "=== ОТЛАДКА СОХРАНЕНИЯ ITEMS ===\n\n";

// Симулируем отправку формы с items
$_POST = array(
    'action' => 'arsenal_save_history',
    '_wpnonce' => wp_create_nonce('arsenal_save_history'),
    'title' => 'История клуба',
    'description' => 'Описание',
    'scale' => '[]',
    'title_second' => 'Рекорды и достижения',
    'records' => json_encode([
        [
            'title' => 'Титулы',
            'style' => 'primary',
            'icon' => '🏆',
            'items' => [
                'Чемпион 2021',
                'Чемпион 2023',
                'Третий элемент'
            ]
        ],
        [
            'title' => 'Результаты',
            'style' => 'white',
            'icon' => '📊',
            'items' => [
                'Первое место',
                'Второе место'
            ]
        ]
    ], JSON_UNESCAPED_UNICODE),
    'achievements' => '[]',
    'title_third' => 'Стадионы',
    'additional_cards' => '[]',
);

echo "1. JSON из POST['records']:\n";
echo $_POST['records'] . "\n\n";

echo "2. После wp_unslash():\n";
$unslashed = wp_unslash($_POST['records']);
echo $unslashed . "\n\n";

echo "3. После JSON decode:\n";
$decoded = json_decode($unslashed, true);
echo print_r($decoded, true) . "\n\n";

echo "4. Проверка items:\n";
foreach ($decoded as $record) {
    echo "Record: " . $record['title'] . "\n";
    echo "Items count: " . count($record['items']) . "\n";
    echo "Items: " . print_r($record['items'], true) . "\n";
}

echo "5. Повторное JSON encode для сохранения:\n";
$reencoded = json_encode($decoded, JSON_UNESCAPED_UNICODE);
echo $reencoded . "\n\n";

echo "6. Проверка валидности JSON:\n";
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON валиден\n";
} else {
    echo "❌ JSON ошибка: " . json_last_error_msg() . "\n";
}

echo "7. Попытка полного цикла сохранения:\n";
$data = array(
    'title'            => sanitize_text_field($_POST['title']),
    'description'      => wp_kses_post($_POST['description']),
    'scale'            => wp_unslash($_POST['scale']),
    'title_second'     => sanitize_text_field($_POST['title_second']),
    'records'          => wp_unslash($_POST['records']),
    'achievements'     => wp_unslash($_POST['achievements']),
    'title_third'      => sanitize_text_field($_POST['title_third']),
    'additional_cards' => wp_unslash($_POST['additional_cards']),
);

echo "Данные перед save_history:\n";
echo "records тип: " . gettype($data['records']) . "\n";
echo "records значение: " . substr($data['records'], 0, 100) . "...\n\n";

// Сохраняем
$result = Arsenal_History_Manager::save_history($data);
echo "Результат save: " . ($result ? 'TRUE' : 'FALSE') . "\n\n";

// Загружаем обратно
$loaded = Arsenal_History_Manager::get_history();
echo "8. Загруженные данные:\n";
echo "records тип: " . gettype($loaded['records']) . "\n";
echo "records содержимое:\n";
echo print_r($loaded['records'], true) . "\n";

// Проверяем items
if (isset($loaded['records'][0]['items'])) {
    echo "✅ Items существуют в первой записи\n";
    echo "Количество items: " . count($loaded['records'][0]['items']) . "\n";
    echo "Items: " . print_r($loaded['records'][0]['items'], true);
} else {
    echo "❌ Items НЕ найдены в первой записи\n";
}

echo "\n\n=== КОНЕЦ ===\n";
