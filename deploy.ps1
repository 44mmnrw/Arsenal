#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Скрипт деплоя для сайта ФК Арсенал Дзержинск
    
.DESCRIPTION
    Выполняет полный деплой темы на production сервер:
    1. Добавляет изменения в Git
    2. Создаёт коммит с описанием
    3. Пушит на GitHub (dev_main)
    4. Копирует тему на production сервер через SSH
    
.PARAMETER Message
    Описание коммита (обязательный параметр)
    
.EXAMPLE
    .\deploy.ps1 "Refactor: Reorganize template structure"
    
.NOTES
    Требует:
    - Git установлен
    - SSH доступ настроен
    - Переменные окружения: SSH_USER, SSH_HOST, REPO_DIR, WEB_ROOT
#>

param(
    [Parameter(Mandatory=$true, HelpMessage="Описание коммита")]
    [string]$Message
)

# === КОНФИГУРАЦИЯ ===
$SSH_USER = "site_user76"
$SSH_HOST = "212.113.120.197"
$REPO_DIR = "/var/www/site_user76/data/arsenal-repo"
$WEB_ROOT = "/var/www/site_user76/data/www/1779917-cq85026.twc1.net"

$ErrorActionPreference = "Stop"

function Write-Status {
    param([string]$Text, [string]$Status = "info")
    $colors = @{
        "success" = "Green"
        "error"   = "Red"
        "warning" = "Yellow"
        "info"    = "Cyan"
    }
    $symbol = @{
        "success" = "✅"
        "error"   = "❌"
        "warning" = "⚠️"
        "info"    = "ℹ️"
    }
    Write-Host "$($symbol[$Status]) $Text" -ForegroundColor $colors[$Status]
}

try {
    Write-Host "`n=== НАЧАЛО ДЕПЛОЯ ===" -ForegroundColor Cyan
    Write-Status "Сообщение коммита: $Message"
    
    # === ШАГ 1: GIT ADD ===
    Write-Host "`n[1/4] Добавление изменений в Git..." -ForegroundColor Cyan
    git add -A
    Write-Status "Изменения добавлены"
    
    # === ШАГ 2: GIT COMMIT ===
    Write-Host "`n[2/4] Создание коммита..." -ForegroundColor Cyan
    git commit -m $Message
    if ($LASTEXITCODE -eq 1) {
        Write-Status "Нет изменений для коммита" "warning"
    } else {
        Write-Status "Коммит создан"
    }
    
    # === ШАГ 3: GIT PUSH ===
    Write-Host "`n[3/4] Пуш на GitHub (dev_main)..." -ForegroundColor Cyan
    git push origin dev_main
    Write-Status "Коммит отправлен на GitHub"
    
    # === ШАГ 4: DEPLOY НА СЕРВЕР ===
    Write-Host "`n[4/4] Копирование темы на production..." -ForegroundColor Cyan
    $deployCmd = @"
rm -rf $WEB_ROOT/wp-content/themes/arsenal && `
cp -r $REPO_DIR/wp-content/themes/arsenal $WEB_ROOT/wp-content/themes/ && `
echo 'Тема скопирована успешно' && `
ls -la $WEB_ROOT/wp-content/themes/arsenal/template-parts/
"@
    
    ssh -o StrictHostKeyChecking=no "$SSH_USER@$SSH_HOST" $deployCmd | Select-Object -Last 10
    Write-Status "Тема скопирована на production"
    
    # === ИТОГИ ===
    Write-Host "`n=== ДЕПЛОЙ ЗАВЕРШЕН ===" -ForegroundColor Green
    Write-Status "✅ Все операции выполнены успешно!"
    Write-Host @"

📊 Статус:
  ✓ Git коммит создан
  ✓ GitHub обновлен (dev_main)
  ✓ Production обновлен ($WEB_ROOT)
  
🌐 Сайт: http://1779917-cq85026.twc1.net
📝 Коммит: $Message

"@ -ForegroundColor Green
    
} catch {
    Write-Status "ОШИБКА: $_" "error"
    exit 1
}
