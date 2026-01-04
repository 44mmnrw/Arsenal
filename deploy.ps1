##############################################
# ДЕПЛОЙ WORDPRESS - ФК АРСЕНАЛ ДЗЕРЖИНСК
##############################################
# Использование: .\deploy.ps1
# Описание: Полный деплой сайта на продакшн
##############################################

param(
    [switch]$SkipDatabase,
    [switch]$SkipFiles
)

$ErrorActionPreference = "Stop"

# ===========================================
# НАСТРОЙКИ
# ===========================================

# Локальная БД
$LOCAL_USER = "arsenal_user"
$LOCAL_PASS = "Arsenal_Secure_2025!"
$LOCAL_DB = "arsenal"

# Продакшн БД
$PROD_USER = "1779917_cq85"
$PROD_PASS = "XD&Hqt1PyzP4"
$PROD_DB = "1779917_cq85"

# Сервер
$SSH_USER = "site_user"
$SSH_HOST = "212.113.120.197"
$WEB_ROOT = "/var/www/site_user/data/www/1779917-cq85026.twc1.net"

# URL
$LOCAL_URL_FULL = "http://arsenal.test:8080"
$LOCAL_URL = "http://arsenal.test"
$PROD_URL = "http://1779917-cq85026.twc1.net"

# Пути
$MYSQLDUMP = "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe"
$BACKUP_DIR = "database"
$BACKUP_FILE = "$BACKUP_DIR\deploy_backup.sql"
$BACKUP_PROD = "$BACKUP_DIR\deploy_backup_prod.sql"

# ===========================================
# ФУНКЦИИ
# ===========================================

function Write-Step {
    param($Message)
    Write-Host "`n[$Message]" -ForegroundColor Cyan
    Write-Host ("=" * 60) -ForegroundColor DarkGray
}

function Write-Success {
    param($Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Error {
    param($Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Write-Warning {
    param($Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

# ===========================================
# НАЧАЛО ДЕПЛОЯ
# ===========================================

Write-Host "`n" -NoNewline
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   ДЕПЛОЙ WORDPRESS - ФК АРСЕНАЛ ДЗЕРЖИНСК            ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# ===========================================
# ШАГ 1: ДАМП БАЗЫ ДАННЫХ
# ===========================================

if (-not $SkipDatabase) {
    Write-Step "ШАГ 1/4: Экспорт базы данных"
    
    try {
        & $MYSQLDUMP `
            --default-character-set=utf8mb4 `
            --no-tablespaces `
            --skip-lock-tables `
            --single-transaction `
            -u $LOCAL_USER `
            "-p$LOCAL_PASS" `
            $LOCAL_DB > $BACKUP_FILE 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            $size = [math]::Round((Get-Item $BACKUP_FILE).Length / 1MB, 2)
            Write-Success "Дамп создан: $BACKUP_FILE ($size MB)"
        } else {
            throw "Ошибка создания дампа"
        }
    } catch {
        Write-Error "Не удалось создать дамп базы данных"
        Write-Host $_.Exception.Message
        exit 1
    }
    
    # Замена URL
    Write-Host "`nЗамена URL в дампе..." -ForegroundColor Yellow
    
    $content = Get-Content $BACKUP_FILE -Raw -Encoding UTF8
    $content = $content -replace [regex]::Escape($LOCAL_URL_FULL), $PROD_URL
    $content = $content -replace [regex]::Escape($LOCAL_URL), $PROD_URL
    $content | Out-File -FilePath $BACKUP_PROD -Encoding UTF8 -NoNewline
    
    Write-Success "URL заменены ($LOCAL_URL → $PROD_URL)"
} else {
    Write-Warning "Пропуск экспорта БД (--SkipDatabase)"
}

# ===========================================
# ШАГ 2: ЗАГРУЗКА И ИМПОРТ БД
# ===========================================

if (-not $SkipDatabase) {
    Write-Step "ШАГ 2/4: Загрузка и импорт базы данных"
    
    Write-Host "Загрузка на сервер..." -ForegroundColor Yellow
    scp $BACKUP_PROD "${SSH_USER}@${SSH_HOST}:/tmp/deploy_backup.sql" 2>&1 | Out-Null
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Дамп загружен на сервер"
    } else {
        Write-Error "Не удалось загрузить дамп"
        exit 1
    }
    
    Write-Host "Импорт в базу данных..." -ForegroundColor Yellow
    ssh "${SSH_USER}@${SSH_HOST}" "mysql -u $PROD_USER -p'$PROD_PASS' $PROD_DB < /tmp/deploy_backup.sql 2>&1" | Out-Null
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "База данных импортирована"
    } else {
        Write-Error "Не удалось импортировать базу данных"
        exit 1
    }
} else {
    Write-Warning "Пропуск импорта БД (--SkipDatabase)"
}

# ===========================================
# ШАГ 3: ЗАГРУЗКА ФАЙЛОВ
# ===========================================

if (-not $SkipFiles) {
    Write-Step "ШАГ 3/4: Загрузка файлов на сервер"
    
    # Тема Arsenal
    Write-Host "Загрузка темы Arsenal..." -ForegroundColor Yellow
    scp -r wp-content/themes/arsenal "${SSH_USER}@${SSH_HOST}:${WEB_ROOT}/wp-content/themes/" 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Тема Arsenal загружена"
    }
    
    # Плагин Arsenal Team Manager
    Write-Host "Загрузка плагина Arsenal Team Manager..." -ForegroundColor Yellow
    scp -r wp-content/plugins/arsenal-team-manager "${SSH_USER}@${SSH_HOST}:${WEB_ROOT}/wp-content/plugins/" 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Плагин загружен"
    }
    
    # Uploads (изображения)
    Write-Host "Загрузка изображений (uploads)..." -ForegroundColor Yellow
    scp -r wp-content/uploads "${SSH_USER}@${SSH_HOST}:${WEB_ROOT}/wp-content/" 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Изображения загружены"
    }
} else {
    Write-Warning "Пропуск загрузки файлов (--SkipFiles)"
}

# ===========================================
# ШАГ 4: ПРОВЕРКА САЙТА
# ===========================================

Write-Step "ШАГ 4/4: Проверка сайта"

Start-Sleep -Seconds 2

try {
    $response = Invoke-WebRequest -Uri $PROD_URL -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
    
    if ($response.StatusCode -eq 200) {
        $size = [math]::Round($response.Content.Length / 1KB, 2)
        Write-Success "Сайт доступен (Status: 200, Size: $size KB)"
        
        # Проверка элементов
        $html = $response.Content
        $hasStats = $html -match 'stats-bar'
        $hasMatches = $html -match 'game-card'
        $hasTable = $html -match 'tournament-table'
        
        Write-Host "`nПроверка элементов страницы:"
        Write-Host "  $(if($hasStats){'✅'}else{'❌'}) Статистика клуба"
        Write-Host "  $(if($hasMatches){'✅'}else{'❌'}) Последние матчи"
        Write-Host "  $(if($hasTable){'✅'}else{'❌'}) Турнирная таблица"
        
    } else {
        Write-Warning "Сайт вернул статус: $($response.StatusCode)"
    }
} catch {
    Write-Error "Не удалось подключиться к сайту"
    Write-Host $_.Exception.Message
}

# ===========================================
# ЗАВЕРШЕНИЕ
# ===========================================

Write-Host "`n" -NoNewline
Write-Host "╔═══════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║              ДЕПЛОЙ УСПЕШНО ЗАВЕРШЁН                 ║" -ForegroundColor Green
Write-Host "╚═══════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "Сайт: " -NoNewline
Write-Host $PROD_URL -ForegroundColor Cyan
Write-Host ""

Write-Host "⚠️  ВАЖНЫЕ ДЕЙСТВИЯ ПОСЛЕ ДЕПЛОЯ:" -ForegroundColor Yellow
Write-Host "   1. Войдите в админку WordPress: $PROD_URL/wp-admin"
Write-Host "   2. Настройте главное меню в Внешний вид → Меню"
Write-Host "   3. Настройте баннер в Внешний вид → Настроить → Arsenal Settings"
Write-Host "   4. Проверьте все страницы сайта"
Write-Host ""

# Очистка временных файлов
if (Test-Path $BACKUP_FILE) {
    Write-Host "Временные файлы сохранены в $BACKUP_DIR" -ForegroundColor DarkGray
}

Write-Host ""
