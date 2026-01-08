DROP TABLE IF EXISTS wp_arsenal_team_contracts;

CREATE TABLE `wp_arsenal_team_contracts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Уникальный ID записи контракта',
  `player_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK playet_id',
  `squad_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Основной' COMMENT 'Тип состава',
  `contract_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_start` date DEFAULT NULL COMMENT 'Дата начала контракта',
  `contract_end` date DEFAULT NULL COMMENT 'Дата окончания контракта',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `squad_id` (`contract_id`),
  KEY `idx_player_id` (`player_id`),
  KEY `idx_status` (`squad_id`),
  KEY `uk_team_season_player` (`player_id`),
  KEY `idx_active_status` (`squad_id`),
  CONSTRAINT `fk_team_contracts_player` FOREIGN KEY (`player_id`) REFERENCES `wp_arsenal_players` (`player_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_team_contracts_squad` FOREIGN KEY (`squad_id`) REFERENCES `wp_arsenal_squad` (`squad_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Таблица хранит сведения и периоде работы игрока в команде, тип состава';

INSERT INTO wp_arsenal_team_contracts VALUES ('7', 'CE478F7B', '2C3F29F0', '21F3D7B3', '3333', '2025-11-06', '2026-02-06', '2025-12-27 11:20:51', '2025-12-30 12:10:01');
INSERT INTO wp_arsenal_team_contracts VALUES ('8', '632B2255', '9DA7A702', '21F3D7B3', '5555', '2025-11-01', '2026-04-30', '2025-12-29 19:40:45', '2025-12-30 12:08:12');
INSERT INTO wp_arsenal_team_contracts VALUES ('12', '0809E965', '8702A112', '21F3D7B3', '4555', '2025-12-01', '2026-07-16', '2025-12-30 12:14:24', '2025-12-30 12:14:24');
INSERT INTO wp_arsenal_team_contracts VALUES ('13', '29D22FEE', '8637B541', '21F3D7B3', '54353453', '2025-12-04', '2026-09-26', '2026-01-06 14:13:09', '2026-01-06 14:13:09');
INSERT INTO wp_arsenal_team_contracts VALUES ('14', 'D0F6DC64', '199693B4', '21F3D7B3', '76544', '2025-10-31', '2026-08-27', '2026-01-06 14:14:08', '2026-01-06 14:14:08');
