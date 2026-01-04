<?php
/**
 * Template Name: Команда / Состав
 * Template Post Type: page
 *
 * Страница с составом команды - сетка карточек игроков
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<?php
	// Подключаем сетку игроков
	get_template_part( 'template-parts/components/players-grid' );
	?>
</main>

<?php
get_footer();

