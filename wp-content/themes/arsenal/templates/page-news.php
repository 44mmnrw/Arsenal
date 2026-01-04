<?php
/**
 * Template Name: Страница новостей
 * Шаблон страницы новостей
 *
 * @package Arsenal
 */

get_header();
?>

<main id="main" class="site-main news-page">
	<div class="container">
		<div class="news-content">
			<!-- Заголовок -->
			<h1 class="page-title">Новости</h1>

			<!-- Фильтры категорий -->
			<div class="news-filters">
				<?php
				// Получаем категории новостей
				$categories = get_categories( array(
					'orderby' => 'name',
					'order'   => 'ASC',
					'hide_empty' => false,
				) );
				
				// Текущая категория
				$current_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
				?>
				
				<a href="<?php echo get_permalink(); ?>" class="filter-btn <?php echo ($current_cat == 0) ? 'active' : ''; ?>">
					Все новости
				</a>
				
				<?php foreach ( $categories as $category ) : ?>
					<a href="<?php echo add_query_arg('cat', $category->term_id, get_permalink()); ?>" 
					   class="filter-btn <?php echo ($current_cat == $category->term_id) ? 'active' : ''; ?>">
						<?php echo esc_html( $category->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>

			<!-- Сетка новостей -->
			<div class="news-grid">
				<?php
				// Параметры запроса
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				
				$args = array(
					'post_type'      => 'post',
					'posts_per_page' => 9,
					'paged'          => $paged,
					'post_status'    => 'publish',
				);
				
				// Если выбрана категория
				if ( $current_cat > 0 ) {
					$args['cat'] = $current_cat;
				}
				
				$news_query = new WP_Query( $args );
				
				if ( $news_query->have_posts() ) :
					while ( $news_query->have_posts() ) : $news_query->the_post();
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
					if ( $news_query->max_num_pages > 1 ) : ?>
						<div class="pagination">
							<?php
							echo paginate_links( array(
								'total'        => $news_query->max_num_pages,
								'current'      => $paged,
								'format'       => '?paged=%#%',
								'show_all'     => false,
								'prev_text'    => __( '← Назад', 'arsenal' ),
								'next_text'    => __( 'Вперед →', 'arsenal' ),
							) );
							?>
						</div>
					<?php endif;
					
					wp_reset_postdata();
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
