<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Arsenal
 */

get_header(); 
?>

<main id="primary" class="site-main page-404">
	<div class="not-found-container">
		<!-- Main 404 Content -->
		<div class="not-found-wrapper">
			<!-- Soccer Field with 404 -->
			<div class="soccer-field-container">
				<div class="soccer-field">
					<!-- Field striped background -->
					<div class="field-stripes"></div>
					
					<!-- Field elements -->
					<div class="field-elements">
						<!-- Center circle -->
						<div class="center-circle"></div>
						<div class="center-dot"></div>
						
						<!-- Side markings -->
						<div class="side-marking left"></div>
						<div class="side-marking right"></div>
						
						<!-- Center line -->
						<div class="center-line"></div>
						
						<!-- Soccer ball emoji -->
						<div class="ball-icon">⚽</div>
					</div>
					
					<!-- Large 404 text -->
					<div class="field-404">404</div>
					
					<!-- Red card -->
					<div class="red-card">
						<span class="card-text">404</span>
					</div>
				</div>
			</div>

			<!-- Text content -->
			<div class="not-found-content">
				<h1 class="not-found-title">Офсайд! Страница не найдена</h1>
				<p class="not-found-description">
					К сожалению, страница, которую вы ищете, ушла за пределы поля. Возможно, судья показал ей красную карточку, или она забила в свои ворота и удалилась.
				</p>

				<!-- Action buttons -->
				<div class="not-found-buttons">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-home">
						<span class="btn-icon">🏠</span>
						<span class="btn-text">Вернуться на главную</span>
					</a>
					<button class="btn btn-secondary btn-back" onclick="window.history.back()">
						<span class="btn-icon">←</span>
						<span class="btn-text">Назад</span>
					</button>
				</div>
			</div>

			<!-- Quick links section -->
			<div class="popular-sections">
				<div class="popular-sections-header">
					<span class="header-icon">🔍</span>
					<h2>Популярные разделы</h2>
				</div>
				
				<div class="quick-links">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="quick-link">
						<div class="link-icon-wrapper">🏠</div>
						<span class="link-text">Главная</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/teams' ) ); ?>" class="quick-link">
						<div class="link-icon-wrapper">👥</div>
						<span class="link-text">Команды</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/matches' ) ); ?>" class="quick-link">
						<div class="link-icon-wrapper">📅</div>
						<span class="link-text">Матчи</span>
					</a>
				</div>
			</div>

			<!-- Fun fact -->
			<div class="fun-fact">
				<span class="fact-icon">⚽</span>
				<span class="fact-label">Факт:</span>
				<span class="fact-text">Красная карточка была впервые применена на чемпионате мира 1970 года</span>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>
