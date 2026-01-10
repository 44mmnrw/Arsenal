/**
 * Триггер для автогенерации match_id в таблице wp_arsenal_matches
 * 
 * Генерирует уникальный ID вида: MATCH_[timestamp]_[random]
 * Пример: MATCH_1673174400_a7f3c
 * 
 * @package Arsenal
 * @since 1.0.0
 */

-- Удалить старый триггер если он существует
DROP TRIGGER IF EXISTS `trg_generate_match_id`;

-- Создать триггер
DELIMITER $$

CREATE TRIGGER `trg_generate_match_id`
BEFORE INSERT ON `wp_arsenal_matches`
FOR EACH ROW
BEGIN
  -- Если match_id не указан или пуст, генерируем новый
  IF NEW.match_id IS NULL OR NEW.match_id = '' THEN
    SET NEW.match_id = CONCAT(
      'MATCH_',
      UNIX_TIMESTAMP(NOW()),
      '_',
      LOWER(HEX(RAND() * 0xFFFFFF))
    );
  END IF;
END$$

DELIMITER ;

-- Проверка триггера
SELECT TRIGGER_SCHEMA, TRIGGER_NAME, EVENT_MANIPULATION, ACTION_STATEMENT 
FROM INFORMATION_SCHEMA.TRIGGERS 
WHERE TRIGGER_NAME = 'trg_generate_match_id';
