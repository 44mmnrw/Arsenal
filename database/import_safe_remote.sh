#!/bin/bash
# Скрипт импорта БД на удаленный сервер через PHP

DUMP_FILE="/tmp/arsenal_full_dump_2026-01-11_15-29-15.sql"
REMOTE_USER="site_user"
REMOTE_HOST="212.113.120.197"
REMOTE_PATH="/var/www/site_user/data/www/1779917-cq85026.twc1.net"

echo "═══════════════════════════════════════════════════════════════════"
echo "  Arsenal Database Import via PHP (Safe Mode)"
echo "═══════════════════════════════════════════════════════════════════"
echo ""

if [ ! -f "$DUMP_FILE" ]; then
    echo "❌ Ошибка: файл дампа не найден: $DUMP_FILE"
    exit 1
fi

echo "✅ Файл дампа найден: $DUMP_FILE"
echo "📏 Размер: $(du -h "$DUMP_FILE" | cut -f1)"
echo ""

# Создаем PHP скрипт для импорта на сервере
cat > /tmp/import_safe.php << 'PHPEOF'
<?php
/**
 * Safe Database Import Script
 * Импортирует SQL дамп, разбивая запросы по одному
 */

// Настройки БД
$db_host = 'localhost';
$db_user = 'arsenal_usr';
$db_pass = 'jV:<Mn2E_&RPZckF';
$db_name = 'arsenal';
$dump_file = isset($argv[1]) ? $argv[1] : '/tmp/arsenal_full_dump_2026-01-11_15-29-15.sql';

// Подключение
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("❌ Ошибка подключения: " . $mysqli->connect_error . "\n");
}

// Проверка файла
if (!file_exists($dump_file)) {
    die("❌ Файл дампа не найден: $dump_file\n");
}

echo "═══════════════════════════════════════════════════════════════════\n";
echo "  Arsenal Database Safe Import\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "\n✅ Файл дампа: $dump_file\n";
echo "📏 Размер: " . number_format(filesize($dump_file) / 1024 / 1024, 2) . " MB\n\n";

// Отключаем foreign keys
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
$mysqli->query("SET UNIQUE_CHECKS = 0");

// Читаем файл и разбиваем на запросы
$sql = file_get_contents($dump_file);
$queries = array_filter(array_map(function($q) {
    return trim($q);
}, explode(';', $sql)));

echo "📝 Всего запросов: " . count($queries) . "\n";
echo "▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁\n\n";

$executed = 0;
$errors = 0;
$error_log = array();

foreach ($queries as $i => $query) {
    if (empty($query)) {
        continue;
    }
    
    // Добавляем точку с запятой если её нет
    if (substr($query, -1) !== ';') {
        $query .= ';';
    }
    
    if (!$mysqli->query($query)) {
        $errors++;
        $error_log[] = "Query " . ($i + 1) . ": " . $mysqli->error;
        echo "❌ Ошибка на запросе " . ($i + 1) . ": " . substr($query, 0, 50) . "...\n";
    } else {
        $executed++;
    }
    
    // Прогресс
    if ($executed % 500 == 0) {
        echo "✓ Выполнено: $executed запросов (" . number_format(($executed / count($queries)) * 100, 1) . "%)\n";
    }
}

// Включаем foreign keys обратно
$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
$mysqli->query("SET UNIQUE_CHECKS = 1");

echo "\n▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔▔\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "  РЕЗУЛЬТАТЫ ИМПОРТА\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "✓ Успешно выполнено: $executed запросов\n";
echo "⚠ Ошибок: $errors\n\n";

if ($errors > 0) {
    echo "Список ошибок:\n";
    foreach ($error_log as $error) {
        echo "  • $error\n";
    }
    echo "\n";
}

// Проверяем таблицы
$result = $mysqli->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$db_name'");
$row = $result->fetch_assoc();
echo "📊 Таблиц в БД: " . $row['table_count'] . "\n";

// Статистика по таблицам Arsenal
$result = $mysqli->query("SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.tables WHERE table_schema = '$db_name' AND TABLE_NAME LIKE 'wp_arsenal_%' ORDER BY TABLE_NAME");
echo "\n📈 Таблицы Arsenal:\n";
while ($row = $result->fetch_assoc()) {
    echo "   • " . str_pad($row['TABLE_NAME'], 40) . " → " . number_format($row['TABLE_ROWS']) . " записей\n";
}

echo "\n✅ Импорт завершён!\n";
echo "═══════════════════════════════════════════════════════════════════\n";

$mysqli->close();
?>
PHPEOF

echo "📤 Копирую PHP скрипт на сервер..."
scp /tmp/import_safe.php "$REMOTE_USER@$REMOTE_HOST:/tmp/"

echo "🔄 Запускаю импорт на сервере..."
ssh "$REMOTE_USER@$REMOTE_HOST" "cd $REMOTE_PATH && php /tmp/import_safe.php '/tmp/arsenal_full_dump_2026-01-11_15-29-15.sql'"

echo ""
echo "═══════════════════════════════════════════════════════════════════"
echo "✅ Деплой и импорт завершены!"
echo "═══════════════════════════════════════════════════════════════════"
