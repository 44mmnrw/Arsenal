-- =====================================================
-- Миграция: Связь должностей с отделами
-- =====================================================
-- Добавить поле department_id в таблицу wp_arsenal_staff_job_titles
-- и создать Foreign Key для связи с wp_arsenal_staff_department

-- Шаг 1: Добавить поле department_id (если ещё нет)
-- Проверка: SHOW COLUMNS FROM wp_arsenal_staff_job_titles LIKE 'department_id';
ALTER TABLE wp_arsenal_staff_job_titles 
ADD COLUMN department_id INT NULL 
AFTER job_title_name;

-- Шаг 2: Добавить внешний ключ (если ещё нет)
-- Предварительно проверить: SHOW CREATE TABLE wp_arsenal_staff_job_titles;
ALTER TABLE wp_arsenal_staff_job_titles 
ADD CONSTRAINT fk_job_title_department_id 
FOREIGN KEY (department_id) 
REFERENCES wp_arsenal_staff_department(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;

-- Шаг 3: (ОПЦИОНАЛЬНО) Обновить существующие должности
-- Раскомментируйте, если нужно привязать все к определённому отделу
-- UPDATE wp_arsenal_staff_job_titles 
-- SET department_id = 1 
-- WHERE department_id IS NULL;

-- =====================================================
-- Проверка результата:
-- =====================================================
-- SELECT id, job_title_name, department_id FROM wp_arsenal_staff_job_titles;
-- SHOW COLUMNS FROM wp_arsenal_staff_job_titles;
