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
wp_enqueue_style( 'arsenal-tournament', get_template_directory_uri() . '/assets/css/page-tournament.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

global $wpdb;

// Получаем активный год сезона
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );
?>

<main id="main" class="site-main tournament-page">
	
	<div class="tournament-container" style="max-width: var(--container-max-width); margin: 0 auto; padding: 0 2.5rem;">
		
		<!-- Заголовок турнира -->
		<div class="tournament-header">
			<h1 class="tournament-title">Кубок Беларуси <?php echo esc_html( $active_season_year ); ?> • Турнирная сетка</h1>
		</div>
		
		<!-- Турнирное дерево -->
		<?php
		get_template_part( 'template-parts/blocks/tournament-bracket' );
		?>
		
	</div><!-- .container -->
	
</main><!-- #main -->

<?php
get_footer();
?>
