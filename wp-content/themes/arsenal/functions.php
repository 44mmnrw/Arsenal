<?php
/**
 * Arsenal Theme Functions
 *
 * @package Arsenal
 * @since 1.0.0
 */

// Безопасность: прямой доступ запрещен
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Константы темы
 */
define( 'ARSENAL_VERSION', '1.0.0' );
define( 'ARSENAL_THEME_DIR', get_template_directory() );
define( 'ARSENAL_THEME_URI', get_template_directory_uri() );

/**
 * Подключение Carbon Fields
 */
require_once ARSENAL_THEME_DIR . '/vendor/autoload.php';
\Carbon_Fields\Carbon_Fields::boot();

/**
 * Разбить текст по двоеточию: часть до : жирная, после обычная
 * 
 * @param string $text Текст
 * @return string HTML с bold/normal текстом
 */
function arsenal_format_item_text( $text ) {
	if ( strpos( $text, ':' ) !== false ) {
		$parts = explode( ':', $text, 2 );
		return '<strong>' . esc_html( $parts[0] . ':' ) . '</strong> ' . esc_html( trim( $parts[1] ) );
	}
	return esc_html( $text );
}

/**
 * Вывести иконку из sprite.svg
 * 
 * @param string $icon Имя иконки (например 'cup' для 'icon-cup')
 * @param string $class Дополнительные классы
 * @return string HTML иконки
 */
function arsenal_get_icon( $icon = 'cup', $class = '' ) {
	if ( empty( $icon ) ) {
		$icon = 'cup';
	}
	
	// Удаляем префикс "icon-" если он есть в переданном значении
	$icon = str_replace( 'icon-', '', $icon );
	
	// Формируем ID символа
	$icon_id = 'icon-' . sanitize_html_class( $icon );
	
	// Полный путь до спрайта
	$sprite_url = get_template_directory_uri() . '/assets/images/sprite.svg';
	
	$classes = 'icon ' . esc_attr( $class );
	
	return sprintf(
		'<svg class="%s" aria-hidden="true"><use xlink:href="%s#%s"></use></svg>',
		$classes,
		esc_url( $sprite_url ),
		esc_attr( $icon_id )
	);
}

/**
 * Отключение встроенных стилей WordPress
 * Все стили управляются через наши CSS файлы
 */
add_action( 'wp_enqueue_scripts', function() {
	// Отключить глобальные стили
	wp_dequeue_style( 'global-styles' );
	wp_deregister_style( 'global-styles' );
	
	// Отключить встроенные стили блоков
	wp_dequeue_style( 'wp-block-library' );
	wp_deregister_style( 'wp-block-library' );
}, 100 );

// Отключить inline глобальные стили в footer
remove_action( 'wp_footer', 'wp_print_global_styles' );
remove_action( 'wp_footer', 'wp_print_global_styles_wrapper' );

// Отключить в редакторе админ-панели
add_action( 'admin_init', function() {
	wp_dequeue_style( 'global-styles' );
	wp_deregister_style( 'global-styles' );
}, 100 );

/**
 * Отключение Гутенберга (блочного редактора)
 * Используется классический редактор с собственными стилями
 */
add_filter( 'use_block_editor_for_post_type', '__return_false' );
add_filter( 'use_block_editor_for_post', '__return_false' );
remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );

/**
 * Оптимизация производительности для страницы academy recruitment
 */
add_action( 'wp_head', function() {
	if ( is_page_template( 'templates/page-academy-recruitment.php' ) ) {
		// Inline критический CSS для hero секции
		echo '<style>
			.page-academy-recruitment .academy-hero {
				background: linear-gradient(135deg, #900 0%, #cc0000 100%);
				border-radius: 10px;
				padding: 48px;
				margin-bottom: 48px;
				color: #fff;
			}
			.page-academy-recruitment .hero-title {
				font-size: 48px;
				font-weight: 700;
				line-height: 1.2;
				margin-bottom: 24px;
				color: #fff;
			}
			.page-academy-recruitment .hero-description {
				font-size: 20px;
				line-height: 1.6;
				margin-bottom: 32px;
				color: #fff;
				max-width: 100%;
			}
		</style>';
		
		// Preload критических шрифтов
		echo '<link rel="preload" as="font" href="' . get_template_directory_uri() . '/assets/fonts/Inter-Regular.woff2" type="font/woff2" crossorigin>';
		echo '<link rel="preload" as="font" href="' . get_template_directory_uri() . '/assets/fonts/Inter-Bold.woff2" type="font/woff2" crossorigin>';
		
		// DNS prefetch для CDN
		echo '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">';
	}
}, 1 );

/**
 * Отложить загрузку non-critical CSS
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_page_template( 'templates/page-academy-recruitment.php' ) ) {
		// Отложить загрузку page-academy-recruitment.css используя media="print" трюк
		// После загрузки страницы JavaScript переключит media на "all"
		wp_enqueue_style(
			'arsenal-academy-recruitment',
			get_template_directory_uri() . '/assets/css/pages/page-academy-recruitment.css',
			array( 'arsenal-footer' ),
			'1.0.0'
		);
		
		// Добавить скрипт для загрузки стилей асинхронно
		echo '<script>
			var link = document.querySelector("link[data-lazy-css]");
			if ( link && link.media === "print" ) {
				link.media = "all";
				link.onload = function() { this.onload = null; };
			}
		</script>';
	}
}, 999 );

/**
 * Подключение Leaflet для интерактивных карт
 */
add_action( 'wp_enqueue_scripts', function() {
	// Проверяем, находимся ли на странице academy recruitment
	if ( is_page_template( 'templates/page-academy-recruitment.php' ) ) {
		// Leaflet CSS
		wp_enqueue_style( 'leaflet-css', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css', array(), '1.9.4' );
		
		// Leaflet JS с defer для неблокирующей загрузки
		wp_enqueue_script( 'leaflet-js', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js', array(), '1.9.4', true );
		
		// Добавить defer атрибут к Leaflet скрипту
		add_filter( 'script_loader_tag', function( $tag, $handle ) {
			if ( 'leaflet-js' === $handle ) {
				return str_replace( ' src=', ' defer src=', $tag );
			}
			return $tag;
		}, 10, 2 );
	}
} );

/**
 * Подключение файлов темы
 */
// require_once ARSENAL_THEME_DIR . '/inc/image-placeholders.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-manager.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-players.php';
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-sponsors.php';

/**
 * Паттерны отключены - используется классический редактор
 */

/**
 * Настройка темы
 */
if ( ! function_exists( 'arsenal_setup' ) ) {
	function arsenal_setup() {
		// Поддержка заголовка документа
		add_theme_support( 'title-tag' );

		// Поддержка миниатюр записей
		add_theme_support( 'post-thumbnails' );

		// Поддержка HTML5
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		) );

		// Отключаем блочный редактор (Gutenberg) везде - используем классический редактор
		add_filter( 'use_block_editor_for_post_type', function() {
			return false;
		}, 10, 2 );

		// Поддержка пользовательского логотипа
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		// Поддержка автоматических RSS ссылок
		add_theme_support( 'automatic-feed-links' );

		// Регистрация меню навигации
		register_nav_menus( array(
		'primary' => __( 'Главное меню', 'arsenal' ),
		) );

		// Локализация темы
		load_theme_textdomain( 'arsenal', ARSENAL_THEME_DIR . '/languages' );
	}
}
add_action( 'after_setup_theme', 'arsenal_setup' );

/**
 * Отключение Gutenberg editor и его скриптов
 */
function arsenal_disable_gutenberg() {
	// Отключаем стили Gutenberg
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	
	// Отключаем скрипты
	wp_dequeue_script( 'wp-embed' );
	wp_dequeue_script( 'wp-editor' );
	wp_dequeue_script( 'edit-widgets' );
}
add_action( 'wp_enqueue_scripts', 'arsenal_disable_gutenberg' );
add_action( 'admin_enqueue_scripts', 'arsenal_disable_gutenberg' );

/**
 * Подключение стилей и скриптов
 */
if ( ! function_exists( 'arsenal_enqueue_scripts' ) ) {
	function arsenal_enqueue_scripts() {
		// Шрифты
		wp_enqueue_style(
			'arsenal-fonts',
			ARSENAL_THEME_URI . '/assets/css/fonts.css',
			array(),
			ARSENAL_VERSION
		);

		// Основной стиль темы (base)
		wp_enqueue_style(
			'arsenal-style',
			get_stylesheet_uri(),
			array( 'arsenal-fonts' ),
			ARSENAL_VERSION
		);

		// Стили шапки
		wp_enqueue_style(
			'arsenal-header',
			ARSENAL_THEME_URI . '/assets/css/header.css',
			array( 'arsenal-style' ),
			ARSENAL_VERSION
		);

		// Main стили (banner, stats-bar, upcoming-match и др.)
		wp_enqueue_style(
			'arsenal-main',
			ARSENAL_THEME_URI . '/assets/css/main.css',
			array( 'arsenal-header' ),
			ARSENAL_VERSION
		);

		// Стили футера
		wp_enqueue_style(
			'arsenal-footer',
			ARSENAL_THEME_URI . '/assets/css/footer.css',
			array( 'arsenal-main' ),
			ARSENAL_VERSION
		);

		// Стили страницы статистики игрока (только для шаблона Статистика игрока)
		if ( get_query_var( 'player_id' ) ) {
			wp_enqueue_style(
				'arsenal-player-page',
				ARSENAL_THEME_URI . '/assets/css/pages/page-player.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы календаря (только для страницы календаря)
		if ( is_page_template( 'templates/page-calendar-full.php' ) || ( function_exists( 'get_page_by_path' ) && is_page( 'calendar' ) ) ) {
			wp_enqueue_style(
				'arsenal-calendar',
				ARSENAL_THEME_URI . '/assets/css/pages/page-calendar-full.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

	// Стили страницы новостей (только для страницы Новости)
	if ( is_page_template( 'templates/page-news.php' ) || is_home() || is_archive() || is_single() ) {
		wp_enqueue_style(
			'arsenal-news-page',
			ARSENAL_THEME_URI . '/assets/css/pages/page-news.css',
			array( 'arsenal-footer' ),
			ARSENAL_VERSION
		);
	}

	// Стили страницы команды (только для страницы Команда)
	if ( is_page_template( 'page-squad-grid.php' ) || is_page_template( 'templates/page-squad-grid.php' ) || ( function_exists( 'get_page_by_path' ) && is_page( 'squad' ) ) || ( function_exists( 'get_page_by_path' ) && is_page( 'team' ) ) || is_page( 'main-squad' ) ) {
		wp_enqueue_style(
			'arsenal-players-grid',
			ARSENAL_THEME_URI . '/assets/css/pages/page-squad-grid.css',
			array( 'arsenal-footer' ),
			ARSENAL_VERSION
		);
	}

	// Стили страницы тренерского штаба (для страницы Тренеры)
	if ( is_page_template( 'templates/page-coaches-grid.php' ) || ( function_exists( 'get_page_by_path' ) && is_page( 'coaches' ) ) || ( function_exists( 'get_page_by_path' ) && is_page( 'тренеры' ) ) ) {
		wp_enqueue_style(
			'arsenal-staff-grid',
			ARSENAL_THEME_URI . '/assets/css/pages/page-staff-grid.css',
			array( 'arsenal-footer' ),
			ARSENAL_VERSION
		);
	}

	// Стили страницы управления клубом (для страницы Руководство)
	if ( is_page_template( 'templates/page-management.php' ) || ( function_exists( 'get_page_by_path' ) && is_page( 'management' ) ) || ( function_exists( 'get_page_by_path' ) && is_page( 'administration' ) ) || ( function_exists( 'get_page_by_path' ) && is_page( 'руководство' ) ) ) {
		wp_enqueue_style(
			'arsenal-management',
			ARSENAL_THEME_URI . '/assets/css/pages/page-management.css',
			array( 'arsenal-footer' ),
			ARSENAL_VERSION
		);
	}

		// Стили страницы турнирной таблицы (только для страницы Standings)
		if ( is_page_template( 'templates/page-standings.php' ) || ( function_exists( 'get_page_by_path' ) && is_page( 'standings' ) ) ) {
			wp_enqueue_style(
				'arsenal-standings',
				ARSENAL_THEME_URI . '/assets/css/pages/page-standings.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы матча
		if ( is_page_template( 'dynamic-pages/page-match.php' ) || ( get_query_var( 'match_date' ) && get_query_var( 'team_id' ) ) ) {
			wp_enqueue_style(
				'arsenal-page-match',
				ARSENAL_THEME_URI . '/assets/css/pages/page-match.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы История клуба (для HTML версии)
		if ( is_page( 'history' ) || is_page( 'история' ) ) {
			wp_enqueue_style(
				'arsenal-page-history',
				ARSENAL_THEME_URI . '/assets/css/pages/page-history.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы Спонсоры и партнеры
		if ( is_page_template( 'templates/page-sponsors.php' ) || is_page( 'sponsors' ) || is_page( 'спонсоры' ) ) {
			wp_enqueue_style(
				'arsenal-page-sponsors',
				ARSENAL_THEME_URI . '/assets/css/pages/page-sponsors.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы Руководство
		if ( is_page_template( 'templates/page-management.php' ) || is_page( 'management' ) || is_page( 'руководство' ) ) {
			wp_enqueue_style(
				'arsenal-page-management',
				ARSENAL_THEME_URI . '/assets/css/pages/page-management.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы Стадион
		if ( is_page_template( 'templates/page-stadium.php' ) || is_page( 'stadium' ) || is_page( 'стадион' ) ) {
			wp_enqueue_style(
				'arsenal-page-stadium',
				ARSENAL_THEME_URI . '/assets/css/pages/page-stadium.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы персонала (тренер, стафф)
		// ВАЖНО: страница может быть динамической (/staff/{id}/), тогда is_page_template() не сработает.
		if ( get_query_var( 'staff_id' ) || is_page_template( 'dynamic-pages/page-staff.php' ) || is_page( 'staff' ) || is_page( 'персонал' ) ) {
			wp_enqueue_style(
				'arsenal-page-staff',
				ARSENAL_THEME_URI . '/assets/css/pages/page-staff.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы Набор в академию
		if ( is_page_template( 'templates/page-academy-recruitment.php' ) || is_page( 'academy-recruitment' ) ) {
			wp_enqueue_style(
				'arsenal-academy-recruitment',
				ARSENAL_THEME_URI . '/assets/css/pages/page-academy-recruitment.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы История
		if ( is_page_template( 'templates/page-history.php' ) || is_page( 'history' ) || is_page( 'история' ) ) {
			wp_enqueue_style(
				'arsenal-page-history',
				ARSENAL_THEME_URI . '/assets/css/page-history.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы Набор в академию
		if ( is_page_template( 'templates/page-academy-recruitment.php' ) || is_page( 'academy-recruitment' ) || is_page( 'набор-в-академию' ) ) {
			wp_enqueue_style(
				'arsenal-page-academy-recruitment',
				ARSENAL_THEME_URI . '/assets/css/pages/page-academy-recruitment.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили страницы 404
		if ( is_404() ) {
			wp_enqueue_style(
				'arsenal-page-404',
				ARSENAL_THEME_URI . '/assets/css/pages/page-404.css',
				array( 'arsenal-footer' ),
				ARSENAL_VERSION
			);
		}

		// Стили для заглушек изображений
		wp_enqueue_style(
			'arsenal-image-placeholders',
			ARSENAL_THEME_URI . '/assets/css/image-placeholders.css',
			array( 'arsenal-footer' ),
			ARSENAL_VERSION
		);

		// Скрипт карусели баннера
		wp_enqueue_script(
			'arsenal-banner-carousel',
			ARSENAL_THEME_URI . '/assets/js/banner-carousel.js',
			array(),
			ARSENAL_VERSION,
			true
		);

		// Скрипт карусели спонсоров
		wp_enqueue_script(
			'arsenal-sponsors-carousel',
			ARSENAL_THEME_URI . '/assets/js/sponsors-carousel.js',
			array(),
			ARSENAL_VERSION,
			true
		);

		// Основной скрипт темы
		wp_enqueue_script(
			'arsenal-script',
			ARSENAL_THEME_URI . '/assets/js/main.js',
			array( 'jquery' ),
			ARSENAL_VERSION,
			true
		);

		// Скрипт для заглушек изображений
		wp_enqueue_script(
			'arsenal-image-placeholders',
			ARSENAL_THEME_URI . '/assets/js/image-placeholders.js',
			array(),
			ARSENAL_VERSION,
			true
		);

		// Lottie player для анимированных иконок
		wp_enqueue_script(
			'lottie-player',
			'https://unpkg.com/@lottiefiles/lottie-player@latest',
			array(),
			'1.0',
			true
		);

		// Контроль интервала проигрывания Lottie анимаций
		wp_enqueue_script(
			'lottie-player-interval',
			ARSENAL_THEME_URI . '/assets/js/lottie-player-interval.js',
			array( 'lottie-player' ),
			ARSENAL_VERSION,
			true
		);

		// Динамический расчет расстояния между точками timeline
		wp_enqueue_script(
			'timeline-dynamic',
			ARSENAL_THEME_URI . '/assets/js/timeline-dynamic.js',
			array(),
			ARSENAL_VERSION,
			true
		);

		// Обработчик hover на timeline точках
		wp_enqueue_script(
			'arsenal-timeline',
			ARSENAL_THEME_URI . '/assets/js/timeline.js',
			array(),
			ARSENAL_VERSION,
			true
		);

		// Передача данных в JavaScript
		wp_localize_script( 'arsenal-script', 'arsenalData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'arsenal-nonce' ),
		) );

		// Комментарии
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'arsenal_enqueue_scripts' );

/**
 * Регистрация виджетов
 */
if ( ! function_exists( 'arsenal_widgets_init' ) ) {
	function arsenal_widgets_init() {
		register_sidebar( array(
			'name'          => __( 'Футер 1', 'arsenal' ),
			'id'            => 'footer-1',
			'description'   => __( 'Первая колонка футера', 'arsenal' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => __( 'Футер 2', 'arsenal' ),
			'id'            => 'footer-2',
			'description'   => __( 'Вторая колонка футера', 'arsenal' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => __( 'Футер 3', 'arsenal' ),
			'id'            => 'footer-3',
			'description'   => __( 'Третья колонка футера', 'arsenal' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}
}
add_action( 'widgets_init', 'arsenal_widgets_init' );

/**
 * Настройка размеров изображений
 */
add_image_size( 'arsenal-featured', 1200, 600, true );
add_image_size( 'arsenal-thumbnail', 400, 300, true );

/**
 * Вывод SVG иконки из спрайта
 *
 * @param string $icon_id ID иконки в спрайте
 * @param int    $width   Ширина иконки (по умолчанию 20)
 * @param int    $height  Высота иконки (по умолчанию = ширине)
 * @param string $class   Дополнительный CSS класс
 * @return void
 */
if ( ! function_exists( 'arsenal_icon' ) ) {
	function arsenal_icon( $icon_id, $width = 20, $height = null, $class = '' ) {
		$height = $height ?? $width;
		$class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
		printf(
			'<svg width="%d" height="%d"%s aria-hidden="true"><use href="#%s"></use></svg>',
			absint( $width ),
			absint( $height ),
			$class_attr,
			esc_attr( $icon_id )
		);
	}
}

/**
 * Подключение SVG спрайта в footer (для inline использования)
 */
if ( ! function_exists( 'arsenal_include_svg_sprite' ) ) {
	function arsenal_include_svg_sprite() {
		$sprite_path = ARSENAL_THEME_DIR . '/assets/images/sprite.svg';
		if ( file_exists( $sprite_path ) ) {
			echo '<div style="display:none;">';
			include $sprite_path;
			echo '</div>';
		}
	}
}
add_action( 'wp_footer', 'arsenal_include_svg_sprite' );

/**
 * Создание обязательных страниц при активации темы
 */
if ( ! function_exists( 'arsenal_create_required_pages' ) ) {
	function arsenal_create_required_pages() {
		$required_pages = array(
			'squad' => array(
				'title'    => 'Основной состав',
				'slug'     => 'squad',
				'content'  => '',
				'template' => 'templates/page-squad-grid.php',
			),
			'calendar' => array(
				'title'    => 'Календарь',
				'slug'     => 'calendar',
				'content'  => '',
				'template' => 'templates/page-calendar-full.php',
			),
			'tournament' => array(
				'title'    => 'Турнирная сетка',
				'slug'     => 'tournament',
				'content'  => '',
				'template' => 'templates/page-tournament.php',
			),
			'standings' => array(
				'title'    => 'Турнирная таблица',
				'slug'     => 'standings',
				'content'  => '',
				'template' => 'templates/page-standings.php',
			),
			'team' => array(
				'title'    => 'Команда',
				'slug'     => 'team',
				'content'  => '',
				'template' => '',
			),
			'club' => array(
				'title'    => 'Клуб',
				'slug'     => 'club',
				'content'  => '',
				'template' => '',
			),
			'seconfd-squad' => array(
				'title'    => 'Дубль',
				'slug'     => 'seconfd-squad',
				'content'  => '',
				'template' => '',
			),
			'match' => array(
				'title'    => 'Матчи',
				'slug'     => 'match',
				'content'  => '',
				'template' => '',
			),
			'news' => array(
				'title'    => 'Новости',
				'slug'     => 'news',
				'content'  => '',
				'template' => '',
			),
			'history' => array(
				'title'    => 'История клуба',
				'slug'     => 'history',
				'content'  => '',
				'template' => '',
			),
			'management' => array(
				'title'    => 'Руководство',
				'slug'     => 'management',
				'content'  => '',
				'template' => '',
			),
		);

		foreach ( $required_pages as $page_data ) {
			// Проверяем, существует ли страница по слагу
			$page = get_page_by_path( $page_data['slug'] );
			
			// Подготавливаем данные для вставки
			$post_data = array(
				'post_title'   => $page_data['title'],
				'post_name'    => $page_data['slug'],
				'post_content' => isset( $page_data['content'] ) ? $page_data['content'] : '',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			);
			
			if ( $page ) {
				// Обновляем существующую страницу
				$post_data['ID'] = $page->ID;
				wp_update_post( $post_data );
				$page_id = $page->ID;
			} else {
				// Создаём новую страницу
				$page_id = wp_insert_post( $post_data );
			}
			
			// Обновляем шаблон страницы
			if ( $page_id && ! is_wp_error( $page_id ) ) {
				if ( ! empty( $page_data['template'] ) ) {
					update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
				} else {
					delete_post_meta( $page_id, '_wp_page_template' );
				}
			}
		}
	}
}
add_action( 'after_switch_theme', 'arsenal_create_required_pages' );

/**
 * Создать главное меню и добавить туда все необходимые пункты
 */
if ( ! function_exists( 'arsenal_create_main_menu' ) ) {
	function arsenal_create_main_menu() {
		// Создаём или получаем меню "Главное меню"
		$menu_name = 'Главное меню';
		$menu_exists = wp_get_nav_menu_object( $menu_name );

		if ( ! $menu_exists ) {
			$menu_id = wp_create_nav_menu( $menu_name );
		} else {
			$menu_id = $menu_exists->term_id;
			
			// Очищаем существующие пункты меню
			$existing_items = wp_get_nav_menu_items( $menu_id );
			if ( $existing_items ) {
				foreach ( $existing_items as $item ) {
					wp_delete_post( $item->ID, true );
				}
			}
		}

		if ( ! is_wp_error( $menu_id ) && $menu_id ) {
			// Пункты меню с их порядком
			$menu_items = array(
				array(
					'title' => 'Основной состав',
					'slug'  => 'squad',
					'order' => 1,
				),
				array(
					'title' => 'Календарь',
					'slug'  => 'calendar',
					'order' => 2,
				),
				array(
					'title' => 'Турнирная сетка',
					'slug'  => 'tournament',
					'order' => 3,
				),
				array(
					'title' => 'Турнирная таблица',
					'slug'  => 'standings',
					'order' => 4,
				),
				array(
					'title' => 'Матчи',
					'slug'  => 'match',
					'order' => 5,
				),
				array(
					'title' => 'Команда',
					'slug'  => 'team',
					'order' => 6,
				),
				array(
					'title' => 'Клуб',
					'slug'  => 'club',
					'order' => 7,
				),
				array(
					'title' => 'Дубль',
					'slug'  => 'seconfd-squad',
					'order' => 8,
				),
				array(
					'title' => 'Новости',
					'slug'  => 'news',
					'order' => 9,
				),
				array(
					'title' => 'История клуба',
					'slug'  => 'history',
					'order' => 10,
				),
				array(
					'title' => 'Руководство',
					'slug'  => 'management',
					'order' => 11,
				),
			);
			
			// Добавляем новые пункты меню
			foreach ( $menu_items as $item ) {
				$page = get_page_by_path( $item['slug'] );

				if ( $page ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-title'      => $item['title'],
							'menu-item-object'     => 'page',
							'menu-item-object-id'  => $page->ID,
							'menu-item-type'       => 'post_type',
							'menu-item-status'     => 'publish',
							'menu-item-position'   => $item['order'],
						)
					);
				}
			}

			// Назначаем меню месту в теме
			set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id ) );
		}
	}
}
add_action( 'after_switch_theme', 'arsenal_create_main_menu' );

/**
 * Получить URL логотипа команды из базы данных
 * 
 * @param string $team_name Название команды
 * @return string URL логотипа или placeholder
 */
if ( ! function_exists( 'arsenal_get_team_logo_url' ) ) {
	function arsenal_get_team_logo_url( $team_name ) {
		global $wpdb;
		
		if ( empty( $team_name ) ) {
			return get_template_directory_uri() . '/assets/images/opponent-placeholder.png';
		}
		
		// Проверяем, это наша команда Арсенал?
		if ( stripos( $team_name, 'Арсенал' ) !== false ) {
			// Поиск по названию
			$logo_url = $wpdb->get_var( $wpdb->prepare(
				"SELECT logo_url FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE %s AND logo_url IS NOT NULL AND logo_url != '' LIMIT 1",
				'%Арсенал%'
			) );
			
			if ( $logo_url ) {
				return arsenal_convert_logo_url( $logo_url );
			}
			
			// Альтернативный поиск по названию
			$logo_url = $wpdb->get_var( $wpdb->prepare(
				"SELECT logo_url FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE %s AND logo_url IS NOT NULL AND logo_url != '' LIMIT 1",
				'%Арсенал%'
			) );
			
			if ( $logo_url ) {
				return arsenal_convert_logo_url( $logo_url );
			}
			
			// Fallback на локальный логотип
			$default_logo = get_template_directory_uri() . '/assets/images/logo-small.png';
			
			// Если файл не существует, используем placeholder
			if ( ! file_exists( get_template_directory() . '/assets/images/logo-small.png' ) ) {
				$default_logo = get_template_directory_uri() . '/assets/images/opponent-placeholder.png';
			}
			
			return $default_logo;
		}
		
		// Ищем логотип по названию команды
		$logo_url = $wpdb->get_var( $wpdb->prepare(
			"SELECT logo_url FROM {$wpdb->prefix}arsenal_teams WHERE name = %s AND logo_url IS NOT NULL AND logo_url != '' LIMIT 1",
			$team_name
		) );
		
		if ( $logo_url ) {
			return arsenal_convert_logo_url( $logo_url );
		}
		
		// Если не найден точный match, пробуем LIKE
		$logo_url = $wpdb->get_var( $wpdb->prepare(
			"SELECT logo_url FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE %s AND logo_url IS NOT NULL AND logo_url != '' LIMIT 1",
			'%' . $wpdb->esc_like( $team_name ) . '%'
		) );
		
		if ( $logo_url ) {
			return arsenal_convert_logo_url( $logo_url );
		}
		
		// Fallback на placeholder
		return get_template_directory_uri() . '/assets/images/opponent-placeholder.png';
	}
}

/**
 * Выводит логотип команды с заглушкой (анимированная камера)
 * 
 * @param string $team_name Название команды
 * @param array  $attrs     Дополнительные атрибуты img/lottie-player
 */
if ( ! function_exists( 'arsenal_render_team_logo' ) ) {
	function arsenal_render_team_logo( $team_name, $attrs = array() ) {
		global $wpdb;
		
		$default_attrs = array(
			'alt'     => esc_attr( $team_name ),
			'class'   => 'team-logo-img',
			'loading' => 'lazy',
		);
		$attrs = wp_parse_args( $attrs, $default_attrs );
		
		// Ищем логотип в БД (не используем функцию, чтобы избежать fallback)
		$logo_url = $wpdb->get_var( $wpdb->prepare(
			"SELECT logo_url FROM {$wpdb->prefix}arsenal_teams 
			 WHERE (name = %s OR name LIKE %s) 
			 AND logo_url IS NOT NULL 
			 AND logo_url != '' 
			 AND logo_url NOT LIKE '%placeholder%'
			 LIMIT 1",
			$team_name,
			'%' . $wpdb->esc_like( $team_name ) . '%'
		) );
		
		if ( ! empty( $logo_url ) ) {
			// Конвертируем URL
			$logo_url = arsenal_convert_logo_url( $logo_url );
			// Выводим картинку
			echo '<img src="' . esc_url( $logo_url ) . '"';
			foreach ( $attrs as $key => $value ) {
				echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
			echo '>';
		} else {
			// Выводим заглушку с анимацией (нет картинки в БД)
			echo '<lottie-player src="' . esc_url( get_template_directory_uri() . '/assets/animations/wired-outline-61-camera-hover-flash.json' ) . '" background="transparent" style="width: 100%; height: 100%; min-height: 60px;"></lottie-player>';
		}
	}
}

/**
 * Конвертирует относительный или абсолютный URL логотипа в правильный формат для HTML
 * Если URL относительный (/wp-content/...), добавляет home_url()
 * Если URL абсолютный или сам по себе, возвращает esc_url()
 *
 * @param string $logo_url URL логотипа из БД
 * @return string Экранированный URL для использования в HTML атрибутах
 */
if ( ! function_exists( 'arsenal_convert_logo_url' ) ) {
	function arsenal_convert_logo_url( $logo_url ) {
		// Если URL пустой, возвращаем пустую строку
		if ( empty( $logo_url ) ) {
			return '';
		}
		
		// Если URL уже абсолютный (начинается с http:// или https://), возвращаем как есть
		if ( $logo_url && ( strpos( $logo_url, 'http://' ) === 0 || strpos( $logo_url, 'https://' ) === 0 ) ) {
			return esc_url( $logo_url );
		}
		
		// Если URL относительный (начинается с /), добавляем home_url()
		if ( $logo_url && strpos( $logo_url, '/' ) === 0 ) {
			return esc_url( home_url( $logo_url ) );
		}
		
		// Если URL не начинается с / или http, добавляем / и затем home_url()
		return esc_url( home_url( '/' . $logo_url ) );
	}
}

/**
 * Получить URL страницы статистики игрока
 *
 * @param int $player_id ID игрока
 * @return string URL страницы статистики
 */
if ( ! function_exists( 'arsenal_get_player_stats_url' ) ) {
	function arsenal_get_player_stats_url( $player_id ) {
		// Ищем страницу с шаблоном "Статистика игрока"
		$page = get_posts( array(
			'post_type'      => 'page',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'dynamic-pages/page-player.php',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		) );
		
		if ( ! empty( $page ) ) {
			$base_url = get_permalink( $page[0]->ID );
			return add_query_arg( 'player_id', $player_id, $base_url );
		}
		
		// Fallback: если страница не найдена, возвращаем URL с параметром
		return add_query_arg( 'player_id', $player_id, home_url( '/' ) );
	}
}

/**
 * Динамические URL для страниц игроков
 */

// Регистрация rewrite rule для URL вида /player/ID/
// ID может быть буквенно-цифровым (например: 2C3F29F0)
if ( ! function_exists( 'arsenal_player_rewrite_rules' ) ) {
	function arsenal_player_rewrite_rules() {
		add_rewrite_rule(
			'^player/([a-zA-Z0-9]+)/?$',
			'index.php?player_id=$matches[1]',
			'top'
		);
	}
}
add_action( 'init', 'arsenal_player_rewrite_rules' );

// Регистрация query var для player_id
if ( ! function_exists( 'arsenal_player_query_vars' ) ) {
	function arsenal_player_query_vars( $vars ) {
		$vars[] = 'player_id';
		return $vars;
	}
}
add_filter( 'query_vars', 'arsenal_player_query_vars' );

// Загрузка шаблона для страницы игрока
if ( ! function_exists( 'arsenal_player_template_include' ) ) {
	function arsenal_player_template_include( $template ) {
		$player_id = get_query_var( 'player_id' );
		
		if ( $player_id ) {
			$player_template = ARSENAL_THEME_DIR . '/dynamic-pages/page-player.php';
			
			if ( file_exists( $player_template ) ) {
				return $player_template;
			}
		}
		
		return $template;
	}
}
add_filter( 'template_include', 'arsenal_player_template_include' );

// Получение URL игрока (поддерживает как числовые ID, так и HEX ID)
if ( ! function_exists( 'arsenal_get_player_url' ) ) {
	function arsenal_get_player_url( $player_id ) {
		// Не используем intval() т.к. ID могут быть в формате HEX (2C3F29F0)
		// Просто santitize и передаем как есть
		$safe_id = sanitize_text_field( $player_id );
		return home_url( '/player/' . $safe_id . '/' );
	}
}

/**
 * ===== ДИНАМИЧЕСКИЕ URL ДЛЯ СОТРУДНИКОВ (STAFF) =====
 * Паттерн: /staff/{ID}/
 */

// Регистрация rewrite rule для URL вида /staff/123/
if ( ! function_exists( 'arsenal_staff_rewrite_rules' ) ) {
	function arsenal_staff_rewrite_rules() {
		add_rewrite_rule(
			'^staff/([0-9]+)/?$',
			'index.php?staff_id=$matches[1]',
			'top'
		);
	}
}
add_action( 'init', 'arsenal_staff_rewrite_rules' );

// Регистрация query var для staff_id
if ( ! function_exists( 'arsenal_staff_query_vars' ) ) {
	function arsenal_staff_query_vars( $vars ) {
		$vars[] = 'staff_id';
		return $vars;
	}
}
add_filter( 'query_vars', 'arsenal_staff_query_vars' );

// Загрузка шаблона для страницы сотрудника
if ( ! function_exists( 'arsenal_staff_template_include' ) ) {
	function arsenal_staff_template_include( $template ) {
		$staff_id = get_query_var( 'staff_id' );

		if ( $staff_id ) {
			$staff_template = ARSENAL_THEME_DIR . '/dynamic-pages/page-staff.php';

			if ( file_exists( $staff_template ) ) {
				return $staff_template;
			}
		}

		return $template;
	}
}
add_filter( 'template_include', 'arsenal_staff_template_include' );

// Получение URL сотрудника
if ( ! function_exists( 'arsenal_get_staff_url' ) ) {
	function arsenal_get_staff_url( $staff_id ) {
		$safe_id = absint( $staff_id );
		return home_url( '/staff/' . $safe_id . '/' );
	}
}

/**
 * Однократный flush rewrite rules после обновлений темы.
 * Нужен, чтобы новые правила (например staff) начали работать без ручного сохранения пермалинков.
 */
if ( ! function_exists( 'arsenal_maybe_flush_rewrite_rules' ) ) {
	function arsenal_maybe_flush_rewrite_rules() {
		$option_key = 'arsenal_rewrite_rules_flushed_v1';
		if ( get_option( $option_key ) ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( $option_key, 1 );
	}
}
add_action( 'init', 'arsenal_maybe_flush_rewrite_rules', 20 );

/**
 * ===== ДИНАМИЧЕСКИЕ URL ДЛЯ МАТЧЕЙ =====
 * Паттерн: /match/{YYYY-MM-DD}/
 */

// Регистрация rewrite rule для URL вида /match/TEAM_ID/YYYY-MM-DD/
if ( ! function_exists( 'arsenal_match_rewrite_rules' ) ) {
	function arsenal_match_rewrite_rules() {
		add_rewrite_rule(
			'^match/([a-zA-Z0-9]+)/([0-9]{4}-[0-9]{2}-[0-9]{2})/?$',
			'index.php?team_id=$matches[1]&match_date=$matches[2]',
			'top'
		);
	}
}
add_action( 'init', 'arsenal_match_rewrite_rules' );

// Регистрация query var для team_id и match_date
if ( ! function_exists( 'arsenal_match_query_vars' ) ) {
	function arsenal_match_query_vars( $vars ) {
		$vars[] = 'team_id';
		$vars[] = 'match_date';
		return $vars;
	}
}
add_filter( 'query_vars', 'arsenal_match_query_vars' );

// Загрузка шаблона для страницы матча
if ( ! function_exists( 'arsenal_match_template_include' ) ) {
	function arsenal_match_template_include( $template ) {
		$team_id = get_query_var( 'team_id' );
		$match_date = get_query_var( 'match_date' );
		
		if ( $team_id && $match_date ) {
			$match_template = ARSENAL_THEME_DIR . '/dynamic-pages/page-match.php';
			
			if ( file_exists( $match_template ) ) {
				return $match_template;
			}
		}
		
		return $template;
	}
}
add_filter( 'template_include', 'arsenal_match_template_include' );

/**
 * ===== ЕДИНАЯ ФУНКЦИЯ РАСЧЕТА ТУРНИРНОЙ ТАБЛИЦЫ =====
 * Используется во всех template-parts для единообразия расчётов
 * 
 * @param int    $year          Год сезона (по умолчанию текущий год)
 * @param string $tournament_id ID турнира для фильтрации (по умолчанию '71CFDAA6')
 * @return array Отсортированная статистика всех команд
 */
if ( ! function_exists( 'arsenal_calculate_standings' ) ) {
	function arsenal_calculate_standings( $year = null, $tournament_id = '71CFDAA6' ) {
		global $wpdb;
		
		if ( null === $year ) {
			$year = intval( date( 'Y' ) );
		}
		
		// ===== ЭТАП 1: Получаем все команды сезона =====
		$teams = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT t.team_id, t.name, t.logo_url
			 FROM {$wpdb->prefix}arsenal_teams t
			 INNER JOIN {$wpdb->prefix}arsenal_matches m ON (t.team_id = m.home_team_id OR t.team_id = m.away_team_id)
			 WHERE YEAR(m.match_date) = %d 
			 AND m.tournament_id = %s
			 AND (m.status = '0083CE05' OR m.status = 'E229BE8C')
			 AND m.home_score IS NOT NULL 
			 AND m.away_score IS NOT NULL
			 ORDER BY t.name",
			$year, $tournament_id
		) );
		
		// Инициализируем статистику для каждой команды
		$standings = [];
		foreach ( $teams as $team ) {
			$standings[ $team->team_id ] = [
				'team_id'         => $team->team_id,
				'name'            => $team->name,
				'logo_url'        => $team->logo_url,
				'played'          => 0,
				'wins'            => 0,
				'draws'           => 0,
				'losses'          => 0,
				'goals_for'       => 0,
				'goals_against'   => 0,
				'goal_diff'       => 0,
				'points'          => 0,
				'yellow_cards'    => 0,
				'red_cards'       => 0,
			];
		}
		
		// ===== ЭТАП 2: Получаем все матчи сезона =====
		$matches = $wpdb->get_results( $wpdb->prepare(
			"SELECT m.match_id, m.home_team_id, m.away_team_id, m.home_score, m.away_score
			 FROM {$wpdb->prefix}arsenal_matches m
			 WHERE YEAR(m.match_date) = %d 
			 AND m.tournament_id = %s
			 AND (m.status = '0083CE05' OR m.status = 'E229BE8C')
			 AND m.home_score IS NOT NULL 
			 AND m.away_score IS NOT NULL
			 ORDER BY m.match_date ASC",
			$year, $tournament_id
		) );
		
		// ===== ЭТАП 3: Подсчитываем статистику =====
		foreach ( $matches as $match ) {
			$home_id = $match->home_team_id;
			$away_id = $match->away_team_id;
			$home_score = (int) $match->home_score;
			$away_score = (int) $match->away_score;
			
			if ( ! isset( $standings[ $home_id ] ) || ! isset( $standings[ $away_id ] ) ) {
				continue;
			}
			
			// Увеличиваем количество сыгранных матчей
			$standings[ $home_id ]['played']++;
			$standings[ $away_id ]['played']++;
			
			// Подсчитываем забитые и пропущенные голы
			$standings[ $home_id ]['goals_for'] += $home_score;
			$standings[ $home_id ]['goals_against'] += $away_score;
			$standings[ $away_id ]['goals_for'] += $away_score;
			$standings[ $away_id ]['goals_against'] += $home_score;
			
			// Результаты матча и очки
			if ( $home_score > $away_score ) {
				$standings[ $home_id ]['wins']++;
				$standings[ $home_id ]['points'] += 3;
				$standings[ $away_id ]['losses']++;
			} elseif ( $home_score < $away_score ) {
				$standings[ $away_id ]['wins']++;
				$standings[ $away_id ]['points'] += 3;
				$standings[ $home_id ]['losses']++;
			} else {
				$standings[ $home_id ]['draws']++;
				$standings[ $away_id ]['draws']++;
				$standings[ $home_id ]['points']++;
				$standings[ $away_id ]['points']++;
			}
			
			// Подсчитываем карточки в матче
			// Получаем жёлтые карточки для домашней команды
			$home_yellow = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events
			 WHERE match_id = %s AND event_type = %s AND player_id IN (SELECT player_id FROM {$wpdb->prefix}arsenal_match_lineups WHERE match_id = %s AND team_id = %s)",
			$match->match_id, '7B83D3F0', $match->match_id, $match->home_team_id
			) );
			if ( $home_yellow ) {
				$standings[ $home_id ]['yellow_cards'] += (int) $home_yellow;
			}
			
			// Получаем красные карточки для домашней команды
			$home_red = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events
			 WHERE match_id = %s AND event_type = %s AND player_id IN (SELECT player_id FROM {$wpdb->prefix}arsenal_match_lineups WHERE match_id = %s AND team_id = %s)",
			$match->match_id, 'FC171553', $match->match_id, $match->home_team_id
			) );
			if ( $home_red ) {
				$standings[ $home_id ]['red_cards'] += (int) $home_red;
			}
			
			// Получаем жёлтые карточки для гостевой команды
			$away_yellow = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events
			 WHERE match_id = %s AND event_type = %s AND player_id IN (SELECT player_id FROM {$wpdb->prefix}arsenal_match_lineups WHERE match_id = %s AND team_id = %s)",
			$match->match_id, '7B83D3F0', $match->match_id, $match->away_team_id
			) );
			if ( $away_yellow ) {
				$standings[ $away_id ]['yellow_cards'] += (int) $away_yellow;
			}
			
			// Получаем красные карточки для гостевой команды
			$away_red = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events
			 WHERE match_id = %s AND event_type = %s AND player_id IN (SELECT player_id FROM {$wpdb->prefix}arsenal_match_lineups WHERE match_id = %s AND team_id = %s)",
			$match->match_id, 'FC171553', $match->match_id, $match->away_team_id
			) );
			if ( $away_red ) {
				$standings[ $away_id ]['red_cards'] += (int) $away_red;
			}
		}
		
		// ===== ЭТАП 4: Рассчитываем goal_diff для каждой команды =====
		foreach ( $standings as &$team ) {
			$team['goal_diff'] = $team['goals_for'] - $team['goals_against'];
		}
		unset( $team );
		
		// ===== ЭТАП 5: Функция для расчета личных встреч =====
		$get_head_to_head = function( $team1_id, $team2_id ) use ( $wpdb, $year, $tournament_id ) {
			$h2h = $wpdb->get_results( $wpdb->prepare(
				"SELECT m.match_id, m.home_team_id, m.away_team_id, m.home_score, m.away_score
				 FROM {$wpdb->prefix}arsenal_matches m
				 WHERE YEAR(m.match_date) = %d
				 AND m.tournament_id = %s
				 AND m.home_score IS NOT NULL
				 AND m.away_score IS NOT NULL
				 AND (
					(m.home_team_id = %s AND m.away_team_id = %s) OR
					(m.home_team_id = %s AND m.away_team_id = %s)
				 )
				 ORDER BY m.match_date ASC",
				$year, $tournament_id,
				$team1_id, $team2_id,
				$team2_id, $team1_id
			) );
			
			$h2h_stats = [
				'points'         => 0,
				'goals_for'      => 0,
				'goals_against'  => 0,
				'yellow_cards'   => 0,
				'matches'        => 0,
			];
			
			foreach ( $h2h as $match ) {
				if ( $match->home_team_id === $team1_id ) {
					$goals_for = (int) $match->home_score;
					$goals_against = (int) $match->away_score;
				} else {
					$goals_for = (int) $match->away_score;
					$goals_against = (int) $match->home_score;
				}
				
				$h2h_stats['goals_for'] += $goals_for;
				$h2h_stats['goals_against'] += $goals_against;
				$h2h_stats['matches']++;
				
				// Очки в личных встречах
				if ( $goals_for > $goals_against ) {
					$h2h_stats['points'] += 3;
				} elseif ( $goals_for === $goals_against ) {
					$h2h_stats['points']++;
				}
				
				// Желтые карточки в личных встречах
				$yellow_cards = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events
				 WHERE match_id = %s AND player_id IN (SELECT player_id FROM {$wpdb->prefix}arsenal_match_lineups WHERE match_id = %s AND team_id = %s) AND event_type = '7B83D3F0'",
				$match->match_id, $match->match_id, $team1_id
				) );
				$h2h_stats['yellow_cards'] += intval( $yellow_cards );
			}
			
			return $h2h_stats;
		};
		
		// ===== ЭТАП 6: Сортировка со сложными правилами =====
		usort( $standings, function( $team_a, $team_b ) use ( $get_head_to_head ) {
			// 1. Сортируем по очкам (DESC)
			if ( $team_a['points'] !== $team_b['points'] ) {
				return $team_b['points'] - $team_a['points'];
			}
			
			// У команд равные очки - применяем правила tie-breaker
			
			// 2. Личные встречи (head-to-head)
			$h2h_a = $get_head_to_head( $team_a['team_id'], $team_b['team_id'] );
			$h2h_b = $get_head_to_head( $team_b['team_id'], $team_a['team_id'] );
			
			// 2a. Очки в личных встречах
			if ( $h2h_a['points'] !== $h2h_b['points'] ) {
				return $h2h_b['points'] - $h2h_a['points'];
			}
			
			// 2b. Разница мячей в личных встречах
			$h2h_diff_a = $h2h_a['goals_for'] - $h2h_a['goals_against'];
			$h2h_diff_b = $h2h_b['goals_for'] - $h2h_b['goals_against'];
			
			if ( $h2h_diff_a !== $h2h_diff_b ) {
				return $h2h_diff_b - $h2h_diff_a;
			}
			
			// 2c. Желтые карточки в личных встречах (больше карточек = ниже в таблице)
			if ( $h2h_a['yellow_cards'] !== $h2h_b['yellow_cards'] ) {
				return $h2h_a['yellow_cards'] - $h2h_b['yellow_cards'];
			}
			
			// 3. Если и в личных встречах равенство - считаем общую статистику
			
			// 3a. Разница мячей (общая)
			if ( $team_a['goal_diff'] !== $team_b['goal_diff'] ) {
				return $team_b['goal_diff'] - $team_a['goal_diff'];
			}
			
			// 3b. Забитые голы (больше побеждает)
			if ( $team_a['goals_for'] !== $team_b['goals_for'] ) {
				return $team_b['goals_for'] - $team_a['goals_for'];
			}
			
			// 3c. Победы (больше побеждает)
			if ( $team_a['wins'] !== $team_b['wins'] ) {
				return $team_b['wins'] - $team_a['wins'];
			}
			
			// 3d. В крайнем случае - по team_id (для стабильности)
			return strcmp( $team_a['team_id'], $team_b['team_id'] );
		} );
		
		return array_values( $standings );
	}
}

/**
 * Склонение русских слов по числам (глобальные функции для переводов)
 */

/**
 * Склонение слова "год"
 * 
 * @param int $age Возраст или количество лет
 * @return string Строка с числом и правильным окончанием (1 год, 2 года, 5 лет)
 */
if ( ! function_exists( 'arsenal_pluralize_years' ) ) {
	function arsenal_pluralize_years( $age ) {
		$remainder = $age % 10;
		$remainder100 = $age % 100;
		
		// Спецслучай для 11-19
		if ( $remainder100 >= 11 && $remainder100 <= 19 ) {
			return $age . ' лет';
		}
		
		// Для остального
		if ( $remainder == 1 ) {
			return $age . ' год';
		} elseif ( $remainder >= 2 && $remainder <= 4 ) {
			return $age . ' года';
		} else {
			return $age . ' лет';
		}
	}
}

/**
 * Склонение слова "зритель"
 * 
 * @param int $count Количество зрителей
 * @return string Строка с числом и правильным окончанием (1 зритель, 2 зрителя, 5 зрителей)
 */
if ( ! function_exists( 'arsenal_pluralize_spectators' ) ) {
	function arsenal_pluralize_spectators( $count ) {
		$remainder = $count % 10;
		$remainder100 = $count % 100;
		
		// Спецслучай для 11-19
		if ( $remainder100 >= 11 && $remainder100 <= 19 ) {
			return $count . ' зрителей';
		}
		
		// Для остального
		if ( $remainder == 1 ) {
			return $count . ' зритель';
		} elseif ( $remainder >= 2 && $remainder <= 4 ) {
			return $count . ' зрителя';
		} else {
			return $count . ' зрителей';
		}
	}
}

/**
 * Склонение слова "игрок"
 * 
 * @param int $count Количество игроков
 * @return string Форма слова (игрок, игрока, игроков)
 */
if ( ! function_exists( 'arsenal_get_player_form' ) ) {
	function arsenal_get_player_form( $count ) {
		$remainder = $count % 10;
		$remainder100 = $count % 100;
		
		// Спецслучай для 11-19
		if ( $remainder100 >= 11 && $remainder100 <= 19 ) {
			return 'игроков';
		}
		
		// Для остального
		if ( $remainder == 1 ) {
			return 'игрок';
		} elseif ( $remainder >= 2 && $remainder <= 4 ) {
			return 'игрока';
		} else {
			return 'игроков';
		}
	}
}

/**
 * Универсальная функция склонения для любого слова
 * 
 * @param int   $number Количество
 * @param array $forms  Массив из 3 форм: [форма_для_1, форма_для_2-4, форма_для_5+]
 * @return string Строка с числом и правильным окончанием
 * 
 * Примеры:
 * arsenal_pluralize(5, ['год', 'года', 'лет']) => "5 лет"
 * arsenal_pluralize(2, ['гол', 'гола', 'голов']) => "2 гола"
 */
if ( ! function_exists( 'arsenal_pluralize' ) ) {
	function arsenal_pluralize( $number, $forms ) {
		$remainder = $number % 10;
		$remainder100 = $number % 100;
		
		// Спецслучай для 11-19
		if ( $remainder100 >= 11 && $remainder100 <= 19 ) {
			return $number . ' ' . $forms[2];
		}
		
		// Для остального
		if ( $remainder == 1 ) {
			return $number . ' ' . $forms[0];
		} elseif ( $remainder >= 2 && $remainder <= 4 ) {
			return $number . ' ' . $forms[1];
		} else {
			return $number . ' ' . $forms[2];
		}
	}
}

/**
 * Подключение дополнительных файлов
 */
require_once ARSENAL_THEME_DIR . '/inc/functions/template-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/functions/player-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/functions/match-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/functions/timeline-functions.php';
require_once ARSENAL_THEME_DIR . '/inc/customizer.php';
/**
 * Функция расчёта турнирной таблицы по сезону
 * 
 * @param string $season_id ID сезона
 * @param int    $year      Год для отображения
 * @return array Отсортированный массив команд с статистикой
 */
if ( ! function_exists( 'arsenal_get_season_standings' ) ) {
	function arsenal_get_season_standings( $season_id = '5B2ABC0C', $year = 2025 ) {
		global $wpdb;

		// Получаем все команды которые участвовали в матчах сезона
		$query = "SELECT DISTINCT t.id, t.name, t.logo_url, t.team_id
		         FROM {$wpdb->prefix}arsenal_teams t
		         INNER JOIN {$wpdb->prefix}arsenal_match_lineups ml ON ml.team_id = t.team_id
		         INNER JOIN {$wpdb->prefix}arsenal_matches m ON ml.match_id = m.match_id
		         WHERE m.season_id = %s
		         ORDER BY t.name";

		$query = $wpdb->prepare( $query, $season_id );
		$teams = $wpdb->get_results( $query );

		if ( empty( $teams ) ) {
			return array();
		}

		// Инициализируем статистику по ID команды
		$standings = [];
		foreach ( $teams as $team ) {
			$standings[ $team->team_id ] = [
				'id'              => $team->id,
				'team_id'         => $team->team_id,
				'name'            => $team->name,
				'logo_url'        => $team->logo_url,
				'played'          => 0,
				'wins'            => 0,
				'draws'           => 0,
				'losses'          => 0,
				'goals_for'       => 0,
				'goals_against'   => 0,
				'points'          => 0,
				'yellow_cards'    => 0,
			];
		}

		// Получаем все ЗАВЕРШЁННЫЕ матчи сезона
		$matches = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.home_team_id, m.away_team_id, m.home_score, m.away_score
				 FROM {$wpdb->prefix}arsenal_matches m
				 WHERE m.season_id = %s 
				 AND m.status = '0083CE05' 
				 AND m.home_score IS NOT NULL 
				 AND m.away_score IS NOT NULL
				 ORDER BY m.match_date ASC",
				$season_id
			)
		);

		foreach ( $matches as $match ) {
			$home_id = $match->home_team_id;
			$away_id = $match->away_team_id;
			$home_score = intval( $match->home_score );
			$away_score = intval( $match->away_score );
			
			if ( ! isset( $standings[ $home_id ] ) || ! isset( $standings[ $away_id ] ) ) {
				continue;
			}
			
			$standings[ $home_id ]['played']++;
			$standings[ $away_id ]['played']++;
			
			$standings[ $home_id ]['goals_for'] += $home_score;
			$standings[ $home_id ]['goals_against'] += $away_score;
			$standings[ $away_id ]['goals_for'] += $away_score;
			$standings[ $away_id ]['goals_against'] += $home_score;
			
			if ( $home_score > $away_score ) {
				$standings[ $home_id ]['wins']++;
				$standings[ $home_id ]['points'] += 3;
				$standings[ $away_id ]['losses']++;
			} elseif ( $home_score < $away_score ) {
				$standings[ $away_id ]['wins']++;
				$standings[ $away_id ]['points'] += 3;
				$standings[ $home_id ]['losses']++;
			} else {
				$standings[ $home_id ]['draws']++;
				$standings[ $away_id ]['draws']++;
				$standings[ $home_id ]['points']++;
				$standings[ $away_id ]['points']++;
			}
		}

		// Сортируем по очкам + разница мячей
		usort( $standings, function( $a, $b ) {
			if ( $a['points'] !== $b['points'] ) {
				return $b['points'] - $a['points'];
			}
			$diff_a = $a['goals_for'] - $a['goals_against'];
			$diff_b = $b['goals_for'] - $b['goals_against'];
			if ( $diff_a !== $diff_b ) {
				return $diff_b - $diff_a;
			}
			return $b['goals_for'] - $a['goals_for'];
		} );

		return $standings;
	}
}

/**
 * Функция преобразования названий позиций во множественное число
 *
 * @param string $position Название позиции в единственном числе
 * @return string Название позиции во множественном числе
 */
if ( ! function_exists( 'arsenal_pluralize_position' ) ) {
	function arsenal_pluralize_position( $position ) {
		$plurals = array(
			'Вратарь'       => 'Вратари',
			'Защитник'      => 'Защитники',
			'Полузащитник'  => 'Полузащитники',
			'Нападающий'    => 'Нападающие',
		);
		
		return isset( $plurals[ $position ] ) ? $plurals[ $position ] : $position;
	}
}

/**
 * SEO теги для 404 страницы
 * Добавляет robots meta tag и canonical link
 */
add_action( 'wp_head', function() {
	if ( is_404() ) {
		// Запрещаем индексирование 404 страницы
		echo '<meta name="robots" content="noindex, follow">' . "\n";
		// Указываем canonical link на главную страницу
		echo '<link rel="canonical" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
	}
}, 1 );

/**
 * Вывод анимированной иконки камеры для плейсхолдера изображения
 * Используется когда изображение не загружено
 * Автоматически управляется через lottie-player-interval.js
 *
 * @return void
 */
if ( ! function_exists( 'arsenal_render_camera_placeholder' ) ) {
	function arsenal_render_camera_placeholder() {
		$icon_url = ARSENAL_THEME_URI . '/assets/animations/wired-outline-61-camera-hover-flash.json';
		echo '<lottie-player src="' . esc_attr( $icon_url ) . '" background="transparent" style="width: 100%; height: 100%; min-height: 300px;"></lottie-player>';
	}
}

/**
 * Подключение класса Arsenal_Staff_Department_Manager
 * для управления отделами сотрудников
 */
require_once ARSENAL_THEME_DIR . '/inc/classes/class-arsenal-staff-department-manager.php';

/**
 * Подключение метаокса для фильтра отдела на странице сотрудников
 */
require_once ARSENAL_THEME_DIR . '/inc/staff-department-metabox.php';

/**
 * Подключение метаокса для выбора стадиона на странице "page-stadium"
 */
require_once ARSENAL_THEME_DIR . '/inc/stadium-selector-metabox.php';

/**
 * Подключение метаокса для выбора состава на странице "page-squad-grid"
 */
require_once ARSENAL_THEME_DIR . '/inc/squad-selector-metabox.php';

/**
 * Подключение Carbon Fields метабоксов
 */
require_once ARSENAL_THEME_DIR . '/inc/carbon-fields-init.php';

/**
 * Подключение Carbon Fields адаптера для академии
 */
require_once ARSENAL_THEME_DIR . '/inc/class-academy-carbon-adapter.php';
require_once ARSENAL_THEME_DIR . '/inc/class-history-carbon-adapter.php';