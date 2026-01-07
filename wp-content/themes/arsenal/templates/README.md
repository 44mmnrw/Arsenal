# Page Templates - Шаблоны страниц

Данная папка содержит кастомные шаблоны страниц, используемые WordPress для различных типов контента.

## Структура

### Page Templates
- **page-standings.php** - Турнирная таблица (структура + компонент blocks/template-standings)
- **page-tournament.php** - Страница турнира (структура + компонент blocks/tournament-bracket)
- **page-match.php** - Детали матча (структура + компонент blocks/match-details)
- **page-squad.php** - Состав команды (структура + компонент components/players-grid)
- **page-calendar.php** - Календарь матчей (структура + компонент components/calendar-full)
- **page-history.php** - История клуба (табы, временная шкала, рекорды, стадионы)
- **page-player.php** - Профиль игрока

## Соглашения

### Именование
- Все файлы имеют префикс `page-` для унификации
- Используется kebab-case: `page-player-stats.php`
- WordPress автоматически сопоставляет `page-{slug}.php` с пользовательскими страницами по slug'у

### Структура page template'а
Типичный шаблон страницы выглядит так:
```php
<?php
/**
 * Шаблон: Название страницы
 * Description: Краткое описание
 */

get_header();
?>

<main id="main" class="site-main page-name">
    <div class="container">
        <!-- Контент страницы -->
        <?php get_template_part( 'template-parts/...' ); ?>
    </div>
</main>

<?php get_footer();
```

### Как связать page template с страницей в WordPress
1. В админке откройте нужную страницу (Pages > Edit)
2. В правой колонке найдите блок "Template" 
3. Выберите нужный шаблон из выпадающего списка
4. Нажмите Update

### Использование компонентов
Page templates включают компоненты из `template-parts/`:
```php
get_template_part( 'template-parts/components/players-grid' );
get_template_part( 'template-parts/blocks/last-games' );
```

## Иерархия Template'ов

WordPress используя следующий порядок поиска шаблона:
1. `page-{slug}.php` - для конкретной страницы (например, `page-squad.php` для `/squad/`)
2. `page-{id}.php` - для страницы по ID
3. `page.php` - для всех страниц (если нет специфичного шаблона)
4. `index.php` - fallback шаблон

## Переиспользованные Template'ы

Некоторые page templates повторяют структуру друг друга:
- `page-standings.php` и `page-tournament.php` имеют похожий layout
- Рассмотрите рефакторинг в будущем: создать базовый `page-layout.php` и использовать его через `get_template_part()`
