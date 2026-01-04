# Template Parts - Структура компонентов

Данная папка содержит переиспользуемые компоненты темы, организованные по логическим категориям.

## Структура

### `components/` - Многоразовые компоненты
Статические компоненты без собственной бизнес-логики:
- **banner.php** - Баннер с параллакс-эффектом (главная страница)
- **news.php** - Блок последних новостей
- **players-grid.php** - Сетка игроков команды
- **sponsors.php** - Блок спонсоров/партнёров
- **calendar-full.php** - Полный календарь матчей
- **tournament-table.php** - Турнирная таблица (JSON-based)

**Использование:**
```php
get_template_part( 'template-parts/components/banner' );
```

### `blocks/` - Динамические блоки
Компоненты со своей SQL-логикой и данными из БД:
- **last-games.php** - Последние результаты матчей (динамический)
- **stats-bar.php** - Статистика клуба (динамический)
- **upcoming-match.php** - Ближайший матч (динамический)
- **match-details.php** - Детали конкретного матча (используется в shortcode)
- **tournament-bracket.php** - Скобка турнира (используется в shortcode)
- **template-standings.php** - Таблица стоящих

**Использование:**
```php
get_template_part( 'template-parts/blocks/last-games' );
get_template_part( 'template-parts/blocks/stats-bar' );
```

### `deprecated/` - Архив старых версий
Старые версии блоков, оставлены для справки:
- **last-games-dynamic.php** - Старая версия блока последних игр
- **stats-bar-dynamic.php** - Старая версия статистики
- **upcoming-match-dynamic.php** - Старая версия ближайшего матча

⚠️ **Эти файлы больше не используются!** Удалить можно, но пока оставлены для справки.

## Соглашения

- Используйте `get_template_part()` для подключения компонентов из PHP файлов
- Компоненты **components/** должны быть полностью независимы от контекста
- Блоки **blocks/** могут использовать глобальные переменные и $_GET параметры
- Каждый компонент отвечает за один функциональный блок

## Миграция из старой структуры

Если вы находите старые пути вроде:
```php
get_template_part( 'template-parts/banner' );           // → template-parts/components/banner
get_template_part( 'template-parts/template', 'match' ); // → template-parts/blocks/match-details
```

Нужно обновить на новые пути согласно таблице выше.
