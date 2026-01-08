DROP TABLE IF EXISTS wp_arsenal_positions;

CREATE TABLE `wp_arsenal_positions` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `position_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `position_id` (`position_id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_position_id` (`position_id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Позиции на поле (вратарь, защитник, полузащитник, нападающий и т.д.)';

INSERT INTO wp_arsenal_positions VALUES ('1', 'A98B3A74', 'Вратарь', '2025-12-25 20:04:55', '2025-12-25 20:04:55');
INSERT INTO wp_arsenal_positions VALUES ('2', '6B9B6564', 'Защитник', '2025-12-25 20:04:55', '2025-12-25 20:04:55');
INSERT INTO wp_arsenal_positions VALUES ('3', '62C23862', 'Полузащитник', '2025-12-25 20:04:55', '2025-12-25 20:04:55');
INSERT INTO wp_arsenal_positions VALUES ('4', '04AADD4E', 'Нападающий', '2025-12-25 20:04:55', '2025-12-25 20:04:55');
