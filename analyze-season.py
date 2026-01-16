#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import json
import os

# Путь к файлу
json_file = r'c:\laragon\www\arsenal\tools\Py\abff_parser\results\Все матчи abbf_2025_details.json'

# Загружаем JSON
with open(json_file, 'r', encoding='utf-8') as f:
    data = json.load(f)

# Ищем матчи с season_id = E3701652
season_id = 'E3701652'
matches = data.get('data', {}).get('matches', [])

print(f"Поиск матчей сезона {season_id}")
print(f"Всего матчей в файле: {len(matches)}\n")

matching_matches = []
for match in matches:
    if match.get('season_id') == season_id:
        matching_matches.append(match)

print(f"Найдено матчей для сезона {season_id}: {len(matching_matches)}\n")

if matching_matches:
    print("Первые 10 матчей:")
    for i, match in enumerate(matching_matches[:10]):
        print(f"\n{i+1}. Match ID: {match.get('match_id', 'N/A')}")
        print(f"   Дата: {match.get('match_date', 'N/A')}")
        print(f"   Дом: {match.get('home_team_id', 'N/A')} ({match.get('home_team_name', 'N/A')})")
        print(f"   Гость: {match.get('away_team_id', 'N/A')} ({match.get('away_team_name', 'N/A')})")
        print(f"   Счет: {match.get('home_score', 'N/A')} - {match.get('away_score', 'N/A')}")
        print(f"   Статус: {match.get('status', 'N/A')}")
        print(f"   Турнир: {match.get('tournament_id', 'N/A')}")
    
    print(f"\n\nВсе уникальные турниры в этом сезоне:")
    tournaments = set()
    for match in matching_matches:
        tournaments.add(match.get('tournament_id', 'N/A'))
    for t in sorted(tournaments):
        count = len([m for m in matching_matches if m.get('tournament_id') == t])
        print(f"  - {t}: {count} матчей")
else:
    print(f"Матчи для сезона {season_id} не найдены")
    
    print("\nДоступные season_id в файле:")
    season_ids = set()
    for match in matches:
        season_ids.add(match.get('season_id', 'N/A'))
    for sid in sorted(season_ids):
        count = len([m for m in matches if m.get('season_id') == sid])
        print(f"  - {sid}: {count} матчей")
