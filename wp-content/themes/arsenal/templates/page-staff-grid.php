<?php
/**
 * Template Name: Страница с персоналом
 * Description: Displays list of coaching staff and specialists grouped by position
 * 
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Подключаем стили страницы
if ( function_exists( 'wp_enqueue_style' ) && defined( 'ARSENAL_THEME_URI' ) ) {
	wp_enqueue_style(
		'arsenal-page-staff-grid',
		ARSENAL_THEME_URI . '/assets/css/pages/page-staff-grid.css',
		array( 'arsenal-footer' ),
		defined( 'ARSENAL_VERSION' ) ? ARSENAL_VERSION : null
	);
}

get_header();

require_once get_template_directory() . '/inc/classes/class-arsenal-staff-department-manager.php';

global $wpdb;

// Получить фильтры из post_meta текущей страницы
$post_id = get_the_ID();
$department_id = get_post_meta( $post_id, '_arsenal_staff_department_filter', true );
$club_type_filter = get_post_meta( $post_id, '_arsenal_staff_club_type_filter', true );

// Получить название отдела
$department_name = '';
if ( ! empty( $department_id ) ) {
	$department = $wpdb->get_row( $wpdb->prepare(
		"SELECT department_name FROM {$wpdb->prefix}arsenal_staff_department WHERE id = %d",
		intval( $department_id )
	) );
	if ( $department ) {
		$department_name = $department->department_name;
	}
}

// Построить SQL запрос с фильтрами
$where_conditions = array();

if ( ! empty( $department_id ) ) {
	$where_conditions[] = $wpdb->prepare( "s.department_id = %d", intval( $department_id ) );
}

if ( ! empty( $club_type_filter ) ) {
	$where_conditions[] = $wpdb->prepare( "s.club_type = %s", sanitize_text_field( $club_type_filter ) );
}

$where_clause = '';
if ( ! empty( $where_conditions ) ) {
	$where_clause = ' WHERE ' . implode( ' AND ', $where_conditions );
}

$sql = "SELECT s.*, jt.job_title_name as job_title, CONCAT(s.first_name, ' ', s.second_name) as full_name
	FROM {$wpdb->prefix}arsenal_staff s
	LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
	{$where_clause}
	ORDER BY jt.job_title_name ASC, s.second_name ASC, s.first_name ASC";

$staff = $wpdb->get_results( $sql );

// Группируем штаб по должностям
$staff_by_job_title = array();
foreach ( $staff as $person ) {
	$job_title = ! empty( $person->job_title ) ? $person->job_title : 'Специалист';
	if ( ! isset( $staff_by_job_title[ $job_title ] ) ) {
		$staff_by_job_title[ $job_title ] = array();
	}
	$staff_by_job_title[ $job_title ][] = $person;
}

?>

<main id="primary" class="site-main">
	<section class="teams-section">
		<div class="container">
			<div class="teams-header">
				<h1 class="teams-title"><?php echo ! empty( $department_name ) ? esc_html( $department_name ) : get_the_title(); ?></h1>
			</div>

			<?php if ( ! empty( $staff ) ) : ?>
				<div class="teams-content">
					<?php
					foreach ( $staff_by_job_title as $job_title => $job_staff ) :
					?>
						<div class="position-group">
							<h2 class="position-heading"><?php echo esc_html( $job_title ); ?></h2>
							
							<div class="players-grid">
								<?php foreach ( $job_staff as $person ) : 
									$photo_url = ! empty( $person->photo_url ) ? $person->photo_url : '';
									$photo_src = function_exists( 'arsenal_convert_logo_url' ) ? arsenal_convert_logo_url( $photo_url ) : $photo_url;
									$name_display = ! empty( $person->full_name ) ? trim( $person->full_name ) : 'Неизвестно';
									$position = ! empty( $person->job_title ) ? $person->job_title : 'Специалист';
								
									// Динамический URL сотрудника (по аналогии со страницей игрока)
									$staff_url = ! empty( $person->id ) ? arsenal_get_staff_url( $person->id ) : '#';
								?>
									<a href="<?php echo esc_url( $staff_url ); ?>" class="coach-card" title="<?php echo esc_attr( $name_display ); ?>">
										<!-- Левая колонка 50%: Фото -->
										<div class="coach-card__photo">
											<?php if ( ! empty( $photo_url ) ) : ?>
												<img 
													src="<?php echo esc_url( $photo_src ); ?>" 
													alt="<?php echo esc_attr( $name_display ); ?>"
													class="coach-card__image"
													loading="lazy"
												>
											<?php else : ?>
												<lottie-player src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/wired-outline-61-camera-hover-flash.json' ); ?>" background="transparent" style="width: 100%; height: 100%; min-height: 300px;"></lottie-player>
											<?php endif; ?>
										</div>

										<!-- Правая колонка 50%: Информация -->
										<div class="coach-card__info">
											<h3 class="coach-card__name"><?php echo esc_html( $name_display ); ?></h3>
											<p class="coach-card__position"><?php echo esc_html( $position ); ?></p>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php
					endforeach;
					?>
				</div>
			<?php else : ?>
				<p class="no-players-message">Тренеры не найдены</p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
