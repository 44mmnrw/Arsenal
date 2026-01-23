#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Скрипт деплоя для ФК Арсенал Дзержинск
    
.DESCRIPTION
    Простой деплой процесс:
    1. Git commit локально
    2. Git push на GitHub (dev_main)
    3. Git pull на production сервер
    
.PARAMETER Message
    Описание коммита (обязательный параметр)
    
.EXAMPLE
    .\deploy.ps1 "Fix: typo in player page"
#>

param(
    [Parameter(Mandatory=$true, HelpMessage="Описание коммита")]
    [string]$Message
)

# Конфигурация сервера
$SSH_USER = "site_user76"
$SSH_HOST = "212.113.120.197"
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
    Write-Host "`n════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host "    ДЕПЛОЙ АРСЕНАЛ ДЗЕРЖИНСК" -ForegroundColor Cyan
    Write-Host "════════════════════════════════════════`n" -ForegroundColor Cyan
    
    # Шаг 1: Git commit
    Write-Host "[1/3] Коммит изменений..." -ForegroundColor Yellow
    git add -A
    git commit -m $Message
    Write-Status "Коммит создан: $Message" "success"
    
    # Шаг 2: Git push
    Write-Host "`n[2/3] Пуш на GitHub..." -ForegroundColor Yellow
    git push origin dev_main
    Write-Status "Отправлено на GitHub (dev_main)" "success"
    
    # Шаг 3: Git pull на production
    Write-Host "`n[3/3] Пулл на production сервер..." -ForegroundColor Yellow
    $pullCmd = "cd $WEB_ROOT && git pull origin dev_main && echo 'Pull успешен' && git log --oneline -1"
    ssh -o StrictHostKeyChecking=no "$SSH_USER@$SSH_HOST" $pullCmd | Tee-Object -Variable pullOutput | Select-Object -Last 5
    
    Write-Status "Production обновлен" "success"
    
    Write-Host "`n════════════════════════════════════════" -ForegroundColor Green
    Write-Host "    ✅ ДЕПЛОЙ ЗАВЕРШЕН" -ForegroundColor Green
    Write-Host "════════════════════════════════════════" -ForegroundColor Green
    Write-Host "`nСайт: http://1779917-cq85026.twc1.net`n" -ForegroundColor Green
    
} catch {
    Write-Status "ОШИБКА: $_" "error"
    exit 1
}
