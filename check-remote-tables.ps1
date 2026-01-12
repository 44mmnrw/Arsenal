#!/usr/bin/env pwsh
# Проверка таблиц на удаленном сервере

$REMOTE_HOST = "212.113.120.197"
$REMOTE_USER = "site_user"
$DB_USER = "arsenal_usr"
$DB_PASS = "jV:<Mn2E_&RPZckF"
$DB_NAME = "arsenal"

Write-Host "═══════════════════════════════════════════════════════════════════"
Write_Host "  Проверка таблиц Arsenal на сервере $REMOTE_HOST"
Write-Host "═══════════════════════════════════════════════════════════════════"
Write-Host ""

# SSH команда
$cmd = @"
mysql -u $DB_USER -p'$DB_PASS' $DB_NAME << 'EOSQL'
SELECT TABLE_NAME, TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_NAME LIKE 'wp_arsenal_%' 
ORDER BY TABLE_NAME;
EOSQL
"@

Write-Host "🔍 Запрашиваю таблицы на сервере..."
Write-Host ""

$output = ssh "${REMOTE_USER}@${REMOTE_HOST}" "$cmd" 2>&1

# Выводим результат
foreach ($line in $output) {
    if ($line -like "*Warning*") {
        continue
    }
    Write-Host $line
}

# Подсчитываем таблицы
$tables = $output | Where-Object { $_ -notlike "*Warning*" -and $_ -notlike "*TABLE_NAME*" -and $_.Trim() -ne "" }
$count = @($tables).Count

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════════════"
Write-Host "✅ Всего таблиц на сервере: $count"
Write-Host "⚠️  Ожидается: 20 таблиц"
Write-Host "═══════════════════════════════════════════════════════════════════"
