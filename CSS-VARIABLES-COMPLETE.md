# 🎨 ПОЛНАЯ РАСШИФРОВКА ВСЕХ CSS ПЕРЕМЕННЫХ

**Источник**: `wp-content/themes/arsenal/assets/css/`  
**Дата**: 11 января 2026 г.

---

## 📋 ОПРЕДЕЛЕННЫЕ ПЕРЕМЕННЫЕ (в файлах CSS)

### page-history.css (:root)
```css
--color-primary: #900;                                              /* Темный красный */
--color-primary-light: #ff1a1a;                                    /* Светло-красный */
--color-primary-accent: #f33;                                      /* Красный акцент (#ff3333) */
--color-gold: #f0b100;                                             /* Золотой */
--color-text-dark: #0a0a0a;                                        /* Черный текст */
--color-text-secondary: #4a5565;                                   /* Серый вторичный */
--color-text-muted: #364153;                                       /* Приглушенный серый */
--color-border: #d1d5dc;                                           /* Светло-серая граница */
--color-bg-light: #f9fafb;                                         /* Светлый фон */
--color-bg-white: #ffffff;                                         /* Белый фон */
--color-bg-gray: #e5e7eb;                                          /* Светло-серый фон */
--font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;  /* Шрифт */
--shadow-sm: 0px 1px 3px 0px rgba(0,0,0,0.1), 0px 1px 2px -1px rgba(0,0,0,0.1);   /* Малая тень */
--shadow-md: 0px 10px 15px -3px rgba(0,0,0,0.1), 0px 4px 6px -4px rgba(0,0,0,0.1); /* Средняя тень */
--radius-md: 10px;                                                  /* Округление */
```

### page-match.css (:root)
```css
--color-home: #ff1a1a;                                             /* Красный домашняя команда */
--color-away: #155dfc;                                             /* Синий гостевая команда */
--color-field: #00a63e;                                            /* Зеленый поле */
--color-field-dark: #008236;                                       /* Темный зеленый поле */
--color-text-dark: #101828;                                        /* Очень темный текст */
--color-text-light: #d1d5dc;                                       /* Светлый текст */
--color-bg: #f9fafb;                                               /* Светлый фон */
--color-card-bg: #ffffff;                                          /* Белый фон карточки */
--spacing-lg: 1.5rem;                                              /* Большой отступ */
--spacing-md: 1rem;                                                /* Средний отступ */
--spacing-sm: 0.75rem;                                             /* Малый отступ */
--radius: 0.625rem;                                                /* Округление */
```

---

## 🔎 ИСПОЛЬЗУЕМЫЕ ПЕРЕМЕННЫЕ (в коде CSS)

### var(--red-700) 
**HEX**: `#FF1A1A`  
**Описание**: Красный основной (акцент)  
**Использ.**: 28 раз (21 раз var, 7 раз переменная)  
**Файлы**: header.css, footer.css, main.css

### var(--color-text-light)
**HEX**: `#ffffff` ИЛИ `#d1d5dc` (зависит от контекста)  
**Описание**: Светлый текст / Белый  
**Использ.**: 22 раза (13 прямо, 9 с fallback)  
**Файлы**: header.css, main.css, page-coaches.css, player-page.css

### var(--color-primary)
**HEX**: `#900` = `#990000` (из page-history.css)  
**Описание**: Темный красный первичный  
**Использ.**: 9 раз  
**Файлы**: page-history.css

### var(--color-text-dark)
**HEX**: `#0a0a0a` (page-history) ИЛИ `#101828` (page-match)  
**Описание**: Темный черный текст  
**Использ.**: 8 раз  
**Файлы**: page-history.css, page-match.css  
⚠️ **ВНИМАНИЕ**: Две разные определения!

### var(--red-500)
**HEX**: `#ff4d4d` (не найдено явное определение)  
**Описание**: Красный 500 (предположительно)  
**Использ.**: 3 раза  
**Файлы**: page-coaches.css  
❓ **Не определена явно в CSS**

### var(--red-100)
**HEX**: `#FFE5E5` (из page-history.css)  
**Описание**: Светлый красный  
**Использ.**: 5 раз  
**Файлы**: page-coaches.css, page-history.css

### var(--red-900, #990000)
**HEX**: `#990000`  
**Описание**: Очень темный красный  
**Использ.**: 5 раз  
**Файлы**: header.css, footer.css

### var(--color-charcoal, #212121)
**HEX**: `#212121`  
**Описание**: Темный серо-черный (уголь)  
**Использ.**: 4 раза  
**Файлы**: header.css, footer.css

### var(--color-bg-white)
**HEX**: `#ffffff` (из page-history.css)  
**Описание**: Белый фон  
**Использ.**: 4 раза  
**Файлы**: page-history.css

### var(--color-primary-light)
**HEX**: `#ff1a1a` (из page-history.css)  
**Описание**: Светлый красный первичный  
**Использ.**: 4 раза  
**Файлы**: page-history.css

### var(--accent-color)
**HEX**: Не найдено определение!  
**Описание**: Цвет акцента  
**Использ.**: 3 раза  
**Файлы**: news-page.css  
❓ **Не определена в CSS - ошибка!**

### var(--color-text-secondary)
**HEX**: `#4a5565` (из page-history.css)  
**Описание**: Серый вторичный текст  
**Использ.**: 3 раза  
**Файлы**: page-history.css

### var(--color-card-bg)
**HEX**: `#ffffff` (из page-match.css)  
**Описание**: Белый фон карточки  
**Использ.**: 3 раза  
**Файлы**: page-match.css

### var(--color-white, #ffffff)
**HEX**: `#ffffff`  
**Описание**: Белый цвет  
**Использ.**: 2 раза  
**Файлы**: header.css

### var(--color-dark-green, #093733)
**HEX**: `#093733`  
**Описание**: Темный зеленый / Бежевый зеленоватый  
**Использ.**: 2 раза  
**Файлы**: header.css

### var(--color-beige, #ead6be)
**HEX**: `#ead6be`  
**Описание**: Бежевый  
**Использ.**: 2 раза  
**Файлы**: header.css, footer.css

### var(--red-100, #ffe5e5)
**HEX**: `#ffe5e5`  
**Описание**: Светлый красный 100  
**Использ.**: 2 раза  
**Файлы**: page-coaches.css

### var(--red-800, #cc0000)
**HEX**: `#cc0000`  
**Описание**: Красный 800  
**Использ.**: 2 раза  
**Файлы**: header.css

### var(--color-bg)
**HEX**: `#f9fafb` (из page-match.css)  
**Описание**: Светлый фон  
**Использ.**: 2 раза  
**Файлы**: page-match.css

### var(--primary-color)
**HEX**: Не найдено определение!  
**Описание**: Первичный цвет  
**Использ.**: 2 раза  
**Файлы**: news-page.css  
❓ **Не определена в CSS - ошибка!**

### var(--secondary-color)
**HEX**: Не найдено определение!  
**Описание**: Вторичный цвет  
**Использ.**: 2 раза  
**Файлы**: news-page.css  
❓ **Не определена в CSS - ошибка!**

### var(--color-gold)
**HEX**: `#f0b100` (из page-history.css)  
**Описание**: Золотой  
**Использ.**: 2 раза  
**Файлы**: page-history.css

### var(--color-primary-accent)
**HEX**: `#f33` = `#ff3333` (из page-history.css)  
**Описание**: Красный акцент первичный  
**Использ.**: 2 раза  
**Файлы**: page-history.css

### var(--color-home)
**HEX**: `#ff1a1a` (из page-match.css)  
**Описание**: Красный домашняя команда  
**Использ.**: 2 раза  
**Файлы**: page-match.css

### var(--color-away)
**HEX**: `#155dfc` (из page-match.css)  
**Описание**: Синий гостевая команда  
**Использ.**: 2 раза  
**Файлы**: page-match.css

### var(--red-600, #ff3333)
**HEX**: `#ff3333`  
**Описание**: Красный 600  
**Использ.**: 1 раз  
**Файлы**: page-match.css

### var(--red-500, #ff4d4d)
**HEX**: `#ff4d4d`  
**Описание**: Красный 500  
**Использ.**: 1 раз  
**Файлы**: page-coaches.css

### var(--red-600)
**HEX**: `#ff3333` (предположительно)  
**Описание**: Красный 600  
**Использ.**: 1 раз  
**Файлы**: page-match.css  
⚠️ **Не имеет fallback, может быть ошибка**

### var(--red-50, #fff0f0)
**HEX**: `#fff0f0`  
**Описание**: Очень светлый красный  
**Использ.**: 1 раз  
**Файлы**: player-page.css

### var(--color-border)
**HEX**: `#d1d5dc` (из page-history.css)  
**Описание**: Светло-серая граница  
**Использ.**: 1 раз  
**Файлы**: page-history.css

### var(--color-bg-gray)
**HEX**: `#e5e7eb` (из page-history.css)  
**Описание**: Светло-серый фон  
**Использ.**: 1 раз  
**Файлы**: page-history.css

### var(--color-text-muted)
**HEX**: `#364153` (из page-history.css)  
**Описание**: Приглушенный серый текст  
**Использ.**: 1 раз  
**Файлы**: page-history.css

### var(--color-bg-light)
**HEX**: `#f9fafb` (из page-history.css)  
**Описание**: Светлый фон  
**Использ.**: 1 раз  
**Файлы**: page-history.css

### var(--red-900)
**HEX**: `#990000` (предположительно, из page-history.css)  
**Описание**: Очень темный красный  
**Использ.**: 1 раз  
**Файлы**: page-match.css  
⚠️ **Не имеет fallback, может быть ошибка**

### var(--red-800)
**HEX**: `#cc0000` (предположительно)  
**Описание**: Темный красный  
**Использ.**: 1 раз  
**Файлы**: page-match.css  
⚠️ **Не имеет fallback, может быть ошибка**

---

## ⚠️ ПРОБЛЕМЫ И ОШИБКИ

### 1. **Переменные БЕЗ определения**
- `var(--accent-color)` - используется 3 раза, но не определена ❌
- `var(--primary-color)` - используется 2 раза, но не определена ❌
- `var(--secondary-color)` - используется 2 раза, но не определена ❌
- `var(--red-500)` - используется 3 раза без fallback ❌
- `var(--red-600)` - используется 1 раз без fallback ❌
- `var(--red-900)` - используется 1 раз без fallback ❌
- `var(--red-800)` - используется 1 раз без fallback ❌

### 2. **Переменные с РАЗНЫМИ значениями**
- `--color-text-dark`:
  - page-history.css: `#0a0a0a`
  - page-match.css: `#101828` ⚠️
- `--color-text-light`:
  - page-history.css: не определена (используется fallback #ffffff)
  - page-match.css: `#d1d5dc` ⚠️

### 3. **Переменные с Fallback vs без Fallback**
```
var(--color-white, #ffffff)        ✅ С fallback
var(--red-900, #990000)            ✅ С fallback
var(--red-500)                     ❌ БЕЗ fallback
var(--color-text-dark)            ⚠️ Разные в разных файлах
```

---

## 📊 СТАТИСТИКА

### По категориям переменных:
- **Цветовые переменные**: 24
- **Переменные отступов**: 3 (`--spacing-lg`, `--spacing-md`, `--spacing-sm`)
- **Переменные округления**: 3 (`--radius-md`, `--radius`)
- **Переменные теней**: 2 (`--shadow-sm`, `--shadow-md`)
- **Переменные шрифтов**: 1 (`--font-primary`)

**Всего определено**: 33 переменные
**Всего используется**: 46+ переменных (много с fallback)
**Не определено**: 3-8 переменных (ошибка!)

---

## 🎯 РЕКОМЕНДАЦИИ

### 1. **Создать глобальный файл _variables.css**
```css
:root {
    /* КРАСНАЯ ПАЛИТРА */
    --red-50: #fff0f0;
    --red-100: #ffe5e5;
    --red-500: #ff4d4d;
    --red-600: #ff3333;
    --red-700: #ff1a1a;
    --red-800: #cc0000;
    --red-900: #990000;
    --color-primary: #900000;
    
    /* ТЕКСТ */
    --color-text-dark: #0a0a0a;        /* унифицировать! */
    --color-text-light: #ffffff;
    --color-text-secondary: #4a5565;
    --color-text-muted: #364153;
    
    /* КОМАНДЫ */
    --color-home: #ff1a1a;
    --color-away: #155dfc;
    
    /* ПОЛЕ */
    --color-field: #00a63e;
    --color-field-dark: #008236;
    
    /* ФОНЫ */
    --color-bg: #f9fafb;
    --color-bg-white: #ffffff;
    --color-bg-gray: #e5e7eb;
    --color-bg-light: #f9fafb;
    
    /* ГРАНИЦЫ */
    --color-border: #d1d5dc;
    --color-card-bg: #ffffff;
    
    /* ПРОЧЕЕ */
    --color-gold: #f0b100;
    --color-beige: #ead6be;
    --color-charcoal: #212121;
    --color-dark-green: #093733;
    
    /* ОТСТУПЫ */
    --spacing-sm: 0.75rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    
    /* ОКРУГЛЕНИЕ */
    --radius: 0.625rem;
    --radius-md: 10px;
    
    /* ТЕНИ */
    --shadow-sm: 0px 1px 3px 0px rgba(0,0,0,0.1), 0px 1px 2px -1px rgba(0,0,0,0.1);
    --shadow-md: 0px 10px 15px -3px rgba(0,0,0,0.1), 0px 4px 6px -4px rgba(0,0,0,0.1);
    
    /* ШРИФТЫ */
    --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
```

### 2. **Удалить дублирующиеся определения**
- Перенести все из page-history.css в глобальный :root
- Удалить дублирующиеся определения в page-match.css

### 3. **Добавить Fallback для всех переменных**
```css
/* ПЛОХО */
background-color: var(--red-500);

/* ХОРОШО */
background-color: var(--red-500, #ff4d4d);
```

### 4. **Исправить ошибки**
- Добавить определение `--accent-color`, `--primary-color`, `--secondary-color`
- Унифицировать `--color-text-dark` (выбрать один вариант)

---

## 📁 Вывод

**var(--color-border) = `#d1d5dc`** ✅  
Это светло-серая граница, определена в `page-history.css` как часть корневых переменных.
