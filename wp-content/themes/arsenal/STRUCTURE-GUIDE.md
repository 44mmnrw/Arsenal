# Быстрая справка: Новая структура темы Arsenal

## 🗂️ Где что находится

### Template Parts
```php
// Статические компоненты
get_template_part( 'template-parts/components/banner' );
get_template_part( 'template-parts/components/news' );
get_template_part( 'template-parts/components/sponsors' );

// Динамические блоки (с БД)
get_template_part( 'template-parts/blocks/stats-bar' );
get_template_part( 'template-parts/blocks/last-games' );
get_template_part( 'template-parts/blocks/upcoming-match' );
get_template_part( 'template-parts/blocks/tournament-table' );
get_template_part( 'template-parts/blocks/template-standings' );

// Сложные layouts
get_template_part( 'template-parts/layouts/tournament-bracket' );
```

### Классы темы
Все классы в `inc/classes/`:
```php
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-players.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-sponsors.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-management-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-department-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-history-carbon-adapter.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-academy-carbon-adapter.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-academy-history-carbon-adapter.php';
```

### Функции
Все функции в `inc/functions/`:
- `template-functions.php` - общие функции шаблонов
- `match-functions.php` - функции для матчей
- `player-functions.php` - функции для игроков
- `timeline-functions.php` - функции таймлайна

### Админ интерфейс
Все админ файлы в `inc/admin/`:
```php
// Metaboxes
inc/admin/metaboxes/squad-selector-metabox.php
inc/admin/metaboxes/stadium-selector-metabox.php
inc/admin/metaboxes/staff-department-metabox.php

// Customizer
inc/admin/customizer.php
```

## 📝 Добавление нового компонента

### 1. Статический компонент
```php
// Создать файл
template-parts/components/my-component.php

// Использовать
get_template_part( 'template-parts/components/my-component' );
```

### 2. Динамический блок
```php
// Создать файл с SQL-логикой
template-parts/blocks/my-block.php

// Использовать
get_template_part( 'template-parts/blocks/my-block' );
```

### 3. Новый класс
```php
// Создать файл
inc/classes/class-arsenal-my-manager.php

// Добавить в bootstrap.php
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-my-manager.php';
```

### 4. Новый metabox
```php
// Создать файл
inc/admin/metaboxes/my-metabox.php

// Добавить в bootstrap.php (в блоке is_admin())
require_once ARSENAL_THEME_DIR . '/inc/admin/metaboxes/my-metabox.php';
```

## 🔧 Центральная загрузка

Все компоненты загружаются через **один файл**:
```php
// functions.php
require_once ARSENAL_THEME_DIR . '/inc/bootstrap.php';
```

## 📂 Структура дерева

```
template-parts/
├── components/    ← Простые переиспользуемые блоки
├── blocks/        ← Динамические блоки с БД
└── layouts/       ← Сложные секции

inc/
├── classes/       ← ВСЕ классы здесь
├── functions/     ← Вспомогательные функции
├── admin/         ← Админ-панель (только is_admin)
│   ├── metaboxes/
│   └── customizer.php
├── database/      ← SQL миграции
└── bootstrap.php  ← Центральная загрузка
```

## ⚡ Быстрые команды

```bash
# Проверить структуру
ls wp-content/themes/arsenal/template-parts
ls wp-content/themes/arsenal/inc

# Найти использование компонента
grep -r "get_template_part.*banner" wp-content/themes/arsenal

# Найти подключения классов
grep -r "require_once.*class-" wp-content/themes/arsenal/inc/bootstrap.php
```

---

**Важно:** Все изменения обратно совместимы. Функциональность не изменилась, улучшена только структура.
