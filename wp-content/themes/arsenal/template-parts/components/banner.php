<?php
/**
 * Template Part: Banner Carousel Section
 *
 * Секция баннера-карусели, отображаемая над футером.
 * Изображения и тексты настраиваются через Customizer.
 * Каждый слайд имеет свой уникальный контент.
 *
 * @package Arsenal
 * @since 1.0.0
 */

// Безопасность: прямой доступ запрещен
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Проверяем, нужно ли показывать баннер
$show_banner = get_theme_mod( 'arsenal_banner_show', true );
if ( ! $show_banner ) {
	return;
}

// Собираем данные слайдов (изображения + контент)
$slides = array();
for ( $i = 1; $i <= 5; $i++ ) {
	$image = get_theme_mod( 'arsenal_banner_slide_' . $i . '_image', '' );
	if ( ! empty( $image ) ) {
		// Для слайда 1 используем fallback к старым настройкам
		if ( $i === 1 ) {
			$label     = get_theme_mod( 'arsenal_banner_slide_1_label', '' );
			$title     = get_theme_mod( 'arsenal_banner_slide_1_title', '' );
			$subtitle  = get_theme_mod( 'arsenal_banner_slide_1_subtitle', '' );
			$info      = get_theme_mod( 'arsenal_banner_slide_1_info', '' );
			$btn1_text = get_theme_mod( 'arsenal_banner_slide_1_btn1_text', '' );
			$btn1_url  = get_theme_mod( 'arsenal_banner_slide_1_btn1_url', '#' );
			$btn2_text = get_theme_mod( 'arsenal_banner_slide_1_btn2_text', '' );
			$btn2_url  = get_theme_mod( 'arsenal_banner_slide_1_btn2_url', '#' );
			
			// Fallback к старым ключам если новые пусты
			if ( empty( $label ) ) {
				$label = get_theme_mod( 'arsenal_banner_label', 'Добро пожаловать' );
			}
			if ( empty( $title ) ) {
				$title = get_theme_mod( 'arsenal_banner_title', 'ФК АРСЕНАЛ' );
			}
			if ( empty( $subtitle ) ) {
				$subtitle = get_theme_mod( 'arsenal_banner_subtitle', 'Держим вас в курсе последних новостей, результатов матчей и предстоящих игр команды.' );
			}
			if ( empty( $info ) ) {
				$info = get_theme_mod( 'arsenal_banner_info', 'Регион: Нижегородская обл. Стадион: «Капролактамовец». Страна: Россия.' );
			}
			if ( empty( $btn1_text ) ) {
				$btn1_text = get_theme_mod( 'arsenal_banner_btn1_text', 'Наша команда' );
			}
			if ( $btn1_url === '#' ) {
				$btn1_url = get_theme_mod( 'arsenal_banner_btn1_url', '#' );
			}
			if ( empty( $btn2_text ) ) {
				$btn2_text = get_theme_mod( 'arsenal_banner_btn2_text', 'Календарь матчей' );
			}
			if ( $btn2_url === '#' ) {
				$btn2_url = get_theme_mod( 'arsenal_banner_btn2_url', '#' );
			}
			
			$slides[] = array(
				'image'     => $image,
				'label'     => $label,
				'title'     => $title,
				'subtitle'  => $subtitle,
				'info'      => $info,
				'btn1_text' => $btn1_text,
				'btn1_url'  => $btn1_url,
				'btn2_text' => $btn2_text,
				'btn2_url'  => $btn2_url,
			);
		} else {
			$slides[] = array(
				'image'     => $image,
				'label'     => get_theme_mod( 'arsenal_banner_slide_' . $i . '_label', '' ),
				'title'     => get_theme_mod( 'arsenal_banner_slide_' . $i . '_title', '' ),
				'subtitle'  => get_theme_mod( 'arsenal_banner_slide_' . $i . '_subtitle', '' ),
				'info'      => get_theme_mod( 'arsenal_banner_slide_' . $i . '_info', '' ),
				'btn1_text' => get_theme_mod( 'arsenal_banner_slide_' . $i . '_btn1_text', '' ),
				'btn1_url'  => get_theme_mod( 'arsenal_banner_slide_' . $i . '_btn1_url', '#' ),
				'btn2_text' => get_theme_mod( 'arsenal_banner_slide_' . $i . '_btn2_text', '' ),
				'btn2_url'  => get_theme_mod( 'arsenal_banner_slide_' . $i . '_btn2_url', '#' ),
			);
		}
	}
}

// Fallback: проверяем старую настройку (если миграция со старой версии)
if ( empty( $slides ) ) {
	$old_image = get_theme_mod( 'arsenal_banner_image', '' );
	if ( ! empty( $old_image ) ) {
		$slides[] = array(
			'image'     => $old_image,
			'label'     => get_theme_mod( 'arsenal_banner_label', 'Добро пожаловать' ),
			'title'     => get_theme_mod( 'arsenal_banner_title', 'ФК АРСЕНАЛ' ),
			'subtitle'  => get_theme_mod( 'arsenal_banner_subtitle', 'Держим вас в курсе последних новостей, результатов матчей и предстоящих игр команды.' ),
			'info'      => get_theme_mod( 'arsenal_banner_info', 'Регион: Нижегородская обл. Стадион: «Капролактамовец». Страна: Россия.' ),
			'btn1_text' => get_theme_mod( 'arsenal_banner_btn1_text', 'Наша команда' ),
			'btn1_url'  => get_theme_mod( 'arsenal_banner_btn1_url', '#' ),
			'btn2_text' => get_theme_mod( 'arsenal_banner_btn2_text', 'Календарь матчей' ),
			'btn2_url'  => get_theme_mod( 'arsenal_banner_btn2_url', '#' ),
		);
	}
}

// Если нет слайдов, не показываем баннер
if ( empty( $slides ) ) {
	return;
}

// Настройки автоплея
$autoplay = get_theme_mod( 'arsenal_banner_autoplay', true );
$interval = get_theme_mod( 'arsenal_banner_interval', 5000 );

// Количество слайдов
$slide_count  = count( $slides );
$is_carousel  = $slide_count > 1;
$banner_class = $is_carousel ? 'site-banner has-carousel' : 'site-banner';
?>

<section class="<?php echo esc_attr( $banner_class ); ?>" 
         aria-label="<?php esc_attr_e( 'Баннер клуба', 'arsenal' ); ?>"
         data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
         data-interval="<?php echo esc_attr( $interval ); ?>">
	
	<!-- Слайды карусели -->
	<div class="banner-slides">
		<?php foreach ( $slides as $index => $slide ) : ?>
			<div class="banner-slide<?php echo $index === 0 ? ' active' : ''; ?>" data-slide="<?php echo esc_attr( $index ); ?>">
				<!-- Фоновое изображение -->
				<div class="banner-background" style="background-image: url('<?php echo esc_url( $slide['image'] ); ?>');"></div>
				
				<!-- Градиентный оверлей -->
				<div class="banner-overlay"></div>
				
				<!-- Контент слайда -->
				<div class="banner-content">
					
					<?php if ( ! empty( $slide['label'] ) ) : ?>
						<span class="banner-label"><?php echo esc_html( $slide['label'] ); ?></span>
					<?php endif; ?>
					
					<?php if ( ! empty( $slide['title'] ) ) : ?>
						<h2 class="banner-title"><?php echo esc_html( $slide['title'] ); ?></h2>
					<?php endif; ?>
					
					<?php if ( ! empty( $slide['subtitle'] ) ) : ?>
						<p class="banner-subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
					<?php endif; ?>
					
					<?php if ( ! empty( $slide['info'] ) ) : ?>
						<p class="banner-info"><?php echo esc_html( $slide['info'] ); ?></p>
					<?php endif; ?>
					
					<?php if ( ! empty( $slide['btn1_text'] ) || ! empty( $slide['btn2_text'] ) ) : ?>
						<div class="banner-buttons">
							<?php if ( ! empty( $slide['btn1_text'] ) ) : ?>
								<a href="<?php echo esc_url( $slide['btn1_url'] ); ?>" class="banner-btn-primary">
									<?php echo esc_html( $slide['btn1_text'] ); ?>
									<?php arsenal_icon( 'icon-arrow-banner', 24, 24 ); ?>
								</a>
							<?php endif; ?>
							
							<?php if ( ! empty( $slide['btn2_text'] ) ) : ?>
								<a href="<?php echo esc_url( $slide['btn2_url'] ); ?>" class="banner-btn-secondary">
									<?php echo esc_html( $slide['btn2_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	
	<?php if ( $is_carousel ) : ?>
		<!-- Навигация карусели -->
		<button class="banner-nav-btn banner-nav-prev" aria-label="<?php esc_attr_e( 'Предыдущий слайд', 'arsenal' ); ?>">
			<?php arsenal_icon( 'icon-chevron-left', 24, 24 ); ?>
		</button>
		
		<button class="banner-nav-btn banner-nav-next" aria-label="<?php esc_attr_e( 'Следующий слайд', 'arsenal' ); ?>">
			<?php arsenal_icon( 'icon-chevron-right', 24, 24 ); ?>
		</button>
		
		<!-- Индикаторы (точки) -->
		<div class="banner-dots" role="tablist" aria-label="<?php esc_attr_e( 'Слайды баннера', 'arsenal' ); ?>">
			<?php for ( $i = 0; $i < $slide_count; $i++ ) : ?>
				<button class="banner-dot<?php echo $i === 0 ? ' active' : ''; ?>" 
				        data-slide="<?php echo esc_attr( $i ); ?>"
				        role="tab"
				        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
				        aria-label="<?php printf( esc_attr__( 'Слайд %d из %d', 'arsenal' ), $i + 1, $slide_count ); ?>">
					<span class="banner-dot-progress"></span>
				</button>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
	
</section>
