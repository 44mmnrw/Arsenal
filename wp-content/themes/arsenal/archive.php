<?php
/**
 * Шаблон архива новостей
 *
 * @package Arsenal
 */

get_header();
?>

<main id="main" class="site-main news-page">
	<div class="container">
		<div class="news-content">
			<!-- Заголовок архива -->
			<h1 class="page-title">
				<?php
				if ( is_category() ) {
					single_cat_title();
				} elseif ( is_tag() ) {
					single_tag_title();
				} elseif ( is_author() ) {
					printf( __( 'Автор: %s', 'arsenal' ), get_the_author() );
				} elseif ( is_day() ) {
					printf( __( 'Архив за: %s', 'arsenal' ), get_the_date() );
				} elseif ( is_month() ) {
					printf( __( 'Архив за: %s', 'arsenal' ), get_the_date( 'F Y' ) );
				} elseif ( is_year() ) {
					printf( __( 'Архив за: %s', 'arsenal' ), get_the_date( 'Y' ) );
				} else {
					_e( 'Архив новостей', 'arsenal' );
				}
				?>
			</h1>

			<!-- Описание категории (если есть) -->
			<?php if ( is_category() && category_description() ) : ?>
				<div class="archive-description">
					<?php echo category_description(); ?>
				</div>
			<?php endif; ?>

			<!-- Фильтры категорий -->
			<div class="news-filters">
				<?php
				$categories = get_categories( array(
					'orderby' => 'name',
					'order'   => 'ASC',
					'hide_empty' => false,
				) );
				
				$current_cat = get_query_var( 'cat' );
				?>
				
				<a href="<?php echo get_post_type_archive_link( 'post' ); ?>" class="filter-btn <?php echo ( ! is_category() ) ? 'active' : ''; ?>">
					Все новости
				</a>
				
				<?php foreach ( $categories as $category ) : ?>
					<a href="<?php echo get_category_link( $category->term_id ); ?>" 
					   class="filter-btn <?php echo ( is_category( $category->term_id ) ) ? 'active' : ''; ?>">
						<?php echo esc_html( $category->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>

			<!-- Сетка новостей -->
			<div class="news-grid">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						?>
						<article class="news-card">
							<a href="<?php the_permalink(); ?>" class="news-card-link">
								<!-- Изображение новости -->
								<div class="news-card-image">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'medium_large' ); ?>
									<?php else : ?>
										<div class="news-placeholder">
											<div class="placeholder-icon">⚽</div>
											<div class="placeholder-text">
												<p class="placeholder-title">Изображение не загружено</p>
												<p class="placeholder-subtitle"><?php the_title(); ?></p>
											</div>
										</div>
									<?php endif; ?>
								</div>

								<!-- Содержимое карточки -->
								<div class="news-card-content">
									<!-- Категория и дата -->
									<div class="news-meta">
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
									<h3 class="news-title"><?php the_title(); ?></h3>

									<!-- Краткое описание -->
									<div class="news-excerpt">
										<?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
									</div>

									<!-- Автор -->
									<div class="news-author">
										Автор: <?php echo get_the_author(); ?>
									</div>
								</div>
							</a>
						</article>
						<?php
					endwhile;
					
					// Пагинация
					the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => __( '← Назад', 'arsenal' ),
						'next_text' => __( 'Вперед →', 'arsenal' ),
					) );
					
				else :
					?>
					<p class="no-news">Новостей пока нет.</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
