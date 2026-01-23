# Полная переустановка базы данных Arsenal с бэкапом

Write-Host "`n=== Полная переустановка базы данных Arsenal ===" -ForegroundColor Cyan
Write-Host ""

Set-Location "c:\laragon\www\arsenal"

# Генерируем имя файла для бэкапа
$date = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupFile = "database\arsenal_local_backup_$date.sql"

Write-Host "Шаг 0: Создание бэкапа текущей базы..." -ForegroundColor Yellow
Write-Host "Файл: $backupFile" -ForegroundColor Gray

$env:MYSQL_PWD = 'Anastasiya12'
mysqldump -u root arsenal > $backupFile

if ($LASTEXITCODE -eq 0) {
    $size = [math]::Round((Get-Item $backupFile).Length / 1MB, 2)
    Write-Host "[OK] Бэкап создан ($size MB)" -ForegroundColor Green
} else {
    Write-Host "[ОШИБКА] Не удалось создать бэкап! Прерываю операцию." -ForegroundColor Red
    Read-Host "Нажмите Enter для выхода"
    exit 1
}

Write-Host "`nШаг 1: Удаление всех таблиц wp_arsenal_*..." -ForegroundColor Yellow
mysql -u root -e "SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS wp_arsenal_achievements, wp_arsenal_coaches, wp_arsenal_event_types, wp_arsenal_leagues, wp_arsenal_management, wp_arsenal_match_events, wp_arsenal_match_lineups, wp_arsenal_matches, wp_arsenal_player_stats_corrections, wp_arsenal_players, wp_arsenal_seasons, wp_arsenal_sponsors, wp_arsenal_squad, wp_arsenal_squad_players, wp_arsenal_stadiums, wp_arsenal_staff, wp_arsenal_staff_department, wp_arsenal_staff_job_titles, wp_arsenal_standings_adjustments, wp_arsenal_teams, wp_arsenal_tournament_brackets, wp_arsenal_tournament_participants, wp_arsenal_tournament_phases, wp_arsenal_tournament_seasons, wp_arsenal_tournaments; SET FOREIGN_KEY_CHECKS = 1;" arsenal

if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Таблицы удалены" -ForegroundColor Green
} else {
    Write-Host "[ОШИБКА] Не удалось удалить таблицы!" -ForegroundColor Red
    Read-Host "Нажмите Enter для выхода"
    exit 1
}

Write-Host "`nШаг 2: Импорт продакшн дампа..." -ForegroundColor Yellow
Write-Host "Файл: database\1779917_cq857246.sql" -ForegroundColor Gray
Write-Host "Это займет 1-2 минуты..." -ForegroundColor Gray

Get-Content "database\1779917_cq857246.sql" | mysql -u root arsenal

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n[OK] Импорт успешно завершен!" -ForegroundColor Green
    Write-Host "`nПроверка данных:" -ForegroundColor Yellow
    mysql -u root -e "SELECT 'wp_arsenal_staff' as 'Таблица', COUNT(*) as 'Записей' FROM wp_arsenal_staff UNION ALL SELECT 'wp_arsenal_squad', COUNT(*) FROM wp_arsenal_squad UNION ALL SELECT 'wp_arsenal_players', COUNT(*) FROM wp_arsenal_players UNION ALL SELECT 'wp_arsenal_matches', COUNT(*) FROM wp_arsenal_matches UNION ALL SELECT 'wp_arsenal_match_events', COUNT(*) FROM wp_arsenal_match_events;" arsenal
} else {
    Write-Host "`n[ОШИБКА] Импорт не выполнен!" -ForegroundColor Red
}

Write-Host ""
Read-Host "Нажмите Enter для завершения"
