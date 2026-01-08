#!/bin/bash

# ============================================================================
# Arsenal Database Import Script
# 
# Назначение: Полный импорт БД Arsenal на сервер с правильной структурой
# 
# Использование:
#   bash /tmp/import-arsenal-db.sh /path/to/arsenal_full_correct.sql
# 
# Что делает:
# 1. Проверяет наличие файла дампа
# 2. Создает резервную копию текущей БД (если есть)
# 3. Удаляет все старые Arsenal таблицы
# 4. Импортирует дамп (CREATE TABLE + INSERT)
# 5. Проверяет успешность импорта
# 6. Выводит статистику
# ============================================================================

set -e

DB_NAME="arsenal"
DB_USER="arsenal_usr"
DB_PASS="jV:<Mn2E_&RPZckF"
DB_HOST="localhost"
DUMP_FILE="${1:-/tmp/arsenal_full_correct.sql}"

echo "═══════════════════════════════════════════════════════════════════"
echo "  Arsenal Database Import Script"
echo "═══════════════════════════════════════════════════════════════════"
echo ""

# 1. Проверка файла
if [ ! -f "$DUMP_FILE" ]; then
    echo "❌ ОШИБКА: Файл дампа не найден: $DUMP_FILE"
    echo "Использование: bash import-arsenal-db.sh /path/to/dump.sql"
    exit 1
fi

echo "✅ Файл дампа найден: $DUMP_FILE"
echo "📏 Размер: $(du -h "$DUMP_FILE" | cut -f1)"
echo ""

# 2. Резервная копия (опционально)
BACKUP_FILE="/tmp/arsenal_backup_$(date +%Y%m%d_%H%M%S).sql"
echo "💾 Создание резервной копии..."
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > "$BACKUP_FILE" 2>/dev/null
echo "✅ Резервная копия: $BACKUP_FILE"
echo ""

# 3. Удаление старых таблиц
echo "🗑️  Удаление старых Arsenal таблиц..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << 'EOF' 2>/dev/null
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS wp_arsenal_match_lineups;
DROP TABLE IF EXISTS wp_arsenal_match_events;
DROP TABLE IF EXISTS wp_arsenal_matches;
DROP TABLE IF EXISTS wp_arsenal_match_statuses;
DROP TABLE IF EXISTS wp_arsenal_standings_adjustments;
DROP TABLE IF EXISTS wp_arsenal_team_seasons;
DROP TABLE IF EXISTS wp_arsenal_team_coaches;
DROP TABLE IF EXISTS wp_arsenal_team_contracts;
DROP TABLE IF EXISTS wp_arsenal_squad;
DROP TABLE IF EXISTS wp_arsenal_players;
DROP TABLE IF EXISTS wp_arsenal_positions;
DROP TABLE IF EXISTS wp_arsenal_event_types;
DROP TABLE IF EXISTS wp_arsenal_coaches;
DROP TABLE IF EXISTS wp_arsenal_stadiums;
DROP TABLE IF EXISTS wp_arsenal_tournaments;
DROP TABLE IF EXISTS wp_arsenal_seasons;
DROP TABLE IF EXISTS wp_arsenal_teams;
DROP TABLE IF EXISTS wp_arsenal_leagues;
DROP TABLE IF EXISTS wp_arsenal_player_seasons;
DROP TABLE IF EXISTS wp_arsenal_player_stats;
DROP TABLE IF EXISTS wp_arsenal_player_transfers;
DROP TABLE IF EXISTS wp_arsenal_standings;
DROP TABLE IF EXISTS wp_arsenal_sync_log;

SET FOREIGN_KEY_CHECKS = 1;
EOF
echo "✅ Старые таблицы удалены"
echo ""

# 4. Импорт дампа
echo "📥 Импорт дампа (это может занять несколько минут)..."
START_TIME=$(date +%s)

mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < "$DUMP_FILE" 2>/dev/null

END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo "✅ Дамп импортирован за $DURATION секунд"
echo ""

# 5. Проверка успешности
echo "🔍 Проверка структуры и данных..."
echo ""

mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << 'VERIFY_EOF' 2>/dev/null
-- Таблицы
SELECT CONCAT('📊 Таблиц: ', COUNT(*)) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='arsenal' AND TABLE_NAME LIKE 'wp_arsenal_%';

-- Основные данные
SELECT CONCAT('👥 Teams: ', COUNT(*)) FROM wp_arsenal_teams;
SELECT CONCAT('👨 Players: ', COUNT(*)) FROM wp_arsenal_players;
SELECT CONCAT('⚽ Matches: ', COUNT(*)) FROM wp_arsenal_matches;
SELECT CONCAT('📍 Positions: ', COUNT(*)) FROM wp_arsenal_positions;
SELECT CONCAT('📋 Squad: ', COUNT(*)) FROM wp_arsenal_squad;

-- Арсенал
SELECT CONCAT('🎯 Arsenal team ID: ', id, ' (',  name, ')') FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1;

-- Игроки основного состава
SELECT CONCAT('⚔️ Arsenal players (squad 21F3D7B3): ', COUNT(DISTINCT p.player_id)) 
FROM wp_arsenal_players p
INNER JOIN wp_arsenal_team_contracts tc ON p.player_id = tc.player_id
WHERE tc.squad_id = '21F3D7B3';

VERIFY_EOF

echo ""
echo "═══════════════════════════════════════════════════════════════════"
echo "✅ ИМПОРТ УСПЕШНО ЗАВЕРШЕН!"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "Резервная копия сохранена: $BACKUP_FILE"
echo ""
