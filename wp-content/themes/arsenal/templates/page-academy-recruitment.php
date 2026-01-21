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

// Подключить адаптер Carbon Fields
require_once get_template_directory() . '/inc/class-academy-carbon-adapter.php';

// Получить ID текущей страницы
$post_id = get_the_ID();

// Загрузить данные через Carbon Fields адаптер
$data = Arsenal_Academy_Carbon_Adapter::get_page_data( $post_id );

// Распаковать данные
$hero = $data['hero_data'] ?? array();
$benefits = $data['benefits_data'] ?? array();
$age_groups = $data['age_groups_data'] ?? array();
$documents = $data['documents_data'] ?? array();
$schedule = $data['schedule_data'] ?? array();
$contacts = $data['contacts_data'] ?? array();
$directions = $data['directions_data'] ?? array();
$social_data = $data['social_data'] ?? array();
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
						<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( $benefit['icon'] ?? 'icon-place', 'icon-24' ); ?>
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
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-checkbox', 'icon-16' ); ?>
							<span><?php echo esc_html( $group['age_range'] ?? '' ); ?></span>
						</li>
						<li>
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-checkbox', 'icon-16' ); ?>
							<span><?php printf( esc_html_x( 'Год рождения: %s', 'academy', 'arsenal' ), esc_html( $group['birth_years'] ?? '' ) ); ?></span>
						</li>
						<li>
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-checkbox', 'icon-16' ); ?>
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
					<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-report', 'icon-20 document-icon' ); ?>
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
						<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( $item['icon'] ?? 'icon-calendar' ); ?>
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
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-place' ); ?>
								<div class="contact-content">
									<p class="contact-label"><?php esc_html_e( 'Адрес', 'arsenal' ); ?></p>
									<p class="contact-value"><?php echo esc_html( $contacts['address'] ); ?></p>
								</div>
							</div>
							<?php endif; ?>
							
							<!-- Phone -->
							<?php if ( ! empty( $contacts['phone'] ) ) : ?>
							<div class="contact-item">
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-phone' ); ?>
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
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-clock' ); ?>
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
							<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-email' ); ?>
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
								<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( 'icon-place', 'map-icon' ); ?>
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
									<span style="display: inline-block; vertical-align: middle; margin-right: 6px; width: 14px; height: 14px;">
										<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( $item['icon'], 'icon-16' ); ?>
									</span>
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
		<?php if ( ! empty( $social_data ) && is_array( $social_data ) ) : ?>
		<div class="contacts-social-section">
			<h3 class="contacts-section-title"><?php esc_html_e( 'Мы в социальных сетях', 'arsenal' ); ?></h3>
			
			<div class="social-links-grid">
				<?php foreach ( $social_data as $social ) : ?>
					<?php if ( ! empty( $social['url'] ) && ! empty( $social['icon'] ) ) : ?>
					<a href="<?php echo esc_url( $social['url'] ); ?>" class="social-button" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( ucfirst( str_replace( 'icon-', '', $social['icon'] ) ) ); ?>">
						<?php echo Arsenal_Academy_Carbon_Adapter::render_icon( $social['icon'], 'icon-24' ); ?>
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
	<?php
	// Получить URL карты из БД
	$map_url = isset( $contacts['map_url'] ) ? $contacts['map_url'] : '';
	$lat = null;
	$lng = null;
	
	// Развернуть короткие ссылки (Google: goo.gl, maps.app.goo.gl | Яндекс: yandex.ru/maps/-)
	if ( preg_match( '/goo\.gl|maps\.app\.goo\.gl|yandex\.ru\/maps\/-/', $map_url ) ) {
		$response = wp_remote_get( $map_url, array(
			'redirection' => 0,
			'timeout'     => 5,
		) );
		
		if ( ! is_wp_error( $response ) ) {
			$location = wp_remote_retrieve_header( $response, 'location' );
			if ( $location ) {
				// Если Яндекс вернул относительный URL, собрать полный
				if ( strpos( $location, '/' ) === 0 ) {
					$parsed = parse_url( $map_url );
					$location = $parsed['scheme'] . '://' . $parsed['host'] . $location;
				}
				$map_url = $location;
			}
		}
	}
	
	// Декодировать URL-encoded параметры
	$map_url = urldecode( $map_url );
	
	// Google Maps: @lat,lng,zoom
	if ( preg_match( '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $map_url, $matches ) ) {
		$lat = floatval( $matches[1] );
		$lng = floatval( $matches[2] );
	}
	// Google Maps: ?q=lat,lng
	elseif ( preg_match( '/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $map_url, $matches ) ) {
		$lat = floatval( $matches[1] );
		$lng = floatval( $matches[2] );
	}
	// Яндекс Карты: ll=lng,lat (обратный порядок!)
	elseif ( preg_match( '/ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $map_url, $matches ) ) {
		$lng = floatval( $matches[1] );
		$lat = floatval( $matches[2] );
	}
	?>
	
	<?php if ( $lat && $lng ) : ?>
	// Координаты извлечены из URL: <?php echo esc_js( $map_url ); ?>
	var mapContainer = document.getElementById( 'academy-map' );
	var academyMap = L.map( 'academy-map', {
		attributionControl: false
	} ).setView( [<?php echo $lat; ?>, <?php echo $lng; ?>], 14 );
	
	// Добавить слой карты (OpenStreetMap)
	L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		minZoom: 10
	} ).addTo( academyMap );
	
	// Добавить маркер с иконкой
	var marker = L.marker( [<?php echo $lat; ?>, <?php echo $lng; ?>] ).addTo( academyMap );
	marker.bindPopup( '<strong>СДЮШ "Арсенал"</strong><br>г. Дзержинск' );
	
	// Открыть попап при загрузке
	marker.openPopup();
	<?php else : ?>
	console.warn( 'Карта не отображается: координаты не найдены в URL' );
	<?php endif; ?>
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

<?php
get_footer();
?>
