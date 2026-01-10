# Исправление ошибки создания матча - Итоговая сводка

## Проблема
При попытке создания матча в админ-панели происходила ошибка:
```
Cannot add or update a child row: a foreign key constraint fails
CONSTRAINT `fk_matches_league_id` FOREIGN KEY (`league_id`)
```

Причина: INSERT запрос не содержал обязательные поля `league_id` и `season_id`, которые требуются по FK ограничениям.

## Решение

### 1. ✅ Обновлена функция `create_match()` в Manager классе
**Файл:** `wp-content/plugins/arsenal-team-manager/admin/class-arsenal-match-manager.php`

- Добавлена валидация `league_id` и `season_id` (линия 142)
- Добавлены эти поля в `$insert_data` массив (линии 152-153)
- Обновлена `$format` массив с типами данных (линии 156-157)

```php
// Валидация обязательных полей
$required = array( 'league_id', 'season_id', 'tournament_id', 'match_date', 'home_team_id', 'away_team_id' );

// Insert data
$insert_data = array(
    'match_id' => null, // БД триггер
    'league_id' => sanitize_text_field( $data['league_id'] ?? '' ),
    'season_id' => sanitize_text_field( $data['season_id'] ?? '' ),
    // ...остальные поля
);
```

### 2. ✅ Обновлены обработчики форм
**Файл:** `wp-content/plugins/arsenal-team-manager/admin/class-arsenal-match-admin.php`

- `handle_create_match()` - добавлено извлечение `league_id` из POST (линия 144)
- `handle_update_match()` - добавлено извлечение `league_id` из POST (линия 206)
- `render_match_form()` - добавлено получение списка лиг `$leagues` (линия 116)

### 3. ✅ Обновлена форма матча
**Файл:** `wp-content/plugins/arsenal-team-manager/admin/views/match-form.php`

- Добавлено поле выбора `league_id` в форму (после турнира)
- Поле помечено как обязательное (required)
- Поле `season_id` теперь помечено как обязательное (required)

```php
<!-- Лига -->
<div>
    <label for="league_id">Лига <span style="color: red;">*</span></label>
    <select name="league_id" id="league_id" required>
        <option value="">— Выберите лигу —</option>
        <?php foreach ( $leagues as $league_id => $league_name ) : ?>
            <option value="<?php echo esc_attr( $league_id ); ?>">
                <?php echo esc_html( $league_name ); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
```

### 4. ✅ БД триггер для автогенерации match_id
**Файл:** `database/create-match-id-trigger.sql`

Триггер уже установлен в БД. Он автоматически генерирует уникальный `match_id` формата:
```
MATCH_[unix_timestamp]_[random_hex]
Пример: MATCH_1673174400_a7f3c
```

## Проверка

Все проверки пройдены ✅:
- Таблицы БД на месте
- Поля `league_id` и `season_id` существуют
- FK ограничения установлены
- Справочные данные присутствуют (2 лиги, 14 сезонов, 2 турнира, 53 команды)
- Класс `Arsenal_Match_Manager` готов

## Требуемые при создании матча поля

Обязательные:
- `league_id` ✅ (теперь в форме)
- `season_id` ✅ (теперь в форме как required)
- `tournament_id` ✅ (уже был)
- `match_date` ✅ (уже был)
- `home_team_id` ✅ (уже был)
- `away_team_id` ✅ (уже был)

## Как тестировать

1. Откройте WordPress админка
2. Перейдите на Arsenal → Матчи → Добавить новый матч
3. Заполните все поля (включая новые: Лига и Сезон)
4. Сохраните матч
5. Матч должен создаться БЕЗ ошибок FK constraint

## Файлы, затронутые изменениями

1. `wp-content/plugins/arsenal-team-manager/admin/class-arsenal-match-manager.php` - Логика создания
2. `wp-content/plugins/arsenal-team-manager/admin/class-arsenal-match-admin.php` - Обработчики форм
3. `wp-content/plugins/arsenal-team-manager/admin/views/match-form.php` - Шаблон формы
4. `service_scripts_ai/test-match-creation.php` - Тест готовности (создан)
5. `service_scripts_ai/apply-match-id-trigger.php` - Утилита для применения триггера (создана)
