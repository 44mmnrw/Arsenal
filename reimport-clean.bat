@echo off
chcp 65001 >nul
echo.
echo === Полная переустановка базы данных Arsenal ===
echo.

cd /d "c:\laragon\www\arsenal"

:: Генерируем имя файла для бэкапа
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c-%%b-%%a)
for /f "tokens=1-2 delims=/:" %%a in ("%TIME%") do (set mytime=%%a%%b)
set backup_file=database\arsenal_local_backup_%mydate%_%mytime%.sql

echo Шаг 0: Создание бэкапа текущей базы...
echo Файл: %backup_file%
mysqldump -u root -pAnastasiya12 arsenal > "%backup_file%"

if %ERRORLEVEL% EQU 0 (
    echo [OK] Бэкап создан
) else (
    echo [ОШИБКА] Не удалось создать бэкап! Прерываю операцию.
    pause
    exit /b 1
)

echo.
echo Шаг 1: Удаление всех таблиц wp_arsenal_*...
mysql -u root -pAnastasiya12 -e "SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS wp_arsenal_achievements, wp_arsenal_coaches, wp_arsenal_event_types, wp_arsenal_leagues, wp_arsenal_management, wp_arsenal_match_events, wp_arsenal_match_lineups, wp_arsenal_matches, wp_arsenal_player_stats_corrections, wp_arsenal_players, wp_arsenal_seasons, wp_arsenal_sponsors, wp_arsenal_squad, wp_arsenal_squad_players, wp_arsenal_stadiums, wp_arsenal_staff, wp_arsenal_staff_department, wp_arsenal_staff_job_titles, wp_arsenal_standings_adjustments, wp_arsenal_teams, wp_arsenal_tournament_brackets, wp_arsenal_tournament_participants, wp_arsenal_tournament_phases, wp_arsenal_tournament_seasons, wp_arsenal_tournaments; SET FOREIGN_KEY_CHECKS = 1;" arsenal

if %ERRORLEVEL% NEQ 0 (
    echo [ОШИБКА] Не удалось удалить таблицы!
    pause
    exit /b 1
)

echo [OK] Таблицы удалены
echo.
echo Шаг 2: Импорт продакшн дампа...
mysql -u root -pAnastasiya12 arsenal < "database\1779917_cq857246.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Импорт успешно завершен!
    echo.
    echo Проверка данных:
    mysql -u root -pAnastasiya12 -e "SELECT 'wp_arsenal_staff' as 'Таблица', COUNT(*) as 'Записей' FROM wp_arsenal_staff UNION ALL SELECT 'wp_arsenal_squad', COUNT(*) FROM wp_arsenal_squad UNION ALL SELECT 'wp_arsenal_players', COUNT(*) FROM wp_arsenal_players UNION ALL SELECT 'wp_arsenal_matches', COUNT(*) FROM wp_arsenal_matches UNION ALL SELECT 'wp_arsenal_match_events', COUNT(*) FROM wp_arsenal_match_events;" arsenal
) else (
    echo.
    echo [ОШИБКА] Импорт не выполнен!
)

echo.
pause
