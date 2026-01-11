import os
import re
from collections import Counter

css_dir = r"c:\laragon\www\arsenal\wp-content\themes\arsenal\assets\css"
all_content = ""

# Читаем все CSS файлы
for file in os.listdir(css_dir):
    if file.endswith(".css"):
        with open(os.path.join(css_dir, file), 'r', encoding='utf-8') as f:
            all_content += f.read() + "\n"

# Ищем цвета
hex_colors = re.findall(r'#[0-9A-Fa-f]{3}\b|#[0-9A-Fa-f]{6}\b', all_content)
rgb_colors = re.findall(r'rgba?\s*\([^)]*\)', all_content)
var_colors = re.findall(r'var\(--[^)]*(?:color|red|green|blue|gray|white|black|gold|beige)[^)]*\)', all_content)

# Нормализуем hex к нижнему регистру
hex_colors = [c.lower() for c in hex_colors]
rgb_colors = [c.lower() for c in rgb_colors]
var_colors = [c.lower() for c in var_colors]

# Объединяем и считаем
all_colors = hex_colors + rgb_colors + var_colors
color_count = Counter(all_colors)

# Сортируем по количеству
sorted_colors = sorted(color_count.items(), key=lambda x: x[1], reverse=True)

# Сохраняем в файл
with open(r"c:\laragon\www\arsenal\COLOR-FREQUENCY.txt", 'w', encoding='utf-8') as f:
    for color, count in sorted_colors:
        f.write(f"{color} - {count}\n")

# Выводим первые 50
for color, count in sorted_colors[:50]:
    print(f"{color} - {count}")

print(f"\n✅ Всего уникальных цветов: {len(sorted_colors)}")
