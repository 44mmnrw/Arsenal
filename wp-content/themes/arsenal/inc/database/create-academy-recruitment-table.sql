-- Таблица для управления контентом страницы набора в академию
-- Использует JSON колонки для гибкого управления данными

CREATE TABLE IF NOT EXISTS `wp_arsenal_academy_recruitment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned NOT NULL COMMENT 'ID страницы в wp_posts',
  
  -- Hero секция
  `hero_data` JSON DEFAULT NULL COMMENT 'Данные hero секции: {title, description, buttons[{text, action, style}]}',
  
  -- Преимущества (Why Us)
  `benefits_data` JSON DEFAULT NULL COMMENT 'Массив карточек преимуществ: [{icon, title, description}]',
  
  -- Возрастные группы
  `age_groups_data` JSON DEFAULT NULL COMMENT 'Массив групп: [{name, age_range, birth_years, schedule, spots_available, spots_status}]',
  
  -- Необходимые документы
  `documents_data` JSON DEFAULT NULL COMMENT 'Массив документов: [{text, icon}] + notice',
  
  -- Расписание просмотров
  `schedule_data` JSON DEFAULT NULL COMMENT 'Расписание: {items: [{icon, heading, text}], notice}',
  
  -- Контакты
  `contacts_data` JSON DEFAULT NULL COMMENT 'Контакты: {address, phone, phone_hours, email, director: {title, name, role, contacts[]}, location: {title, distance, transport: {car, bus}}, social: [{name, url}]}',
  
  -- FAQ
  `faq_data` JSON DEFAULT NULL COMMENT 'Частые вопросы: [{question, answer}]',
  
  -- Метаданные
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Управление контентом страницы набора в академию через JSON';

-- Пример структуры данных для заполнения:
/*
INSERT INTO `wp_arsenal_academy_recruitment` 
(`page_id`, `hero_data`, `benefits_data`, `age_groups_data`, `documents_data`, `schedule_data`, `contacts_data`, `faq_data`) 
VALUES (
  -- page_id (нужно получить из wp_posts)
  123,
  
  -- hero_data
  '{
    "title": "Набор в академию",
    "description": "СДЮШ \\"Арсенал\\" объявляет набор детей в возрасте от 8 до 17 лет.",
    "buttons": [
      {"text": "Подать заявку", "action": "apply", "style": "primary"},
      {"text": "Контакты", "action": "#contacts", "style": "secondary"}
    ]
  }',
  
  -- benefits_data
  '[
    {
      "icon": "coaches",
      "title": "Профессиональные тренеры",
      "description": "Все наши тренеры имеют лицензии UEFA и многолетний опыт работы с детьми"
    },
    {
      "icon": "facilities",
      "title": "Современная база",
      "description": "Два полноразмерных поля, крытый манеж, тренажерный зал и медицинский кабинет"
    },
    {
      "icon": "safety",
      "title": "Безопасность",
      "description": "Полная медицинская страховка, контроль здоровья и профессиональное оборудование"
    },
    {
      "icon": "pro",
      "title": "Путь в профи",
      "description": "12 выпускников нашей академии уже играют за основную команду в Высшей лиге"
    }
  ]',
  
  -- age_groups_data
  '[
    {
      "name": "U-9",
      "age_range": "8-9 лет",
      "birth_years": "2016-2017",
      "schedule": "Пн, Ср, Пт: 16:00-17:30",
      "spots_available": 15,
      "spots_status": "normal"
    },
    {
      "name": "U-11",
      "age_range": "10-11 лет",
      "birth_years": "2014-2015",
      "schedule": "Вт, Чт, Сб: 16:00-17:30",
      "spots_available": 12,
      "spots_status": "normal"
    },
    {
      "name": "U-13",
      "age_range": "12-13 лет",
      "birth_years": "2012-2013",
      "schedule": "Пн, Ср, Пт: 17:30-19:00",
      "spots_available": 8,
      "spots_status": "warning"
    },
    {
      "name": "U-15",
      "age_range": "14-15 лет",
      "birth_years": "2010-2011",
      "schedule": "Вт, Чт, Сб: 17:30-19:00",
      "spots_available": 5,
      "spots_status": "danger"
    },
    {
      "name": "U-17",
      "age_range": "16-17 лет",
      "birth_years": "2008-2009",
      "schedule": "Пн, Ср, Пт: 19:00-20:30",
      "spots_available": 3,
      "spots_status": "danger"
    }
  ]',
  
  -- documents_data
  '{
    "items": [
      {"text": "Медицинская справка о допуске к занятиям спортом"},
      {"text": "Свидетельство о рождении (копия)"},
      {"text": "Паспорт одного из родителей (копия)"},
      {"text": "Фотография 3x4 (2 шт.)"},
      {"text": "Спортивная форма и обувь"}
    ],
    "notice": "Медицинская справка должна быть получена не ранее чем за 1 месяц до начала занятий. Все копии документов должны быть заверены."
  }',
  
  -- schedule_data
  '{
    "items": [
      {
        "icon": "calendar",
        "heading": "Каждую субботу",
        "text": "10:00 - 12:00"
      },
      {
        "icon": "location",
        "heading": "Стадион",
        "text": "ул. Спортивная, 2"
      },
      {
        "icon": "phone",
        "heading": "Запись",
        "text": "+375 (17) 123-45-70"
      }
    ],
    "notice": "Предварительная запись обязательна! Позвоните или напишите нам заранее."
  }',
  
  -- contacts_data
  '{
    "address": "ул. Спортивная, 2, г. Дзержинск, Минская обл., 222720",
    "phone": "+375 (17) 123-45-70",
    "phone_hours": "Пн-Пт: 9:00-18:00, Сб: 9:00-14:00",
    "email": "academy@arsenal-dzr.by",
    "director": {
      "title": "Директор СДЮШ",
      "name": "Петр Иванович Кузнецов",
      "role": "Директор с 2010 года",
      "contacts": [
        {"type": "phone", "value": "+375 (17) 123-45-71"},
        {"type": "email", "value": "kuznetsov@arsenal-dzr.by"}
      ]
    },
    "location": {
      "title": "Спортивный комплекс \\"Арсенал\\"",
      "distance": "15 минут от центра города",
      "transport": {
        "car": "Бесплатная парковка на территории",
        "bus": "Автобусы №12, 34, 56 (остановка \\"Спортивная\\")"
      }
    },
    "social": [
      {"name": "Instagram", "url": "#"},
      {"name": "Facebook", "url": "#"},
      {"name": "YouTube", "url": "#"},
      {"name": "Telegram", "url": "#"}
    ]
  }',
  
  -- faq_data
  '[
    {
      "question": "Сколько стоят занятия?",
      "answer": "Обучение в СДЮШ \\"Арсенал\\" бесплатное. Все занятия финансируются клубом. Родителям необходимо приобрести только спортивную форму и обувь."
    },
    {
      "question": "Нужен ли опыт игры в футбол?",
      "answer": "Нет, опыт не требуется. Мы принимаем детей с любым уровнем подготовки. Главное — желание заниматься футболом и физическое здоровье."
    },
    {
      "question": "Как проходит отбор?",
      "answer": "Отбор проходит в форме просмотра на тренировочной базе. Тренеры оценивают физические данные, координацию, скорость и технику владения мячом. Решение принимается в течение недели после просмотра."
    },
    {
      "question": "Можно ли совмещать занятия с учебой?",
      "answer": "Да, расписание составлено с учетом школьных занятий. Тренировки проходят во второй половине дня. Мы также помогаем с индивидуальным графиком при необходимости."
    }
  ]'
);
*/
