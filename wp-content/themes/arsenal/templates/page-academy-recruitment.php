<?php
/**
 * Template Name: Набор в академию
 * Template Post Type: page
 * Description: Страница набора детей в академию СДЮШ
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();
?>

<main id="main" class="site-main page-academy-recruitment">
	<div class="container">
		<!-- Hero Section with CTA -->
		<section class="academy-hero" aria-labelledby="academy-title">
			<div class="hero-content">
				<h1 id="academy-title" class="hero-title"><?php esc_html_e( 'Набор в академию', 'arsenal' ); ?></h1>
				<p class="hero-description">
					<?php 
					esc_html_e( 'СДЮШ "Арсенал" объявляет набор детей в возрасте от 8 до 17 лет. Открой для себя мир профессионального футбола!', 'arsenal' ); 
					?>
				</p>
				<div class="hero-actions">
					<button class="btn btn-primary btn-lg" data-action="apply">
						<?php esc_html_e( 'Подать заявку', 'arsenal' ); ?>
					</button>
					<a href="#contacts" class="btn btn-secondary btn-lg">
						<?php esc_html_e( 'Контакты', 'arsenal' ); ?>
					</a>
				</div>
			</div>
		</section>

		<!-- Why Us Section -->
		<section class="academy-why-us" aria-labelledby="why-us-title">
			<h2 id="why-us-title" class="section-title"><?php esc_html_e( 'Почему мы?', 'arsenal' ); ?></h2>
			
			<div class="benefits-grid">
				<!-- Professional Coaches -->
				<article class="benefit-card">
					<div class="benefit-icon benefit-icon-coaches">
						<svg class="icon-24" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
						</svg>
					</div>
					<h3 class="benefit-title"><?php esc_html_e( 'Профессиональные тренеры', 'arsenal' ); ?></h3>
					<p class="benefit-description">
						<?php esc_html_e( 'Все наши тренеры имеют лицензии UEFA и многолетний опыт работы с детьми', 'arsenal' ); ?>
					</p>
				</article>

				<!-- Modern Facilities -->
				<article class="benefit-card">
					<div class="benefit-icon benefit-icon-facilities">
						<svg class="icon-24" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
						</svg>
					</div>
					<h3 class="benefit-title"><?php esc_html_e( 'Современная база', 'arsenal' ); ?></h3>
					<p class="benefit-description">
						<?php esc_html_e( 'Два полноразмерных поля, крытый манеж, тренажерный зал и медицинский кабинет', 'arsenal' ); ?>
					</p>
				</article>

				<!-- Safety -->
				<article class="benefit-card">
					<div class="benefit-icon benefit-icon-safety">
						<svg class="icon-24" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
						</svg>
					</div>
					<h3 class="benefit-title"><?php esc_html_e( 'Безопасность', 'arsenal' ); ?></h3>
					<p class="benefit-description">
						<?php esc_html_e( 'Полная медицинская страховка, контроль здоровья и профессиональное оборудование', 'arsenal' ); ?>
					</p>
				</article>

				<!-- Path to Pro -->
				<article class="benefit-card">
					<div class="benefit-icon benefit-icon-pro">
						<svg class="icon-24" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
						</svg>
					</div>
					<h3 class="benefit-title"><?php esc_html_e( 'Путь в профи', 'arsenal' ); ?></h3>
					<p class="benefit-description">
						<?php esc_html_e( '12 выпускников нашей академии уже играют за основную команду в Высшей лиге', 'arsenal' ); ?>
					</p>
				</article>
			</div>
		</section>

		<!-- Age Groups Section -->
		<section class="academy-age-groups" aria-labelledby="age-groups-title">
			<h2 id="age-groups-title" class="section-title"><?php esc_html_e( 'Возрастные группы', 'arsenal' ); ?></h2>
			
			<div class="age-groups-grid">
				<!-- U-9 -->
				<div class="age-group-card">
					<div class="age-group-header">
						<h3 class="age-group-name">U-9</h3>
						<span class="age-group-spots">15 мест</span>
					</div>
					<ul class="age-group-details">
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( '8-9 лет', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Год рождения: 2016-2017', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Пн, Ср, Пт: 16:00-17:30', 'arsenal' ); ?></span>
						</li>
					</ul>
				</div>

				<!-- U-11 -->
				<div class="age-group-card">
					<div class="age-group-header">
						<h3 class="age-group-name">U-11</h3>
						<span class="age-group-spots">12 мест</span>
					</div>
					<ul class="age-group-details">
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( '10-11 лет', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Год рождения: 2014-2015', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Вт, Чт, Сб: 16:00-17:30', 'arsenal' ); ?></span>
						</li>
					</ul>
				</div>

				<!-- U-13 -->
				<div class="age-group-card">
					<div class="age-group-header">
						<h3 class="age-group-name">U-13</h3>
						<span class="age-group-spots age-group-spots-warning">8 мест</span>
					</div>
					<ul class="age-group-details">
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( '12-13 лет', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Год рождения: 2012-2013', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Пн, Ср, Пт: 17:30-19:00', 'arsenal' ); ?></span>
						</li>
					</ul>
				</div>

				<!-- U-15 -->
				<div class="age-group-card">
					<div class="age-group-header">
						<h3 class="age-group-name">U-15</h3>
						<span class="age-group-spots age-group-spots-danger">5 мест</span>
					</div>
					<ul class="age-group-details">
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( '14-15 лет', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Год рождения: 2010-2011', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Вт, Чт, Сб: 17:30-19:00', 'arsenal' ); ?></span>
						</li>
					</ul>
				</div>

				<!-- U-17 -->
				<div class="age-group-card">
					<div class="age-group-header">
						<h3 class="age-group-name">U-17</h3>
						<span class="age-group-spots age-group-spots-danger">3 мест</span>
					</div>
					<ul class="age-group-details">
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( '16-17 лет', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Год рождения: 2008-2009', 'arsenal' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
							</svg>
							<span><?php esc_html_e( 'Пн, Ср, Пт: 19:00-20:30', 'arsenal' ); ?></span>
						</li>
					</ul>
				</div>
			</div>
		</section>

		<!-- Required Documents Section -->
		<section class="academy-documents" aria-labelledby="documents-title">
			<h2 id="documents-title" class="section-title"><?php esc_html_e( 'Необходимые документы', 'arsenal' ); ?></h2>
			
			<div class="documents-grid">
				<div class="document-item">
					<svg class="icon-20 document-icon" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
					</svg>
					<span><?php esc_html_e( 'Медицинская справка о допуске к занятиям спортом', 'arsenal' ); ?></span>
				</div>

				<div class="document-item">
					<svg class="icon-20 document-icon" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
					</svg>
					<span><?php esc_html_e( 'Свидетельство о рождении (копия)', 'arsenal' ); ?></span>
				</div>

				<div class="document-item">
					<svg class="icon-20 document-icon" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
					</svg>
					<span><?php esc_html_e( 'Паспорт одного из родителей (копия)', 'arsenal' ); ?></span>
				</div>

				<div class="document-item">
					<svg class="icon-20 document-icon" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
					</svg>
					<span><?php esc_html_e( 'Фотография 3x4 (2 шт.)', 'arsenal' ); ?></span>
				</div>

				<div class="document-item">
					<svg class="icon-20 document-icon" viewBox="0 0 24 24" aria-hidden="true">
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
					</svg>
					<span><?php esc_html_e( 'Спортивная форма и обувь', 'arsenal' ); ?></span>
				</div>
			</div>

			<div class="documents-notice">
				<strong><?php esc_html_e( 'Важно:', 'arsenal' ); ?></strong>
				<?php esc_html_e( 'Медицинская справка должна быть получена не ранее чем за 1 месяц до начала занятий. Все копии документов должны быть заверены.', 'arsenal' ); ?>
			</div>
		</section>

		<!-- Tryout Schedule Section -->
		<section class="academy-schedule" aria-labelledby="schedule-title">
			<h2 id="schedule-title" class="section-title"><?php esc_html_e( 'Расписание просмотров', 'arsenal' ); ?></h2>
			
			<div class="schedule-grid">
				<div class="schedule-item">
					<div class="schedule-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/>
						</svg>
					</div>
					<h3 class="schedule-heading"><?php esc_html_e( 'Каждую субботу', 'arsenal' ); ?></h3>
					<p class="schedule-text">10:00 - 12:00</p>
				</div>

				<div class="schedule-item">
					<div class="schedule-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
						</svg>
					</div>
					<h3 class="schedule-heading"><?php esc_html_e( 'Стадион', 'arsenal' ); ?></h3>
					<p class="schedule-text"><?php esc_html_e( 'ул. Спортивная, 2', 'arsenal' ); ?></p>
				</div>

				<div class="schedule-item">
					<div class="schedule-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
						</svg>
					</div>
					<h3 class="schedule-heading"><?php esc_html_e( 'Запись', 'arsenal' ); ?></h3>
					<p class="schedule-text">+375 (17) 123-45-70</p>
				</div>
			</div>

			<div class="schedule-notice">
				<strong><?php esc_html_e( 'Предварительная запись обязательна!', 'arsenal' ); ?></strong>
				<?php esc_html_e( 'Позвоните или напишите нам заранее.', 'arsenal' ); ?>
			</div>
		</section>

		<!-- Contacts Section -->
		<section id="contacts" class="academy-contacts" aria-labelledby="contacts-title">
			<h2 id="contacts-title" class="section-title"><?php esc_html_e( 'Контакты', 'arsenal' ); ?></h2>
			
			<div class="contacts-grid">
				<!-- Admin Info -->
				<div class="contacts-column">
					<h3 class="contacts-subtitle"><?php esc_html_e( 'Администрация СДЮШ', 'arsenal' ); ?></h3>
					
					<div class="contact-item">
						<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
						</svg>
						<div class="contact-info">
							<p class="contact-label"><?php esc_html_e( 'Адрес', 'arsenal' ); ?></p>
							<p class="contact-value"><?php esc_html_e( 'ул. Спортивная, 2, г. Дзержинск, Минская обл., 222720', 'arsenal' ); ?></p>
						</div>
					</div>

					<div class="contact-item">
						<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
						</svg>
						<div class="contact-info">
							<p class="contact-label"><?php esc_html_e( 'Телефон', 'arsenal' ); ?></p>
							<p class="contact-value">+375 (17) 123-45-70</p>
							<p class="contact-hours"><?php esc_html_e( 'Пн-Пт: 9:00-18:00, Сб: 9:00-14:00', 'arsenal' ); ?></p>
						</div>
					</div>

					<div class="contact-item">
						<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
							<path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
						</svg>
						<div class="contact-info">
							<p class="contact-label"><?php esc_html_e( 'Email', 'arsenal' ); ?></p>
							<p class="contact-value">academy@arsenal-dzr.by</p>
						</div>
					</div>

					<!-- Director Info -->
					<div class="director-card">
						<h4 class="director-title"><?php esc_html_e( 'Директор СДЮШ', 'arsenal' ); ?></h4>
						<p class="director-name"><?php esc_html_e( 'Петр Иванович Кузнецов', 'arsenal' ); ?></p>
						<p class="director-role"><?php esc_html_e( 'Директор с 2010 года', 'arsenal' ); ?></p>
						<p class="director-contact">📞 +375 (17) 123-45-71</p>
						<p class="director-contact">📧 kuznetsov@arsenal-dzr.by</p>
					</div>
				</div>

				<!-- Location Info -->
				<div class="contacts-column">
					<h3 class="contacts-subtitle"><?php esc_html_e( 'Как нас найти', 'arsenal' ); ?></h3>
					
					<div class="location-map">
						<div class="map-placeholder">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-13c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5z"/>
							</svg>
							<p class="map-title"><?php esc_html_e( 'Интерактивная карта', 'arsenal' ); ?></p>
							<p class="map-location"><?php esc_html_e( 'Спортивный комплекс "Арсенал"', 'arsenal' ); ?></p>
							<p class="map-distance"><?php esc_html_e( '15 минут от центра города', 'arsenal' ); ?></p>
						</div>
					</div>

					<div class="transport-info">
						<p><strong>🚗 <?php esc_html_e( 'На автомобиле:', 'arsenal' ); ?></strong> <?php esc_html_e( 'Бесплатная парковка на территории', 'arsenal' ); ?></p>
						<p><strong>🚌 <?php esc_html_e( 'На общественном транспорте:', 'arsenal' ); ?></strong> <?php esc_html_e( 'Автобусы №12, 34, 56 (остановка "Спортивная")', 'arsenal' ); ?></p>
					</div>

					<!-- Social Media -->
					<div class="social-section">
						<h4 class="social-title"><?php esc_html_e( 'Мы в социальных сетях', 'arsenal' ); ?></h4>
						<div class="social-links">
							<a href="#" class="social-link">📱 Instagram</a>
							<a href="#" class="social-link">📘 Facebook</a>
							<a href="#" class="social-link">📺 YouTube</a>
							<a href="#" class="social-link">💬 Telegram</a>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- FAQ Section -->
		<section class="academy-faq" aria-labelledby="faq-title">
			<h2 id="faq-title" class="section-title"><?php esc_html_e( 'Частые вопросы', 'arsenal' ); ?></h2>
			
			<div class="faq-list">
				<details class="faq-item">
					<summary class="faq-question">
						<?php esc_html_e( 'Сколько стоят занятия?', 'arsenal' ); ?>
					</summary>
					<p class="faq-answer">
						<?php esc_html_e( 'Обучение в СДЮШ "Арсенал" бесплатное. Все занятия финансируются клубом. Родителям необходимо приобрести только спортивную форму и обувь.', 'arsenal' ); ?>
					</p>
				</details>

				<details class="faq-item">
					<summary class="faq-question">
						<?php esc_html_e( 'Нужен ли опыт игры в футбол?', 'arsenal' ); ?>
					</summary>
					<p class="faq-answer">
						<?php esc_html_e( 'Нет, опыт не требуется. Мы принимаем детей с любым уровнем подготовки. Главное — желание заниматься футболом и физическое здоровье.', 'arsenal' ); ?>
					</p>
				</details>

				<details class="faq-item">
					<summary class="faq-question">
						<?php esc_html_e( 'Как проходит отбор?', 'arsenal' ); ?>
					</summary>
					<p class="faq-answer">
						<?php esc_html_e( 'Отбор проходит в форме просмотра на тренировочной базе. Тренеры оценивают физические данные, координацию, скорость и технику владения мячом. Решение принимается в течение недели после просмотра.', 'arsenal' ); ?>
					</p>
				</details>

				<details class="faq-item">
					<summary class="faq-question">
						<?php esc_html_e( 'Можно ли совмещать занятия с учебой?', 'arsenal' ); ?>
					</summary>
					<p class="faq-answer">
						<?php esc_html_e( 'Да, расписание составлено с учетом школьных занятий. Тренировки проходят во второй половине дня. Мы также помогаем с индивидуальным графиком при необходимости.', 'arsenal' ); ?>
					</p>
				</details>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
?>
