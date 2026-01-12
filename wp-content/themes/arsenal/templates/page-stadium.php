<?php
/**
 * Template Name: Стадион
 * Template Post Type: page
 *
 * @package Arsenal
 */

get_header(); ?>

<main id="primary" class="site-main">
	<!-- Hero Banner with Stadium Image -->
	<div class="stadium-hero">
		<div class="stadium-hero__image-wrapper">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'full', array(
					'class' => 'stadium-hero__image',
					'alt'   => get_the_title(),
				) );
			} else {
				echo '<div class="stadium-hero__image-placeholder" aria-hidden="true"></div>';
			}
			?>
			<div class="stadium-hero__overlay"></div>
		</div>

		<div class="stadium-hero__content">
			<div class="container">
				<h1 class="stadium-hero__title"><?php echo esc_html( get_the_title() ); ?></h1>
				<div class="stadium-hero__meta">
					<div class="stadium-hero__meta-item">
						<svg class="stadium-hero__meta-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
							<path d="M10 2C5.58 2 2 5.58 2 10c0 5.74 7.05 8.5 8 8.5s8-2.76 8-8.5c0-4.42-3.58-8-8-8zm0 12c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
						</svg>
						<span><?php esc_html_e( 'Борисов, Беларусь', 'arsenal' ); ?></span>
					</div>
					<div class="stadium-hero__meta-item">
						<svg class="stadium-hero__meta-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
							<path d="M10 2a8 8 0 100 16 8 8 0 000-16zm.5 11H9V7h1.5v6z"/>
						</svg>
						<span><?php esc_html_e( 'Вместимость: 5 402', 'arsenal' ); ?></span>
					</div>
					<div class="stadium-hero__meta-item">
						<svg class="stadium-hero__meta-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
							<path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm0 6a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2v-4a2 2 0 00-2-2H4z"/>
						</svg>
						<span><?php esc_html_e( 'Открыт: 1959 г.', 'arsenal' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<!-- Main Content Section -->
		<div class="stadium-grid">
			<div class="stadium-main">
				<!-- Stats Cards -->
				<div class="stadium-stats">
					<div class="stat-card">
						<div class="stat-card__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
							</svg>
						</div>
						<div class="stat-card__number">5 402</div>
						<div class="stat-card__label"><?php esc_html_e( 'Вместимость', 'arsenal' ); ?></div>
					</div>

					<div class="stat-card">
						<div class="stat-card__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
							</svg>
						</div>
						<div class="stat-card__number">1959</div>
						<div class="stat-card__label"><?php esc_html_e( 'Год постройки', 'arsenal' ); ?></div>
					</div>

					<div class="stat-card">
						<div class="stat-card__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
							</svg>
						</div>
						<div class="stat-card__number">2013</div>
						<div class="stat-card__label"><?php esc_html_e( 'Реконструкция', 'arsenal' ); ?></div>
					</div>

					<div class="stat-card">
						<div class="stat-card__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
							</svg>
						</div>
						<div class="stat-card__number">105x68</div>
						<div class="stat-card__label"><?php esc_html_e( 'Размеры поля', 'arsenal' ); ?></div>
					</div>
				</div>

				<!-- About Stadium -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'О стадионе', 'arsenal' ); ?></h2>
					<div class="stadium-text">
						<p><?php esc_html_e( 'Стадион был построен в 1959 году и первоначально имел всего одну трибуну вместимостью 2600 мест. На нём играли городские команды, в их числе «Фомальгаут», в 1995 году выступавший во Второй лиге (втором по силе дивизионе первенства Беларуси).', 'arsenal' ); ?></p>
						<p><?php esc_html_e( 'Масштабная реконструкция стадиона была проведена в 1998—2001 годах, после того, как ФК БАТЭ получил право участвовать в Высшей лиге чемпионата страны. Вместимость единственной трибуны была увеличена до 3 100 зрителей, полностью перестелили газон. Тогда же было заложено тренировочное поле, установлена компьютерная система полива «Rain-Bird», реконструироованы подъездные пути, инженерные сети, оборудованы пресс-центр, ложа почетных гостей и комментаторские кабины.', 'arsenal' ); ?></p>
						<p><?php esc_html_e( 'В 2002 году была построена восточная трибуна, вместимость стадиона увеличилась до 5402 мест. На обеих трибунах были установлены индивидуальные пластиковые кресла. Был уложен новый, качественный газон, поле оборудовали системой полива. Через два года установили большое электронное информационное табло, ещё через год — 4 мачты искусственного освещения. В 2008 году над восточной трибуной появился козырёк из стали, алюминия и поликарбоната. ', 'arsenal' ); ?></p>
						<p><?php esc_html_e( 'В июне 1998 года борисовский городской стадион был принят европейской комиссией УЕФА и получил право на проведение международных матчей. ФК БАТЭ проводил на стадионе матчи квалификационных раундов еврокубков. Также на стадионе проводит свои домашние матчи молодёжная сборная Беларуси по футболу. ', 'arsenal' ); ?></p>
						<p><?php esc_html_e( 'В начале мая 2013 года на стадионе была смонтирована система подогрева газона.', 'arsenal' ); ?></p>
					</div>
				</section>

				<!-- Technical Characteristics -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'Технические характеристики', 'arsenal' ); ?></h2>
					<div class="stadium-specs">
						<div class="spec-item">
							<div class="spec-item__label"><?php esc_html_e( 'Покрытие', 'arsenal' ); ?></div>
							<div class="spec-item__value"><?php esc_html_e( 'Натуральный газон', 'arsenal' ); ?></div>
						</div>
						<div class="spec-item">
							<div class="spec-item__label"><?php esc_html_e( 'Освещение', 'arsenal' ); ?></div>
							<div class="spec-item__value"><?php esc_html_e( 'Есть (1200 люкс)', 'arsenal' ); ?></div>
						</div>
						<div class="spec-item">
							<div class="spec-item__label"><?php esc_html_e( 'Размеры поля', 'arsenal' ); ?></div>
							<div class="spec-item__value">105 x 68 м</div>
						</div>
						<div class="spec-item">
							<div class="spec-item__label"><?php esc_html_e( 'VIP-места', 'arsenal' ); ?></div>
							<div class="spec-item__value">50 <?php esc_html_e( 'мест', 'arsenal' ); ?></div>
						</div>
					</div>
				</section>

				<!-- Stadium Sectors -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'Секторы стадиона', 'arsenal' ); ?></h2>
					<div class="stadium-sectors">
						<div class="sector-item">
							<div class="sector-item__info">
								<div class="sector-item__name"><?php esc_html_e( 'Главная трибуна', 'arsenal' ); ?></div>
								<div class="sector-item__type"><?php esc_html_e( 'Сидячие места', 'arsenal' ); ?></div>
							</div>
							<div class="sector-item__count">1 800 <?php esc_html_e( 'мест', 'arsenal' ); ?></div>
						</div>

						<div class="sector-item">
							<div class="sector-item__info">
								<div class="sector-item__name"><?php esc_html_e( 'Восточная трибуна', 'arsenal' ); ?></div>
								<div class="sector-item__type"><?php esc_html_e( 'Сидячие места', 'arsenal' ); ?></div>
							</div>
							<div class="sector-item__count">1 200 <?php esc_html_e( 'мест', 'arsenal' ); ?></div>
						</div>

						<div class="sector-item">
							<div class="sector-item__info">
								<div class="sector-item__name"><?php esc_html_e( 'Западная трибуна', 'arsenal' ); ?></div>
								<div class="sector-item__type"><?php esc_html_e( 'Стоячие места', 'arsenal' ); ?></div>
							</div>
							<div class="sector-item__count">800 <?php esc_html_e( 'мест', 'arsenal' ); ?></div>
						</div>

						<div class="sector-item">
							<div class="sector-item__info">
								<div class="sector-item__name"><?php esc_html_e( 'Южная трибуна (гостевая)', 'arsenal' ); ?></div>
								<div class="sector-item__type"><?php esc_html_e( 'Стоячие места', 'arsenal' ); ?></div>
							</div>
							<div class="sector-item__count">400 <?php esc_html_e( 'мест', 'arsenal' ); ?></div>
						</div>
					</div>
				</section>

				<!-- Stadium History -->
				<section class="stadium-section">
					<h2><?php esc_html_e( 'История стадиона', 'arsenal' ); ?></h2>
					<div class="stadium-timeline">
						<div class="timeline-item">
							<div class="timeline-item__dot"></div>
							<div class="timeline-item__content">
								<div class="timeline-item__year">1958</div>
								<div class="timeline-item__title"><?php esc_html_e( 'Открытие стадиона', 'arsenal' ); ?></div>
								<div class="timeline-item__text"><?php esc_html_e( 'Стадион "Строитель" был построен и открыт для проведения футбольных матчей и легкоатлетических соревнований.', 'arsenal' ); ?></div>
							</div>
						</div>

						<div class="timeline-item">
							<div class="timeline-item__dot"></div>
							<div class="timeline-item__content">
								<div class="timeline-item__year">1995</div>
								<div class="timeline-item__title"><?php esc_html_e( 'Первая реконструкция', 'arsenal' ); ?></div>
								<div class="timeline-item__text"><?php esc_html_e( 'Проведена модернизация трибун и беговых дорожек. Установлены новые прожекторы.', 'arsenal' ); ?></div>
							</div>
						</div>

						<div class="timeline-item">
							<div class="timeline-item__dot"></div>
							<div class="timeline-item__content">
								<div class="timeline-item__year">2015</div>
								<div class="timeline-item__title"><?php esc_html_e( 'Масштабная реконструкция', 'arsenal' ); ?></div>
								<div class="timeline-item__text"><?php esc_html_e( 'Полная реконструкция стадиона: замена покрытия поля, установка современных трибун, раздевалок и административных помещений.', 'arsenal' ); ?></div>
							</div>
						</div>

						<div class="timeline-item timeline-item--last">
							<div class="timeline-item__dot"></div>
							<div class="timeline-item__content">
								<div class="timeline-item__year">2020</div>
								<div class="timeline-item__title"><?php esc_html_e( 'Модернизация освещения', 'arsenal' ); ?></div>
								<div class="timeline-item__text"><?php esc_html_e( 'Установлена современная система освещения, соответствующая стандартам УЕФА для проведения вечерних матчей.', 'arsenal' ); ?></div>
							</div>
						</div>
					</div>
				</section>

				<!-- How to Get There -->
				<section class="stadium-section">
					<h2 class="stadium-section__heading-with-icon">
						<svg class="stadium-section__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
						</svg>
						<?php esc_html_e( 'Как добраться', 'arsenal' ); ?>
					</h2>
					<div class="stadium-directions">
						<div class="direction-card">
							<div class="direction-card__header">
								<svg class="direction-card__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
								</svg>
								<h3><?php esc_html_e( 'Автобус', 'arsenal' ); ?></h3>
							</div>
							<div class="direction-card__info">
								<div class="direction-card__item">
									<span class="direction-card__label"><?php esc_html_e( 'Маршрут:', 'arsenal' ); ?></span>
									<span class="direction-card__value">№5, №12, №18</span>
								</div>
								<div class="direction-card__item">
									<span class="direction-card__label"><?php esc_html_e( 'Остановка:', 'arsenal' ); ?></span>
									<span class="direction-card__value"><?php esc_html_e( 'Остановка "Стадион Строитель"', 'arsenal' ); ?></span>
								</div>
								<div class="direction-card__highlight">
									<svg class="direction-card__highlight-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
										<path d="M8 1a7 7 0 100 14A7 7 0 008 1z"/>
									</svg>
									<span><?php esc_html_e( '15 минут от центра города', 'arsenal' ); ?></span>
								</div>
							</div>
						</div>

						<div class="direction-card">
							<div class="direction-card__header">
								<svg class="direction-card__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
								</svg>
								<h3><?php esc_html_e( 'Автомобиль', 'arsenal' ); ?></h3>
							</div>
							<div class="direction-card__info">
								<div class="direction-card__item">
									<span class="direction-card__label"><?php esc_html_e( 'Маршрут:', 'arsenal' ); ?></span>
									<span class="direction-card__value">М1 (E30) → Дзержинск → ул. Спортивная</span>
								</div>
								<div class="direction-card__item">
									<span class="direction-card__label"><?php esc_html_e( 'Парковка:', 'arsenal' ); ?></span>
									<span class="direction-card__value"><?php esc_html_e( 'Бесплатная парковка на территории', 'arsenal' ); ?></span>
								</div>
								<div class="direction-card__highlight">
									<svg class="direction-card__highlight-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
										<path d="M8 1a7 7 0 100 14A7 7 0 008 1z"/>
									</svg>
									<span><?php esc_html_e( '30 минут от Минска', 'arsenal' ); ?></span>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>

			<!-- Sidebar -->
			<aside class="stadium-sidebar">
				<!-- Contacts -->
				<section class="stadium-card">
					<h3><?php esc_html_e( 'Контакты', 'arsenal' ); ?></h3>
					<div class="stadium-card__content">
						<div class="contact-item">
							<svg class="contact-item__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path d="M10 2C5.58 2 2 5.58 2 10c0 5.74 7.05 8.5 8 8.5s8-2.76 8-8.5c0-4.42-3.58-8-8-8z"/>
							</svg>
							<div class="contact-item__text"><?php esc_html_e( 'ул. Спортивная, 1, Дзержинск, Минская область, 222720', 'arsenal' ); ?></div>
						</div>
						<div class="contact-item">
							<svg class="contact-item__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.8c.164.983.637 2.83 1.39 4.467a1 1 0 01-.502 1.21l-.933.467c-.41.207-.667.72-.67 1.449a16.913 16.913 0 006.57 6.57c.729-.003 1.242.26 1.449.67l.467-.933a1 1 0 011.21-.502c1.636.753 3.484 1.226 4.467 1.39a1 1 0 01.8.986v2.153a1 1 0 01-1 1h-1C9.716 20 3 13.284 3 5V4a1 1 0 011-1h2z"/>
							</svg>
							<div class="contact-item__text">+375 (1716) 4-53-21</div>
						</div>
						<div class="contact-item">
							<svg class="contact-item__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 107.748-1.153A4.5 4.5 5.5 13z"/>
							</svg>
							<div class="contact-item__text">53.6833° N, 27.1333° E</div>
						</div>
						<button class="stadium-card__button" onclick="window.open('https://www.google.com/maps/search/53.6833,+27.1333')">
							<svg class="stadium-card__button-icon" viewBox="0 0 18 18" fill="currentColor" aria-hidden="true">
								<path d="M10 2C5.58 2 2 5.58 2 10c0 5.74 7.05 8.5 8 8.5s8-2.76 8-8.5c0-4.42-3.58-8-8-8z"/>
							</svg>
							<?php esc_html_e( 'Открыть на карте', 'arsenal' ); ?>
						</button>
					</div>
				</section>

				<!-- Infrastructure -->
				<section class="stadium-card">
					<h3><?php esc_html_e( 'Инфраструктура', 'arsenal' ); ?></h3>
					<div class="stadium-card__content">
						<div class="infrastructure-item">
							<div class="infrastructure-item__icon">
								<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
									<path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3z"/>
								</svg>
							</div>
							<div class="infrastructure-item__text">
								<div class="infrastructure-item__name"><?php esc_html_e( 'Парковка', 'arsenal' ); ?></div>
								<div class="infrastructure-item__desc">150 <?php esc_html_e( 'мест для автомобилей', 'arsenal' ); ?></div>
							</div>
						</div>

						<div class="infrastructure-item">
							<div class="infrastructure-item__icon">
								<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
									<path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3z"/>
								</svg>
							</div>
							<div class="infrastructure-item__text">
								<div class="infrastructure-item__name"><?php esc_html_e( 'VIP-ложа', 'arsenal' ); ?></div>
								<div class="infrastructure-item__desc"><?php esc_html_e( 'На 50 персон с отдельным входом', 'arsenal' ); ?></div>
							</div>
						</div>

						<div class="infrastructure-item">
							<div class="infrastructure-item__icon">
								<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
									<path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3z"/>
								</svg>
							</div>
							<div class="infrastructure-item__text">
								<div class="infrastructure-item__name"><?php esc_html_e( 'Тренажерный зал', 'arsenal' ); ?></div>
								<div class="infrastructure-item__desc"><?php esc_html_e( 'Современное оборудование', 'arsenal' ); ?></div>
							</div>
						</div>

						<div class="infrastructure-item">
							<div class="infrastructure-item__icon">
								<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
									<path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3z"/>
								</svg>
							</div>
							<div class="infrastructure-item__text">
								<div class="infrastructure-item__name"><?php esc_html_e( 'Медпункт', 'arsenal' ); ?></div>
								<div class="infrastructure-item__desc"><?php esc_html_e( 'Полностью оборудован', 'arsenal' ); ?></div>
							</div>
						</div>
					</div>
				</section>

				<!-- Match Day -->
				<section class="stadium-card stadium-card--match-day">
					<h3 class="stadium-card__title-with-icon">
						<svg class="stadium-card__title-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
							<path d="M5 3a2 2 0 00-2 2v6h4V5a2 2 0 00-2-2zm0 0h.01M9 3a2 2 0 00-2 2v6h4V5a2 2 0 00-2-2z"/>
						</svg>
						<?php esc_html_e( 'В день матча', 'arsenal' ); ?>
					</h3>
					<ul class="stadium-card__list">
						<li><?php esc_html_e( 'Ворота открываются за 1 час до начала', 'arsenal' ); ?></li>
						<li><?php esc_html_e( 'Запрещены пиротехнические средства', 'arsenal' ); ?></li>
						<li><?php esc_html_e( 'Буфеты работают с момента открытия', 'arsenal' ); ?></li>
						<li><?php esc_html_e( 'Парковка бесплатная для всех зрителей', 'arsenal' ); ?></li>
					</ul>
				</section>
			</aside>
		</div>
	</div>
</main>

<?php get_footer();
