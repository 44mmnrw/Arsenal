<?php
/**
 * Template Part: News Section Component
 * 
 * Компонент для отображения последних 3 новостей клуба на главной странице.
 * Динамически подтягивает новости из WordPress постов и отображает их в виде карточек.
 * 
 * Назначение:
 * - Отображение актуальных новостей на главной странице сайта
 * - Привлечение внимания посетителей к свежему контенту
 * - Ссылка на полную страницу новостей для просмотра архива
 * 
 * Использование:
 * Включение в шаблон:
 *     get_template_part( 'template-parts/components/news' );
 * 
 * Включение в функции:
 *     do_action( 'arsenal_after_matches_section' );
 *
 * Отображаемые элементы:
 * - Заголовок раздела "Актуальные новости"
 * - Ссылка "Все новости" на страницу архива
 * - 3 последние новости в виде карточек с:
 *   * Изображением (featured image)
 *   * Датой публикации
 *   * Категорией
 *   * Заголовком
 *   * Выписка текста (первые 20 слов)
 *   * Ссылкой "Читать далее"
 * 
 * Стили:
 * - CSS класс: .news-section
 * - Сетка: .news-grid (CSS Grid, 3 колонки)
 * - Карточка: .news-card с подклассами (__image, __content, __meta, __title, __excerpt)
 * 
 * Зависимости:
 * - WP_Query для получения постов
 * - get_template_directory_uri() для URL ресурсов
 * - arsenal_icon() функция для иконок (стрелка)
 * 
 * История версий:
 * - v1.0 (04.01.2026) - Начальная версия с динамическими постами
 * - Добавлен fallback при отсутствии новостей (3 заглушки)
 * - Поддержка featured image и категорий
 * 
 * @package Arsenal
 * @since 1.0.0
 * @link https://www.figma.com/design/3PDLTXxabQeweijDRr6x3E/Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="news-section">
	<div class="container">
		<div class="section-header">
			<h2 class="section-title"><?php esc_html_e( 'Актуальные новости', 'arsenal' ); ?></h2>
			<?php
			// Получаем ID страницы новостей
			$news_page = get_page_by_path( 'novosti' );
			if ( ! $news_page ) {
				$news_page = get_page_by_path( 'news' );
			}
			$news_url = $news_page ? get_permalink( $news_page->ID ) : home_url( '/news/' );
			?>
			<a href="<?php echo esc_url( $news_url ); ?>" class="section-link">
				<?php esc_html_e( 'Все новости', 'arsenal' ); ?>
				<?php arsenal_icon( 'icon-arrow-right', 20, 20 ); ?>
			</a>
		</div>
		
		<div class="news-grid">
			<?php
			// Запрос последних 3 новостей
			$news_query = new WP_Query( array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );
			
			if ( $news_query->have_posts() ) :
				while ( $news_query->have_posts() ) : $news_query->the_post();
					$category = get_the_category();
					?>
					<article class="news-card">
						<div class="news-card__image">
							<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
							<?php else : ?>
								<a href="<?php the_permalink(); ?>">
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/news-placeholder.jpg' ); ?>" alt="<?php the_title_attribute(); ?>">
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
								<?php arsenal_icon( 'icon-arrow-right-sm', 16, 16 ); ?>
							</a>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				// Если новостей нет, показываем заглушку
				for ( $i = 0; $i < 3; $i++ ) :
					?>
					<article class="news-card">
						<div class="news-card__image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/news-placeholder.jpg' ); ?>" alt="<?php esc_attr_e( 'Новость', 'arsenal' ); ?>">
						</div>
						<div class="news-card__content">
							<div class="news-card__meta">
								<time class="news-card__date"><?php echo date( 'j F Y' ); ?></time>
								<span class="news-card__dot">•</span>
								<span class="news-card__category"><?php esc_html_e( 'Новости клуба', 'arsenal' ); ?></span>
							</div>
							<h3 class="news-card__title">
								<?php esc_html_e( 'Здесь будет новость', 'arsenal' ); ?>
							</h3>
							<p class="news-card__excerpt">
								<?php esc_html_e( 'Добавьте новости через админ-панель WordPress, и они автоматически появятся здесь.', 'arsenal' ); ?>
							</p>
						</div>
					</article>
					<?php
				endfor;
			endif;
			?>
		</div>
	</div>
</section>
