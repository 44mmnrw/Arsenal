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

// Используем дефолтные данные ДЛЯ HERO (быстрая отрисовка)
$hero = array(
	'title' => 'Набор в академию',
	'description' => 'СДЮШ "Арсенал" объявляет набор детей в возрасте от 8 до 17 лет.',
	'buttons' => array(
		array( 'text' => 'Подать заявку' ),
		array( 'text' => 'Контакты' ),
	),
);

// Загружаем остальные данные из БД синхронно
require_once get_template_directory() . '/inc/class-academy-recruitment-manager.php';
$data = Arsenal_Academy_Recruitment_Manager::get_page_data( 1 );

if ( ! $data ) {
	$default = Arsenal_Academy_Recruitment_Manager::get_default_data();
	$data = $default;
}

// Распаковать остальные данные
$benefits = $data['benefits_data'] ?? array();
$age_groups = $data['age_groups_data'] ?? array();
$documents = $data['documents_data'] ?? array();
$schedule = $data['schedule_data'] ?? array();
$contacts = $data['contacts_data'] ?? array();
$directions = $data['directions_data'] ?? array();
$faq = $data['faq_data'] ?? array();
?>

<main id="main" class="site-main page-academy-recruitment">
	<div class="container">
		<!-- Hero Section with CTA -->
		<section class="academy-hero" aria-labelledby="academy-title">
			<div class="hero-content">
				<h1 id="academy-title" class="hero-title"><?php echo esc_html( $hero['title'] ?? 'Набор в академию' ); ?></h1>
				<p class="hero-description">
					<?php echo esc_html( $hero['description'] ?? 'СДЮШ "Арсенал" объявляет набор детей в возрасте от 8 до 17 лет.' ); ?>
				</p>
				<div class="hero-actions">
					<button class="btn btn-primary btn-lg" data-action="apply">
						<?php echo esc_html( ( $hero['buttons'][0]['text'] ?? 'Подать заявку' ) ); ?>
					</button>
					<a href="#contacts" class="btn btn-secondary btn-lg">
						<?php echo esc_html( ( $hero['buttons'][1]['text'] ?? 'Контакты' ) ); ?>
					</a>
				</div>
			</div>
		</section>

		<!-- Why Us Section -->
		<section class="academy-why-us" aria-labelledby="why-us-title">
			<h2 id="why-us-title" class="section-title"><?php esc_html_e( 'Почему мы?', 'arsenal' ); ?></h2>
			
			<div class="benefits-grid">
				<?php foreach ( $benefits as $benefit ) : ?>
				<!-- Benefit Card -->
				<article class="benefit-card">
					<div class="benefit-icon benefit-icon-<?php echo esc_attr( $benefit['icon'] ?? 'default' ); ?>">
						<svg class="icon-24" viewBox="0 0 24 24" aria-hidden="true">
							<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $benefit['icon'] ?? 'icon-place' ); ?>"></use>
						</svg>
					</div>
					<h3 class="benefit-title"><?php echo esc_html( $benefit['title'] ?? '' ); ?></h3>
					<p class="benefit-description">
						<?php echo esc_html( $benefit['description'] ?? '' ); ?>
					</p>
				</article>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Age Groups Section -->
		<section class="academy-age-groups" aria-labelledby="age-groups-title">
			<h2 id="age-groups-title" class="section-title"><?php esc_html_e( 'Возрастные группы', 'arsenal' ); ?></h2>
			
			<div class="age-groups-grid">
				<?php foreach ( $age_groups as $group ) : ?>
				<div class="age-group-card">
					<div class="age-group-header">
						<h3 class="age-group-name"><?php echo esc_html( $group['name'] ?? '' ); ?></h3>
						<span class="age-group-spots<?php echo ( isset( $group['spots_status'] ) && $group['spots_status'] !== 'normal' ) ? ' age-group-spots-' . esc_attr( $group['spots_status'] ) : ''; ?>">
							<?php echo esc_html( $group['spots_available'] ?? 0 ); ?> <?php esc_html_e( 'мест', 'arsenal' ); ?>
						</span>
					</div>
					<ul class="age-group-details">
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-checkbox"></use>
							</svg>
							<span><?php echo esc_html( $group['age_range'] ?? '' ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-checkbox"></use>
							</svg>
							<span><?php printf( esc_html_x( 'Год рождения: %s', 'academy', 'arsenal' ), esc_html( $group['birth_years'] ?? '' ) ); ?></span>
						</li>
						<li>
							<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
								<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-checkbox"></use>
							</svg>
							<span><?php echo esc_html( $group['schedule'] ?? '' ); ?></span>
						</li>
					</ul>
				</div>
				<?php endforeach; ?>
			</div>
		</section>

		<!-- Required Documents Section -->
		<section class="academy-documents" aria-labelledby="documents-title">
			<h2 id="documents-title" class="section-title"><?php esc_html_e( 'Необходимые документы', 'arsenal' ); ?></h2>
			
			<div class="documents-grid">
				<?php foreach ( ( $documents['items'] ?? array() ) as $doc ) : ?>
				<div class="document-item">
				<svg class="icon-20 document-icon" viewBox="0 0 24 24" aria-hidden="true">
					<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-report"></use>
				</svg>
					<span><?php echo esc_html( $doc['text'] ?? '' ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $documents['notice'] ) ) : ?>
			<div class="documents-notice">
				<strong><?php esc_html_e( 'Важно:', 'arsenal' ); ?></strong>
				<?php echo esc_html( $documents['notice'] ); ?>
			</div>
			<?php endif; ?>
		</section>

		<!-- Tryout Schedule Section -->
		<section class="academy-schedule" aria-labelledby="schedule-title">
			<h2 id="schedule-title" class="section-title"><?php esc_html_e( 'Расписание просмотров', 'arsenal' ); ?></h2>
			
			<div class="schedule-grid">
				<?php foreach ( ( $schedule['items'] ?? array() ) as $item ) : ?>
				<div class="schedule-item">
					<div class="schedule-icon">
					<svg viewBox="0 0 24 24" aria-hidden="true">
						<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $item['icon'] ?? 'icon-calendar' ); ?>"></use>
					</svg>
					</div>
					<h3 class="schedule-heading"><?php echo esc_html( $item['heading'] ?? '' ); ?></h3>
					<p class="schedule-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $schedule['notice'] ) ) : ?>
			<div class="schedule-notice">
				<strong><?php echo esc_html( $schedule['notice'] ); ?></strong>
			</div>
			<?php endif; ?>
		</section>

		<!-- Contacts Section -->
		<section id="contacts" class="academy-contacts" aria-labelledby="contacts-title">
			<h2 id="contacts-title" class="section-title"><?php esc_html_e( 'Контакты', 'arsenal' ); ?></h2>
			
			<!-- 2-Column Grid Layout -->
			<div class="contacts-2col">
				<!-- LEFT COLUMN: Administration & Director -->
				<div class="contacts-left-column">
					
					<!-- Administration Section -->
					<div class="contacts-section">
						<h3 class="contacts-section-title"><?php esc_html_e( 'Администрация СДЮШ', 'arsenal' ); ?></h3>
						
						<div class="contacts-items">
							<!-- Address -->
							<?php if ( ! empty( $contacts['address'] ) ) : ?>
							<div class="contact-item">
								<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
									<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-place"></use>
								</svg>
								<div class="contact-content">
									<p class="contact-label"><?php esc_html_e( 'Адрес', 'arsenal' ); ?></p>
									<p class="contact-value"><?php echo esc_html( $contacts['address'] ); ?></p>
								</div>
							</div>
							<?php endif; ?>
							
							<!-- Phone -->
							<?php if ( ! empty( $contacts['phone'] ) ) : ?>
							<div class="contact-item">
								<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
									<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-phone"></use>
								</svg>
								<div class="contact-content">
									<p class="contact-label"><?php esc_html_e( 'Телефон', 'arsenal' ); ?></p>
									<p class="contact-value">
										<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $contacts['phone'] ) ); ?>">
											<?php echo esc_html( $contacts['phone'] ); ?>
										</a>
									</p>
								</div>
							</div>
							<?php endif; ?>
							
							<!-- Schedule -->
							<?php if ( ! empty( $contacts['working_schedule'] ) && is_array( $contacts['working_schedule'] ) ) : ?>
							<div class="contact-item">
								<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
									<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-clock"></use>
								</svg>
								<div class="contact-content">
									<p class="contact-label"><?php esc_html_e( 'Время работы', 'arsenal' ); ?></p>
									<p class="contact-value">
										<?php 
										// Форматировать расписание в строку для вывода
										$schedule_str = '';
										foreach ( $contacts['working_schedule'] as $idx => $schedule ) {
											if ( $idx > 0 ) {
												$schedule_str .= ', ';
											}
											// Сокращение дней (Пн, Вт, etc)
											$day_short = substr( $schedule['day'], 0, 2 );
											$schedule_str .= $day_short . ': ' . esc_html( $schedule['time'] );
										}
										echo esc_html( $schedule_str );
										?>
									</p>
								</div>
							</div>
							<?php endif; ?>
							
							<!-- Email -->
							<?php if ( ! empty( $contacts['email'] ) ) : ?>
							<div class="contact-item">
								<svg class="icon-20" viewBox="0 0 24 24" aria-hidden="true">
									<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-email"></use>
								</svg>
								<div class="contact-content">
									<p class="contact-label"><?php esc_html_e( 'Email', 'arsenal' ); ?></p>
									<p class="contact-value">
										<a href="mailto:<?php echo esc_attr( $contacts['email'] ); ?>">
											<?php echo esc_html( $contacts['email'] ); ?>
										</a>
									</p>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Director Section -->
					<?php if ( ! empty( $contacts['director'] ) && is_array( $contacts['director'] ) ) : ?>
					<div class="contacts-section">
						<h3 class="contacts-section-title"><?php esc_html_e( 'Директор СДЮШ', 'arsenal' ); ?></h3>
						
						<div class="director-card">
							<p class="director-name"><?php echo esc_html( $contacts['director']['name'] ?? '' ); ?></p>
							<?php if ( ! empty( $contacts['director']['role'] ) ) : ?>
							<p class="director-role"><?php echo esc_html( $contacts['director']['role'] ); ?></p>
							<?php endif; ?>
							
							<?php if ( ! empty( $contacts['director']['contacts'] ) && is_array( $contacts['director']['contacts'] ) ) : ?>
								<?php 
								$phone = $contacts['director']['contacts'][0]['value'] ?? '';
								$email = $contacts['director']['contacts'][1]['value'] ?? '';
								?>
								<div class="director-contacts">
									<?php if ( ! empty( $phone ) ) : ?>
									<p class="director-contact">
										📞 <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>">
											<?php echo esc_html( $phone ); ?>
										</a>
									</p>
									<?php endif; ?>
									<?php if ( ! empty( $email ) ) : ?>
									<p class="director-contact">
										📧 <a href="mailto:<?php echo esc_attr( $email ); ?>">
											<?php echo esc_html( $email ); ?>
										</a>
									</p>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<!-- RIGHT COLUMN: Map & Directions -->
				<div class="contacts-right-column">
					
					<!-- How to Find Us -->
					<div class="contacts-section">
						<h3 class="contacts-section-title"><?php esc_html_e( 'Как нас найти', 'arsenal' ); ?></h3>
						
						<!-- Interactive Map Container -->
						<div id="academy-map-container" class="academy-map-wrapper">
							<div id="academy-map"></div>
							<div class="map-placeholder">
								<svg class="map-icon" viewBox="0 0 24 24" aria-hidden="true">
									<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#icon-place"></use>
								</svg>
								<p class="map-text"><?php esc_html_e( 'Интерактивная карта', 'arsenal' ); ?></p>
								<p class="map-subtext"><?php esc_html_e( 'Спортивный комплекс "Арсенал"', 'arsenal' ); ?><br><?php esc_html_e( '15 минут от центра города', 'arsenal' ); ?></p>
							</div>
						</div>

						<!-- Transport Info -->
						<div class="transport-directions">
						<?php if ( ! empty( $directions['items'] ) && is_array( $directions['items'] ) ) : ?>
							<?php foreach ( $directions['items'] as $item ) : ?>
							<p>
								<strong>
									<?php if ( ! empty( $item['icon'] ) ) : ?>
									<svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true" style="display: inline-block; vertical-align: middle; margin-right: 6px; width: 14px; height: 14px;">
										<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $item['icon'] ); ?>"></use>
									</svg>
									<?php endif; ?>
									<?php echo esc_html( $item['transport'] ); ?>
								</strong> 
								<?php echo esc_html( $item['route'] ?? '' ); ?>
								<?php if ( ! empty( $item['time'] ) ) : ?>
									<span style="color: #99a1af;"> (<?php echo esc_html( $item['time'] ); ?>)</span>
								<?php endif; ?>
							</p>
							<?php endforeach; ?>
						<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- FULL WIDTH: Social Media Section -->
			<?php if ( isset( $contacts['social'] ) && is_array( $contacts['social'] ) && count( $contacts['social'] ) > 0 ) : ?>
			<div class="contacts-social-section">
				<h3 class="contacts-section-title"><?php esc_html_e( 'Мы в социальных сетях', 'arsenal' ); ?></h3>
				
				<div class="social-links-grid">
					<?php foreach ( $contacts['social'] as $social ) : ?>
					<?php if ( ! empty( $social['url'] ) && ! empty( $social['icon'] ) ) : ?>
					<a href="<?php echo esc_url( $social['url'] ); ?>" class="social-button" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( ucfirst( str_replace( 'icon-', '', $social['icon'] ) ) ); ?>">
						<svg class="icon-24" viewBox="0 0 24 24" aria-hidden="true">
							<use xlink:href="<?php echo get_template_directory_uri(); ?>/assets/images/sprite.svg#<?php echo esc_attr( $social['icon'] ); ?>"></use>
						</svg>
					</a>
					<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</section>

		<!-- FAQ Section -->
		<section class="academy-faq" aria-labelledby="faq-title">
			<h2 id="faq-title" class="section-title"><?php esc_html_e( 'Частые вопросы', 'arsenal' ); ?></h2>
			
			<div class="faq-list">
				<?php foreach ( ( $faq ?? array() ) as $item ) : ?>
				<details class="faq-item">
					<summary class="faq-question">
						<?php echo esc_html( $item['question'] ?? '' ); ?>
					</summary>
					<p class="faq-answer">
						<?php echo esc_html( $item['answer'] ?? '' ); ?>
					</p>
				</details>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
</main>

<script>
function initAcademyMap() {
	// Инициализация Leaflet карты
	if ( typeof L !== 'undefined' && document.getElementById( 'academy-map' ) ) {
		// Координаты Дзержинска, Беларусь (центр города)
		var mapContainer = document.getElementById( 'academy-map' );
		var academyMap = L.map( 'academy-map', {
			attributionControl: false
		} ).setView( [53.6603, 27.5334], 14 );
		
		// Добавить слой карты (OpenStreetMap)
		L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			minZoom: 10
		} ).addTo( academyMap );
		
		// Добавить маркер с иконкой
		var marker = L.marker( [53.6603, 27.5334] ).addTo( academyMap );
		marker.bindPopup( '<strong>СДЮШ "Арсенал"</strong><br>г. Дзержинск' );
		
		// Открыть попап при загрузке
		marker.openPopup();
	}
}

// Проверяем когда загрузилась Leaflet библиотека
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', function() {
		// Если Leaflet уже загружена
		if ( typeof L !== 'undefined' ) {
			initAcademyMap();
		} else {
			// Ждем загрузки Leaflet
			var checkInterval = setInterval( function() {
				if ( typeof L !== 'undefined' ) {
					clearInterval( checkInterval );
					initAcademyMap();
				}
			}, 100 );
		}
	} );
} else {
	// DOM уже загружен
	if ( typeof L !== 'undefined' ) {
		initAcademyMap();
	} else {
		var checkInterval = setInterval( function() {
			if ( typeof L !== 'undefined' ) {
				clearInterval( checkInterval );
				initAcademyMap();
			}
		}, 100 );
	}
}
</script>

<script>
// Асинхронная загрузка данных для страницы
document.addEventListener( 'DOMContentLoaded', function() {
	fetch( '<?php echo esc_url( rest_url( 'arsenal/v1/academy-recruitment/1' ) ); ?>' )
		.then( response => response.json() )
		.then( data => {
			// Данные загружены
		} );
} );
</script>

<?php
get_footer();
?>
