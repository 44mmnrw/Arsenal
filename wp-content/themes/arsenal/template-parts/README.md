# Template Parts - Структура компонентов

Данная папка содержит переиспользуемые компоненты темы, организованные по логическим категориям.

## Структура

### `components/` - Переиспользуемые компоненты
Статические компоненты без собственной бизнес-логики:
- **banner.php** - Баннер с параллакс-эффектом (главная страница)
- **news.php** - Блок последних новостей
- **sponsors.php** - Блок спонсоров/партнёров

**Использование:**
```php
get_template_part( 'template-parts/components/banner' );
```

### `blocks/` - Динамические блоки
Компоненты со своей SQL-логикой и данными из БД:
- **last-games.php** - Последние результаты матчей (динамический)
- **stats-bar.php** - Статистика клуба (динамический)
- **upcoming-match.php** - Ближайший матч (динамический)
- **tournament-table.php** - Турнирная таблица (JSON-based)
- **template-standings.php** - Таблица стоящих

**Использование:**
```php
get_template_part( 'template-parts/blocks/last-games' );
get_template_part( 'template-parts/blocks/stats-bar' );
```

### `layouts/` - Сложные секции
Компоненты со сложной структурой и логикой:
- **tournament-bracket.php** - Скобка турнира (используется в shortcode)

**Использование:**
```php
get_template_part( 'template-parts/layouts/tournament-bracket' );
```

## Соглашения

- Используйте `get_template_part()` для подключения компонентов из PHP файлов
- Компоненты **components/** должны быть полностью независимы от контекста
- Блоки **blocks/** могут использовать глобальные переменные и $_GET параметры
- Layouts **layouts/** содержат сложные структуры с множественной вложенностью
- Каждый компонент отвечает за один функциональный блок

## Миграция из старой структуры

Если вы находите старые пути вроде:
```php
get_template_part( 'template-parts/banner' );           // → template-parts/components/banner
get_template_part( 'template-parts/template', 'match' ); // → template-parts/blocks/match-details
```

Нужно обновить на новые пути согласно таблице выше.
