<?php
/**
 * Шаблон архива категорий
 *
 * @package Arsenal
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="container">
		<h1 class="page-title"><?php single_cat_title(); ?></h1>
		
		<div class="news-grid">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$category = get_the_category();
					?>
					<div class="news-card">
						<div class="news-card__image">
							<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
							<?php else : ?>
								<a href="<?php the_permalink(); ?>">
									<lottie-player src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/wired-outline-61-camera-hover-flash.json' ); ?>" background="transparent" style="width: 100%; height: 100%; min-height: 250px;"></lottie-player>
								</a>
							<?php endif; ?>
						</div>
						<div class="news-card__content">
							<div class="news-card__meta">
								<time class="news-card__date" datetime="<?php echo get_the_date( 'c' ); ?>">
									<?php echo get_the_date( 'j F Y' ); ?>
								</time>
								<?php if ( ! empty( $category ) ) : ?>
									<span class="news-card__dot">•</span>
									<a href="<?php echo esc_url( get_category_link( $category[0]->term_id ) ); ?>" class="news-card__category">
										<?php echo esc_html( $category[0]->name ); ?>
									</a>
								<?php endif; ?>
							</div>
							<h3 class="news-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							<p class="news-card__excerpt">
								<?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
							</p>
							<a href="<?php the_permalink(); ?>" class="news-card__link">
								<?php esc_html_e( 'Читать далее', 'arsenal' ); ?>
								<?php arsenal_icon( 'icon-arrow-right', 16, 16 ); ?>
							</a>
						</div>
					</div>
					<?php
				endwhile;
				the_posts_pagination();
			else :
				echo '<p>' . esc_html_e( 'Записей не найдено', 'arsenal' ) . '</p>';
			endif;
			?>
		</div>
	</div>
</main>

<?php
get_footer();
