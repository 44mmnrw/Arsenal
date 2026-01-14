<?php
/**
 * Шаблон страницы турнирного дерева
 *
 * Template Name: Турнирное дерево
 * Template Post Type: page
 * 
 * Страница с турнирным деревом (скобками) на полную ширину
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();

// Enqueue tournament styles
wp_enqueue_style( 'arsenal-tournament', get_template_directory_uri() . '/assets/css/pages/page-tournament.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

global $wpdb;

// Получаем активный год сезона из настроек (управляется в дашборде плагина)
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );
$active_season_id = get_option( 'arsenal_active_season_id', null );
?>

<main id="main" class="site-main tournament-page">
	
	<div class="tournament-container">
		
		<!-- Заголовок турнира -->
		<div class="tournament-header">
			<h1 class="tournament-title">Кубок Беларуси <?php echo esc_html( $active_season_year ); ?> • Турнирная сетка</h1>
		</div>
		
		<!-- Турнирное дерево -->
		<?php
		get_template_part( 'template-parts/sections/tournament-bracket' );
		?>
		
	</div><!-- .container -->
	
</main><!-- #main -->

<?php
get_footer();
?>
