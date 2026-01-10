# Руководство разработки WordPress - ⚽ ФК Арсенал Дзержинск

## Обзор проекта
Сайт **официального футбольного клуба Арсенал Дзержинск** (Беларусь) на **WordPress 6.9**.

**Среды:**
- **Локальная**: Laragon (Windows) → `http://arsenal.test`
- **Продакшн**: `http://1779917-cq85026.twc1.net/` (через Git-деплой)
- **БД**: `arsenal` (MySQL, префикс `wp_`, user: `arsenal_user`)
- **Репозиторий**: https://github.com/44mmnrw/Arsenal (ветка `dev_main`)


## Архитектура проекта

### Трёхуровневая архитектура

**1. Тема Arsenal (Презентация)**
- **Корень**: `wp-content/themes/arsenal/`
- Классическая PHP-тема с Vanilla CSS/JS (без блочного редактора)
- Система стилей: `fonts.css` → `style.css` (base) → `header.css` → `main.css` → `footer.css`
- Префикс функций: `arsenal_*`
- Template parts: `template-parts/*-dynamic.php` (запросы к БД через `$wpdb`)
- **Новые компоненты** (класс 2025):
  - `inc/class-stat-cards-manager.php` - управление статистическими карточками (JSON в post_meta)
  - `inc/class-stat-cards-metabox.php` - админ-интерфейс метаокса (280 строк, jQuery UI)
  - Система полностью независима от ACF/Pods плагинов

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

### Работа с БД Arsenal

Используйте класс `Arsenal_Database` (Singleton) для управления схемой БД. Актуальное описание всех 18 таблиц смотрите в [docs/DATABASE-SCHEMA.md](../docs/DATABASE-SCHEMA.md).

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

### Система управления JSON-данными (Stat Cards)

**Новый паттерн управления данными через post_meta (без плагинов):**

```php
// Подключить менеджер
require_once get_template_directory() . '/inc/class-stat-cards-manager.php';

// Получить карточки со страницы
$cards = Arsenal_Stat_Cards_Manager::get_cards( $post_id );
// Результат: array( ['stat_title' => '15', 'stat_value' => 'Побед'], ... )

// Добавить новую карточку
Arsenal_Stat_Cards_Manager::add_card( '15', 'Побед в сезоне', $post_id );

// Обновить карточку
Arsenal_Stat_Cards_Manager::update_card( 0, '16', 'Побед (новое)', $post_id );

// Удалить карточку
Arsenal_Stat_Cards_Manager::delete_card( 0, $post_id );

// Получить все карточки (сырые данные)
$raw_cards = get_post_meta( $post_id, '_arsenal_stat_cards_json', true );
// Формат: JSON строка в post_meta
```

**Админ-интерфейс:**
- Класс `Arsenal_Stat_Cards_Metabox` автоматически регистрирует метаокс на страницах
- JavaScript управление (добавить/удалить карточку в интерфейсе)
- Данные сохраняются как JSON в `wp_postmeta` с ключом `_arsenal_stat_cards_json`

**Использование на фронтенде:**
```php
// В шаблоне page-history.php
$stat_cards = Arsenal_Stat_Cards_Manager::get_cards( get_the_ID() );
foreach ( $stat_cards as $card ) {
    echo '<div class="stat-card">';
    echo '<span class="stat-number">' . esc_html( $card['stat_title'] ) . '</span>';
    echo '<span class="stat-label">' . esc_html( $card['stat_value'] ) . '</span>';
    echo '</div>';
}
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

**⚠️ ВАЖНО про удаленные плагины:**
- **Удалены**: Advanced Custom Fields (ACF), Pods плагин и их кастомные функции
- **Причина**: Система управления данными (Stat Cards) переведена на JSON (post_meta) без зависимостей
- **Файлы удалены**:
  - `inc/acf-fields.php` - больше не используется
  - `inc/pods-fields.php` - больше не используется
- **Функции удалены**: `arsenal_get_pod_field()`, `arsenal_get_pod_repeater()` - заменены на JSON-систему

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
git push origin dev_main

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

**Утилиты для проверки и управления БД** (запуск через `php script.php`):
- `service_scripts_ai/check-database.php` - полная проверка структуры БД (таблицы, поля, индексы, FK, количество записей)
- `install-database.php` - установка/обновление схемы БД (интерактивное меню)
- `import-data.php` - импорт данных из JSON файлов в БД

**Пример проверки данных:**
```bash
php service_scripts_ai/check-database.php  # Вывод всех wp_arsenal_* таблиц со структурой и статистикой
php install-database.php                   # Интерактивное меню управления БД (создать/обновить/очистить)
php import-data.php                        # Импорт данных из JSON
```

**Результат `check-database.php`:**
- Список всех таблиц с количеством строк и размером
- Для каждой таблицы: поля, типы, ограничения (PK, FK, UNIQUE)
- Внешние ключи и их связи
- Проверка целостности FK

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

**Текущие скрипты:**
- `service_scripts_ai/check-database.php` - полная проверка структуры и статистики БД (таблицы, поля, индексы, FK, количество записей)
- `service_scripts_ai/clean-history-page-blocks.php` - очистка старых блоков со страницы История
- `service_scripts_ai/clean-history-page-full.php` - полная очистка страницы История
- `cleanup-old-fields-web.php`, `cleanup-old-fields.php` - удаление устаревших полей
- `delete_old_meta_fields.php` - удаление старых meta полей
- `update-cup-tours.php` - обновление турниров (кубков)
- `verify-db-restore.sh`, `import-arsenal-db.sh` - утилиты БД

**Соглашения:**
- Все скрипты должны быть самостоятельными и не требовать включения в продакшн
- Каждый скрипт должен иметь PHPDoc комментарий с описанием цели
- Для скриптов миграций используйте префикс: `migrate_*.php`
- Для скриптов валидации используйте префикс: `validate_*.php`
- Для скриптов проверки используйте префикс: `check-*.php`
- Скрипты могут использовать WordPress функции, но должны быть запускаемы отдельно (через `php script.php`)
- Временные файлы (JSON, CSV) из service_scripts_ai не коммитятся в Git

**Пример использования:**
```bash
# Запуск скрипта проверки БД
php service_scripts_ai/check-database.php

# Запуск скрипта валидации (если есть)
php service_scripts_ai/validators/validate_players.php

# Запуск скрипта миграции
php service_scripts_ai/migrations/migrate_standings.php

# Запуск скрипта очистки
php service_scripts_ai/clean-history-page-blocks.php
```

## Ключевые файлы проекта для быстрого старта

| Файл | Назначение | Язык |
|------|-----------|------|
| `functions.php` | Основные хуки темы и подключения | PHP |
| `inc/class-stat-cards-manager.php` | Управление статистическими карточками | PHP |
| `inc/class-stat-cards-metabox.php` | Admin UI для карточек | PHP + jQuery |
| `inc/database/class-arsenal-database.php` | Управление схемой БД Arsenal | PHP |
| `template-parts/*-dynamic.php` | Компоненты с SQL запросами | PHP |
| `assets/css/page-match.css` | Стили страницы матча (1000+ строк) | CSS |
| `wp-content/plugins/arsenal-team-manager/` | Управление командой и игроками | PHP |

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

## Типичные задачи разработки

### Добавить новый блок на главную страницу
1. Создать шаблон в 	emplate-parts/ (например my-block.php или my-block-dynamic.php)
2. Добавить хук подключения в unctions.php
3. Если нужны данные из БД - использовать $wpdb в *-dynamic.php версии
4. Добавить CSS в соответствующий файл в ssets/css/
5. Проверить на локальной среде и задеплоить

### Добавить статистические карточки на страницу
1. Открыть WordPress админку → Pages → Edit нужная страница
2. Скролл до метаокса "Статистические карточки"
3. Нажать "[+ Добавить карточку]"
4. Заполнить цифру и описание
5. Сохранить страницу

### Добавить новое поле в админ-панель игрока
1. Посмотреть wp-content/plugins/arsenal-team-manager/admin/ - там находятся админ-формы
2. Добавить input field в HTML форму
3. Обновить SQL запрос в wp_arsenal_players
4. Добавить код сохранения в PHP обработчик

### Обновить данные команд и матчей из ABFF
1. Перейти в 	ools/Py/abff_parser/
2. Установить зависимости: pip install -r requirements.txt
3. Запустить процесс в 4 фазы (см. раздел "Работа с данными (ABFF Parser)")
4. Проверить: php check-database.php
5. Задеплоить изменения

### Добавить новый CSS стиль
1. Выбрать правильный файл в ssets/css/ (или создать новый модульный файл)
2. Добавить стиль с префиксом класса (например .match-detail-page)
3. Если стиль для определенной страницы - использовать page-*.css
4. Подключить в unctions.php через wp_enqueue_style()
5. Проверить каскадность и responsive дизайн

## Типичные задачи разработки

### Добавить новый блок на главную страницу
1. Создать шаблон в `template-parts/` (например `my-block.php` или `my-block-dynamic.php`)
2. Добавить хук подключения в `functions.php`
3. Если нужны данные из БД - использовать `` в `*-dynamic.php` версии
4. Добавить CSS в соответствующий файл в `assets/css/`
5. Проверить на локальной среде и задеплоить

### Добавить статистические карточки на страницу
1. Открыть WordPress админку → Pages → Edit нужная страница
2. Скролл до метаокса "Статистические карточки"
3. Нажать "[+ Добавить карточку]"
4. Заполнить цифру и описание
5. Сохранить страницу

### Добавить новое поле в админ-панель игрока
1. Посмотреть `wp-content/plugins/arsenal-team-manager/admin/` - там находятся админ-формы
2. Добавить input field в HTML форму
3. Обновить SQL запрос в `wp_arsenal_players`
4. Добавить код сохранения в PHP обработчик

### Обновить данные команд и матчей из ABFF
1. Перейти в `tools/Py/abff_parser/`
2. Установить зависимости: `pip install -r requirements.txt`
3. Запустить процесс в 4 фазы (см. раздел "Работа с данными (ABFF Parser)")
4. Проверить: `php service_scripts_ai/check-database.php`
5. Задеплоить изменения

### Добавить новый CSS стиль
1. Выбрать правильный файл в `assets/css/` (или создать новый модульный файл)
2. Добавить стиль с префиксом класса (например `.match-detail-page`)
3. Если стиль для определенной страницы - использовать `page-*.css`
4. Подключить в `functions.php` через `wp_enqueue_style()`
5. Проверить каскадность и responsive дизайн