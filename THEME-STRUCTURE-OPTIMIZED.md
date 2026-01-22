# Структура темы Arsenal - После оптимизации

## Изменения структуры файлов

### ✅ Выполненные изменения

#### 1. Реорганизация `template-parts/`

**БЫЛО:**
```
template-parts/
  ├── widgets/         ← неясное название
  │   ├── banner.php
  │   ├── news.php
  │   ├── sponsors.php
  │   ├── stats-bar.php
  │   ├── last-games.php
  │   ├── upcoming-match.php
  │   ├── tournament-table.php
  │   └── template-standings.php
  ├── sections/        ← 1 файл
  │   └── tournament-bracket.php
  └── archive/         ← deprecated
      ├── last-games-dynamic.php
      ├── stats-bar-dynamic.php
      └── upcoming-match-dynamic.php
```

**СТАЛО:**
```
template-parts/
  ├── components/      ← переиспользуемые статические компоненты
  │   ├── banner.php
  │   ├── news.php
  │   └── sponsors.php
  ├── blocks/          ← динамические блоки с SQL-логикой
  │   ├── stats-bar.php
  │   ├── last-games.php
  │   ├── upcoming-match.php
  │   ├── tournament-table.php
  │   └── template-standings.php
  └── layouts/         ← сложные секции
      └── tournament-bracket.php
```

**Удалено:** `archive/`, `widgets/`, `sections/`

#### 2. Реорганизация `inc/`

**БЫЛО:**
```
inc/
  ├── classes/
  │   ├── class-arsenal-staff-manager.php
  │   ├── class-arsenal-players.php
  │   ├── class-arsenal-sponsors.php
  │   ├── class-arsenal-management-manager.php
  │   └── class-arsenal-staff-department-manager.php
  ├── functions/
  │   ├── template-functions.php
  │   ├── match-functions.php
  │   ├── player-functions.php
  │   └── timeline-functions.php
  ├── database/
  │   ├── create-tables.sql
  │   └── create-academy-recruitment-table.sql
  ├── carbon-fields-init.php
  ├── class-history-carbon-adapter.php        ← не в classes/
  ├── class-academy-carbon-adapter.php        ← не в classes/
  ├── class-academy-history-carbon-adapter.php← не в classes/
  ├── class-arsenal-installer.php             ← не в classes/
  ├── squad-selector-metabox.php              ← не в admin/
  ├── stadium-selector-metabox.php            ← не в admin/
  ├── staff-department-metabox.php            ← не в admin/
  └── customizer.php                          ← не в admin/
```

**СТАЛО:**
```
inc/
  ├── classes/                                ← ВСЕ классы теперь здесь
  │   ├── class-arsenal-staff-manager.php
  │   ├── class-arsenal-players.php
  │   ├── class-arsenal-sponsors.php
  │   ├── class-arsenal-management-manager.php
  │   ├── class-arsenal-staff-department-manager.php
  │   ├── class-history-carbon-adapter.php
  │   ├── class-academy-carbon-adapter.php
  │   └── class-academy-history-carbon-adapter.php
  ├── functions/
  │   ├── template-functions.php
  │   ├── match-functions.php
  │   ├── player-functions.php
  │   └── timeline-functions.php
  ├── database/
  │   ├── create-tables.sql
  │   └── create-academy-recruitment-table.sql
  ├── admin/                                  ← NEW: админ интерфейс
  │   ├── metaboxes/
  │   │   ├── squad-selector-metabox.php
  │   │   ├── stadium-selector-metabox.php
  │   │   └── staff-department-metabox.php
  │   └── customizer.php
  ├── class-arsenal-installer.php             ← остался в корне (особый класс)
  ├── carbon-fields-init.php
  └── bootstrap.php                           ← NEW: центральный файл подключений
```

#### 3. Упрощение `functions.php`

**БЫЛО (множественные require_once):**
```php
require_once ARSENAL_THEME_DIR . '/inc/class-arsenal-installer.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-players.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-sponsors.php';
// ... и так далее
```

**СТАЛО (один bootstrap):**
```php
require_once ARSENAL_THEME_DIR . '/inc/bootstrap.php';
```

### 📋 Обновленные пути

#### Вызовы `get_template_part()`:

- ✅ `template-parts/widgets/banner` → `template-parts/components/banner`
- ✅ `template-parts/widgets/news` → `template-parts/components/news`
- ✅ `template-parts/widgets/sponsors` → `template-parts/components/sponsors`
- ✅ `template-parts/widgets/stats-bar` → `template-parts/blocks/stats-bar`
- ✅ `template-parts/widgets/last-games` → `template-parts/blocks/last-games`
- ✅ `template-parts/widgets/upcoming-match` → `template-parts/blocks/upcoming-match`
- ✅ `template-parts/widgets/tournament-table` → `template-parts/blocks/tournament-table`
- ✅ `template-parts/sections/tournament-bracket` → `template-parts/layouts/tournament-bracket`

#### Вызовы `require_once()`:

Все подключения теперь централизованы в `/inc/bootstrap.php`:
- ✅ Классы из `inc/classes/`
- ✅ Функции из `inc/functions/`
- ✅ Админ файлы из `inc/admin/` (только для is_admin())
- ✅ Carbon Fields инициализация

### 🎯 Преимущества новой структуры

1. **Понятная иерархия** - сразу видно назначение каждой папки
2. **Централизованная загрузка** - все подключения в `bootstrap.php`
3. **Логическое разделение** - классы, функции, админ-интерфейс отдельно
4. **Производительность** - админ файлы загружаются только в админке
5. **Масштабируемость** - легко добавлять новые компоненты
6. **Чистота кода** - убраны устаревшие файлы

### 📝 Новая структура папок

```
wp-content/themes/arsenal/
├── assets/
│   ├── css/
│   │   ├── fonts.css
│   │   ├── header.css
│   │   ├── main.css
│   │   ├── footer.css
│   │   └── pages/              (16 файлов)
│   ├── js/
│   │   ├── banner-carousel.js
│   │   ├── main.js
│   │   └── ...
│   ├── fonts/
│   ├── images/
│   └── icons/
├── inc/
│   ├── classes/                (8 классов)
│   ├── functions/              (4 файла)
│   ├── database/               (SQL миграции)
│   ├── admin/
│   │   ├── metaboxes/          (3 metabox'а)
│   │   └── customizer.php
│   ├── bootstrap.php           ← NEW
│   ├── class-arsenal-installer.php
│   └── carbon-fields-init.php
├── template-parts/
│   ├── components/             (3 компонента)
│   ├── blocks/                 (5 блоков)
│   └── layouts/                (1 layout)
├── templates/                  (12 page templates)
├── dynamic-pages/              (3 dynamic templates)
├── data/                       (JSON данные)
├── vendor/                     (Composer зависимости)
├── functions.php               ← упрощен
├── front-page.php
├── header.php
├── footer.php
├── style.css
└── ...
```

### 🔄 Следующие шаги (опционально)

1. Объединить `templates/` и `dynamic-pages/` (было пропущено)
2. Добавить автозагрузчик классов (PSR-4)
3. Создать класс `Arsenal_Template_Loader` для унификации загрузки шаблонов
4. Добавить unit-тесты для критических функций

---

**Дата изменений:** 22 января 2026  
**Статус:** ✅ Реализовано (этапы 2, 3, 4)
