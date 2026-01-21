<?php
/**
 * Template: Academy History Page
 * Description: Страница истории академии
 * 
 * @package Arsenal
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="site-main">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-academy-history' ); ?>>
		
		<div class="entry-content">
			<?php
			the_content();
			?>
		</div>

	</article>
</main>

<?php
get_footer();
