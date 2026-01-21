# Управление страницей набора в академию

## Обзор

Администраторская панель для управления контентом страницы "Набор в академию" через JSON данные в БД.

## Доступ

Перейдите в админку WordPress → **Набор в академию**

## Структура данных

Все данные страницы хранятся в таблице `wp_arsenal_academy_recruitment` с следующими JSON колонками:

### 1. Hero секция
```json
{
  "title": "Набор в академию",
  "description": "СДЮШ Арсенал объявляет...",
  "buttons": [
    { "text": "Подать заявку", "action": "apply", "style": "primary" },
    { "text": "Контакты", "action": "#contacts", "style": "secondary" }
  ]
}
```

### 2. Преимущества (Why Us)
```json
[
  {
    "icon": "coaches",
    "title": "Профессиональные тренеры",
    "description": "Все наши тренеры имеют..."
  },
  ...
]
```

### 3. Возрастные группы
```json
[
  {
    "name": "U-9",
    "age_range": "8-9 лет",
    "birth_years": "2016-2017",
    "schedule": "Пн, Ср, Пт: 16:00-17:30",
    "spots_available": 15,
    "spots_status": "normal"  // или "warning", "danger"
  },
  ...
]
```

### 4. Необходимые документы
```json
{
  "items": [
    { "text": "Медицинская справка..." },
    { "text": "Свидетельство о рождении..." }
  ],
  "notice": "Все документы должны быть заверены..."
}
```

### 5. Расписание просмотров
```json
{
  "items": [
    { "icon": "calendar", "heading": "Каждую субботу", "text": "10:00 - 12:00" },
    { "icon": "location", "heading": "Стадион", "text": "ул. Спортивная, 2" },
    { "icon": "phone", "heading": "Запись", "text": "+375 (17) 123-45-70" }
  ],
  "notice": "Предварительная запись обязательна!..."
}
```

### 6. Контакты и информация
```json
{
  "address": "ул. Спортивная, 2...",
  "phone": "+375 (17) 123-45-70",
  "email": "academy@arsenal-dzr.by",
  "director": {
    "title": "Директор СДЮШ",
    "name": "Петр Иванович Кузнецов",
    "role": "Директор с 2010 года",
    "contacts": [
      { "type": "phone", "value": "+375 (17) 123-45-71" },
      { "type": "email", "value": "kuznetsov@arsenal-dzr.by" }
    ]
  }
}
```

### 7. Частые вопросы (FAQ)
```json
[
  {
    "question": "Сколько стоят занятия?",
    "answer": "Обучение в СДЮШ Арсенал бесплатное..."
  },
  ...
]
```

## Использование в коде

### Получить все данные страницы
```php
require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';

$page_id = 123; // ID страницы
$data = Arsenal_Academy_Recruitment_Manager::get_page_data( $page_id );

// $data содержит все JSON данные
```

### Получить конкретную секцию
```php
$benefits = Arsenal_Academy_Recruitment_Manager::get_section( $page_id, 'benefits' );
// Результат: массив с преимуществами
```

### Обновить данные
```php
Arsenal_Academy_Recruitment_Manager::update_page_data( $page_id, array(
    'hero_data' => [
        'title' => 'Новый заголовок',
        'description' => 'Новое описание',
        'buttons' => [...]
    ]
) );
```

### Обновить одну секцию
```php
Arsenal_Academy_Recruitment_Manager::update_section( $page_id, 'benefits', $benefits_array );
```

## Интеграция с фронтенд шаблоном

Обновите [page-academy-recruitment.php](../templates/page-academy-recruitment.php) для использования данных из БД вместо хардкода:

```php
<?php
require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';

get_header();
$page_id = get_the_ID();
$data = Arsenal_Academy_Recruitment_Manager::get_page_data( $page_id );

if ( $data && $data['hero_data'] ) {
    $hero = $data['hero_data'];
    echo '<h1>' . esc_html( $hero['title'] ) . '</h1>';
    echo '<p>' . esc_html( $hero['description'] ) . '</p>';
}
// ... остальной код
get_footer();
?>
```

## Файлы

- **Админ форма**: `wp-content/plugins/arsenal-team-manager/admin/views/academy-recruitment-form.php`
- **Админ класс**: `wp-content/plugins/arsenal-team-manager/admin/class-academy-recruitment-admin.php`
- **Менеджер данных**: `wp-content/themes/arsenal/inc/class-academy-recruitment-manager.php`
- **Стили формы**: `wp-content/plugins/arsenal-team-manager/admin/assets/css/academy-recruitment-form.css`
- **Шаблон страницы**: `wp-content/themes/arsenal/templates/page-academy-recruitment.php`

## SQL таблица

```sql
CREATE TABLE `wp_arsenal_academy_recruitment` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `page_id` bigint(20) UNIQUE,
  `hero_data` JSON,
  `benefits_data` JSON,
  `age_groups_data` JSON,
  `documents_data` JSON,
  `schedule_data` JSON,
  `contacts_data` JSON,
  `faq_data` JSON,
  `created_at` datetime,
  `updated_at` datetime
)
```

## Развертывание

1. Таблица БД автоматически создается при загрузке темы (в `Arsenal_Academy_Recruitment_Manager::init()`)
2. Админ-интерфейс регистрируется в плагине `arsenal-team-manager`
3. Данные сохраняются через AJAX форму в админке

## Статусы возрастных групп

- **normal** (зелёный) - есть свободные места
- **warning** (жёлтый) - мало мест
- **danger** (красный) - критическое количество мест
