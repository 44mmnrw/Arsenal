# ✅ История клуба - Система управления (post_meta версия)

## 📋 Что изменилось

**Перешли с системы отдельной таблицы БД на WordPress post_meta:**
- ❌ Удалена таблица `wp_arsenal_history`
- ❌ Удалена система CRUD со списком записей
- ✅ Добавлена одна форма управления историей
- ✅ Данные хранятся в post_meta страницы
- ✅ Как Stat Cards - никакой таблицы БД не нужно

---

## 🎯 Как работает

### Manager класс
```php
// Получить историю со страницы
$history = Arsenal_History_Manager::get_history();
// Возвращает array с полями: title, description, scale, title_second, records, achievements, title_third, additional_cards

// Сохранить историю
Arsenal_History_Manager::save_history( $data );
```

### Хранение данных
- **Где:** WordPress post_meta таблица `wp_postmeta`
- **Meta-ключ:** `_arsenal_history_data`
- **Страница:** Страница с slug `history`
- **Формат:** JSON строка

---

## 🔧 Установка и использование

### 1️⃣ Создать страницу "История" в WordPress
```
Админ-панель → Pages → Add New
- Title: "История"
- Slug: "history"
- Publish
```

### 2️⃣ Открыть админ-панель
```
URL: http://arsenal.test/wp-admin
Menu: Арсенал → История клуба
```

### 3️⃣ Заполнить форму
- **Название главной секции** - заголовок
- **Описание** - основной текст (с редактором)
- **Временная шкала (JSON)** - массив событий
- **Рекорды и достижения** - название + JSON данные
- **Домашние стадионы** - название + JSON данные

### 4️⃣ Нажать "Сохранить историю"
Данные сохранятся в post_meta страницы.

---

## 📝 Структура данных

### Пример истории
```php
$history = array(
    'title'            => 'История клуба',
    'description'      => '<p>Текст истории...</p>',
    'scale'            => array(
        array( 'year' => 2018, 'event' => 'Основание клуба' ),
        array( 'year' => 2020, 'event' => 'Первый сезон' ),
    ),
    'title_second'     => 'Рекорды и достижения',
    'records'          => array(
        array( 'record' => 'Побед подряд', 'value' => '15' ),
    ),
    'achievements'     => array(
        array( 'title' => 'Чемпион лиги 2023', 'year' => 2023 ),
    ),
    'title_third'      => 'Домашние стадионы',
    'additional_cards' => array(
        array( 'name' => 'Стадион 1', 'years' => '2018-2020' ),
    ),
);
```

---

## 🖥️ Использование на фронтенде

### Получить историю на странице
```php
<?php
// В шаблоне page-history.php
$history = Arsenal_History_Manager::get_history();
?>

<div class="club-history">
    <h1><?php echo esc_html( $history['title'] ); ?></h1>
    <div class="description">
        <?php echo wp_kses_post( $history['description'] ); ?>
    </div>
    
    <!-- Временная шкала -->
    <?php if ( ! empty( $history['scale'] ) ) : ?>
        <div class="timeline">
            <?php foreach ( $history['scale'] as $event ) : ?>
                <div class="timeline-event">
                    <span class="year"><?php echo esc_html( $event['year'] ); ?></span>
                    <span class="event"><?php echo esc_html( $event['event'] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
```

---

## 📁 Структура файлов

```
wp-content/plugins/arsenal-team-manager/
├── admin/
│   ├── class-arsenal-history-manager.php     # Работа с post_meta
│   ├── class-arsenal-history-admin.php       # Admin UI
│   └── views/
│       └── history-form.php                  # Одна форма управления
├── arsenal-team-manager.php                  # Главный файл (обновлен)
└── ...

wp-content/themes/arsenal/
├── templates/
│   └── page-history.php                      # Страница истории (не изменялась)
├── assets/css/pages/
│   └── page-history.css                      # Стили (не изменялись)
└── ...
```

---

## 🔐 Безопасность

✅ **Реализовано:**
- Проверка прав: `manage_options`
- Nonce токены: `wp_nonce_field()` и `wp_verify_nonce()`
- Санитизация: `sanitize_text_field()`, `wp_kses_post()`
- JSON валидация: проверка на корректность JSON

---

## 🚀 Как получить историю в коде

### В PHP шаблоне
```php
$history = Arsenal_History_Manager::get_history();
echo $history['title'];          // "История клуба"
echo $history['description'];    // HTML контент
```

### В плагине
```php
// Получить и использовать
$history_data = Arsenal_History_Manager::get_history();
if ( ! empty( $history_data['achievements'] ) ) {
    // Вывести достижения
}
```

### В REST API (если нужно)
```
GET /wp-json/arsenal/v1/history
```

---

## 💾 Как данные сохраняются

```
WP Админ-форма → $_POST → Arsenal_History_Admin::handle_save_history()
                    ↓
            Arsenal_History_Manager::save_history()
                    ↓
         update_post_meta( page_id, '_arsenal_history_data', json )
                    ↓
        wp_postmeta таблица в БД (JSON строка)
```

---

## ✅ Проверка установки

Откройте SQL консоль и выполните:
```sql
SELECT * FROM wp_postmeta WHERE meta_key = '_arsenal_history_data';
```

Должна быть одна запись с JSON данными истории.

---

## 🎯 Плюсы нового подхода

✅ Нет отдельной таблицы БД  
✅ Простая система как Stat Cards  
✅ Данные привязаны к странице WordPress  
✅ Легче переносить между окружениями  
✅ Одна форма управления всеми полями  
✅ Встроенный WYSIWYG редактор для описания  
✅ JSON поля для гибкости данных  

---

## 📚 Связанные файлы

- [page-history.php](../../themes/arsenal/templates/page-history.php) - шаблон страницы
- [page-history.css](../../themes/arsenal/assets/css/pages/page-history.css) - стили страницы
- [Arsenal_History_Manager](class-arsenal-history-manager.php) - менеджер данных
- [Arsenal_History_Admin](class-arsenal-history-admin.php) - админ-интерфейс

---

## 🔄 История версий

| Версия | Дата | Изменения |
|--------|------|-----------|
| 2.0 | 17.01.2026 | Переход на post_meta (одна форма) |
| 1.0 | 16.01.2026 | Система с таблицей БД (УДАЛЕНО) |

---

**Статус:** ✅ Рабочее состояние  
**Версия:** 2.0  
**Дата:** 17 января 2026

## 🎯 Функциональность

### Основные возможности:
- **Список записей истории** - просмотр всех записей в таблице с сортировкой и постраничной навигацией
- **Создание записей** - добавление новых записей истории с заполнением всех полей
- **Редактирование** - изменение существующих записей
- **Удаление** - удаление записей из истории с подтверждением
- **JSON-данные** - поддержка сложных структур данных для времeнной шкалы, рекордов и достижений
- **Современный UI** - адаптивный интерфейс, совместимый с WordPress стилями

## 🗄️ Структура БД

**Таблица:** `wp_arsenal_history`

```sql
CREATE TABLE `wp_arsenal_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,                    -- Название главной секции
  `description` LONGTEXT,                            -- Основной текст истории
  `scale` JSON,                                      -- Временная шкала (JSON)
  `title_second` VARCHAR(255),                       -- Название второй секции
  `records` JSON,                                    -- Рекорды (JSON)
  `achievements` JSON,                               -- Достижения (JSON)
  `title_third` VARCHAR(255),                        -- Название третьей секции
  `additional_cards` JSON,                           -- Дополнительные карточки (JSON)
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_title` (`title`)
);
```

## 📁 Файловая структура

```
wp-content/plugins/arsenal-team-manager/admin/
├── class-arsenal-history-manager.php      # Класс управления историей (CRUD операции)
├── class-arsenal-history-admin.php         # Админ-интерфейс (отображение и обработка)
├── views/
│   ├── history-list.php                   # Шаблон списка записей
│   └── history-form.php                   # Шаблон формы добавления/редактирования
└── assets/css/
    └── admin.css                          # CSS стили (добавлены новые классы)
```

## 🔧 Классы и методы

### Arsenal_History_Manager (Singleton)

**Методы:**
- `get_instance()` - получить singleton экземпляр
- `get_all()` - получить все записи
- `get_by_id($id)` - получить запись по ID
- `create($data)` - создать новую запись
- `update($id, $data)` - обновить запись
- `delete($id)` - удалить запись

**Параметры `$data`:**
```php
[
    'title' => 'История клуба',              // VARCHAR(255)
    'description' => 'Текст истории...',     // LONGTEXT
    'scale' => '[{...}]',                    // JSON string
    'title_second' => 'Рекорды',             // VARCHAR(255)
    'records' => '[{...}]',                  // JSON string
    'achievements' => '[{...}]',             // JSON string
    'title_third' => 'Стадионы',             // VARCHAR(255)
    'additional_cards' => '[{...}]'          // JSON string
]
```

### Arsenal_History_Admin

**Методы:**
- `__init__()` - инициализация хуков
- `render_history_list()` - отображение списка
- `render_history_form()` - отображение формы
- `handle_create_history()` - обработка создания
- `handle_update_history()` - обработка обновления
- `handle_delete_history()` - обработка удаления

## 🎨 Меню и страницы

### Меню в админ-панели:
- **Родительский пункт:** Арсенал → История клуба
- **Slug:** `arsenal-history`
- **Скрытые страницы:**
  - `arsenal-history-add` - добавление новой записи
  - `arsenal-history-edit` - редактирование записи

### URL страниц:
- Список: `/wp-admin/admin.php?page=arsenal-history`
- Добавить: `/wp-admin/admin.php?page=arsenal-history-add`
- Редактировать: `/wp-admin/admin.php?page=arsenal-history-edit&history_id=1`

## 📊 JSON-форматы данных

### Временная шкала (scale)
```json
[
  {
    "year": 2018,
    "event": "Основание клуба"
  },
  {
    "year": 2021,
    "event": "Чемпион Первой лиги"
  }
]
```

### Рекорды (records)
```json
[
  {
    "name": "Чемпион Второй лиги",
    "year": 2019
  },
  {
    "name": "Двукратный чемпион Первой лиги",
    "years": "2021, 2023"
  }
]
```

### Достижения (achievements)
```json
[
  {
    "title": "Титулы",
    "items": [
      "Двукратный чемпион Первой лиги (2021, 2023)",
      "Чемпион Второй лиги (2019)"
    ]
  },
  {
    "title": "Лучшие результаты",
    "items": [
      "В Высшей лиге: 10 место (2024)",
      "Самая крупная победа: 3:0 (Шахтер, 2024)"
    ]
  }
]
```

### Дополнительные карточки (additional_cards)
```json
[
  {
    "name": "РЦОП-Стайки",
    "location": "Минск",
    "capacity": "1,500"
  },
  {
    "name": "Городской стадион",
    "location": "Борисов",
    "capacity": "5,402"
  }
]
```

## 🔐 Безопасность

### Проверки:
- **Права доступа:** `manage_options` (только администраторы)
- **Nonce validation:** все формы защищены nonce
- **Санитизация:** все входные данные санитизированы через `sanitize_text_field()`, `wp_kses_post()`
- **Подготовленные запросы:** использование `$wpdb->prepare()`

## 💻 Использование в коде

### Получить все записи истории:
```php
$history_records = Arsenal_History_Manager::get_all();

foreach ( $history_records as $record ) {
    echo $record->title;
    echo $record->description;
    $timeline = json_decode( $record->scale );
}
```

### Получить конкретную запись:
```php
$history = Arsenal_History_Manager::get_by_id( 1 );

if ( $history ) {
    $achievements = json_decode( $history->achievements, true );
    // Использование данных...
}
```

### Создать новую запись:
```php
$data = [
    'title' => 'История клуба',
    'description' => 'Текст истории...',
    'scale' => json_encode( $timeline_data ),
    'title_second' => 'Рекорды',
    'records' => json_encode( $records_data ),
    'achievements' => json_encode( $achievements_data ),
    'title_third' => 'Стадионы',
    'additional_cards' => json_encode( $stadiums_data )
];

$history_id = Arsenal_History_Manager::create( $data );
```

## 🎨 CSS классы

### Форма:
- `.arsenal-history-form` - основной контейнер формы
- `.form-section` - секция формы с белым фоном
- `.form-actions` - кнопки сохранения

### Список:
- `.wp-list-table` - таблица со списком записей
- `.column-id`, `.column-title` - колонки таблицы
- `.button` - кнопки действий

## 📱 Адаптивность

Все элементы интерфейса адаптированы для мобильных устройств:
- Форма: переносится в одну колонку на мобильных
- Таблица: скрываются неважные колонки на мобильных
- Кнопки: увеличиваются по размеру на мобильных

## 🔄 Интеграция с WordPress

### Хуки (Actions):
- `admin_post_arsenal_create_history` - обработка создания
- `admin_post_arsenal_update_history` - обработка обновления
- `admin_post_arsenal_delete_history` - обработка удаления

### Меню (Submenu Pages):
- `add_submenu_page()` - все страницы зарегистрированы через стандартный API

## 📝 Установка таблицы

**Автоматическая установка:**
```bash
php install-history-table.php
```

**Вывод:**
```
✓ Таблица wp_arsenal_history успешно создана (или уже существует)
```

## 🐛 Отладка

### Включить режим отладки WordPress:
```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

### Логи находятся в:
`wp-content/debug.log`

## 📖 Документация

Полная документация по разработке: `docs/QUICKSTART.md`

## 👤 Автор

Разработка: Arsenal Team Manager v1.0.0

## 📄 Лицензия

GPL v2 or later
