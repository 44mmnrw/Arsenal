#!/bin/bash
# ============================================================================
# Arsenal Database Restoration Verification Script
# 
# Назначение: Проверка успешного восстановления базы данных Arsenal на сервере
# 
# Что проверяет:
# 1. Количество таблиц wp_arsenal_*
# 2. Структуру таблицы wp_arsenal_teams (правильные колонки)
# 3. Количество записей во всех основных таблицах
# 4. Наличие команды "Арсенал Дзержинск" с правильной структурой
# 5. Связи между таблицами (игроки, матчи, позиции)
# ============================================================================

DB_NAME="arsenal"
DB_USER="arsenal_usr"
DB_PASS="jV:<Mn2E_&RPZckF"
DB_HOST="localhost"

echo "🔍 ПРОВЕРКА ВОССТАНОВЛЕНИЯ БД ARSENAL"
echo "═════════════════════════════════════════"
echo ""

# 1. Проверка таблиц
echo "📋 1. ТАБЛИЦЫ:"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES LIKE 'wp_arsenal_%';" | wc -l

echo ""
echo "📊 2. СТРУКТУРА wp_arsenal_teams:"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "DESCRIBE wp_arsenal_teams;" | grep -E "^(id|name|is_arsenal|code|country_code|logo_url)"

echo ""
echo "📈 3. КОЛИЧЕСТВО ДАННЫХ:"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << 'MYSQL_EOF'
SELECT 'wp_arsenal_teams' as table_name, COUNT(*) as record_count FROM wp_arsenal_teams
UNION ALL SELECT 'wp_arsenal_players', COUNT(*) FROM wp_arsenal_players
UNION ALL SELECT 'wp_arsenal_matches', COUNT(*) FROM wp_arsenal_matches
UNION ALL SELECT 'wp_arsenal_positions', COUNT(*) FROM wp_arsenal_positions
UNION ALL SELECT 'wp_arsenal_squad', COUNT(*) FROM wp_arsenal_squad
UNION ALL SELECT 'wp_arsenal_team_contracts', COUNT(*) FROM wp_arsenal_team_contracts
UNION ALL SELECT 'wp_arsenal_seasons', COUNT(*) FROM wp_arsenal_seasons
UNION ALL SELECT 'wp_arsenal_leagues', COUNT(*) FROM wp_arsenal_leagues;
MYSQL_EOF

echo ""
echo "🎯 4. КОМАНДА АРСЕНАЛ (is_arsenal=1):"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "SELECT id, name, is_arsenal, code, country_code FROM wp_arsenal_teams WHERE is_arsenal = 1;"

echo ""
echo "👥 5. ИГРОКИ АРСЕНАЛА (в основном составе 21F3D7B3):"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "
SELECT COUNT(DISTINCT p.player_id) as arsenal_players_count
FROM wp_arsenal_players p
INNER JOIN wp_arsenal_team_contracts tc ON p.player_id = tc.player_id
WHERE tc.squad_id = '21F3D7B3';"

echo ""
echo "⚽ 6. МАТЧИ АРСЕНАЛА:"
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME -e "
SELECT 
    COUNT(*) as total_matches,
    SUM(CASE WHEN status = 'FT' THEN 1 ELSE 0 END) as finished,
    SUM(CASE WHEN status = 'NS' THEN 1 ELSE 0 END) as scheduled
FROM wp_arsenal_matches;"

echo ""
echo "✅ ПРОВЕРКА ЗАВЕРШЕНА"
