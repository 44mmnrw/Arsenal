DROP TABLE IF EXISTS wp_arsenal_squad;

CREATE TABLE `wp_arsenal_squad` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `squad_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MD5 хеш статуса',
  `squad_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Название статуса',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_id` (`squad_id`),
  UNIQUE KEY `uk_status_id` (`squad_id`),
  UNIQUE KEY `uk_status_name` (`squad_name`),
  UNIQUE KEY `status_id_2` (`squad_id`),
  UNIQUE KEY `status_id_3` (`squad_id`),
  KEY `idx_status_id` (`squad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Справочная таблица статусов составов';

INSERT INTO wp_arsenal_squad VALUES ('1', '21F3D7B3', 'Основной состав', '2025-12-26 18:52:51', '2025-12-26 18:54:57');
INSERT INTO wp_arsenal_squad VALUES ('2', '43915DAC', 'Дубль', '2025-12-26 18:52:51', '2025-12-26 18:55:07');
