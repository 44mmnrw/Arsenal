# 🎨 Система загрузки SVG Спрайта в Arsenal Theme

## Как работает динамическая загрузка спрайта?

### ✅ Да, спрайт подключается **полностью динамически**

---

## Архитектура загрузки

### 1️⃣ **Инициализация в wp_footer**

**Файл:** `wp-content/themes/arsenal/functions.php` (строка 549)

```php
add_action( 'wp_footer', 'arsenal_include_svg_sprite' );
```

Спрайт подключается на хуке **`wp_footer`** - это означает, что он загружается в конце **body** каждой страницы.

### 2️⃣ **Функция включения спрайта**

**Файл:** `wp-content/themes/arsenal/functions.php` (строки 540-548)

```php
function arsenal_include_svg_sprite() {
    $sprite_path = ARSENAL_THEME_DIR . '/assets/images/sprite.svg';
    if ( file_exists( $sprite_path ) ) {
        echo '<div style="display:none;">';
        include $sprite_path;  // ← Вставляет весь SVG спрайт в скрытый div
        echo '</div>';
    }
}
```

**Как это работает:**
- ✅ Проверяет существование файла `sprite.svg`
- ✅ Если файл есть - **инклудит его в HTML** (вставляет весь SVG в скрытый div)
- ✅ SVG становится доступным для **всех `<use>` элементов** на странице

### 3️⃣ **Использование спрайта через `<use>`**

После инклюда спрайта, любые иконки загружаются по ссылке:

```html
<svg>
    <use xlink:href="/assets/images/sprite.svg#icon-calendar"></use>
</svg>

<!-- ИЛИ с PHP (после инклюда спрайта можно просто использовать якорь) -->
<svg>
    <use xlink:href="#icon-calendar"></use>
</svg>
```

### 4️⃣ **Вспомогательная функция для вывода иконок**

**Файл:** `wp-content/themes/arsenal/functions.php` (строки 520-531)

```php
function arsenal_icon( $icon_id, $width = 20, $height = null, $class = '' ) {
    $height = $height ?? $width;
    $class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
    printf(
        '<svg width="%d" height="%d"%s aria-hidden="true"><use href="#%s"></use></svg>',
        absint( $width ),
        absint( $height ),
        $class_attr,
        esc_attr( $icon_id )
    );
}
```

**Примеры использования в шаблонах:**

```php
<?php arsenal_icon( 'icon-calendar', 24 ); ?>
<?php arsenal_icon( 'icon-phone', 16, 16, 'custom-class' ); ?>
<?php arsenal_icon( 'icon-people', 20 ); ?>
```

---

## Поток загрузки на странице Стадион

```
1. get_header() → wp_head()
   ↓
2. HTML страницы + иконки в <use xlink:href="/sprite.svg#icon-*">
   ↓
3. get_footer() → wp_footer() action
   ↓
4. arsenal_include_svg_sprite() вызывается
   ↓
5. Весь sprite.svg вставляется в скрытый <div style="display:none;">
   ↓
6. Все <use> ссылки на странице теперь имеют доступ к символам спрайта
   ↓
7. Иконки отрисовываются браузером
```

---

## Преимущества такого подхода

| Преимущество | Описание |
|---|---|
| 🔄 **Динамический** | Спрайт загружается PHP функцией, можно модифицировать подключение |
| ⚡ **Производительность** | SVG хранится в одном файле, один HTTP запрос |
| 📱 **Масштабируемость** | SVG векторный - отлично выглядит на любых разрешениях |
| 🎯 **Гибкость** | Легко добавлять новые иконки, менять цвета через CSS |
| 🔒 **Инкапсуляция** | Спрайт в скрытом div - не видна пользователю |

---

## На странице Стадион

**Как используются иконки в stat_cards:**

```php
// Данные из JSON
$card = [
    'title' => 'Год основания',
    'value' => '1959',
    'icon' => 'icon-calendar'  // ← Имя иконки из спрайта
]

// Вывод в шаблоне (page-stadium.php, строка 133)
<svg>
    <use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $icon_id ); ?>"></use>
</svg>
```

Путь можно сокращать (после инклюда спрайта):
```php
<use href="#<?php echo esc_attr( $icon_id ); ?>"></use>
```

---

## Доступные иконки в спрайте

- `icon-calendar` - Календарь 📅
- `icon-people` - Люди 👥
- `icon-place` - Место 📍
- `icon-phone` - Телефон ☎️
- `icon-email` - Email 📧
- `icon-clock` - Часы 🕐
- `icon-report` - Отчет 📄
- `icon-event` - События ⚡
- `icon-arrow-right` - Стрелка вправо ➡️
- `icon-arrow-left` - Стрелка влево ⬅️
- `icon-arrow-up` - Стрелка вверх ⬆️
- `icon-arrow-down` - Стрелка вниз ⬇️
- И ещё 10+ иконок (см. `sprite.svg`)

---

## Проверка работы

### На фронтенде:
```bash
# Откройте DevTools (F12) → Elements
# В конце <body> найдете:
<div style="display:none;">
    <svg>
        <symbol id="icon-calendar">...</symbol>
        <symbol id="icon-people">...</symbol>
        ...
    </svg>
</div>
```

### Проверка на странице Стадион:
1. Откройте страницу стадиона
2. Прокрутите вниз - увидите карточки со своими иконками
3. F12 → Elements → найдите `<svg><use href="#icon-calendar"></use></svg>`
4. Иконка должна отображаться в браузере

---

## Заключение

✅ **Спрайт загружается ПОЛНОСТЬЮ ДИНАМИЧЕСКИ на каждой странице**
✅ **Используется стандартный SVG `<use>` механизм**
✅ **Все иконки доступны через JSON в админ-форме**
✅ **Система готова к масштабированию - легко добавлять новые иконки**
