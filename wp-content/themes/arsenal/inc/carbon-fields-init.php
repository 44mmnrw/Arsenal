<?php
/**
 * Carbon Fields - Конфигурация метабоксов
 * 
 * Управление страницей набора в академию через post_meta
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Получить список иконок из sprite.svg
 */
function arsenal_get_sprite_icons() {
	$sprite_path = get_template_directory() . '/assets/images/sprite.svg';
	$icons = array( '' => '-- Выбрать иконку --' );
	
	if ( file_exists( $sprite_path ) ) {
		$sprite_content = file_get_contents( $sprite_path );
		preg_match_all( '/<symbol\s+id="([^"]+)"[^>]*data-name="([^"]+)"/', $sprite_content, $matches );
		
		if ( ! empty( $matches[1] ) ) {
			for ( $i = 0; $i < count( $matches[1] ); $i++ ) {
				$icons[ $matches[1][$i] ] = $matches[2][$i];
			}
		}
	}
	
	return $icons;
}

/**
 * Регистрация метабоксов Carbon Fields
 */
add_action( 'carbon_fields_register_fields', function() {
	Container::make( 'post_meta', 'academy_recruitment_data', 'Данные страницы набора в академию' )
		->where( 'post_template', '=', 'templates/page-academy-recruitment.php' )
		->add_tab( 'Hero секция', [
			Field::make( 'text', '_academy_hero_title', 'Заголовок' )
				->set_default_value( 'Набор в академию' ),
			
		Field::make( 'textarea', '_academy_hero_description', 'Описание' ),
		] )
		
		->add_tab( 'Преимущества', [
			Field::make( 'complex', '_academy_benefits', 'Карточки преимуществ' )
				->add_fields( [
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
					Field::make( 'text', 'title', 'Заголовок' ),
					Field::make( 'textarea', 'description', 'Описание' ),
				] )
				->set_header_template( '
					<% if (title) { %>
						<%- title %>
					<% } else { %>
						Новая карточка
					<% } %>
				' ),
		] )
		
		->add_tab( 'Возрастные группы', [
			Field::make( 'complex', '_academy_age_groups', 'Группы' )
				->add_fields( [
					Field::make( 'text', 'name', 'Название' )
						->set_help_text( 'Например: U-9' ),
					Field::make( 'text', 'age_range', 'Возраст' )
						->set_help_text( 'Например: 8-9 лет' ),
					Field::make( 'text', 'birth_years', 'Год рождения' )
						->set_help_text( 'Например: 2016-2017' ),
					Field::make( 'text', 'schedule', 'Расписание' )
						->set_help_text( 'Например: Пн, Ср, Пт: 16:00-17:30' ),
					Field::make( 'text', 'spots_available', 'Доступно мест' )

						->set_default_value( '0' ),
					Field::make( 'select', 'spots_status', 'Статус мест' )
						->add_options( [
							'normal'  => 'Обычный',
							'warning' => 'Внимание',
							'danger'  => 'Критический',
						] )
						->set_default_value( 'normal' ),
				] )
				->set_header_template( '
					<% if (name) { %>
						<%- name %> <% if (age_range) { %>(<%- age_range %>)<% } %>
					<% } else { %>
						Новая группа
					<% } %>
				' ),
		] )
		
		->add_tab( 'Документы', [
			Field::make( 'complex', '_academy_documents', 'Список документов' )
				->add_fields( [
					Field::make( 'text', 'text', 'Описание документа' ),
				] )
				->set_header_template( '
					<% if (text) { %>
						<%- text %>
					<% } else { %>
						Новый документ
					<% } %>
				' ),
			
			Field::make( 'textarea', '_academy_documents_notice', 'Примечание' ),
		] )
		
		->add_tab( 'Расписание просмотров', [
			Field::make( 'complex', '_academy_schedule', 'Расписание' )
				->add_fields( [
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
					Field::make( 'text', 'heading', 'Заголовок' )
						->set_help_text( 'Например: Каждую субботу' ),
					Field::make( 'text', 'text', 'Время' )
						->set_help_text( 'Например: 10:00 - 12:00' ),
				] )
				->set_header_template( '
					<% if (heading) { %>
						<%- heading %>
					<% } else { %>
						Новый пункт
					<% } %>
				' ),
			
			Field::make( 'textarea', '_academy_schedule_notice', 'Примечание' ),
		] )
		
		->add_tab( 'Контакты', [
			Field::make( 'text', '_academy_contacts_address', 'Адрес' ),
			Field::make( 'text', '_academy_contacts_phone', 'Телефон' ),
			Field::make( 'text', '_academy_contacts_email', 'Email' ),
			
			Field::make( 'complex', '_academy_working_schedule', 'Рабочие дни и время' )
				->add_fields( [
					Field::make( 'text', 'day', 'День недели' ),
					Field::make( 'text', 'time', 'Время работы' ),
				] )
				->set_header_template( '
					<% if (day) { %>
						<%- day %> <% if (time) { %>— <%- time %><% } %>
					<% } else { %>
						Новый день
					<% } %>
				' ),
			
			Field::make( 'text', '_academy_map_url', 'Ссылка на карту' )
				->set_help_text( 'Google Maps или Яндекс Карты. ВАЖНО: используйте полную ссылку с координатами, не короткую (goo.gl). Пример: https://www.google.com/maps?q=53.6603,27.5334' ),
		] )
		
		->add_tab( 'Директор', [
			Field::make( 'text', '_academy_director_name', 'Имя директора' ),
			Field::make( 'text', '_academy_director_role', 'Должность/Стаж' ),
			Field::make( 'text', '_academy_director_phone', 'Телефон' ),
			Field::make( 'text', '_academy_director_email', 'Email' ),
		] )
		
		->add_tab( 'Маршруты проезда', [
			Field::make( 'complex', '_academy_directions', 'Маршруты' )
				->add_fields( [
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
					Field::make( 'text', 'transport', 'Транспорт' )
						->set_help_text( 'Например: Автобус' ),
					Field::make( 'text', 'route', 'Маршрут' )
						->set_help_text( 'Например: №24, 45' ),
					Field::make( 'text', 'time', 'Время в пути' )
						->set_help_text( 'Например: 15 минут' ),
				] )
					->set_header_template( '
						<% if (transport) { %>
							<%- transport %> <% if (route) { %>— <%- route %><% } %>
						<% } else { %>
							Новый маршрут
						<% } %>
					' ),
		] )
		
		->add_tab( 'Социальные сети', [
			Field::make( 'complex', '_academy_social', 'Ссылки на соцсети' )
				->add_fields( [
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
					Field::make( 'text', 'url', 'URL профиля' ),
				] )
				->set_header_template( '
					<% if (url) { %>
						<%- url %>
					<% } else { %>
						Новая соцсеть
					<% } %>
				' ),
		] )
		
		->add_tab( 'FAQ', [
			Field::make( 'complex', '_academy_faq', 'Частые вопросы' )
				->add_fields( [
					Field::make( 'text', 'question', 'Вопрос' ),
					Field::make( 'textarea', 'answer', 'Ответ' ),
				] )
				->set_header_template( '
					<% if (question) { %>
						<%- question %>
					<% } else { %>
						Новый вопрос
					<% } %>
				' ),
		] );

	// Метабокс для страницы История клуба
	Container::make( 'post_meta', 'history_page_data', 'История клуба' )
		->where( 'post_template', '=', 'templates/page-history.php' )
		->add_tab( 'Основная информация', [
			Field::make( 'text', '_history_title', 'Название секции' )
				->set_default_value( 'История клуба' ),
			Field::make( 'textarea', '_history_description', 'Описание' ),
			Field::make( 'complex', '_history_scale', 'События' )
				->add_fields( [
					Field::make( 'text', 'year', 'Год' ),
					Field::make( 'text', 'event', 'Событие' ),
				] )
				->set_header_template( '
					<% if (year) { %>
						<%- year %> <% if (event) { %>— <%- event %><% } %>
					<% } else { %>
						Новое событие
					<% } %>
				' ),
		] )
		
		->add_tab( 'Рекорды и достижения', [
			Field::make( 'text', '_history_title_second', 'Название секции' )
				->set_default_value( 'Рекорды и достижения' ),
			
			Field::make( 'complex', '_history_records', 'Рекорды' )
				->add_fields( [
					Field::make( 'text', 'title', 'Название' ),
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
					Field::make( 'select', 'style', 'Стиль' )
						->add_options( [
							'primary' => 'Primary',
							'white'   => 'White',
						] ),
					Field::make( 'complex', 'items', 'Пункты' )
						->add_fields( [
							Field::make( 'text', 'text', 'Текст' ),
						] ),
				] )
				->set_header_template( '
					<% if (title) { %>
						<%- title %>
					<% } else { %>
						Новый рекорд
					<% } %>
				' ),
			
			Field::make( 'complex', '_history_achievements', 'Достижения' )
				->add_fields( [
					Field::make( 'text', 'label', 'Название' ),
					Field::make( 'text', 'description', 'Значение' ),
				] )
				->set_header_template( '
					<% if (label) { %>
						<%- label %>
					<% } else { %>
						Новое достижение
					<% } %>
				' ),
		] )
		
		->add_tab( 'Дополнительная секция', [
			Field::make( 'text', '_history_title_third', 'Название секции' )
				->set_default_value( 'Дополнительная информация' ),
			
			Field::make( 'complex', '_history_additional_cards', 'Карточки' )
				->add_fields( [
					Field::make( 'text', 'label', 'Название' ),
					Field::make( 'text', 'description', 'Описание' ),
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
				] )
				->set_header_template( '
					<% if (label) { %>
						<%- label %>
					<% } else { %>
						Новая карточка
					<% } %>
				' ),
		] );

	// Страница истории академии
	Container::make( 'post_meta', 'academy_history_data', 'Данные страницы истории академии' )
		->where( 'post_template', '=', 'templates/page-academy-history.php' )
		
		->add_tab( 'Hero секция', [
			Field::make( 'textarea', '_academy_history_hero_description', 'Описание' )

				->set_default_value( 'Спортивная детско-юношеская школа "Арсенал" — футбольная академия клуба, основанная в 2010 году. За 15 лет работы школа подготовила более 500 молодых футболистов.' ),
		] )
		
		->add_tab( 'Статистика', [
			Field::make( 'complex', '_academy_history_stat_cards', 'Статистические карточки' )
				->add_fields( [
					Field::make( 'select', 'icon', 'Иконка' )
						->add_options( 'arsenal_get_sprite_icons' ),
					Field::make( 'text', 'number', 'Цифра' ),
					Field::make( 'text', 'label', 'Название' ),
				] )
				->set_header_template( '
					<% if (label) { %>
						<%- number %> <%- label %>
					<% } else { %>
						Новая карточка
					<% } %>
				' ),
		] )
		
		->add_tab( 'Ключевые события', [
			Field::make( 'complex', '_academy_history_timeline', 'События' )
				->add_fields( [
					Field::make( 'text', 'title', 'Название события' ),
					Field::make( 'text', 'year', 'Год' ),
					Field::make( 'textarea', 'description', 'Описание' ),
				] )
				->set_header_template( '
					<% if (title) { %>
						<%- title %> (<%- year %>)
					<% } else { %>
						Новое событие
					<% } %>
				' ),
		] )
		
		->add_tab( 'Тренерский штаб', [
			Field::make( 'complex', '_academy_history_staff', 'Тренеры' )
				->add_fields( [
					Field::make( 'text', 'name', 'ФИ' ),
					Field::make( 'text', 'position', 'Должность' ),
					Field::make( 'text', 'since', 'С какого года' ),
				] )
				->set_header_template( '
					<% if (name) { %>
						<%- name %>
					<% } else { %>
						Новый тренер
					<% } %>
				' ),
		] );
} );
