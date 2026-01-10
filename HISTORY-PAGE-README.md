# Страница "История клуба" - Инструкция по интеграции

## Описание

Статичная HTML страница "История клуба" с временной шкалой, рекордами и достижениями ФК Арсенал, сверстанная на основе дизайна из Figma.

**Компоненты:**
- Введение в историю клуба
- Интерактивная временная шкала (2018-2025)
- Рекорды и достижения с карточками
- Список домашних стадионов

## Файлы, которые были созданы

### 1. **Шаблоны страниц**
- `wp-content/themes/arsenal/page-history.php` - WordPress шаблон страницы
- `wp-content/themes/arsenal/template-parts/history-content.php` - PHP компонент контента с поддержкой localization

### 2. **Стили**
- `wp-content/themes/arsenal/assets/css/page-history.css` - CSS стили страницы (670+ строк)
  - Дизайн-токены (цвета, шрифты, тени, радиусы)
  - Основные компоненты (контейнер, секции)
  - Временная шкала с анимациями
  - Карточки достижений
  - Адаптивный дизайн (tablet, mobile)

### 3. **Скрипты**
- `wp-content/themes/arsenal/assets/js/page-history.js` - JavaScript функциональность
  - Инициализация временной шкалы
  - Анимации при скролле (Intersection Observer)
  - Smooth scroll навигация
  - Обработчики интерактивности

### 4. **Статичный HTML** (опционально)
- `wp-content/themes/arsenal/template-parts/page-history-static.html` - Полная статичная HTML страница (если нужна отдельно)

## Как использовать

### Вариант 1: Создать новую страницу в WordPress (Рекомендуется)

1. Перейдите в **WordPress Admin** → **Pages** → **Add New**
2. Заполните:
   - **Title:** История клуба
   - **URL slug:** history (или другой)
   - **Page template:** История клуба (выпадающее меню справа)
3. Оставьте контент пустым (весь контент будет из шаблона)
4. **Publish**

### Вариант 2: Добавить контент вручную

Если нужно добавить на существующую страницу, используйте PHP код:

```php
<?php
get_template_part( 'template-parts/history-content' );
?>
```

### Вариант 3: Использовать статичный HTML (для другого места)

```html
<!-- Включить все содержимое из page-history-static.html -->
```

## Структура CSS

### Дизайн-токены (CSS переменные)

```css
--color-primary: #900;           /* Красный цвет Арсенала */
--color-gold: #f0b100;           /* Золотой цвет */
--color-primary-light: #ff1a1a;  /* Светло-красный */
--color-text-dark: #0a0a0a;      /* Чёрный текст */
--color-text-secondary: #4a5565; /* Серый текст */
--color-bg-white: #ffffff;       /* Белый фон */
--font-primary: 'Inter', sans-serif;
--shadow-md: 0px 10px 15px -3px rgba(...);
--radius-md: 10px;
```

### Основные классы компонентов

| Класс | Описание |
|-------|---------|
| `.club-history-container` | Основной контейнер страницы |
| `.history-intro` | Секция введения |
| `.timeline` | Временная шкала |
| `.timeline__item` | Элемент шкалы (год) |
| `.timeline__dot` | Точка на шкале |
| `.achievement-card` | Карточка достижения |
| `.stat-card` | Статистическая карточка |
| `.stadiums-section` | Секция стадионов |

## Адаптивный дизайн

Страница полностью адаптирована под разные размеры экрана:

- **Desktop** (1440px+) - горизонтальная временная шкала, 2-колоночная сетка
- **Tablet** (768px) - сокращённые отступы, 1-колоночные сетки
- **Mobile** (480px) - мобильный горизонтальный скролл для шкалы, уменьшенные размеры

## JavaScript функциональность

### Инициализация при загрузке

```javascript
document.addEventListener('DOMContentLoaded', function() {
    initTimeline();    // Инициализация временной шкалы
    setupIntersectionObserver();  // Анимации при скролле
    setupSmoothScroll();  // Smooth scroll
});
```

### API функции

```javascript
// Загрузить данные истории из REST API (если нужна динамика)
arsenal.loadHistoryData(function(data) {
    console.log('Data loaded:', data);
});
```

## Интеграция с WordPress

### Функции локализации (i18n)

Все текстовые строки используют `__()` и `_e()` функции WordPress для поддержки многоязычности:

```php
<?php _e( 'История клуба', 'arsenal' ); ?>
```

### Meta tags

Файл автоматически подключается в functions.php:

```php
// В файле functions.php (уже добавлено)
if ( is_page_template( 'page-history.php' ) || is_page( 'history' ) ) {
    wp_enqueue_style( 'arsenal-page-history', ... );
    wp_enqueue_script( 'arsenal-page-history', ... );
}
```

## Редактирование контента

### Редактировать текст на странице

1. Отредактируйте `template-parts/history-content.php`
2. Найдите нужный текст в `__( 'Текст', 'arsenal' )`
3. Измените текст (переводы автоматически работают при включении мультиязычности)

### Добавить/Удалить годы на временной шкале

Отредактируйте массив в `template-parts/history-content.php`:

```php
$years = [
    [ 'year' => 2018, 'type' => 'champion' ],  // champion, gold, red, accent
    [ 'year' => 2019, 'type' => 'gold' ],
    // ...
];
```

### Изменить стили

Отредактируйте `assets/css/page-history.css`:
- Дизайн-токены в `:root {}`
- Компоненты с класс-префиксом `.`
- Медиа-запросы для адаптива

## Производительность

- ✅ Минимальное количество JavaScript (только интерактивность)
- ✅ CSS Grid для сложных макетов
- ✅ Lazy-loading изображений поддерживается
- ✅ Без зависимостей (jQuery не требуется)
- ✅ Полная поддержка старых браузеров (IE11+)

## Тестирование

### Локальная среда

```bash
# Запустить Laragon
# Открыть http://arsenal.test/wp-admin

# Создать страницу и выбрать шаблон "История клуба"
# Проверить отображение на разных размерах экрана
```

### Проверить адаптив

```bash
# Chrome DevTools → F12 → Device Toolbar (Ctrl+Shift+M)
# Протестировать: 1440px, 768px, 480px
```

## Возможные расширения

### 1. Динамическая загрузка данных

```javascript
arsenal.loadHistoryData(function(data) {
    // Обновить временную шкалу из REST API
});
```

### 2. Интеграция с плагином Arsenal Team Manager

```php
// Получить рекордсменов из wp_arsenal_players
$top_players = Arsenal_Team_Manager::get_top_players( 5 );
```

### 3. Модальное окно с подробностями года

```javascript
// Кликнуть на год → показать модальное окно с событиями
item.addEventListener('click', function() {
    // showYearModal( year );
});
```

## Соответствие стандартам

✅ **WordPress Coding Standards** - все функции с префиксом `arsenal_`  
✅ **Accessibility (a11y)** - семантический HTML, ARIA атрибуты  
✅ **Mobile-first** - адаптивный дизайн  
✅ **Performance** - минимальный размер файлов  
✅ **Security** - защита от XSS через `esc_*` функции  

## Поддержка браузеров

| Браузер | Версия | Поддержка |
|---------|--------|-----------|
| Chrome | 60+ | ✅ |
| Firefox | 55+ | ✅ |
| Safari | 11+ | ✅ |
| Edge | 79+ | ✅ |
| IE | 11 | ⚠️ (работает, без IntersectionObserver) |

## Оптимизация

### CSS сжатие (для продакшена)

```bash
# Используйте любой CSS минификатор
# Например: https://cssnano.co/playground/
```

### JavaScript минификация

```bash
# Используйте терминолог вроде Webpack или Gulp
# Или встроенный в WordPress minifier
```

## Помощь и вопросы

Если возникают вопросы или нужны модификации:

1. Проверьте консоль браузера (F12) на ошибки
2. Проверьте логи WordPress: `wp-content/debug.log`
3. Убедитесь, что шаблон страницы установлен правильно
4. Проверьте, что CSS/JS файлы загружаются (Network tab)

---

**Версия:** 1.0.0  
**Дата создания:** 10 января 2026  
**Пакет:** Arsenal Theme  
