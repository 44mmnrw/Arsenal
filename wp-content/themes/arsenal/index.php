<?php
/**
 * Основной шаблон темы
 *
 * @package Arsenal
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php
						if ( is_singular() ) :
							the_title( '<h1 class="entry-title">', '</h1>' );
						else :
							the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
						endif;
						?>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="post-thumbnail">
							<?php the_post_thumbnail( 'arsenal-featured' ); ?>
						</div>
					<?php endif; ?>

					<div class="entry-content">
						<?php
						if ( is_singular() ) :
							the_content();
						else :
							the_excerpt();
						endif;
						?>
					</div>

					<footer class="entry-footer">
						<?php
						if ( ! is_singular() ) :
							?>
							<a href="<?php the_permalink(); ?>" class="read-more">
								<?php esc_html_e( 'Читать далее', 'arsenal' ); ?>
							</a>
							<?php
						endif;
						?>
					</footer>
				</article>
				<?php
			endwhile;

			the_posts_navigation();
		else :
			?>
			<p><?php esc_html_e( 'Записи не найдены.', 'arsenal' ); ?></p>
			<?php
		endif;
		?>
	</div>
</main>

<?php
get_footer();
