# Руководство разработки WordPress - ⚽ ФК Арсенал Дзержинск

## Обзор проекта
Сайт **официального футбольного клуба Арсенал Дзержинск** (Беларусь) на **WordPress 6.9**.

**Среды:**
- **Локальная**: Laragon (Windows) → `http://arsenal.test`
- **Продакшн**: `http://1779917-cq85026.twc1.net/` (через Git-деплой)
- **БД**: `arsenal` (MySQL, префикс `wp_`, user: `arsenal_user`)
- **Репозиторий**: https://github.com/44mmnrw/Arsenal (ветка `dev`)

⚠️ **КРИТИЧНОЕ**: Известна проблема несовместимости ID команд в БД (см. `CRITICAL_BUG_TEAM_IDS.md`). 76% матчей имеют невалидные ссылки на команды (HEX ID vs NUMERIC ID).

## Архитектура проекта

### Трёхуровневая архитектура

**1. Тема Arsenal (Презентация)**
- **Корень**: `wp-content/themes/arsenal/`
- Классическая PHP-тема с Vanilla CSS/JS (без блочного редактора)
- Система стилей: `fonts.css` → `style.css` (base) → `header.css` → `main.css` → `footer.css`
- Префикс функций: `arsenal_*`
- Template parts: `template-parts/*-dynamic.php` (запросы к БД через `$wpdb`)

**2. Плагин Arsenal Team Manager (Бизнес-логика)**
- **Корень**: `wp-content/plugins/arsenal-team-manager/`
- Админка управления командой: игроки, позиции, статистика
- Singleton паттерн: `Arsenal_Team_Manager::get_instance()`
- Admin меню: `arsenal-team` (родительский), `arsenal-players`, `arsenal-player-add`

**3. Кастомные таблицы БД (Данные)**
- **Префикс**: `wp_arsenal_*` (8 основных таблиц)
- Реляционная модель: лиги → сезоны → команды → игроки → матчи → события
- **Класс БД**: `Arsenal_Database` (singleton) в `wp-content/themes/arsenal/inc/database/`
- **Миграции**: `create-tables-simple.sql` + `add-foreign-keys.sql`

### Структура темы Arsenal
```
arsenal/
├── assets/
│   ├── css/                    # Модульные стили (fonts, header, main, footer)
│   ├── js/banner-carousel.js   # Parallax эффект баннера
│   └── images/                 # Спрайты, логотипы
├── inc/
│   ├── database/               # Класс Arsenal_Database + SQL миграции
│   ├── api/                    # REST API endpoints (если есть)
│   ├── customizer.php          # Настройки темы (1400+ строк)
│   └── template-functions.php  # Вспомогательные функции
├── template-parts/
│   ├── stats-bar-dynamic.php   # Блок статистики (SQL запросы)
│   ├── stats-bar.php           # Стандартный блок статистики
│   ├── last-games-dynamic.php  # Последние матчи (SQL)
│   ├── last-games.php          # Альтернативная версия
│   ├── upcoming-match-dynamic.php  # Ближайший матч (SQL)
│   ├── upcoming-match.php       # Альтернативная версия
│   ├── players-grid.php        # Сетка игроков
│   ├── tournament-table.php    # Турнирная таблица
│   ├── template-tournament-bracket.php # Скобка турнира
│   ├── banner.php              # Баннер с изображением
│   ├── calendar-full.php       # Полный календарь
│   ├── news.php                # Новости
│   ├── sponsors.php            # Спонсоры
│   └── template-standings.php  # Таблица стоящих
├── functions.php               # Регистрация стилей/скриптов, хуки
├── front-page.php              # Главная страница (баннер + статистика)
├── page-standings.php          # Страница турнирной таблицы
├── page-tournament.php         # Страница турнира
└── templates/                  # Custom page templates
```

**Паттерн template-parts**: использованы `*-dynamic.php` версии для компонентов с прямыми SQL запросами через `$wpdb`. Обычные версии без SQL используют встроенные WordPress функции.

### Ядро WordPress (НЕ МОДИФИЦИРОВАТЬ)
- `wp-admin/`, `wp-includes/`, корневые PHP файлы - стандартное ядро WP
- `wp-config.php` - конфигурация (не в Git, специфична для окружения)

## ⚠️ КРИТИЧНЫЕ ИЗВЕСТНЫЕ ПРОБЛЕМЫ

### Несовместимость ID команд в базе данных (76% матчей затронуто)

**Проблема:** Таблицы `wp_arsenal_matches` и `wp_arsenal_teams` используют несовместимые форматы ID:
- `wp_arsenal_matches`: `home_team_id`, `away_team_id` = HEX строки (varchar) - `"0720C44E"`, `"D3949C90"`
- `wp_arsenal_teams`: `id` = NUMERIC (int) - `1`, `2`, `3`...
- **Результат**: При JOIN и выборке команд 76% матчей не находят свои команды

**Откуда это взялось:**
1. Парсер (tools/Py/abff_parser) получает HEX ID от championship.abff.by API
2. При импорте в `wp_arsenal_matches` сохраняет их как есть (HEX)
3. При импорте в `wp_arsenal_teams` создает новые NUMERIC ID (автоинкремент)
4. Связь потеряна!

**Где это видно:**
- Файл: `CRITICAL_BUG_TEAM_IDS.md` (корень проекта) - детальный анализ
- Проверка: `php check-database.php` покажет 1143-1152 матчей с невалидными team_id

**Как это влияет на код:**
- Запросы в template-parts с LEFT JOIN на teams часто возвращают NULL для команд
- Нужна оборонительная кодировка: всегда проверять `if ( $home_team )` перед выводом
- Никогда не полагайтесь на team_id в matches для связи с teams

**Решение:** Требуется переиндексация (см. CRITICAL_BUG_TEAM_IDS.md вариант 1 или 2)

## Основные концепции WordPress

### Система хуков (Actions и Filters)
Архитектура WordPress построена на **хуках** - основном механизме расширения:

**Actions** - выполнение кода в определенных точках:
```php
do_action( 'after_setup_theme' );  // Вызов хука
add_action( 'after_setup_theme', 'my_function' );  // Подписка на хук
```

**Filters** - изменение данных перед выводом:
```php
apply_filters( 'the_content', $content );  // Вызов фильтра
add_filter( 'the_content', 'my_filter' );  // Подписка на фильтр
```

Все встроенные хуки определены в `wp-includes/default-filters.php`.

### Разработка темы Arsenal

**Паттерны именования и хуки:**
```php
// Префикс темы: arsenal_*
define( 'ARSENAL_VERSION', '1.0.0' );
define( 'ARSENAL_THEME_DIR', get_template_directory() );
define( 'ARSENAL_THEME_URI', get_template_directory_uri() );

// Проверка существования функции
if ( ! function_exists( 'arsenal_setup' ) ) {
    function arsenal_setup() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        register_nav_menus( array( 'primary' => __( 'Главное меню', 'arsenal' ) ) );
        load_theme_textdomain( 'arsenal', ARSENAL_THEME_DIR . '/languages' );
    }
}

// Основные хуки темы
add_action( 'after_setup_theme', 'arsenal_setup' );
add_action( 'wp_enqueue_scripts', 'arsenal_enqueue_scripts' );
add_action( 'widgets_init', 'arsenal_widgets_init' );
```

**Порядок подключения CSS (каскадно):**
```php
// 1. fonts.css → 2. style.css (base) → 3. header.css → 4. main.css → 5. footer.css
wp_enqueue_style( 'arsenal-main', ..., array( 'arsenal-header' ), ARSENAL_VERSION );
```

**JavaScript + локализация данных:**
```php
wp_localize_script( 'arsenal-script', 'arsenalData', array(
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce'   => wp_create_nonce( 'arsenal-nonce' ),
) );
```

### Работа с кастомной БД Arsenal

**Основные таблицы** (`wp_arsenal_*`):
- `leagues` - турниры (ID, название, страна)
- `seasons` - сезоны (год, даты, flashscore_id)
- `teams` - команды (ID, название, логотип, is_arsenal флаг)
- `players` - игроки (ID из SStats API, имя, позиция, фото)
- `matches` - матчи (home/away команды, счёт, статус, venue_id)
- `match_events` - события (голы, карточки, замены)
- `match_lineups` - составы (стартовые и запасные)
- `venues` - стадионы (ID = MD5(название), название, город)

**Прямые SQL запросы (используется в template-parts/):**
```php
global $wpdb;

// Найти ID команды Арсенал
$arsenal_id = $wpdb->get_var( "SELECT id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );

// Получить последние матчи с JOIN
$matches = $wpdb->get_results( $wpdb->prepare(
    "SELECT m.*, ht.name AS home_team, at.name AS away_team
     FROM wp_arsenal_matches m
     LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.id
     LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.id
     WHERE (m.home_team_id = %d OR m.away_team_id = %d)
     AND m.status = 'FT'
     ORDER BY m.match_date DESC
     LIMIT 5",
    $arsenal_id, $arsenal_id
) );

// Безопасные запросы ВСЕГДА через prepare()
$player = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM wp_arsenal_players WHERE id = %d",
    $player_id
) );
```

**⚠️ ВАЖНО при работе с matches и teams:**
```php
// Всегда проверяйте, нашлась ли команда (из-за известного бага несовместимости ID)
if ( $home_team ) {
    echo $home_team->name;  // Безопасно
} else {
    // Fallback: получить команду по HEX ID из matches напрямую
    // или вывести placeholder
    echo 'Неизвестная команда';
}
```

**Класс Arsenal_Database (управление схемой):**
```php
// Получить singleton экземпляр
$db = Arsenal_Database::get_instance();

// Создать/обновить таблицы
$db->init(); // Автоматически проверяет версию БД

// Получить статистику
$stats = $db->get_stats(); // ['leagues' => 1, 'teams' => 16, ...]

// Проверить существование таблиц
if ( $db->tables_exist() ) { /* ... */ }
```

### Разработка плагинов

**Структура плагина:**
```php
<?php
/**
 * Plugin Name: Название плагина
 * Description: Описание
 * Version: 1.0.0
 * Author: Автор
 * Text Domain: my-plugin
 */

// Безопасность: прямой доступ запрещен
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

// Используйте хуки для функциональности
add_action( 'init', 'my_plugin_init' );

function my_plugin_init() {
    // Инициализация плагина
}
```

**Рекомендации:**
- Используйте префикс плагина: `plugin_name_function()`
- Включайте контроль доступа проверкой `ABSPATH`
- Зарегистрируйте все функции на соответствующих хуках
- Документируйте все общественные функции PHPDoc комментариями

## Рабочие процессы разработки

### Локальная среда (Laragon)
- Запустите Laragon и откройте `http://arsenal.test`
- БД: `arsenal` (пользователь: `arsenal_user`, управляйте через phpMyAdmin)
- Путь проекта: `C:\laragon\www\arsenal`

### Процесс деплоя (Git Pull на сервер)
**Ежедневный workflow:**
```powershell
# 1. Локальный коммит и push
git add .
git commit -m "Описание изменений"
git push origin dev

# 2. Деплой на сервер (одна команда)
ssh site_user@212.113.120.197 "cd /var/www/site_user/data/arsenal-repo && bash deploy.sh"

# ИЛИ автоматически через скрипт (Windows)
.\deploy-windows.ps1 "Описание изменений"
```

**Архитектура деплоя:**
- Git-репозиторий на сервере: `/var/www/site_user/data/arsenal-repo`
- Рабочий сайт: `/var/www/site_user/data/www/1779917-cq85026.twc1.net/`
- Скрипт `deploy.sh` копирует тему `arsenal/` из репо на рабочий сайт
- **НЕ синхронизируются**: `wp-config.php`, `wp-content/uploads/`, `*.sql`

**Полная синхронизация** (файлы + меню + данные):
```powershell
.\sync-all.ps1 "Описание изменений"  # Windows
# Опции: -SkipMenu, -SkipDatabase
```

### Работа с данными (ABFF Parser)
Используется **парсер championship.abff.by** для получения футбольных данных.

**Источник данных**: https://championship.abff.by (турнир ID: 7, сезон: 54)  
**Парсер**: `tools/Py/abff_parser/`  
**Документация**: `tools/Py/abff_parser/QUICKSTART.md`, `docs/PARSER-DATABASE-GUIDE.md`

**4-фазный процесс обновления данных:**
```bash
cd tools/Py/abff_parser

# Фаза 1: Команды и игроки
python parse_player_stats.py    # Парсинг → player_stats.json
python import_to_db.py           # Импорт в БД

# Фаза 2: Календарь матчей
python fetch_fixtures_page.py   # Получение HTML календаря
python download_match_pages.py  # Скачивание страниц матчей

# Фаза 3: События матчей (голы, карточки)
python parse_match_events.py    # Парсинг → match_events.json
python import_match_events.py   # Импорт в БД

# Фаза 4: Составы команд
python parse_match_lineups_new.py  # Парсинг → match_lineups.json
python import_match_lineups.py     # Импорт в БД
```

**Важно:** Обязательна кодировка UTF-8:
```powershell
$env:PYTHONIOENCODING="utf-8"
python parse_player_stats.py
```

### Работа с БД Arsenal

**Утилиты корневой директории** (запуск через `php script.php`):
- `check-database.php` - просмотр структуры и данных всех таблиц
- `install-database.php` - установка/обновление схемы БД (интерактивное меню)
- `import-data.php` - импорт данных из JSON файлов в БД

**Пример проверки данных:**
```bash
php check-database.php        # Структура всех таблиц + количество записей
php install-database.php      # Интерактивное меню управления БД
php import-data.php           # Импорт данных из JSON
```

**Управление схемой БД:**
```php
// Через класс Arsenal_Database
require_once get_template_directory() . '/inc/database/class-arsenal-database.php';

$db = Arsenal_Database::get_instance();
$db->init(); // Создание/обновление таблиц

// ИЛИ через скрипт установки
php install-database.php
// Меню: 1=пересоздать, 2=обновить, 3=очистить, 4=информация
```

### Включение режима отладки
Отредактируйте `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );      // Логи в wp-content/debug.log
define( 'WP_DEBUG_DISPLAY', false );  // Не показывать ошибки на сайте
```

### Полезные команды (если установлен WP-CLI)
```bash
wp plugin list              # Список плагинов
wp theme activate arsenal   # Активируйте тему
wp db export backup.sql     # Экспорт БД
```

### Резервная копия БД
```bash
mysqldump -u root -p arsenal > backup.sql
```

## Соглашения по безопасности

### Никогда не коммитьте чувствительные данные
- `wp-config.php` содержит учетные данные БД и ключи безопасности
- Для продакшена используйте переменные окружения или отдельные файлы конфигурации
- Генерируйте уникальные ключи для каждого окружения на https://api.wordpress.org/secret-key/1.1/salt/

### Валидация и санитизация данных
```php
// Всегда очищайте входные данные
$safe_input = sanitize_text_field( $_POST['user_input'] );

// Экранируйте вывод в HTML
echo esc_html( $user_content );
echo esc_url( $link );
echo esc_attr( $attribute );

// Проверяйте nonce при обработке форм
wp_verify_nonce( $_POST['_wpnonce'], 'action_name' );
```

### Проверка прав доступа
```php
// Проверяйте возможности пользователя
if ( current_user_can( 'manage_options' ) ) {
    // Только для администраторов
}

// Используйте проверки перед операциями с данными
if ( ! current_user_can( 'edit_post', $post_id ) ) {
    wp_die( 'Доступ запрещен' );
}
```

## Стандарты кода

Следуйте [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/):

- **Отступы**: табуляция (не пробелы)
- **Имена функций**: строчные с подчеркиванием `my_function_name()`
- **Имена классов**: PascalCase с подчеркиванием `My_Plugin_Class`
- **Префиксы**: добавляйте префикс ко всем функциям и классам
- **Комментарии**: используйте PHPDoc для документирования

```php
/**
 * Краткое описание функции
 *
 * Длинное описание, если необходимо.
 *
 * @param string $name  Описание параметра
 * @param int    $count Количество элементов
 * @return array Результат функции
 * @since 1.0.0
 */
function my_function( $name, $count ) {
    return array();
}
```

## Точки интеграции

### Работа с базой данных
Используйте глобальный объект `$wpdb`:
```php
global $wpdb;

// Получение результатов
$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts LIMIT 10" );

// Безопасные запросы с подстановкой переменных
$results = $wpdb->get_results( 
    $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID = %d", $post_id ) 
);
```

### REST API
- Основные endpoint'ы: `/wp-json/wp/v2/`
- Регистрация своих маршрутов:
```php
add_action( 'rest_api_init', function() {
    register_rest_route( 'my-plugin/v1', '/data', array(
        'methods'  => 'GET',
        'callback' => 'my_rest_callback',
        'permission_callback' => '__return_true',
    ) );
} );
```

### Admin AJAX
```php
// На фронтенде
wp_enqueue_script( 'my-script' );
wp_localize_script( 'my-script', 'myAjax', array(
    'url' => admin_url( 'admin-ajax.php' ),
) );

// На стороне сервера (для авторизованных пользователей)
add_action( 'wp_ajax_my_action', 'my_ajax_handler' );

// Для публичных запросов
add_action( 'wp_ajax_nopriv_my_action', 'my_ajax_handler' );

function my_ajax_handler() {
    check_ajax_referer( 'nonce_name' );
    // Обработка запроса
    wp_send_json_success( $data );
}
```

## Сервисные скрипты AI (service_scripts_ai)

**Назначение:** Папка для хранения всех вспомогательных скриптов, которые создаются AI для проверок, миграций, валидации данных и других служебных задач.

**Структура:**
```
service_scripts_ai/
├── migrations/          # Скрипты миграций БД
├── validators/          # Скрипты проверки данных
├── cleaners/            # Скрипты очистки и нормализации данных
├── reports/             # Скрипты генерации отчетов
└── utilities/           # Прочие служебные скрипты
```

**Соглашения:**
- Все скрипты должны быть самостоятельными и не требовать включения в продакшн
- Каждый скрипт должен иметь PHPDoc комментарий с описанием цели
- Для скриптов миграций используйте префикс: `migrate_*.php`
- Для скриптов валидации используйте префикс: `validate_*.php`
- Скрипты могут использовать WordPress функции, но должны быть запускаемы отдельно
- Временные файлы (JSON, CSV) из service_scripts_ai не коммитятся в Git

**Пример использования:**
```bash
# Запуск скрипта проверки
php service_scripts_ai/validators/validate_players.php

# Запуск скрипта миграции
php service_scripts_ai/migrations/migrate_standings.php
```

## Полезные ссылки
- **Версия WordPress**: `wp-includes/version.php`
- **Встроенные хуки**: `wp-includes/default-filters.php`
- **Функции темы**: `wp-content/themes/arsenal/functions.php`
- **Документация проекта**: 
  - `docs/QUICKSTART.md` - быстрая шпаргалка
  - `docs/DEPLOY.md` - подробное руководство по деплою
  - `docs/DATABASE-SCHEMA.md` - схема БД (8 таблиц)
  - `docs/PARSER-DATABASE-GUIDE.md` - руководство по парсеру (1166 строк)
  - `docs/STRUCTURE.md` - структура проекта
