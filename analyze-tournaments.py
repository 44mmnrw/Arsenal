#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import json

json_file = r'c:\laragon\www\arsenal\tools\Py\abff_parser\results\Все матчи abbf_2025_details.json'

with open(json_file, 'r', encoding='utf-8') as f:
    data = json.load(f)

matches = data.get('data', {}).get('matches', [])

# Группируем по season_id и tournament_id
season_tournament_map = {}
for match in matches:
    season_id = match.get('season_id', 'N/A')
    tournament_id = match.get('tournament_id', 'N/A')
    
    if season_id not in season_tournament_map:
        season_tournament_map[season_id] = {}
    
    if tournament_id not in season_tournament_map[season_id]:
        season_tournament_map[season_id][tournament_id] = 0
    
    season_tournament_map[season_id][tournament_id] += 1

print("Турниры по сезонам:\n")
print("=" * 60)

# Ищем нужный нам tournament_id 71CFDAA6
target_tournament = '71CFDAA6'

for season_id in sorted(season_tournament_map.keys()):
    tournaments = season_tournament_map[season_id]
    print(f"\nСезон: {season_id}")
    for tour_id, count in sorted(tournaments.items()):
        marker = " ← TARGET" if tour_id == target_tournament else ""
        print(f"  - {tour_id}: {count} матчей{marker}")

print("\n" + "=" * 60)
print(f"\nМатчи для турнира {target_tournament} по сезонам:")
target_seasons = {}
for season_id, tournaments in season_tournament_map.items():
    if target_tournament in tournaments:
        target_seasons[season_id] = tournaments[target_tournament]

for season_id in sorted(target_seasons.keys()):
    count = target_seasons[season_id]
    print(f"  - {season_id}: {count} матчей")
