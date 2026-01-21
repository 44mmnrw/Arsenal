# Carbon Fields - Миграция завершена ✅

## Что сделано

### 1. Установка Carbon Fields
- ✅ Установлен через Composer в тему: `wp-content/themes/arsenal/vendor/`
- ✅ Подключен в `functions.php`
- ✅ Автоматически загружается с темой (без плагинов)

### 2. Файлы

**Новые:**
- `inc/carbon-fields-init.php` - конфигурация метабоксов (226 строк)
- `inc/class-academy-carbon-adapter.php` - адаптер для чтения данных (199 строк)
- `test-carbon-fields.php` - тест установки

**Обновленные:**
- `functions.php` - подключение Carbon Fields и адаптера
- `templates/page-academy-recruitment.php` - использует Carbon Adapter

**Старые (теперь не используются):**
- `plugins/arsenal-team-manager/admin/class-academy-recruitment-admin.php` (211 строк)
- `plugins/arsenal-team-manager/admin/views/academy-recruitment-form.php` (968 строк)
- `inc/class-academy-page-manager.php` (271 строк)

**Итого:** 1450 строк кода → 425 строк (уменьшение на 70%)

---

## Как работает

### Админка (автоматически)
WordPress админка → Страницы → "Набор в школу" → видны метабоксы Carbon Fields с вкладками:
- Hero секция
- Преимущества (repeater)
- Возрастные группы (repeater)
- Документы (repeater)
- Расписание просмотров (repeater)
- Контакты
- Директор
- Маршруты проезда (repeater)
- Социальные сети (repeater)
- FAQ (repeater)

### Фронтенд
Шаблон `templates/page-academy-recruitment.php` загружает данные через:
```php
$data = Arsenal_Academy_Carbon_Adapter::get_page_data( get_the_ID() );
```

---

## Преимущества

### До (старая система)
- ❌ 968 строк JavaScript
- ❌ Ручное управление JSON
- ❌ Проблемы с сохранением
- ❌ Нет валидации
- ❌ Хардкод данных

### После (Carbon Fields)
- ✅ Автоматический UI
- ✅ Встроенные repeater fields
- ✅ Нативная работа с post_meta
- ✅ Drag & Drop сортировка
- ✅ Валидация полей
- ✅ Нет хардкода

---

## Следующие шаги

### 1. Проверка установки
```bash
php test-carbon-fields.php
```

### 2. Заполнение данных
1. Откройте WordPress админку
2. Перейдите: **Страницы → Набор в школу**
3. Заполните метабоксы Carbon Fields:
   - Добавьте карточки преимуществ (кнопка "+")
   - Добавьте возрастные группы
   - Заполните контакты и маршруты
4. Нажмите **Обновить**

### 3. Проверка фронтенда
Откройте страницу на сайте: `http://arsenal.test/nabor-v-shkolu/`

---

## Удаление старых файлов (опционально)

После проверки можно удалить:
```bash
rm wp-content/plugins/arsenal-team-manager/admin/class-academy-recruitment-admin.php
rm wp-content/plugins/arsenal-team-manager/admin/views/academy-recruitment-form.php
rm wp-content/themes/arsenal/inc/class-academy-page-manager.php
```

---

## Технические детали

### Хранение данных
Все данные сохраняются в `wp_postmeta` с префиксом `_academy_`:
- `_academy_hero_title`
- `_academy_hero_description`
- `_academy_benefits` (массив)
- `_academy_age_groups` (массив)
- `_academy_contacts_address`
- И т.д.

### Чтение данных
```php
// Прямой доступ
$benefits = carbon_get_post_meta( $post_id, '_academy_benefits' );

// Через адаптер (рекомендуется)
$data = Arsenal_Academy_Carbon_Adapter::get_page_data( $post_id );
```

### Иконки
Автоматически загружаются из `assets/images/sprite.svg`:
```php
arsenal_get_sprite_icons() // Возвращает список для select
```

---

## Решенные проблемы

✅ Данные не сохранялись → Carbon Fields автоматически сохраняет в post_meta  
✅ Хардкод в админке → Все поля теперь динамические  
✅ 1000 строк JavaScript → Встроенный UI Carbon Fields  
✅ Ручное управление JSON → Автоматическая сериализация  
✅ Нет валидации → Встроенная валидация типов полей  

---

## Совместимость

✅ Работает на чистом WordPress (без плагинов)  
✅ Деплоится через Git вместе с темой  
✅ Composer пакеты включены в репозиторий  
✅ Не требует активации плагинов  
✅ Обратная совместимость: старые данные (если были) можно мигрировать  

---

## Документация Carbon Fields

Официальная документация: https://carbonfields.net/docs/

**Типы полей:**
- `text` - текстовое поле
- `textarea` - многострочный текст
- `select` - выпадающий список
- `complex` - repeater field (массив объектов)

**Примеры:**
```php
Field::make( 'complex', 'benefits' )
    ->add_fields([
        Field::make( 'text', 'title' ),
        Field::make( 'textarea', 'description' ),
    ]);
```
