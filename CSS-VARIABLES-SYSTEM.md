# CSS Variables System для управления контейнерами

## Обзор

Реализована система управления паддингом контейнеров через CSS переменные. Вместо硬кодирования паддинга в каждом файле стилей, используются переменные `--container-padding-x` и `--container-padding-y`.

## Основные переменные (style.css)

```css
:root {
    --container-padding-x: 0;      /* Горизонтальный паддинг */
    --container-padding-y: 0;      /* Вертикальный паддинг */
}
```

## Правило для всех контейнеров

```css
.container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

## Переопределения по страницам

### 1. **Header** (`header.css`)
```css
.site-header {
    --container-padding-x: 1.25rem;
}

.header-container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

### 2. **Players Grid / Teams Section** (`players-grid.css`)
```css
.teams-section {
    --container-padding-x: 20px;
}

.container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

### 3. **Coaches Page** (`page-coaches.css`)
- Наследует переменные из `players-grid.css` и `.teams-section`
- `--container-padding-x: 20px`

### 4. **Standings Page** (`standings.css`)
```css
.standings-page {
    --container-padding-x: 20px;
}
```

### 5. **Tournament Page** (`page-tournament.css`)
```css
.tournament-page {
    --container-padding-x: 2.5rem;
}

.tournament-container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

### 6. **News Page** (`news-page.css`)
```css
.news-page {
    --container-padding-x: 20px;
}

.single-news-page {
    --container-padding-x: 20px;
    --container-padding-y: 48px;
}
```

**Media-queries (1200px):**
```css
@media (max-width: 1200px) {
    .news-page {
        --container-padding-x: 40px;
    }
}
```

### 7. **Match Page** (`page-match.css`)
```css
.match-page {
    --container-padding-x: 3.9375rem;
    --container-padding-y: 1.5rem;
}

.match-container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

**Media-queries:**
- **1200px-1024px:** `padding: 1.25rem 2rem;`
- **До 1024px:** `padding: 1rem 1.5rem;`
- **До 768px:** `padding: 0.75rem;`
- **До 480px:** `padding: 0.5rem;`

### 8. **Player Page** (`player-page.css`)
```css
.player-page {
    --container-padding-x: 40px;
}

.player-container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

**Media-queries:**
- **768px:** `--container-padding-x: 20px;`
- **480px:** `--container-padding-x: 12px;`

### 9. **Main Content Sections** (`main.css`)
```css
.news-section {
    --container-padding-x: 1rem;
}

.last-games-section {
    --container-padding-x: 1rem;
}

.tournament-table-section {
    --container-padding-x: 1rem;
}

.sponsors-section {
    --container-padding-x: 1rem;
}
```

### 10. **Footer** (`footer.css`)
```css
.site-footer {
    --container-padding-x: 1rem;
}

.footer-main .container {
    padding: var(--container-padding-y) var(--container-padding-x);
}

.footer-bottom .container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

## Преимущества системы

1. ✅ **Единая точка управления** - все паддинги контейнеров в переменных
2. ✅ **Легкость отладки** - быстро найти и изменить паддинг любой страницы
3. ✅ **Масштабируемость** - просто добавить новую переменную для нового блока
4. ✅ **DRY принцип** - избегаем дублирования кода
5. ✅ **Поддержка responsive** - media-queries просто переопределяют переменные
6. ✅ **Производительность** - нет лишних CSS правил

## Как добавить новый контейнер

1. Определите класс контейнера в CSS:
```css
.my-page {
    --container-padding-x: 20px;
    --container-padding-y: 10px;
}

.my-container {
    padding: var(--container-padding-y) var(--container-padding-x);
}
```

2. Используйте переменные в HTML контейнере
3. При необходимости добавьте media-query переопределения

## Текущее состояние

✅ **Обновлены файлы:**
- `style.css` - переменные в :root и правило .container
- `header.css` - переменные в .site-header
- `players-grid.css` - переменные в .teams-section
- `page-coaches.css` - наследует из players-grid.css
- `standings.css` - переменные в .standings-page
- `page-tournament.css` - переменные в .tournament-page
- `news-page.css` - переменные в .news-page и .single-news-page
- `main.css` - переменные в .news-section, .last-games-section, .tournament-table-section, .sponsors-section
- `footer.css` - переменные в .site-footer
- `player-page.css` - переменные в .player-page с media-query поддержкой
- `page-match.css` - переменные в .match-page с полной responsive поддержкой

## Тестирование

При добавлении нового контейнера или изменении паддинга:
1. Проверьте на всех экранах (desktop, tablet, mobile)
2. Убедитесь, что переменная работает корректно
3. Проверьте media-queries

## Документация

См. также:
- `docs/DATABASE-SCHEMA.md` - схема БД
- `DEPLOY.md` - процесс деплоя
- `README.md` - основная информация проекта
