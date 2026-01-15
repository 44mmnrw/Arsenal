<?php
/**
 * Template Name: Staff (Персонал)
 * Description: Страница деталей о сотруднике клуба (тренер, стафф)
 * 
 * @package Arsenal
 * @since 1.0.0
 */

// Подключаем стили страницы (на динамических URL вида /staff/{id}/ условия в functions.php
// могут не сработать, поэтому дублируем enqueue прямо в шаблоне).
if ( function_exists( 'wp_enqueue_style' ) && defined( 'ARSENAL_THEME_URI' ) ) {
	wp_enqueue_style(
		'arsenal-page-staff',
		ARSENAL_THEME_URI . '/assets/css/pages/page-staff.css',
		array( 'arsenal-footer' ),
		defined( 'ARSENAL_VERSION' ) ? ARSENAL_VERSION : null
	);
}

get_header();

// Подключить менеджер сотрудников
require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

// ===== ДИНАМИЧЕСКИЙ РЕЖИМ (по аналогии с page-player.php) =====
// Берем staff_id из rewrite rule (/staff/{id}/) или из GET (?staff_id=)
$staff_id = get_query_var( 'staff_id' );
if ( ! $staff_id ) {
	$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
}

// Получить ID поста (если шаблон используется как Page-template)
$post_id = get_the_ID();

// Если пришли по /staff/{id}/ — загружаем сотрудника по ID
if ( $staff_id ) {
	$staff = Arsenal_Staff_Manager::get_staff_member( $staff_id );
	if ( $staff ) {
		$staff_full_name = trim( $staff->first_name . ' ' . $staff->second_name );
		add_filter( 'pre_get_document_title', function() use ( $staff_full_name ) {
			return $staff_full_name;
		}, 999 );
	}
} else {
	// Старый режим: ищем сотрудника по названию страницы
	$post_title = get_the_title( $post_id );
	global $wpdb;
	$staff = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}arsenal_staff 
		 WHERE CONCAT(first_name, ' ', second_name) = %s 
		 LIMIT 1",
		$post_title
	) );
}

// Если это динамический URL и сотрудник не найден — показываем понятную ошибку
if ( $staff_id && ! $staff ) {
	?>
	<main class="staff-detail-page">
		<div class="staff-container">
			<div class="staff-content">
				<h1>Сотрудник не найден</h1>
				<p>Сотрудник с ID <?php echo esc_html( $staff_id ); ?> не найден в базе данных.</p>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
}

// Если сотрудник найден в БД - загрузить его данные
if ( $staff ) {
	$staff_position = '';
	if ( $staff->job_title_id ) {
		$job_title = Arsenal_Staff_Manager::get_job_title( $staff->job_title_id );
		$staff_position = $job_title ? $job_title->job_title_name : '';
	}
	$staff_birthdate = $staff->birth_date ?: '';
	$staff_experience = '';
	$staff_photo_url = ! empty( $staff->photo_url ) ? $staff->photo_url : '';
	$staff_photo_src = function_exists( 'arsenal_convert_logo_url' ) ? arsenal_convert_logo_url( $staff_photo_url ) : $staff_photo_url;
	$staff_bio = ! empty( $staff->bio ) ? $staff->bio : '';
	$staff_achievements = array(); // Можно загрузить из post_meta если нужно
	$staff_career = array(); // Можно загрузить из post_meta если нужно
	$staff_contract_start = ! empty( $staff->contract_start ) ? $staff->contract_start : '';
	$staff_nationality = '';
	if ( property_exists( $staff, 'nationality' ) && ! empty( $staff->nationality ) ) {
		$staff_nationality = $staff->nationality;
	} elseif ( property_exists( $staff, 'citizenship' ) && ! empty( $staff->citizenship ) ) {
		$staff_nationality = $staff->citizenship;
	}
} else {
	// Fallback: получить данные из post_meta (старая система)
	$staff_position = get_post_meta( $post_id, '_staff_position', true ) ?: 'Главный тренер';
	$staff_birthdate = get_post_meta( $post_id, '_staff_birthdate', true ) ?: '';
	$staff_experience = get_post_meta( $post_id, '_staff_experience', true ) ?: '';
	$staff_photo_url = '';
	$staff_photo_src = '';
	$staff_bio = '';
	$staff_achievements = get_post_meta( $post_id, '_staff_achievements', true ) ?: array();
	$staff_career = get_post_meta( $post_id, '_staff_career', true ) ?: array();
	$staff_contract_start = '';
	$staff_nationality = get_post_meta( $post_id, '_staff_nationality', true );

	// Если achievement и career в виде JSON, распарсить их
	if ( is_string( $staff_achievements ) ) {
		$staff_achievements = json_decode( $staff_achievements, true ) ?: array();
	}
	if ( is_string( $staff_career ) ) {
		$staff_career = json_decode( $staff_career, true ) ?: array();
	}
}

// Форматирование даты рождения
$staff_birthdate_display = 'Не указано';
if ( ! empty( $staff_birthdate ) ) {
	$birth_timestamp = strtotime( $staff_birthdate );
	if ( $birth_timestamp ) {
		$staff_birthdate_display = wp_date( 'j F Y', $birth_timestamp );
	} else {
		$staff_birthdate_display = $staff_birthdate;
	}
}

// Опыт работы: если не указан явно, вычисляем по contract_start
if ( empty( $staff_experience ) && ! empty( $staff_contract_start ) ) {
	$contract_timestamp = strtotime( $staff_contract_start );
	if ( $contract_timestamp ) {
		$contract_date = new DateTime( '@' . $contract_timestamp );
		$contract_date->setTimezone( wp_timezone() );
		$now = new DateTime( 'now', wp_timezone() );
		$diff_years = $contract_date->diff( $now )->y;
		if ( $diff_years > 0 ) {
			if ( function_exists( 'arsenal_pluralize_years' ) ) {
				$staff_experience = arsenal_pluralize_years( $diff_years );
			} else {
				$staff_experience = sprintf( _n( '%d год', '%d лет', $diff_years, 'arsenal' ), $diff_years );
			}
		}
	}
}

// Гражданство
if ( empty( $staff_nationality ) ) {
	$staff_nationality = 'Беларусь';
}

?>

<main class="staff-detail-page">
	<!-- HERO SECTION -->
	<div class="staff-hero">
		<div class="staff-hero__overlay"></div>
		<div class="staff-hero__content">
			<div class="staff-hero__inner">
				<!-- Main Info Container -->
				<div class="staff-hero__main">
					<!-- Photo -->
					<div class="staff-hero__photo-wrapper">
						<div class="staff-hero__photo">
							<?php
							// ВАЖНО: если сотрудник найден в БД, фото берем строго из wp_arsenal_staff.photo_url
							if ( $staff && ! empty( $staff_photo_src ) ) {
								echo '<img src="' . esc_url( $staff_photo_src ) . '" alt="' . esc_attr( trim( $staff->first_name . ' ' . $staff->second_name ) ) . '" class="staff-hero__photo-img" loading="lazy">';
							} elseif ( has_post_thumbnail( $post_id ) ) {
								echo get_the_post_thumbnail( $post_id, 'large', array(
								'class' => 'staff-hero__photo-img',
								'alt'   => get_the_title( $post_id ),
							) );
							} else {
								echo '<div class="staff-hero__photo-placeholder">Фото не загружено</div>';
							}
							?>
						</div>
					</div>

					<!-- Info Block -->
					<div class="staff-hero__info">
						<!-- Position Badge -->
						<div class="staff-hero__position">
							<?php echo esc_html( $staff_position ); ?>
						</div>

						<!-- Name -->
						<h1 class="staff-hero__title">
							<?php 
							if ( $staff ) {
								echo esc_html( $staff->first_name . ' ' . $staff->second_name );
							} else {
								the_title();
							}
							?>
						</h1>

						<!-- Details Grid -->
						<div class="staff-hero__details">
							<div class="staff-hero__detail-item">
								<span class="staff-hero__detail-label">Дата рождения</span>
								<span class="staff-hero__detail-value">
									<?php echo esc_html( $staff_birthdate ?: 'Не указано' ); ?>
								</span>
							</div>

							<div class="staff-hero__detail-item">
								<span class="staff-hero__detail-label">Опыт работы</span>
								<span class="staff-hero__detail-value">
									<?php echo esc_html( $staff_experience ?: 'Не указано' ); ?>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- CONTENT SECTION -->
	<div class="staff-container">
		<div class="staff-container__inner">
			<div class="staff-content">
			<!-- BIOGRAPHY -->
			<div class="staff-section staff-section--biography">
				<div class="staff-section__header staff-section__header--biography">
					<svg class="staff-section__icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11C11 9.75 13 8.5 13 7C13 5.9 12.1 5 11 5C9.9 5 9 5.9 9 7H7C7 4.79 8.79 3 11 3C13.21 3 15 4.79 15 7C15 8.5 17 9.75 17 13H13Z" fill="currentColor"/>
					</svg>
					<h2 class="staff-section__title">Биография</h2>
				</div>

				<div class="staff-section__content">
					<?php
					if ( ! empty( $staff_bio ) ) {
						echo '<div class="staff-section__text">' . wp_kses_post( $staff_bio ) . '</div>';
					} elseif ( has_excerpt( $post_id ) ) {
						echo '<p class="staff-section__text">' . wp_kses_post( get_the_excerpt( $post_id ) ) . '</p>';
					} else {
						echo '<p class="staff-section__text">Опытный тренер с богатой карьерой. Работает с командой с 2021 года. Известен своим тактическим мастерством и умением мотивировать игроков. Под его руководством команда показывает стабильные результаты и динамичный футбол.</p>';
					}
					?>
				</div>
			</div>

			<!-- ACHIEVEMENTS -->
			<div class="staff-section staff-section--achievements">
				<div class="staff-section__header staff-section__header--achievements">
					<svg class="staff-section__icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M12 2L15.09 8.26H22L16.54 12.82L18.63 19.08L12 14.54L5.37 19.08L7.46 12.82L2 8.26H8.91L12 2Z" fill="currentColor"/>
					</svg>
					<h2 class="staff-section__title">Достижения</h2>
				</div>

				<div class="staff-section__content staff-achievements">
					<?php
					if ( ! empty( $staff_achievements ) && is_array( $staff_achievements ) ) {
						foreach ( $staff_achievements as $achievement ) {
							?>
							<div class="staff-achievement-item">
								<div class="staff-achievement-icon">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
										<path d="M12 2L15.09 8.26H22L16.54 12.82L18.63 19.08L12 14.54L5.37 19.08L7.46 12.82L2 8.26H8.91L12 2Z" fill="currentColor"/>
									</svg>
								</div>
								<p class="staff-achievement-text">
									<?php echo esc_html( $achievement ); ?>
								</p>
							</div>
							<?php
						}
					} else {
						// Дефолтные достижения для демо
						$default_achievements = array(
							'Чемпион второй лиги 2022',
							'Тренер года 2023',
							'Вывел команду в первую лигу',
							'Обладатель лицензии UEFA Pro',
						);
						foreach ( $default_achievements as $achievement ) {
							?>
							<div class="staff-achievement-item">
								<div class="staff-achievement-icon">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
										<path d="M12 2L15.09 8.26H22L16.54 12.82L18.63 19.08L12 14.54L5.37 19.08L7.46 12.82L2 8.26H8.91L12 2Z" fill="currentColor"/>
									</svg>
								</div>
								<p class="staff-achievement-text">
									<?php echo esc_html( $achievement ); ?>
								</p>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>

			<!-- CAREER -->
			<div class="staff-section staff-section--career">
				<div class="staff-section__header staff-section__header--career">
					<svg class="staff-section__icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M20 6H16V4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4V6H4C2.9 6 2 6.9 2 8V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V8C22 6.9 21.1 6 20 6ZM10 4H14V6H10V4ZM20 19H4V8H20V19Z" fill="currentColor"/>
					</svg>
					<h2 class="staff-section__title">Карьера</h2>
				</div>

				<div class="staff-section__content staff-career">
					<?php
					if ( ! empty( $staff_career ) && is_array( $staff_career ) ) {
						foreach ( $staff_career as $job ) {
							?>
							<div class="staff-career-item">
								<div class="staff-career-icon">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
										<path d="M20 6H16V4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4V6H4C2.9 6 2 6.9 2 8V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V8C22 6.9 21.1 6 20 6ZM10 4H14V6H10V4ZM20 19H4V8H20V19Z" fill="currentColor"/>
									</svg>
								</div>
								<div class="staff-career-info">
									<h4 class="staff-career-title">
										<?php echo esc_html( isset( $job['title'] ) ? $job['title'] : '' ); ?>
									</h4>
									<p class="staff-career-club">
										<?php echo esc_html( isset( $job['club'] ) ? $job['club'] : '' ); ?>
									</p>
									<p class="staff-career-period">
										<?php echo esc_html( isset( $job['period'] ) ? $job['period'] : '' ); ?>
									</p>
								</div>
							</div>
							<?php
						}
					} else {
						?>
						<div class="staff-career-item">
							<div class="staff-career-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M20 6H16V4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4V6H4C2.9 6 2 6.9 2 8V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V8C22 6.9 21.1 6 20 6ZM10 4H14V6H10V4ZM20 19H4V8H20V19Z" fill="currentColor"/>
								</svg>
							</div>
							<div class="staff-career-info">
								<h4 class="staff-career-title">Главный тренер</h4>
								<p class="staff-career-club">ФК Арсенал Дзержинск</p>
								<p class="staff-career-period">Опыт: 15 лет</p>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			</div>

			<!-- SIDEBAR -->
			<aside class="staff-sidebar">
				<div class="staff-info-card">
					<div class="staff-info-card__header">
						<span class="staff-info-card__title">Информация</span>
					</div>
					<div class="staff-info-card__body">
						<div class="staff-info-card__row">
							<span class="staff-info-card__label">Должность</span>
							<span class="staff-info-card__value"><?php echo esc_html( $staff_position ?: 'Не указано' ); ?></span>
						</div>
						<div class="staff-info-card__row">
							<span class="staff-info-card__label">Дата рождения</span>
							<span class="staff-info-card__value"><?php echo esc_html( $staff_birthdate_display ); ?></span>
						</div>
						<div class="staff-info-card__row">
							<span class="staff-info-card__label">Опыт работы</span>
							<span class="staff-info-card__value"><?php echo esc_html( $staff_experience ?: 'Не указано' ); ?></span>
						</div>
						<div class="staff-info-card__row">
							<span class="staff-info-card__label">Национальность</span>
							<span class="staff-info-card__value"><?php echo esc_html( $staff_nationality ?: 'Не указано' ); ?></span>
						</div>
					</div>
				</div>
				<div class="staff-fact-card">
					<div class="staff-fact-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
							<path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11C11 9.75 13 8.5 13 7C13 5.9 12.1 5 11 5C9.9 5 9 5.9 9 7H7C7 4.79 8.79 3 11 3C13.21 3 15 4.79 15 7C15 8.5 17 9.75 17 13H13Z" fill="currentColor"/>
						</svg>
					</div>
					<h3 class="staff-fact-title">Интересный факт</h3>
					<p class="staff-fact-text">
						<?php
						$fact = get_post_meta( $post_id, '_staff_interesting_fact', true );
						echo esc_html( $fact ?: 'Чемпион второй лиги 2022' );
						?>
					</p>
				</div>
			</aside>
		</div>
	</div>
</main>

<?php
get_footer();
