<?php
/**
 * Arsenal Theme Customizer
 *
 * Добавляет настройки темы в раздел "Внешний вид → Настроить"
 *
 * @package Arsenal
 * @since 1.0.0
 */

// Безопасность: прямой доступ запрещен
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Регистрация настроек Customizer
 *
 * @param WP_Customize_Manager $wp_customize Объект Customizer.
 */
function arsenal_customize_register( $wp_customize ) {

	/*
	 * ==============================================
	 * Панель: Баннер
	 * ==============================================
	 */
	$wp_customize->add_panel(
		'arsenal_banner_panel',
		array(
			'title'       => __( 'Баннер', 'arsenal' ),
			'description' => __( 'Настройки баннера-карусели на главной странице.', 'arsenal' ),
			'priority'    => 90,
		)
	);

	/*
	 * ==============================================
	 * Секция: Общие настройки баннера
	 * ==============================================
	 */
	$wp_customize->add_section(
		'arsenal_banner_section',
		array(
			'title'       => __( 'Общие настройки', 'arsenal' ),
			'description' => __( 'Основные параметры баннера-карусели.', 'arsenal' ),
			'priority'    => 10,
			'panel'       => 'arsenal_banner_panel',
		)
	);

	// Показать/скрыть баннер
	$wp_customize->add_setting(
		'arsenal_banner_show',
		array(
			'default'           => true,
			'sanitize_callback' => 'arsenal_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_banner_show',
		array(
			'label'   => __( 'Показать баннер', 'arsenal' ),
			'section' => 'arsenal_banner_section',
			'type'    => 'checkbox',
		)
	);

	// Автопроигрывание
	$wp_customize->add_setting(
		'arsenal_banner_autoplay',
		array(
			'default'           => true,
			'sanitize_callback' => 'arsenal_sanitize_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_banner_autoplay',
		array(
			'label'   => __( 'Автоматическая смена слайдов', 'arsenal' ),
			'section' => 'arsenal_banner_section',
			'type'    => 'checkbox',
		)
	);

	// Интервал смены слайдов
	$wp_customize->add_setting(
		'arsenal_banner_interval',
		array(
			'default'           => 5000,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_banner_interval',
		array(
			'label'       => __( 'Интервал смены (мс)', 'arsenal' ),
			'description' => __( '5000 = 5 секунд', 'arsenal' ),
			'section'     => 'arsenal_banner_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 2000,
				'max'  => 15000,
				'step' => 500,
			),
		)
	);

	/*
	 * Слайды карусели (до 5 штук) — каждый со своим контентом
	 */
	$slide_count = 5;

	for ( $i = 1; $i <= $slide_count; $i++ ) {
		
		// Отдельная секция для каждого слайда
		$wp_customize->add_section(
			'arsenal_banner_slide_' . $i . '_section',
			array(
				'title'    => sprintf( __( 'Слайд %d', 'arsenal' ), $i ),
				'priority' => 10 + $i,
				'panel'    => 'arsenal_banner_panel',
			)
		);

		// Изображение слайда
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_image',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'arsenal_banner_slide_' . $i . '_image',
				array(
					'label'       => __( 'Фоновое изображение', 'arsenal' ),
					'description' => __( 'Рекомендуемый размер: 1662x700 пикселей. Если не загружено — слайд не отображается.', 'arsenal' ),
					'section'     => 'arsenal_banner_slide_' . $i . '_section',
				)
			)
		);

		// Метка (красный текст)
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_label',
			array(
				'default'           => ( $i === 1 ) ? 'Добро пожаловать' : '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_label',
			array(
				'label'   => __( 'Метка (красный текст)', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'text',
			)
		);

		// Заголовок
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_title',
			array(
				'default'           => ( $i === 1 ) ? 'ФК АРСЕНАЛ' : '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_title',
			array(
				'label'   => __( 'Заголовок', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'text',
			)
		);

		// Описание
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_subtitle',
			array(
				'default'           => ( $i === 1 ) ? 'Держим вас в курсе последних новостей, результатов матчей и предстоящих игр команды.' : '',
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_subtitle',
			array(
				'label'   => __( 'Описание', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'textarea',
			)
		);

		// Дополнительная информация
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_info',
			array(
				'default'           => ( $i === 1 ) ? 'Регион: Нижегородская обл. Стадион: «Капролактамовец». Страна: Россия.' : '',
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_info',
			array(
				'label'   => __( 'Дополнительная информация', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'textarea',
			)
		);

		// Кнопка 1 — текст
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_btn1_text',
			array(
				'default'           => ( $i === 1 ) ? 'Наша команда' : '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_btn1_text',
			array(
				'label'   => __( 'Кнопка 1 — текст', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'text',
			)
		);

		// Кнопка 1 — ссылка
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_btn1_url',
			array(
				'default'           => '#',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_btn1_url',
			array(
				'label'   => __( 'Кнопка 1 — ссылка', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'url',
			)
		);

		// Кнопка 2 — текст
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_btn2_text',
			array(
				'default'           => ( $i === 1 ) ? 'Календарь матчей' : '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_btn2_text',
			array(
				'label'   => __( 'Кнопка 2 — текст', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'text',
			)
		);

		// Кнопка 2 — ссылка
		$wp_customize->add_setting(
			'arsenal_banner_slide_' . $i . '_btn2_url',
			array(
				'default'           => '#',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'arsenal_banner_slide_' . $i . '_btn2_url',
			array(
				'label'   => __( 'Кнопка 2 — ссылка', 'arsenal' ),
				'section' => 'arsenal_banner_slide_' . $i . '_section',
				'type'    => 'url',
			)
		);
	}

	/*
	 * ==============================================
	 * Секция: Информация о клубе
	 * ==============================================
	 */
	$wp_customize->add_section(
		'arsenal_club_info',
		array(
			'title'       => __( 'Информация о клубе', 'arsenal' ),
			'description' => __( 'Название клуба, адрес и ссылки на социальные сети.', 'arsenal' ),
			'priority'    => 30,
		)
	);

	// Название клуба
	$wp_customize->add_setting(
		'arsenal_club_name',
		array(
			'default'           => 'ФК Арсенал',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_club_name',
		array(
			'label'   => __( 'Название клуба', 'arsenal' ),
			'section' => 'arsenal_club_info',
			'type'    => 'text',
		)
	);

	// Город клуба
	$wp_customize->add_setting(
		'arsenal_club_city',
		array(
			'default'           => 'г. Дзержинск',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_club_city',
		array(
			'label'   => __( 'Город', 'arsenal' ),
			'section' => 'arsenal_club_info',
			'type'    => 'text',
		)
	);

	// Адрес клуба
	$wp_customize->add_setting(
		'arsenal_club_address',
		array(
			'default'           => 'г. Дзержинск, ул. Октябрьская, 2',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_club_address',
		array(
			'label'   => __( 'Адрес клуба', 'arsenal' ),
			'section' => 'arsenal_club_info',
			'type'    => 'text',
		)
	);

	// Facebook
	$wp_customize->add_setting(
		'arsenal_social_facebook',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_social_facebook',
		array(
			'label'       => __( 'Facebook URL', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_club_info',
			'type'        => 'url',
		)
	);

	// Instagram
	$wp_customize->add_setting(
		'arsenal_social_instagram',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_social_instagram',
		array(
			'label'       => __( 'Instagram URL', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_club_info',
			'type'        => 'url',
		)
	);

	// YouTube
	$wp_customize->add_setting(
		'arsenal_social_youtube',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_social_youtube',
		array(
			'label'       => __( 'YouTube URL', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_club_info',
			'type'        => 'url',
		)
	);

	// Telegram
	$wp_customize->add_setting(
		'arsenal_social_telegram',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_social_telegram',
		array(
			'label'       => __( 'Telegram URL', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_club_info',
			'type'        => 'url',
		)
	);

	// VK
	$wp_customize->add_setting(
		'arsenal_social_vk',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_social_vk',
		array(
			'label'       => __( 'VK URL', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_club_info',
			'type'        => 'url',
		)
	);

	/*
	 * ==============================================
	 * Секция: Контакты в футере
	 * ==============================================
	 */
	$wp_customize->add_section(
		'arsenal_contacts',
		array(
			'title'       => __( 'Контакты', 'arsenal' ),
			'description' => __( 'Контактная информация в футере сайта.', 'arsenal' ),
			'priority'    => 35,
		)
	);

	// Название стадиона
	$wp_customize->add_setting(
		'arsenal_contact_stadium',
		array(
			'default'           => 'Стадион "Арсенал"',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_contact_stadium',
		array(
			'label'    => __( 'Название стадиона', 'arsenal' ),
			'section'  => 'arsenal_contacts',
			'type'     => 'text',
			'priority' => 10,
		)
	);

	// Адрес
	$wp_customize->add_setting(
		'arsenal_contact_address',
		array(
			'default'           => 'г. Дзержинск, ул. Спортивная, 1',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_contact_address',
		array(
			'label'    => __( 'Адрес стадиона', 'arsenal' ),
			'section'  => 'arsenal_contacts',
			'type'     => 'text',
			'priority' => 20,
		)
	);

	// Телефон 1
	$wp_customize->add_setting(
		'arsenal_contact_phone1',
		array(
			'default'           => '+375 (17) 123-45-67',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_contact_phone1',
		array(
			'label'    => __( 'Телефон 1', 'arsenal' ),
			'section'  => 'arsenal_contacts',
			'type'     => 'text',
			'priority' => 30,
		)
	);

	// Телефон 2
	$wp_customize->add_setting(
		'arsenal_contact_phone2',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_contact_phone2',
		array(
			'label'       => __( 'Телефон 2', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_contacts',
			'type'        => 'text',
			'priority'    => 40,
		)
	);

	// Email
	$wp_customize->add_setting(
		'arsenal_contact_email',
		array(
			'default'           => 'info@arsenal-dz.by',
			'sanitize_callback' => 'sanitize_email',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_contact_email',
		array(
			'label'    => __( 'Email', 'arsenal' ),
			'section'  => 'arsenal_contacts',
			'type'     => 'email',
			'priority' => 50,
		)
	);

	// Время работы
	$wp_customize->add_setting(
		'arsenal_contact_hours',
		array(
			'default'           => 'Пн-Пт: 9:00 - 18:00',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_contact_hours',
		array(
			'label'    => __( 'Время работы', 'arsenal' ),
			'section'  => 'arsenal_contacts',
			'type'     => 'text',
			'priority' => 60,
		)
	);

	/*
	 * ==============================================
	 * Секция: Меню футера
	 * ==============================================
	 */
	$wp_customize->add_section(
		'arsenal_footer_menu',
		array(
			'title'       => __( 'Меню футера', 'arsenal' ),
			'description' => __( 'Настройка ссылок навигации в футере сайта.', 'arsenal' ),
			'priority'    => 36,
		)
	);

	// Колонка "Клуб" - Заголовок
	$wp_customize->add_setting(
		'arsenal_menu_club_title',
		array(
			'default'           => 'Клуб',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_title',
		array(
			'label'    => __( 'Колонка 1: Заголовок', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 10,
		)
	);

	// Клуб - Ссылка 1: О клубе
	$wp_customize->add_setting(
		'arsenal_menu_club_link1_text',
		array(
			'default'           => 'О клубе',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link1_text',
		array(
			'label'    => __( 'Ссылка 1: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 11,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_club_link1_url',
		array(
			'default'           => '/about/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link1_url',
		array(
			'label'       => __( 'Ссылка 1: URL', 'arsenal' ),
			'description' => __( 'Относительный URL (например: /about/) или полный', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'url',
			'priority'    => 12,
		)
	);

	// Клуб - Ссылка 2: История
	$wp_customize->add_setting(
		'arsenal_menu_club_link2_text',
		array(
			'default'           => 'История',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link2_text',
		array(
			'label'    => __( 'Ссылка 2: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 13,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_club_link2_url',
		array(
			'default'           => '/history/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link2_url',
		array(
			'label'    => __( 'Ссылка 2: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 14,
		)
	);

	// Клуб - Ссылка 3: Команда
	$wp_customize->add_setting(
		'arsenal_menu_club_link3_text',
		array(
			'default'           => 'Команда',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link3_text',
		array(
			'label'    => __( 'Ссылка 3: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 15,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_club_link3_url',
		array(
			'default'           => '/team/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link3_url',
		array(
			'label'    => __( 'Ссылка 3: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 16,
		)
	);

	// Клуб - Ссылка 4: Тренерский штаб
	$wp_customize->add_setting(
		'arsenal_menu_club_link4_text',
		array(
			'default'           => 'Тренерский штаб',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link4_text',
		array(
			'label'    => __( 'Ссылка 4: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 17,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_club_link4_url',
		array(
			'default'           => '/coaches/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link4_url',
		array(
			'label'    => __( 'Ссылка 4: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 18,
		)
	);

	// Клуб - Ссылка 5: Руководство
	$wp_customize->add_setting(
		'arsenal_menu_club_link5_text',
		array(
			'default'           => 'Руководство',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link5_text',
		array(
			'label'       => __( 'Ссылка 5: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 19,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_club_link5_url',
		array(
			'default'           => '/management/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link5_url',
		array(
			'label'    => __( 'Ссылка 5: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 20,
		)
	);

	// Клуб - Ссылка 6
	$wp_customize->add_setting(
		'arsenal_menu_club_link6_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link6_text',
		array(
			'label'       => __( 'Ссылка 6: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 21,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_club_link6_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_club_link6_url',
		array(
			'label'    => __( 'Ссылка 6: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 22,
		)
	);

	// Колонка "Матчи" - Заголовок
	$wp_customize->add_setting(
		'arsenal_menu_matches_title',
		array(
			'default'           => 'Матчи',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_title',
		array(
			'label'    => __( 'Колонка 2: Заголовок', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 30,
		)
	);

	// Матчи - Ссылка 1: Расписание
	$wp_customize->add_setting(
		'arsenal_menu_matches_link1_text',
		array(
			'default'           => 'Расписание',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link1_text',
		array(
			'label'    => __( 'Ссылка 1: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 31,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_matches_link1_url',
		array(
			'default'           => '/schedule/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link1_url',
		array(
			'label'    => __( 'Ссылка 1: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 32,
		)
	);

	// Матчи - Ссылка 2: Результаты
	$wp_customize->add_setting(
		'arsenal_menu_matches_link2_text',
		array(
			'default'           => 'Результаты',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link2_text',
		array(
			'label'    => __( 'Ссылка 2: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 33,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_matches_link2_url',
		array(
			'default'           => '/results/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link2_url',
		array(
			'label'    => __( 'Ссылка 2: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 34,
		)
	);

	// Матчи - Ссылка 3: Турнирная таблица
	$wp_customize->add_setting(
		'arsenal_menu_matches_link3_text',
		array(
			'default'           => 'Турнирная таблица',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link3_text',
		array(
			'label'    => __( 'Ссылка 3: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 35,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_matches_link3_url',
		array(
			'default'           => '/standings/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link3_url',
		array(
			'label'    => __( 'Ссылка 3: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 36,
		)
	);

	// Матчи - Ссылка 4: Билеты
	$wp_customize->add_setting(
		'arsenal_menu_matches_link4_text',
		array(
			'default'           => 'Билеты',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link4_text',
		array(
			'label'       => __( 'Ссылка 4: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 37,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_matches_link4_url',
		array(
			'default'           => '/tickets/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link4_url',
		array(
			'label'    => __( 'Ссылка 4: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 38,
		)
	);

	// Матчи - Ссылка 5
	$wp_customize->add_setting(
		'arsenal_menu_matches_link5_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link5_text',
		array(
			'label'       => __( 'Ссылка 5: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 39,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_matches_link5_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link5_url',
		array(
			'label'    => __( 'Ссылка 5: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 40,
		)
	);

	// Матчи - Ссылка 6
	$wp_customize->add_setting(
		'arsenal_menu_matches_link6_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link6_text',
		array(
			'label'       => __( 'Ссылка 6: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 41,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_matches_link6_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_matches_link6_url',
		array(
			'label'    => __( 'Ссылка 6: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 42,
		)
	);

	// Колонка "Новости" - Заголовок
	$wp_customize->add_setting(
		'arsenal_menu_news_title',
		array(
			'default'           => 'Новости',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_title',
		array(
			'label'    => __( 'Колонка 3: Заголовок', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 50,
		)
	);

	// Новости - Ссылка 1: Все новости
	$wp_customize->add_setting(
		'arsenal_menu_news_link1_text',
		array(
			'default'           => 'Все новости',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link1_text',
		array(
			'label'    => __( 'Ссылка 1: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 51,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_news_link1_url',
		array(
			'default'           => '/news/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link1_url',
		array(
			'label'    => __( 'Ссылка 1: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 52,
		)
	);

	// Новости - Ссылка 2: Команда
	$wp_customize->add_setting(
		'arsenal_menu_news_link2_text',
		array(
			'default'           => 'Команда',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link2_text',
		array(
			'label'    => __( 'Ссылка 2: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 53,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_news_link2_url',
		array(
			'default'           => '/news/team/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link2_url',
		array(
			'label'    => __( 'Ссылка 2: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 54,
		)
	);

	// Новости - Ссылка 3: Матчи
	$wp_customize->add_setting(
		'arsenal_menu_news_link3_text',
		array(
			'default'           => 'Матчи',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link3_text',
		array(
			'label'    => __( 'Ссылка 3: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 55,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_news_link3_url',
		array(
			'default'           => '/news/matches/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link3_url',
		array(
			'label'    => __( 'Ссылка 3: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 56,
		)
	);

	// Новости - Ссылка 4: Клуб
	$wp_customize->add_setting(
		'arsenal_menu_news_link4_text',
		array(
			'default'           => 'Клуб',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link4_text',
		array(
			'label'    => __( 'Ссылка 4: Текст', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'text',
			'priority' => 57,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_news_link4_url',
		array(
			'default'           => '/news/club/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link4_url',
		array(
			'label'    => __( 'Ссылка 4: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 58,
		)
	);

	// Новости - Ссылка 5: Медиа
	$wp_customize->add_setting(
		'arsenal_menu_news_link5_text',
		array(
			'default'           => 'Медиа',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link5_text',
		array(
			'label'       => __( 'Ссылка 5: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 59,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_news_link5_url',
		array(
			'default'           => '/news/media/',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link5_url',
		array(
			'label'    => __( 'Ссылка 5: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 60,
		)
	);

	// Новости - Ссылка 6
	$wp_customize->add_setting(
		'arsenal_menu_news_link6_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link6_text',
		array(
			'label'       => __( 'Ссылка 6: Текст', 'arsenal' ),
			'description' => __( 'Оставьте пустым, чтобы скрыть', 'arsenal' ),
			'section'     => 'arsenal_footer_menu',
			'type'        => 'text',
			'priority'    => 61,
		)
	);

	$wp_customize->add_setting(
		'arsenal_menu_news_link6_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'arsenal_menu_news_link6_url',
		array(
			'label'    => __( 'Ссылка 6: URL', 'arsenal' ),
			'section'  => 'arsenal_footer_menu',
			'type'     => 'url',
			'priority' => 62,
		)
	);
}
add_action( 'customize_register', 'arsenal_customize_register' );

/**
 * Санитизация чекбокса
 *
 * @param bool $checked Значение чекбокса.
 * @return bool Очищенное значение.
 */
function arsenal_sanitize_checkbox( $checked ) {
	return ( isset( $checked ) && true === $checked ) ? true : false;
}

/**
 * Добавление JS для live preview в Customizer
 */
function arsenal_customize_preview_js() {
	wp_enqueue_script(
		'arsenal-customizer-preview',
		get_template_directory_uri() . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'customize_preview_init', 'arsenal_customize_preview_js' );
