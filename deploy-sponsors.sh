#!/bin/bash
#
# Скрипт для миграции таблицы wp_arsenal_sponsors на удаленный сервер
#
# Использование:
#   bash deploy-sponsors.sh [export|upload|full]
#
# Команды:
#   export     Только экспортировать таблицу (создать SQL файл)
#   upload     Только загрузить файл на сервер
#   full       Полная миграция (export + upload)
#

# === КОНФИГУРАЦИЯ ===
REMOTE_USER="${SSH_USER}"
REMOTE_HOST="${SSH_HOST}"
REMOTE_PATH="/tmp/"
REMOTE_DB="arsenal"
REMOTE_DB_USER="arsenal_user"

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
EXPORT_DIR="$PROJECT_ROOT/service_scripts_ai/exports"
TIMESTAMP=$(date +%Y-%m-%d_%H-%M-%S)
FILENAME="wp_arsenal_sponsors_$TIMESTAMP.sql"
EXPORT_FILE="$EXPORT_DIR/$FILENAME"

# === ЦВЕТА ===
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# === ФУНКЦИИ ===

log_info() {
    echo -e "${BLUE}ℹ  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Функция экспорта
export_table() {
    log_info "Экспортирую таблицу wp_arsenal_sponsors..."
    
    php "$PROJECT_ROOT/service_scripts_ai/migrate-sponsors-table.php" --export
    
    if [ $? -eq 0 ]; then
        log_success "Таблица экспортирована в $EXPORT_FILE"
        return 0
    else
        log_error "Ошибка при экспорте таблицы"
        return 1
    fi
}

# Функция загрузки на сервер
upload_to_server() {
    if [ ! -f "$EXPORT_FILE" ]; then
        log_error "Файл не найден: $EXPORT_FILE"
        return 1
    fi
    
    log_info "Загружаю файл на удаленный сервер..."
    scp "$EXPORT_FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH$FILENAME"
    
    if [ $? -eq 0 ]; then
        log_success "Файл загружен: $REMOTE_PATH$FILENAME"
        return 0
    else
        log_error "Ошибка при загрузке файла"
        return 1
    fi
}

# Функция импорта на сервере
import_on_server() {
    log_info "Импортирую таблицу на удаленный сервер..."
    
    IMPORT_CMD="mysql -u $REMOTE_DB_USER -p$REMOTE_DB < $REMOTE_PATH$FILENAME"
    
    ssh "$REMOTE_USER@$REMOTE_HOST" "$IMPORT_CMD"
    
    if [ $? -eq 0 ]; then
        log_success "Таблица импортирована на сервере"
        return 0
    else
        log_error "Ошибка при импорте таблицы"
        return 1
    fi
}

# Функция проверки
verify_on_server() {
    log_info "Проверяю таблицу на сервере..."
    
    CHECK_CMD="mysql -u $REMOTE_DB_USER -p$REMOTE_DB -e 'SELECT COUNT(*) as rows FROM wp_arsenal_sponsors;'"
    
    RESULT=$(ssh "$REMOTE_USER@$REMOTE_HOST" "$CHECK_CMD" 2>/dev/null | tail -1)
    
    if [ ! -z "$RESULT" ]; then
        log_success "Таблица на сервере содержит $RESULT строк"
        return 0
    else
        log_error "Ошибка при проверке таблицы на сервере"
        return 1
    fi
}

# === MAIN ===

COMMAND="${1:-full}"

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║  МИГРАЦИЯ ТАБЛИЦЫ wp_arsenal_sponsors                       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

case $COMMAND in
    export)
        export_table
        ;;
    
    upload)
        if [ ! -f "$EXPORT_FILE" ]; then
            log_error "Файл экспорта не найден: $EXPORT_FILE"
            log_info "Сначала выполните: bash deploy-sponsors.sh export"
            exit 1
        fi
        upload_to_server
        ;;
    
    full)
        if export_table; then
            if upload_to_server; then
                log_warning "Файл загружен на сервер. Для импорта выполните:"
                echo ""
                echo "ssh $REMOTE_USER@$REMOTE_HOST"
                echo "mysql -u $REMOTE_DB_USER -p$REMOTE_DB < $REMOTE_PATH$FILENAME"
                echo ""
                log_info "Или запустите: bash deploy-sponsors.sh import"
            fi
        fi
        ;;
    
    import)
        if import_on_server; then
            verify_on_server
        fi
        ;;
    
    check)
        verify_on_server
        ;;
    
    *)
        log_error "Неизвестная команда: $COMMAND"
        echo ""
        echo "Доступные команды:"
        echo "  export    Экспортировать таблицу в SQL"
        echo "  upload    Загрузить файл на сервер"
        echo "  import    Импортировать на сервере (требует SSH доступа)"
        echo "  full      Полная миграция (export + upload)"
        echo "  check     Проверить таблицу на сервере"
        echo ""
        exit 1
        ;;
esac

echo ""
