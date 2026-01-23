@echo off
chcp 65001 >nul
echo.
echo === Импорт базы данных Arsenal ===
echo.

cd /d "c:\laragon\www\arsenal"

echo Импорт дампа из database\1779917_cq857246.sql...
mysql -u root -pAnastasiya12 arsenal < "database\1779917_cq857246.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Импорт успешно завершен!
    echo.
    echo Проверка данных:
    mysql -u root -pAnastasiya12 -e "SELECT table_name, table_rows FROM information_schema.tables WHERE table_schema='arsenal' AND table_name LIKE 'wp_arsenal_%%' ORDER BY table_rows DESC LIMIT 10;" arsenal
) else (
    echo.
    echo [ОШИБКА] Импорт не выполнен!
)

echo.
pause
