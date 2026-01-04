<?php
/**
 * Шаблон подвала сайта
 *
 * @package Arsenal
 */
?>

	<footer id="colophon" class="site-footer">
		<?php
		// Получаем настройки из Customizer (дефолты задаются в customizer.php)
		$club_name    = get_theme_mod( 'arsenal_club_name', '' );
		$club_city    = get_theme_mod( 'arsenal_club_city', '' );
		$club_address = get_theme_mod( 'arsenal_club_address', '' );
		
		$social_links = array(
			'facebook'  => array(
				'url'   => get_theme_mod( 'arsenal_social_facebook', '' ),
				'icon'  => 'icon-facebook',
				'label' => 'Facebook',
				'size'  => array( 17, 17 ),
			),
			'instagram' => array(
				'url'   => get_theme_mod( 'arsenal_social_instagram', '' ),
				'icon'  => 'icon-instagram',
				'label' => 'Instagram',
				'size'  => array( 17, 17 ),
			),
			'youtube'   => array(
				'url'   => get_theme_mod( 'arsenal_social_youtube', '' ),
				'icon'  => 'icon-youtube',
				'label' => 'YouTube',
				'size'  => array( 17, 12 ),
			),
			'telegram'  => array(
				'url'   => get_theme_mod( 'arsenal_social_telegram', '' ),
				'icon'  => 'icon-telegram',
				'label' => 'Telegram',
				'size'  => array( 17, 17 ),
			),
			'vk'        => array(
				'url'   => get_theme_mod( 'arsenal_social_vk', '' ),
				'icon'  => 'icon-vk',
				'label' => 'VK',
				'size'  => array( 17, 17 ),
			),
		);
		?>
		<!-- Основная часть футера -->
		<div class="footer-main">
			<div class="container">
				<div class="footer-content">
					<!-- Логотип и контакты -->
					<div class="footer-brand">
						<div class="footer-brand-top">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo">
							<div class="footer-logo-icon">
							<?php echo file_get_contents( get_template_directory() . '/assets/images/arsenal-logo.svg' ); ?>
							</div>
						</a>
							<div class="footer-brand-info">
								<span class="footer-brand-name"><?php echo esc_html( $club_name ); ?></span>
								<span class="footer-brand-city"><?php echo esc_html( $club_city ); ?></span>
							</div>
						</div>
						<div class="footer-brand-address"><?php echo esc_html( $club_address ); ?></div>
						<!-- Социальные сети -->
						<div class="footer-social">
							<?php foreach ( $social_links as $network => $social ) : ?>
								<?php if ( ! empty( $social['url'] ) ) : ?>
									<a href="<?php echo esc_url( $social['url'] ); ?>" class="footer-social-link footer-social-<?php echo esc_attr( $network ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
										<?php arsenal_icon( $social['icon'], $social['size'][0], $social['size'][1] ); ?>
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Навигация -->
					<div class="footer-nav">
						<?php
						// Получаем настройки меню из Customizer
						$menu_club_title    = get_theme_mod( 'arsenal_menu_club_title', 'Клуб' );
						$menu_matches_title = get_theme_mod( 'arsenal_menu_matches_title', 'Матчи' );
						$menu_news_title    = get_theme_mod( 'arsenal_menu_news_title', 'Новости' );

						// Клуб - ссылки
						$club_links = array(
							array(
								'text' => get_theme_mod( 'arsenal_menu_club_link1_text', 'О клубе' ),
								'url'  => get_theme_mod( 'arsenal_menu_club_link1_url', '/about/' ),
							),
							array(
								'text' => get_theme_mod( 'arsenal_menu_club_link2_text', 'История' ),
								'url'  => get_theme_mod( 'arsenal_menu_club_link2_url', '/history/' ),
							),
							array(
								'text' => get_theme_mod( 'arsenal_menu_club_link3_text', 'Команда' ),
								'url'  => get_theme_mod( 'arsenal_menu_club_link3_url', '/team/' ),
							),
							array(
								'text' => get_theme_mod( 'arsenal_menu_club_link4_text', 'Тренерский штаб' ),
								'url'  => get_theme_mod( 'arsenal_menu_club_link4_url', '/coaches/' ),
							),
							array(
								'text' => get_theme_mod( 'arsenal_menu_club_link5_text', 'Руководство' ),
								'url'  => get_theme_mod( 'arsenal_menu_club_link5_url', '/management/' ),
							),
						array(
							'text' => get_theme_mod( 'arsenal_menu_club_link6_text', '' ),
							'url'  => get_theme_mod( 'arsenal_menu_club_link6_url', '' ),
						),
					);

					// Матчи - ссылки
					$matches_links = array(
						array(
							'text' => get_theme_mod( 'arsenal_menu_matches_link1_text', 'Расписание' ),
							'url'  => get_theme_mod( 'arsenal_menu_matches_link1_url', '/schedule/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_matches_link2_text', 'Результаты' ),
							'url'  => get_theme_mod( 'arsenal_menu_matches_link2_url', '/results/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_matches_link3_text', 'Турнирная таблица' ),
							'url'  => get_theme_mod( 'arsenal_menu_matches_link3_url', '/standings/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_matches_link4_text', 'Билеты' ),
							'url'  => get_theme_mod( 'arsenal_menu_matches_link4_url', '/tickets/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_matches_link5_text', '' ),
							'url'  => get_theme_mod( 'arsenal_menu_matches_link5_url', '' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_matches_link6_text', '' ),
							'url'  => get_theme_mod( 'arsenal_menu_matches_link6_url', '' ),
						),
					);

					// Новости - ссылки
					$news_links = array(
						array(
							'text' => get_theme_mod( 'arsenal_menu_news_link1_text', 'Все новости' ),
							'url'  => get_theme_mod( 'arsenal_menu_news_link1_url', '/news/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_news_link2_text', 'Команда' ),
							'url'  => get_theme_mod( 'arsenal_menu_news_link2_url', '/news/team/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_news_link3_text', 'Матчи' ),
							'url'  => get_theme_mod( 'arsenal_menu_news_link3_url', '/news/matches/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_news_link4_text', 'Клуб' ),
							'url'  => get_theme_mod( 'arsenal_menu_news_link4_url', '/news/club/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_news_link5_text', 'Медиа' ),
							'url'  => get_theme_mod( 'arsenal_menu_news_link5_url', '/news/media/' ),
						),
						array(
							'text' => get_theme_mod( 'arsenal_menu_news_link6_text', '' ),
							'url'  => get_theme_mod( 'arsenal_menu_news_link6_url', '' ),
						),
					);

					// Массив всех колонок меню
					$menu_columns = array(
						array(
							'title' => $menu_club_title,
							'links' => $club_links,
						),
						array(
							'title' => $menu_matches_title,
							'links' => $matches_links,
						),
						array(
							'title' => $menu_news_title,
							'links' => $news_links,
						),
					);

					// Вывод колонок меню
						foreach ( $menu_columns as $column ) :
							?>
							<div class="footer-nav-column footer-accordion-section">
								<span class="footer-nav-title"><?php echo esc_html( $column['title'] ); ?></span>
								<button class="footer-accordion-toggle" aria-expanded="false">
									<span class="footer-nav-title"><?php echo esc_html( $column['title'] ); ?></span>
									<span class="footer-accordion-icon">+</span>
								</button>
								<ul class="footer-nav-links">
									<?php foreach ( $column['links'] as $link ) : ?>
										<?php if ( ! empty( $link['text'] ) ) : ?>
											<li>
												<a href="<?php echo esc_url( home_url( $link['url'] ) ); ?>">
													<?php echo esc_html( $link['text'] ); ?>
												</a>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
							</div>
							<?php
						endforeach;
						?>
					</div>

					<!-- Контакты -->
					<div class="footer-contacts footer-accordion-section">
						<span class="footer-contacts-heading">Контакты</span>
						<button class="footer-accordion-toggle" aria-expanded="false">
							<span class="footer-contacts-heading">Контакты</span>
							<span class="footer-accordion-icon">+</span>
						</button>
						<?php
						$phone1  = get_theme_mod( 'arsenal_contact_phone1', '' );
						$phone2  = get_theme_mod( 'arsenal_contact_phone2', '' );
						$email   = get_theme_mod( 'arsenal_contact_email', '' );
						$stadium = get_theme_mod( 'arsenal_contact_stadium', '' );
						$address = get_theme_mod( 'arsenal_contact_address', '' );
						$hours   = get_theme_mod( 'arsenal_contact_hours', '' );
						?>
						
						<div class="footer-contacts-content">
							<?php if ( $stadium || $address ) : ?>
							<div class="footer-contacts-group">
								<span class="footer-contacts-icon"><?php arsenal_icon( 'icon-address', 16, 16 ); ?></span>
								<ul class="footer-contacts-list">
									<?php if ( $stadium ) : ?>
										<li><?php echo esc_html( $stadium ); ?></li>
									<?php endif; ?>
									<?php if ( $address ) : ?>
										<li><?php echo esc_html( $address ); ?></li>
									<?php endif; ?>
								</ul>
							</div>
							<?php endif; ?>

							<?php if ( $phone1 || $phone2 ) : ?>
							<div class="footer-contacts-group">
								<span class="footer-contacts-icon"><?php arsenal_icon( 'icon-phone', 16, 16 ); ?></span>
								<ul class="footer-contacts-list">
									<?php if ( $phone1 ) : ?>
										<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone1 ) ); ?>"><?php echo esc_html( $phone1 ); ?></a></li>
									<?php endif; ?>
									<?php if ( $phone2 ) : ?>
										<li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone2 ) ); ?>"><?php echo esc_html( $phone2 ); ?></a></li>
									<?php endif; ?>
								</ul>
							</div>
							<?php endif; ?>
							
							<?php if ( $email ) : ?>
							<div class="footer-contacts-group">
								<span class="footer-contacts-icon"><?php arsenal_icon( 'icon-email', 16, 16 ); ?></span>
								<ul class="footer-contacts-list">
									<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
								</ul>
							</div>
							<?php endif; ?>
							
							<?php if ( $hours ) : ?>
							<div class="footer-contacts-group">
								<span class="footer-contacts-icon"><?php arsenal_icon( 'icon-clock', 16, 16 ); ?></span>
								<ul class="footer-contacts-list">
									<li><?php echo esc_html( $hours ); ?></li>
								</ul>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Нижняя часть футера с копирайтом -->
		<div class="footer-bottom">
			<div class="container">
				<div class="footer-bottom-content">
					<div class="footer-bottom-left">
						<span class="footer-copyright">© <?php echo date( 'Y' ); ?> ФК Арсенал Дзержинск</span>
						<?php
						$user_agreement = get_page_by_path( 'user-agreement' );
						if ( $user_agreement ) : ?>
							<a href="<?php echo esc_url( get_permalink( $user_agreement ) ); ?>" class="footer-link">Пользовательское соглашение</a>
						<?php endif; ?>
					</div>
					<div class="footer-bottom-right">
						<?php
						$privacy_policy = get_page_by_path( 'privacy-policy' );
						if ( $privacy_policy ) : ?>
							<a href="<?php echo esc_url( get_permalink( $privacy_policy ) ); ?>" class="footer-link">Политика конфиденциальности</a>
						<?php endif; ?>
						<span class="footer-developer">Разработка и поддержка: <a href="#" class="footer-link footer-link--accent">Веб-студия</a></span>
					</div>
				</div>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Аккордеон для всех секций (навигация + контакты)
	const accordionToggles = document.querySelectorAll('.footer-accordion-toggle');
	
	accordionToggles.forEach(function(toggle) {
		toggle.addEventListener('click', function() {
			const section = this.closest('.footer-accordion-section');
			const isExpanded = this.getAttribute('aria-expanded') === 'true';
			
			this.setAttribute('aria-expanded', !isExpanded);
			section.classList.toggle('active');
		});
	});
});
</script>

<?php wp_footer(); ?>

</body>
</html>
