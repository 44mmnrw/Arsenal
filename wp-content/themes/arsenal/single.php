<?php
/**
 * Шаблон одиночной записи (новости)
 *
 * @package Arsenal
 */

get_header();
?>

<main id="main" class="site-main single-news-page">
	<!-- Полноширинное изображение новости -->
	<div class="single-news-hero">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-news-hero__image">
				<?php the_post_thumbnail( 'full' ); ?>
			</div>
		<?php endif; ?>
		<div class="single-news-hero__overlay"></div>
	</div>

	<div class="container">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-news-article' ); ?>>
			<!-- Категория -->
			<div class="single-news-category">
				<?php
				$category = get_the_category();
				if ( ! empty( $category ) ) {
					echo esc_html( $category[0]->name );
				}
				?>
			</div>

			<!-- Заголовок -->
			<h1 class="single-news-title"><?php the_title(); ?></h1>

			<!-- Метаданные -->
			<div class="single-news-meta">
				<div class="single-news-meta__item">
					<?php arsenal_icon( 'icon-date', 18, 18 ); ?>
					<span><?php echo get_the_date( 'j F Y г.' ); ?></span>
				</div>
				<div class="single-news-meta__item">
					<?php arsenal_icon( 'icon-man', 18, 18 ); ?>
					<span><?php 
						$author_id = get_post_field( 'post_author', get_the_ID() );
						$author = get_the_author_meta( 'display_name', $author_id );
						echo ! empty( $author ) ? esc_html( $author ) : esc_html_e( 'Администратор', 'arsenal' );
					?></span>
				</div>
				<div class="single-news-meta__item">
					<?php arsenal_icon( 'icon-tag', 18, 18 ); ?>
					<span>
						<?php
						$category = get_the_category();
						if ( ! empty( $category ) ) {
							echo '<a href="' . esc_url( get_category_link( $category[0]->term_id ) ) . '">' . esc_html( $category[0]->name ) . '</a>';
						}
						?>
					</span>
				</div>
			</div>

			<!-- Основной контент -->
			<div class="single-news-content">
				<?php the_content(); ?>
			</div>

			<!-- Блок шаринга -->
			<div class="single-news-share">
				<div class="single-news-share__buttons">
					<span class="single-news-share__icon">
						<?php arsenal_icon( 'icon-share', 20, 20 ); ?>
					</span>
					<a href="javascript:void(0);" 
					   onclick="shareToFacebook('<?php echo esc_js( get_permalink() ); ?>')" 
					   class="single-news-share__btn single-news-share__btn--facebook" 
					   title="<?php esc_attr_e( 'Поделиться в Facebook', 'arsenal' ); ?>">
						<?php arsenal_icon( 'icon-facebook', 18, 18 ); ?>
					</a>
					<a href="javascript:void(0);" 
					   onclick="shareToVK('<?php echo esc_js( get_permalink() ); ?>', '<?php echo esc_js( get_the_title() ); ?>')" 
					   class="single-news-share__btn single-news-share__btn--vk" 
					   title="<?php esc_attr_e( 'Поделиться в ВКонтакте', 'arsenal' ); ?>">
						<?php arsenal_icon( 'icon-vk', 18, 18 ); ?>
					</a>
				</div>
			</div>

			<script>
			// Шаринг в Facebook
			function shareToFacebook(url) {
				const appUrl = 'fb://share/?link=' + encodeURIComponent(url);
				const browserUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
				
				// Пытаемся открыть приложение
				const timeoutId = setTimeout(() => {
					window.open(browserUrl, '_blank');
				}, 1000);
				
				// Попытка открыть app
				window.location = appUrl;
			}

			// Шаринг в VK
			function shareToVK(url, title) {
				const appUrl = 'vkontakte://share?url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title);
				const browserUrl = 'https://vk.com/share.php?url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title);
				
				// Пытаемся открыть приложение
				const timeoutId = setTimeout(() => {
					window.open(browserUrl, '_blank');
				}, 1000);
				
				// Попытка открыть app
				window.location = appUrl;
			}
			</script>

			<!-- Похожие новости -->
			<div class="single-news-related">
				<h2 class="single-news-related__title"><?php esc_html_e( 'Похожие новости', 'arsenal' ); ?></h2>
				<div class="news-grid">
					<?php
					// Получаем похожие новости по категории
					$current_categories = wp_get_post_categories( get_the_ID() );
					$related_args = array(
						'post_type'      => 'post',
						'posts_per_page' => 3,
						'post__not_in'   => array( get_the_ID() ),
						'category__in'   => $current_categories,
						'post_status'    => 'publish',
						'orderby'        => 'date',
						'order'          => 'DESC',
					);
					$related_query = new WP_Query( $related_args );

					if ( $related_query->have_posts() ) :
						while ( $related_query->have_posts() ) : $related_query->the_post();
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
						wp_reset_postdata();
					else :
						// Если похожих новостей нет, показываем любые 3 последние новости
						$fallback_args = array(
							'post_type'      => 'post',
							'posts_per_page' => 3,
							'post__not_in'   => array( get_the_ID() ),
							'post_status'    => 'publish',
							'orderby'        => 'date',
							'order'          => 'DESC',
						);
						$fallback_query = new WP_Query( $fallback_args );

						if ( $fallback_query->have_posts() ) :
							while ( $fallback_query->have_posts() ) : $fallback_query->the_post();
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
							wp_reset_postdata();
						endif;
					endif;
					?>
				</div>
			</div>

			<!-- Кнопка вернуться -->
			<div class="single-news-back">
				<a href="javascript:history.back();" class="single-news-back__button">
					<div class="single-news-back__icon">
						<?php arsenal_icon( 'icon-arrow-left', 20, 20 ); ?>
					</div>
					<span><?php esc_html_e( 'Вернуться к списку новостей', 'arsenal' ); ?></span>
				</a>
			</div>
		</article>
	</div>
</main>

<?php
get_footer();
