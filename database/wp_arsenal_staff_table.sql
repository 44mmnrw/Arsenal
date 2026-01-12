SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wp_arsenal_staff`;
CREATE TABLE `wp_arsenal_staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `second_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title_id` int DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_first_name` (`first_name`),
  KEY `idx_second_name` (`second_name`),
  KEY `idx_job_title_id` (`job_title_id`),
  CONSTRAINT `fk_staff_job_title` FOREIGN KEY (`job_title_id`) REFERENCES `wp_arsenal_staff_job_titles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wp_arsenal_staff` (`id`, `first_name`, `second_name`, `job_title_id`, `birth_date`, `contract_start`, `contract_end`, `phone`, `email`, `photo_url`, `bio`, `created_at`, `updated_at`) VALUES
('1', 'Вячеслав', 'Вашкевич', '1', NULL, NULL, NULL, '', '', '/wp-content/uploads/2026/01/coach.png', '', '2026-01-11 11:45:07', '2026-01-11 11:55:41');

SET FOREIGN_KEY_CHECKS=1;
