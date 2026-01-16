<?php
/**
 * Класс для управления отделами и фильтрацией сотрудников
 * 
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! class_exists( 'Arsenal_Staff_Department_Manager' ) ) {
	class Arsenal_Staff_Department_Manager {

		/**
		 * Получить все отделы
		 * 
		 * @return array Массив объектов отделов
		 */
		public static function get_all_departments() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'arsenal_staff_department';
			
			return $wpdb->get_results(
				"SELECT * FROM {$table_name} ORDER BY id ASC"
			);
		}

		/**
		 * Получить отдел по ID
		 * 
		 * @param int $department_id ID отдела
		 * @return object|null Объект отдела или null
		 */
		public static function get_department( $department_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'arsenal_staff_department';
			
			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE id = %d",
					$department_id
				)
			);
		}

		/**
		 * Получить сотрудников по отделу
		 * 
		 * @param int $department_id ID отдела
		 * @return array Массив объектов сотрудников
		 */
		public static function get_staff_by_department( $department_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'arsenal_staff';
			
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE department_id = %d ORDER BY first_name, second_name",
					$department_id
				)
			);
		}

		/**
		 * Получить все сотрудники
		 * 
		 * @return array Массив объектов сотрудников
		 */
		public static function get_all_staff() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'arsenal_staff';
			
			return $wpdb->get_results(
				"SELECT * FROM {$table_name} ORDER BY first_name, second_name"
			);
		}
	}
}
