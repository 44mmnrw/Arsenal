<?php
/**
 * Шаблон страницы "История ДЮСШ"
 *
 * Template Name: История ДЮСШ
 * Template Post Type: page
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();

// Подключение стилей страницы
wp_enqueue_style( 'arsenal-academy-history', get_template_directory_uri() . '/assets/css/pages/page-academy-history.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

?>

<main id="main" class="site-main">
	<div class="academy-history-container">
		
		<!-- Заголовок и описание -->
		<section class="academy-history-intro">
			<div class="academy-history-intro__header">
				<h1 class="academy-history-intro__title">История ДЮСШ</h1>
				<p class="academy-history-intro__description">Спортивная детско-юношеская школа "Арсенал" — футбольная академия клуба, основанная в 2010 году. За 15 лет работы школа подготовила более 500 молодых футболистов.</p>
			</div>

			<!-- Статистические карточки -->
			<div class="academy-stats-grid">
				<div class="academy-stat-card">
					<div class="academy-stat-card__icon">
						<svg width="32" height="32" viewBox="0 0 32 32" fill="none">
							<path d="M16 2C8.27 2 2 8.27 2 16s6.27 14 14 14 14-6.27 14-14S23.73 2 16 2zm0 26c-6.63 0-12-5.37-12-12s5.37-12 12-12 12 5.37 12 12-5.37 12-12 12z" fill="currentColor"/>
							<path d="M16 7c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm0 16c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-stat-card__content">
						<h3 class="academy-stat-card__number">8 чемпионств</h3>
						<p class="academy-stat-card__label">Областных турниров</p>
					</div>
				</div>

				<div class="academy-stat-card">
					<div class="academy-stat-card__icon">
						<svg width="32" height="32" viewBox="0 0 32 32" fill="none">
							<path d="M16 2C8.27 2 2 8.27 2 16s6.27 14 14 14 14-6.27 14-14S23.73 2 16 2zm0 26c-6.63 0-12-5.37-12-12s5.37-12 12-12 12 5.37 12 12-5.37 12-12 12z" fill="currentColor"/>
							<path d="M16 7c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm0 16c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-stat-card__content">
						<h3 class="academy-stat-card__number">500+ воспитанников</h3>
						<p class="academy-stat-card__label">За всю историю</p>
					</div>
				</div>

				<div class="academy-stat-card">
					<div class="academy-stat-card__icon">
						<svg width="32" height="32" viewBox="0 0 32 32" fill="none">
							<path d="M16 2C8.27 2 2 8.27 2 16s6.27 14 14 14 14-6.27 14-14S23.73 2 16 2zm0 26c-6.63 0-12-5.37-12-12s5.37-12 12-12 12 5.37 12 12-5.37 12-12 12z" fill="currentColor"/>
							<path d="M16 7c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm0 16c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-stat-card__content">
						<h3 class="academy-stat-card__number">12 игроков</h3>
						<p class="academy-stat-card__label">В основном составе</p>
					</div>
				</div>

				<div class="academy-stat-card">
					<div class="academy-stat-card__icon">
						<svg width="32" height="32" viewBox="0 0 32 32" fill="none">
							<path d="M16 2C8.27 2 2 8.27 2 16s6.27 14 14 14 14-6.27 14-14S23.73 2 16 2zm0 26c-6.63 0-12-5.37-12-12s5.37-12 12-12 12 5.37 12 12-5.37 12-12 12z" fill="currentColor"/>
							<path d="M16 7c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm0 16c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-stat-card__content">
						<h3 class="academy-stat-card__number">Лучшая ДЮСШ</h3>
						<p class="academy-stat-card__label">Минской области (2021)</p>
					</div>
				</div>
			</div>
		</section>

		<!-- Ключевые события -->
		<section class="academy-events-section">
			<h2 class="academy-events-section__title">Ключевые события</h2>
			
			<div class="academy-events-timeline">
				<!-- Событие 1 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Основание ДЮСШ</h3>
							<span class="academy-event-card__year">2010</span>
						</div>
						<p class="academy-event-card__description">Спортивная детско-юношеская школа "Арсенал" была официально создана при поддержке ФК Арсенал (Дзержинск).</p>
					</div>
				</div>

				<!-- Событие 2 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Первые успехи</h3>
							<span class="academy-event-card__year">2012</span>
						</div>
						<p class="academy-event-card__description">Команда U-13 заняла 3 место в областном чемпионате.</p>
					</div>
				</div>

				<!-- Событие 3 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Расширение базы</h3>
							<span class="academy-event-card__year">2015</span>
						</div>
						<p class="academy-event-card__description">Открытие новой тренировочной базы с двумя полноразмерными футбольными полями.</p>
					</div>
				</div>

				<!-- Событие 4 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Первый воспитанник в основе</h3>
							<span class="academy-event-card__year">2017</span>
						</div>
						<p class="academy-event-card__description">Дмитрий Кравченко дебютировал в основном составе ФК Арсенал.</p>
					</div>
				</div>

				<!-- Событие 5 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Золото областного первенства</h3>
							<span class="academy-event-card__year">2019</span>
						</div>
						<p class="academy-event-card__description">Команда U-15 стала победителем областного чемпионата.</p>
					</div>
				</div>

				<!-- Событие 6 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Лучшая СДЮШ региона</h3>
							<span class="academy-event-card__year">2021</span>
						</div>
						<p class="academy-event-card__description">Школа признана лучшей в Минской области по результатам проверки ФФБ.</p>
					</div>
				</div>

				<!-- Событие 7 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">5 воспитанников в основе</h3>
							<span class="academy-event-card__year">2023</span>
						</div>
						<p class="academy-event-card__description">Рекордное количество воспитанников выступают за основную команду в Высшей лиге.</p>
					</div>
				</div>

				<!-- Событие 8 -->
				<div class="academy-event-card">
					<div class="academy-event-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="currentColor"/>
							<path d="M12.5 6H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="currentColor"/>
						</svg>
					</div>
					<div class="academy-event-card__content">
						<div class="academy-event-card__header">
							<h3 class="academy-event-card__title">Современность</h3>
							<span class="academy-event-card__year">2025</span>
						</div>
						<p class="academy-event-card__description">В академии тренируется более 150 детей. Работают 8 тренеров с лицензиями UEFA.</p>
					</div>
				</div>
			</div>
		</section>

		<!-- Тренерский штаб -->
		<section class="academy-staff-section">
			<h2 class="academy-staff-section__title">Тренерский штаб</h2>
			
			<div class="academy-coaches-grid">
				<div class="academy-coach-card">
					<h3 class="academy-coach-card__name">Петр Иванович Кузнецов</h3>
					<p class="academy-coach-card__position">Директор СДЮШ</p>
					<p class="academy-coach-card__since">С 2010 года</p>
				</div>

				<div class="academy-coach-card">
					<h3 class="academy-coach-card__name">Алексей Владимирович Морозов</h3>
					<p class="academy-coach-card__position">Тренер U-17</p>
					<p class="academy-coach-card__since">С 2015 года</p>
				</div>

				<div class="academy-coach-card">
					<h3 class="academy-coach-card__name">Игорь Петрович Семенов</h3>
					<p class="academy-coach-card__position">Тренер U-15</p>
					<p class="academy-coach-card__since">С 2018 года</p>
				</div>

				<div class="academy-coach-card">
					<h3 class="academy-coach-card__name">Дмитрий Александрович Федоров</h3>
					<p class="academy-coach-card__position">Тренер U-13</p>
					<p class="academy-coach-card__since">С 2020 года</p>
				</div>

				<div class="academy-coach-card">
					<h3 class="academy-coach-card__name">Сергей Иванович Козлов</h3>
					<p class="academy-coach-card__position">Тренер U-11</p>
					<p class="academy-coach-card__since">С 2021 года</p>
				</div>

				<div class="academy-coach-card">
					<h3 class="academy-coach-card__name">Максим Николаевич Волков</h3>
					<p class="academy-coach-card__position">Тренер U-9</p>
					<p class="academy-coach-card__since">С 2023 года</p>
				</div>
			</div>
		</section>

		<!-- Тренировочная база -->
		<section class="academy-facilities-section">
			<h2 class="academy-facilities-section__title">Тренировочная база</h2>
			
			<div class="academy-facilities-grid">
				<div class="academy-facility-item">
					<div class="academy-facility-item__icon">
						<svg width="28" height="28" viewBox="0 0 28 28" fill="none">
							<path d="M14 2C7.373 2 2 7.373 2 14s5.373 12 12 12 12-5.373 12-12S20.627 2 14 2z" stroke="currentColor" stroke-width="2" fill="none"/>
							<path d="M14 7v7h7" stroke="currentColor" stroke-width="2" fill="none"/>
						</svg>
					</div>
					<div class="academy-facility-item__content">
						<h3 class="academy-facility-item__heading">Спортивный комплекс "Арсенал"</h3>
						<ul class="academy-facility-item__list">
							<li>• 2 полноразмерных поля (105x68 м)</li>
							<li>• Крытый манеж с искусственным покрытием</li>
							<li>• Тренажерный зал</li>
							<li>• Раздевалки и душевые</li>
						</ul>
					</div>
				</div>

				<div class="academy-facility-item">
					<div class="academy-facility-item__icon">
						<svg width="28" height="28" viewBox="0 0 28 28" fill="none">
							<path d="M14 2C7.373 2 2 7.373 2 14s5.373 12 12 12 12-5.373 12-12S20.627 2 14 2z" stroke="currentColor" stroke-width="2" fill="none"/>
							<path d="M14 8v8m-4-4h8" stroke="currentColor" stroke-width="2" fill="none"/>
						</svg>
					</div>
					<div class="academy-facility-item__content">
						<h3 class="academy-facility-item__heading">Инфраструктура</h3>
						<ul class="academy-facility-item__list">
							<li>• Медицинский кабинет</li>
							<li>• Аудиторные классы</li>
							<li>• Столовая на 80 мест</li>
							<li>• Парковка для родителей</li>
						</ul>
					</div>
				</div>
			</div>
		</section>

		<!-- Запись в академию -->
		<section class="academy-enrollment-section">
			<h2 class="academy-enrollment-section__title">Запись в академию</h2>
			<p class="academy-enrollment-section__description">Мы приглашаем детей от 8 до 17 лет на занятия в нашей футбольной академии. Тренировки проводятся профессиональными тренерами с лицензиями UEFA.</p>
			
			<div class="academy-enrollment-info">
				<div class="academy-enrollment-column">
					<div class="academy-enrollment-item">
						<span class="academy-enrollment-item__emoji">📍</span>
						<div class="academy-enrollment-item__content">
							<strong>Адрес:</strong> ул. Спортивная, 2, г. Дзержинск
						</div>
					</div>
					<div class="academy-enrollment-item">
						<span class="academy-enrollment-item__emoji">📞</span>
						<div class="academy-enrollment-item__content">
							<strong>Телефон:</strong> +375 (17) 123-45-70
						</div>
					</div>
				</div>

				<div class="academy-enrollment-column">
					<div class="academy-enrollment-item">
						<span class="academy-enrollment-item__emoji">📧</span>
						<div class="academy-enrollment-item__content">
							<strong>Email:</strong> academy@arsenal-dzr.by
						</div>
					</div>
					<div class="academy-enrollment-item">
						<span class="academy-enrollment-item__emoji">⏰</span>
						<div class="academy-enrollment-item__content">
							<strong>Просмотры:</strong> каждую субботу в 10:00
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
