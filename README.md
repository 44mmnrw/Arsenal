# ⚽ ФК Арсенал Дзержинск - Официальный сайт

Официальный веб-сайт футбольного клуба **Арсенал Дзержинск** (Республика Беларусь) на платформе **WordPress 6.9**.

## 📋 Обзор

- **CMS**: WordPress 6.9
- **Тема**: Arsenal (классическая PHP-тема без блочного редактора)
- **Архитектура**: Трёхуровневая (тема + плагин + кастомная БД)
- **Статус**: В активной разработке

## 🌍 Окружения

| Окружение | URL | Описание |
|-----------|-----|---------|
| **Локальная** | `http://arsenal.test` | Laragon (Windows) |
| **Продакшн** | `http://1779917-cq85026.twc1.net/` | Боевой сервер |

## 🏗️ Архитектура проекта

### Трёхуровневая структура

```
Arsenal (проект)
├── 1️⃣ Тема Arsenal (Презентационный слой)
│   ├── wp-content/themes/arsenal/
│   ├── PHP-шаблоны, CSS/JS
│   └── Функции: arsenal_*
│
├── 2️⃣ Плагин Arsenal Team Manager (Бизнес-логика)
│   ├── wp-content/plugins/arsenal-team-manager/
│   ├── Управление командой, игроками, матчами
│   └── Admin панель: Команды, Игроки, Матчи
│
└── 3️⃣ Кастомная БД Arsenal (Слой данных)
    ├── 8 таблиц: wp_arsenal_*
    ├── Лиги → Сезоны → Команды → Матчи
    └── Класс: Arsenal_Database (singleton)
```

### Ключевые компоненты

| Компонент | Расположение | Функция |
|-----------|--------------|---------|
| **Тема** | `wp-content/themes/arsenal/` | Визуализация, стили, JS |
| **Плагин** | `wp-content/plugins/arsenal-team-manager/` | Управление данными |
| **БД** | `wp-content/themes/arsenal/inc/database/` | Схема и миграции |
| **Парсер** | `tools/Py/abff_parser/` | Импорт данных от championship.abff.by |

## 📁 Структура темы

```
wp-content/themes/arsenal/
├── assets/
│   ├── css/           # Модульные стили (fonts, header, main, footer)
│   ├── js/            # JavaScript (banner-carousel и др.)
│   └── images/        # Изображения, спрайты
├── inc/
│   ├── database/      # Класс Arsenal_Database + SQL миграции
│   ├── api/           # REST API endpoints
│   ├── customizer.php # Настройки темы
│   └── template-functions.php
├── template-parts/    # Компоненты страниц
│   ├── *-dynamic.php  # Версии с прямыми SQL запросами
│   └── *-static.php   # Версии с встроенными функциями WP
├── templates/         # Шаблоны кастомных страниц
├── functions.php      # Регистрация стилей/скриптов, хуки
└── front-page.php     # Главная страница
```

## 🗄️ Таблицы БД

Все таблицы имеют префикс `wp_arsenal_`:

- `leagues` — Турниры/Лиги
- `seasons` — Сезоны
- `teams` — Команды
- `players` — Игроки
- `matches` — Матчи
- `match_events` — События (голы, карточки)
- `match_lineups` — Составы матчей
- `venues` — Стадионы

## 🚀 Быстрый старт (Разработка)

### Локальная установка

1. **Запустить Laragon** и открыть `http://arsenal.test`
2. **БД**: `arsenal` (пользователь: `arsenal_user`)
3. **Путь проекта**: `C:\laragon\www\arsenal`

### Первый запуск

```bash
# 1. Проверить структуру БД
php check-database.php

# 2. Установить/обновить таблицы
php install-database.php

# 3. Активировать тему в WordPress админке
```

### Разработка

```bash
# Включить режим отладки
# Отредактируй wp-config.php:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );      # Логи → wp-content/debug.log
define( 'WP_DEBUG_DISPLAY', false ); # Не показывать ошибки на сайте
```

## 📚 Документация

- [`docs/QUICKSTART.md`](./docs/QUICKSTART.md) — Быстрая шпаргалка
- [`docs/DATABASE-SCHEMA.md`](./docs/DATABASE-SCHEMA.md) — Схема БД
- [`docs/PARSER-DATABASE-GUIDE.md`](./docs/PARSER-DATABASE-GUIDE.md) — Парсер данных
- [`docs/DEPLOY.md`](./docs/DEPLOY.md) — Деплой на сервер
- [`docs/STRUCTURE.md`](./docs/STRUCTURE.md) — Подробная структура проекта
- [`.github/copilot-instructions.md`](./.github/copilot-instructions.md) — Полное руководство разработки

## 🔄 Git Workflow

## 🚀 Быстрый деплой

### Одна команда — и все готово!

**Windows (PowerShell):**
```powershell
.\deploy.ps1 "Описание изменений"
```

**Linux/Mac (Bash):**
```bash
./deploy.sh "Описание изменений"
```

Скрипт автоматически:
1. ✅ Создаёт коммит в Git
2. ✅ Пушит на GitHub (dev_main)
3. ✅ Копирует тему на production сервер
4. ✅ Показывает результаты

## 🔄 Git Workflow

### Ветки

- **`main`** — Production-ready код (боевой сервер)
- **`dev_main`** — Разработка (staging, автоматический деплой)

### Учетные данные сервера

```
SSH: site_user76@212.113.120.197
Repository: /var/www/site_user76/data/arsenal-repo
Web Root: /var/www/site_user76/data/www/1779917-cq85026.twc1.net
```

### Ручной деплой (если скрипт не работает)

```bash
# 1. Локально
git add .
git commit -m "Описание"
git push origin dev_main

# 2. На сервере (SSH)
ssh site_user76@212.113.120.197
cd /var/www/site_user76/data/arsenal-repo
git pull origin dev_main
cp -r wp-content/themes/arsenal/* /var/www/site_user76/data/www/1779917-cq85026.twc1.net/wp-content/themes/arsenal/
```

## 📊 Парсер данных (ABFF)

Автоматический импорт данных с championship.abff.by:

```bash
cd tools/Py/abff_parser

# Этап 1: Команды и игроки
python parse_player_stats.py && python import_to_db.py

# Этап 2: Календарь матчей
python fetch_fixtures_page.py && python download_match_pages.py

# Этап 3: События матчей
python parse_match_events.py && python import_match_events.py

# Этап 4: Составы
python parse_match_lineups_new.py && python import_match_lineups.py
```

**⚠️ Важно:** Устанавливай кодировку UTF-8:
```powershell
$env:PYTHONIOENCODING="utf-8"
```

## 💻 Стек технологий

| Слой | Технология | Версия |
|------|-----------|--------|
| **Backend** | PHP | 7.4+ |
| **CMS** | WordPress | 6.9 |
| **Frontend** | Vanilla JS/CSS | ES6+ |
| **БД** | MySQL | 5.7+ |
| **Парсер** | Python | 3.8+ |
| **Контроль версий** | Git | — |

## 🔐 Безопасность

### Соглашения

- ⛔ **НЕ коммитить** `wp-config.php` (учетные данные)
- ✅ **Валидировать** все входные данные (`sanitize_text_field()`)
- ✅ **Экранировать** вывод (`esc_html()`, `esc_url()`)
- ✅ **Проверять** права доступа (`current_user_can()`)
- ✅ **Верифицировать** nonce при обработке форм (`wp_verify_nonce()`)

## 📋 Соглашения по коду

Следуй [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/):

- **Отступы**: табуляция
- **Функции**: `snake_case` с префиксом `arsenal_*`
- **Классы**: `PascalCase` с префиксом `Arsenal_*`
- **Комментарии**: PHPDoc

```php
/**
 * Краткое описание функции
 *
 * @param string $name  Описание параметра
 * @param int    $count Количество
 * @return array Результат
 * @since 1.0.0
 */
function arsenal_function( $name, $count ) {
    // ...
}
```

## 🤝 Вклад в проект

1. Создать ветку от `dev_main`: `git checkout -b feature/название`
2. Делать коммиты с понятными сообщениями
3. Пушить в `dev_main`
4. При готовности — merge в `main`

## 📞 Контакты

- **GitHub**: https://github.com/44mmnrw/Arsenal
- **Сайт**: http://1779917-cq85026.twc1.net/

---

**Последнее обновление**: 5 января 2026 г.  
**Версия WordPress**: 6.9  
**Версия темы**: 1.0.0
