<?php
/**
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
	$staff_experience = $staff->experience ?: '';
	$staff_photo_url = ! empty( $staff->photo_url ) ? $staff->photo_url : '';
	$staff_photo_src = function_exists( 'arsenal_convert_logo_url' ) ? arsenal_convert_logo_url( $staff_photo_url ) : $staff_photo_url;
	$staff_bio = ! empty( $staff->bio ) ? $staff->bio : '';
	// Загружаем achievements из JSON поля
	$staff_achievements = array();
	if ( ! empty( $staff->achievements ) ) {
		$achievements_json = json_decode( $staff->achievements, true );
		$staff_achievements = is_array( $achievements_json ) ? $achievements_json : array();
	}
	
	// Загружаем career_positions из JSON поля
	$staff_career = array();
	if ( ! empty( $staff->career_positions ) ) {
		$career_json = json_decode( $staff->career_positions, true );
		$staff_career = is_array( $career_json ) ? $career_json : array();
	}
	
	$staff_contract_start = ! empty( $staff->contract_start ) ? $staff->contract_start : '';
	$staff_nationality = ! empty( $staff->citizenship ) ? $staff->citizenship : '';
	$staff_interesting_fact = ! empty( $staff->interesting_fact ) ? $staff->interesting_fact : '';
	if ( ! $staff_nationality && property_exists( $staff, 'nationality' ) && ! empty( $staff->nationality ) ) {
		$staff_nationality = $staff->nationality;
	}
	
	// Применяем склонение к experience если это число
	if ( ! empty( $staff_experience ) && is_numeric( $staff_experience ) ) {
		if ( function_exists( 'arsenal_pluralize_years' ) ) {
			$staff_experience = arsenal_pluralize_years( (int) $staff_experience );
		}
	}
}

// Форматирование даты рождения
$staff_birthdate_display = 'Не указано';
if ( ! empty( $staff_birthdate ) ) {
	$birth_timestamp = strtotime( $staff_birthdate );
	if ( $birth_timestamp ) {
		$staff_birthdate_display = date_i18n( 'j F Y', $birth_timestamp );
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

	<main class="player-page">
	<!-- HERO SECTION -->
	<div class="player-hero">
		<div class="player-hero__overlay"></div>
		<div class="player-hero__content player-container">
			<div class="player-hero__inner">
				<!-- Main Info Container -->
				<div class="staff-hero__main">
					<!-- Photo -->
					<div class="staff-photo-wrapper">						
							<?php
							// ВАЖНО: если сотрудник найден в БД, фото берем строго из wp_arsenal_staff.photo_url
							if ( $staff && ! empty( $staff_photo_src ) ) {
								echo '<img src="' . esc_url( $staff_photo_src ) . '" alt="' . esc_attr( trim( $staff->first_name . ' ' . $staff->second_name ) ) . '" class="staff-photo-img" loading="lazy">';

							} else {
								arsenal_render_camera_placeholder();
							}
							?>						
					</div>
					<!-- Info Block -->
					<div class="staff-info-block">
						<!-- Position Badge -->
						<div class="staff-position-badge">
							<?php echo esc_html( $staff_position ); ?>
						</div>

						<!-- Name -->
						<h1 class="staff-title">
							<?php 
							if ( $staff ) {
								echo esc_html( $staff->first_name . ' ' . $staff->second_name );
							} else {
								the_title();
							}
							?>
						</h1>

						<!-- Details Grid -->
						<div class="staff-details-grid">
							<div class="staff-detail-item">
								<span class="staff-detail-label">Дата рождения</span>
								<span class="staff-detail-value">
									<?php echo esc_html( $staff_birthdate_display ?: 'Не указано' ); ?>
								</span>
							</div>

							<div class="staff-detail-item">
								<span class="staff-detail-label">Опыт работы</span>
								<span class="staff-detail-value">
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
	<div class="staff-detail-container container">
		<div class="staff-detail-inner">
			<div class="staff-content">
			<!-- BIOGRAPHY -->
			<div class="staff-section staff-section--biography">
				<div class="staff-section__header staff-section__header--biography">
					<svg class="staff-section__icon"><use xlink:href="<?php echo esc_attr( ARSENAL_THEME_URI ); ?>/assets/images/sprite.svg#icon-bio"></use></svg>
					<h2 class="staff-section__title">Биография</h2>
				</div>

				<div class="staff-section__content">
					<?php
					if ( ! empty( $staff_bio ) ) {
						echo '<div class="staff-section__text">' . wp_kses_post( $staff_bio ) . '</div>';
					} elseif ( has_excerpt( $post_id ) ) {
						echo '<p class="staff-section__text">' . wp_kses_post( get_the_excerpt( $post_id ) ) . '</p>';
					}
					?>
				</div>
			</div>

			<!-- ACHIEVEMENTS -->
			<div class="staff-section staff-section--achievements">
				<div class="staff-section__header staff-section__header--achievements">
					<svg class="staff-section__icon"><use xlink:href="<?php echo esc_attr( ARSENAL_THEME_URI ); ?>/assets/images/sprite.svg#icon-cup"></use></svg>
					<h2 class="staff-section__title">Достижения</h2>
				</div>

				<div class="staff-section__content staff-achievements">
					<?php
					if ( ! empty( $staff_achievements ) && is_array( $staff_achievements ) ) {
						foreach ( $staff_achievements as $achievement ) {
							?>
							<div class="staff-achievement-item">
							<div class="staff-achievement-icon">
									<svg class="staff-section__icon"><use xlink:href="<?php echo esc_attr( ARSENAL_THEME_URI ); ?>/assets/images/sprite.svg#icon-checkbox"></use></svg>
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
					<svg class="staff-section__icon"><use xlink:href="<?php echo esc_attr( ARSENAL_THEME_URI ); ?>/assets/images/sprite.svg#icon-career"></use></svg>
					<h2 class="staff-section__title">Карьера</h2>
				</div>

				<div class="staff-section__content staff-career">
					<?php
					if ( ! empty( $staff_career ) && is_array( $staff_career ) ) {
						foreach ( $staff_career as $position ) {
							?>
							<div class="staff-career-item">
								<div class="staff-career-icon">
									<svg class="staff-section__icon"><use xlink:href="<?php echo esc_attr( ARSENAL_THEME_URI ); ?>/assets/images/sprite.svg#icon-checkbox"></use></svg>
								</div>
								<div class="staff-career-info">
									<h4 class="staff-career-title">
										<?php echo esc_html( $position['title'] ?? '' ); ?>
									</h4>
									<p class="staff-career-club">
										<?php echo esc_html( $position['organization'] ?? '' ); ?>
									</p>
									<p class="staff-career-period">
										<?php echo esc_html( $position['experience'] ?? '' ); ?>
									</p>
								</div>
							</div>
							<?php
						}
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
							<span class="staff-info-card__label">Гражданство</span>
							<span class="staff-info-card__value"><?php echo esc_html( $staff_nationality ?: 'Не указано' ); ?></span>
						</div>
					</div>
				</div>
				<?php if ( ! empty( $staff_interesting_fact ) ) : ?>
				<div class="staff-fact-card">
					<div class="staff-fact-icon">
						<svg class="staff-section__icon"><use xlink:href="<?php echo esc_attr( ARSENAL_THEME_URI ); ?>/assets/images/sprite.svg#icon-fact"></use></svg>
					</div>
					<h3 class="staff-fact-title">Интересный факт</h3>
					<p class="staff-fact-text">
						<?php echo wp_kses_post( $staff_interesting_fact ); ?>
					</p>
				</div>
				<?php endif; ?>
			</aside>
		</div>
	</div>
</main>

<?php
get_footer();
