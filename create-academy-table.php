<?php
/**
 * Создание таблицы wp_arsenal_academy_recruitment
 * 
 * Запустить: php create-academy-table.php
 */

require_once 'wp-load.php';

global $wpdb;

$table = $wpdb->prefix . 'arsenal_academy_recruitment';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS `$table` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`page_id` bigint(20) unsigned NOT NULL COMMENT 'ID страницы в wp_posts',
	`hero_data` JSON DEFAULT NULL COMMENT 'Данные hero секции',
	`benefits_data` JSON DEFAULT NULL COMMENT 'Массив карточек преимуществ',
	`age_groups_data` JSON DEFAULT NULL COMMENT 'Массив возрастных групп',
	`documents_data` JSON DEFAULT NULL COMMENT 'Массив необходимых документов',
	`schedule_data` JSON DEFAULT NULL COMMENT 'Расписание просмотров',
	`contacts_data` JSON DEFAULT NULL COMMENT 'Контактная информация',
	`faq_data` JSON DEFAULT NULL COMMENT 'Частые вопросы',
	`created_at` datetime DEFAULT CURRENT_TIMESTAMP,
	`updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `page_id` (`page_id`)
) $charset_collate COMMENT='Управление контентом страницы набора в академию';";

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
dbDelta( $sql );

echo "✅ Таблица $table создана или обновлена\n";
