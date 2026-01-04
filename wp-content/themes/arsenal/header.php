<?php
/**
 * Шаблон шапки сайта
 *
 * @package Arsenal
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#main">
		<?php esc_html_e( 'Перейти к содержимому', 'arsenal' ); ?>
	</a>

	<header id="masthead" class="site-header">
		<div class="header-container">
			<!-- Логотип -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
				<div class="logo-icon">
				<?php echo file_get_contents( get_template_directory() . '/assets/images/arsenal-logo.svg' ); ?>
				</div>
				<div class="logo-text">
					<span class="logo-title">ФК Арсенал</span>
					<span class="logo-subtitle">Дзержинск</span>
				</div>
			</a>

			<!-- Навигация -->
			<nav id="site-navigation" class="main-navigation">
				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
					<span class="hamburger"></span>
				</button>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'nav-menu',
					'fallback_cb'    => false,
				) );
				?>
			</nav>
		</div>
	</header>
