<?php
/**
 * Шаблон одиночной записи (новости)
 *
 * @package Arsenal
 */

get_header();
?>

<main id="main" class="site-main single-news-page">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-news-article' ); ?>>
				<!-- Хлебные крошки -->
				<div class="breadcrumbs">
					<a href="<?php echo home_url(); ?>">Главная</a>
					<span class="separator">→</span>
					<a href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>">Новости</a>
					<span class="separator">→</span>
					<span class="current"><?php the_title(); ?></span>
				</div>

				<!-- Метаданные -->
				<div class="single-news-meta">
					<span class="news-category">
						<?php
						$category = get_the_category();
						if ( ! empty( $category ) ) {
							echo esc_html( $category[0]->name );
						}
						?>
					</span>
					<span class="news-date"><?php echo get_the_date( 'j F Y г.' ); ?></span>
				</div>

				<!-- Заголовок -->
				<h1 class="single-news-title"><?php the_title(); ?></h1>

				<!-- Автор -->
				<div class="single-news-author">
					Автор: <?php echo get_the_author(); ?>
				</div>

				<!-- Изображение -->
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="single-news-image">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<!-- Содержимое -->
				<div class="single-news-content">
					<?php the_content(); ?>
				</div>

				<!-- Теги -->
				<?php if ( has_tag() ) : ?>
					<div class="single-news-tags">
						<?php the_tags( '<strong>Теги:</strong> ', ', ', '' ); ?>
					</div>
				<?php endif; ?>

				<!-- Навигация между записями -->
				<div class="post-navigation">
					<?php
					$prev_post = get_previous_post();
					$next_post = get_next_post();
					?>
					
					<?php if ( $prev_post ) : ?>
						<div class="nav-previous">
							<a href="<?php echo get_permalink( $prev_post ); ?>">
								← <?php echo get_the_title( $prev_post ); ?>
							</a>
						</div>
					<?php endif; ?>
					
					<?php if ( $next_post ) : ?>
						<div class="nav-next">
							<a href="<?php echo get_permalink( $next_post ); ?>">
								<?php echo get_the_title( $next_post ); ?> →
							</a>
						</div>
					<?php endif; ?>
				</div>
			</article>

		<?php endwhile; ?>
	</div>
</main>

<?php
get_footer();
