@echo off
chcp 65001 >nul
echo.
echo === Бэкап локальной базы данных Arsenal ===
echo.

cd /d "c:\laragon\www\arsenal\database"

:: Генерируем имя файла с датой и временем
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c-%%b-%%a)
for /f "tokens=1-2 delims=/:" %%a in ("%TIME%") do (set mytime=%%a%%b)
set filename=arsenal_local_backup_%mydate%_%mytime%.sql

echo Создание бэкапа: %filename%
echo.

mysqldump -u root -pAnastasiya12 arsenal > "%filename%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Бэкап успешно создан!
    for %%A in ("%filename%") do echo Размер: %%~zA байт
    echo Файл: database\%filename%
) else (
    echo.
    echo [ОШИБКА] Не удалось создать бэкап!
)

echo.
pause
