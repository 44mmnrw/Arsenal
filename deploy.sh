#!/bin/bash

##############################################################################
# Deploy Script for ФК Арсенал Дзержинск
#
# Выполняет полный деплой темы на production сервер:
# 1. Добавляет изменения в Git
# 2. Создаёт коммит с описанием
# 3. Пушит на GitHub (dev_main)
# 4. Копирует тему на production сервер через SSH
#
# Usage: ./deploy.sh "Описание коммита"
#
##############################################################################

set -e

# === КОНФИГУРАЦИЯ ===
SSH_USER="site_user76"
SSH_HOST="212.113.120.197"
REPO_DIR="/var/www/site_user76/data/arsenal-repo"
WEB_ROOT="/var/www/site_user76/data/www/1779917-cq85026.twc1.net"

# === ФУНКЦИИ ===
status_success() {
    echo "✅ $1"
}

status_error() {
    echo "❌ $1" >&2
    exit 1
}

status_info() {
    echo "ℹ️  $1"
}

status_warning() {
    echo "⚠️  $1"
}

# === ПРОВЕРКА АРГУМЕНТОВ ===
if [ -z "$1" ]; then
    status_error "Используйте: $0 \"Описание коммита\""
fi

COMMIT_MSG="$1"

# === НАЧАЛО ДЕПЛОЯ ===
echo ""
echo "=== НАЧАЛО ДЕПЛОЯ ==="
status_info "Сообщение коммита: $COMMIT_MSG"

# === ШАГ 1: GIT ADD ===
echo ""
echo "[1/4] Добавление изменений в Git..."
git add -A
status_success "Изменения добавлены"

# === ШАГ 2: GIT COMMIT ===
echo ""
echo "[2/4] Создание коммита..."
if git commit -m "$COMMIT_MSG"; then
    status_success "Коммит создан"
else
    status_warning "Нет изменений для коммита"
fi

# === ШАГ 3: GIT PUSH ===
echo ""
echo "[3/4] Пуш на GitHub (dev_main)..."
git push origin dev_main
status_success "Коммит отправлен на GitHub"

# === ШАГ 4: DEPLOY НА СЕРВЕР ===
echo ""
echo "[4/4] Копирование темы на production..."
ssh -o StrictHostKeyChecking=no "$SSH_USER@$SSH_HOST" \
    "rm -rf $WEB_ROOT/wp-content/themes/arsenal && \
     cp -r $REPO_DIR/wp-content/themes/arsenal $WEB_ROOT/wp-content/themes/ && \
     echo 'Тема скопирована успешно' && \
     ls -la $WEB_ROOT/wp-content/themes/arsenal/template-parts/" | tail -10

status_success "Тема скопирована на production"

# === ИТОГИ ===
echo ""
echo "=== ДЕПЛОЙ ЗАВЕРШЕН ==="
status_success "Все операции выполнены успешно!"

cat << EOF

📊 Статус:
  ✓ Git коммит создан
  ✓ GitHub обновлен (dev_main)
  ✓ Production обновлен ($WEB_ROOT)
  
🌐 Сайт: http://1779917-cq85026.twc1.net
📝 Коммит: $COMMIT_MSG

EOF
