-- Таблица спонсоров и партнеров клуба
CREATE TABLE IF NOT EXISTS `wp_arsenal_sponsors` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Название спонсора/партнера',
  `description` longtext COMMENT 'Описание спонсора',
  `type` varchar(50) NOT NULL COMMENT 'Тип: general_sponsor (генеральный спонсор) или partner',
  `industry` varchar(100) COMMENT 'Отрасль/сектор (Промышленность, Спортивная экипировка, Энергетика, Автомобили, Банковские услуги, Медиа и т.д.)',
  `logo_url` varchar(500) COMMENT 'URL логотипа спонсора',
  `website_url` varchar(500) COMMENT 'URL сайта спонсора',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Активен ли спонсор (1 - да, 0 - нет)',
  `order_index` int(11) DEFAULT 0 COMMENT 'Порядок отображения',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `is_active` (`is_active`),
  KEY `order_index` (`order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица спонсоров и партнеров ФК Арсенал';

-- Вставка тестовых данных
INSERT INTO `wp_arsenal_sponsors` (name, description, type, industry, logo_url, is_active, order_index) VALUES
('БелТехПром', 'Крупнейший промышленный холдинг Минской области. Генеральный спонсор клуба с 2018 года. Компания активно поддерживает развитие спорта в регионе и инвестирует в модернизацию инфраструктуры стадиона.', 'general_sponsor', 'Промышленность', '/wp-content/themes/arsenal/assets/images/sponsors/beltehprom-logo.png', 1, 1),
('СпортЭкипировка', 'Официальный поставщик спортивной экипировки и формы команды. Сотрудничество с 2019 года.', 'partner', 'Спортивная экипировка', '/wp-content/themes/arsenal/assets/images/sponsors/sportekiprovka-logo.png', 1, 1),
('Энергетик', 'Региональная энергетическая компания. Обеспечивает стадион электроэнергией и поддерживает молодежные программы клуба.', 'partner', 'Энергетика', '/wp-content/themes/arsenal/assets/images/sponsors/energetik-logo.png', 1, 2),
('АвтоБел', 'Крупнейший автодилер региона. Предоставляет транспортные услуги для команды на выездные матчи.', 'partner', 'Автомобили', '/wp-content/themes/arsenal/assets/images/sponsors/autobel-logo.png', 1, 3),
('БелБанк', 'Официальный банковский партнер клуба. Обеспечивает финансовые услуги и поддержку болельщицких программ.', 'partner', 'Банковские услуги', '/wp-content/themes/arsenal/assets/images/sponsors/belbank-logo.png', 1, 4),
('МедиаГруп', 'Медиахолдинг, освещающий все матчи клуба. Производит видеоконтент и ведет трансляции домашних игр.', 'partner', 'Медиа', '/wp-content/themes/arsenal/assets/images/sponsors/mediagroup-logo.png', 1, 5);
