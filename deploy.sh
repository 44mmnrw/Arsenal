#!/bin/bash

# Скрипт автоматического деплоя для ФК Арсенал
# Запуск: bash deploy.sh

set -e  # Остановка при ошибке

# Цвета для вывода
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Конфигурация
REPO_DIR="/var/www/site_user/data/arsenal-repo"
WEB_DIR="/var/www/site_user/data/www/1779917-cq85026.twc1.net"
REPO_URL="https://github.com/44mmnrw/arsenal.git"
BRANCH="dev_main"

echo -e "${GREEN}=== Начало деплоя ФК Арсенал ===${NC}"
echo -e "${YELLOW}Директория репозитория: ${REPO_DIR}${NC}"
echo -e "${YELLOW}Веб-директория: ${WEB_DIR}${NC}"

# Переход в директорию репозитория
cd "$REPO_DIR"

# Получение изменений с удалённого репозитория
echo -e "${GREEN}Получение изменений из Git...${NC}"
git fetch origin

# Вывод текущей версии
CURRENT_VERSION=$(git rev-parse --short HEAD)
echo -e "${YELLOW}Текущая версия: ${CURRENT_VERSION}${NC}"

# Обновление репозитория
echo -e "${GREEN}Обновление до последней версии...${NC}"
git pull origin "$BRANCH"

# Новая версия после pull
NEW_VERSION=$(git rev-parse --short HEAD)
echo -e "${YELLOW}Новая версия: ${NEW_VERSION}${NC}"

if [ "$CURRENT_VERSION" == "$NEW_VERSION" ]; then
    echo -e "${YELLOW}Нет новых изменений для деплоя${NC}"
    exit 0
fi

# Копирование файлов темы
echo -e "${GREEN}Копирование темы Arsenal...${NC}"
rsync -av --delete \
    --exclude='*.md' \
    --exclude='.git*' \
    "$REPO_DIR/wp-content/themes/arsenal/" \
    "$WEB_DIR/wp-content/themes/arsenal/"

# Копирование плагинов (если есть)
if [ -d "$REPO_DIR/wp-content/plugins" ]; then
    echo -e "${GREEN}Копирование плагинов...${NC}"
    rsync -av --delete \
        --exclude='akismet' \
        --exclude='hello.php' \
        "$REPO_DIR/wp-content/plugins/" \
        "$WEB_DIR/wp-content/plugins/"
fi

# Установка прав доступа
echo -e "${GREEN}Установка прав доступа...${NC}"
find "$WEB_DIR/wp-content/themes/arsenal" -type d -exec chmod 755 {} \;
find "$WEB_DIR/wp-content/themes/arsenal" -type f -exec chmod 644 {} \;

echo -e "${GREEN}=== Деплой успешно завершён! ===${NC}"
echo -e "${YELLOW}Версия: ${CURRENT_VERSION} → ${NEW_VERSION}${NC}"
echo -e "${GREEN}Сайт: http://1779917-cq85026.twc1.net/${NC}"
