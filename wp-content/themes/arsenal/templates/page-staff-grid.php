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

get_header();

global $wpdb;

// Получить фильтры из post_meta текущей страницы
$post_id = get_the_ID();
$department_id = get_post_meta( $post_id, '_arsenal_staff_department_filter', true );

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
$sql = "SELECT s.*, jt.job_title_name as job_title, jt.job_title_name_plural, CONCAT(s.first_name, ' ', s.second_name) as full_name
	FROM {$wpdb->prefix}arsenal_staff s
	INNER JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id";

if ( ! empty( $department_id ) ) {
	$sql .= $wpdb->prepare( " WHERE s.department_id = %d", intval( $department_id ) );
}

$sql .= " ORDER BY jt.job_title_name ASC, s.second_name ASC, s.first_name ASC";

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
						// Получить множественную форму из первого сотрудника
						$job_title_display = $job_title;
						if ( ! empty( $job_staff[0]->job_title_name_plural ) ) {
							$job_title_display = $job_staff[0]->job_title_name_plural;
						}
					?>
						<div class="position-group">
							<h2 class="position-heading"><?php echo esc_html( $job_title_display ); ?></h2>
							
							<div class="staff-grid">
								<?php foreach ( $job_staff as $person ) : 
									$photo_url = ! empty( $person->photo_url ) ? $person->photo_url : '';
									$photo_src = function_exists( 'arsenal_convert_logo_url' ) ? arsenal_convert_logo_url( $photo_url ) : $photo_url;
									$name_display = ! empty( $person->full_name ) ? trim( $person->full_name ) : 'Неизвестно';
									$position = ! empty( $person->job_title ) ? $person->job_title : 'Специалист';
								
									// Динамический URL сотрудника (по аналогии со страницей игрока)
									$staff_url = function_exists( 'arsenal_get_staff_url' ) && ! empty( $person->id ) ? arsenal_get_staff_url( $person->id ) : '#';
								?>
									<a href="<?php echo esc_url( $staff_url ); ?>" class="staff-card" title="<?php echo esc_attr( $name_display ); ?>">
										<!-- Левая колонка 50%: Фото -->
										<div class="staff-card__photo">
											<?php if ( ! empty( $photo_url ) ) : ?>
												<img 
													src="<?php echo esc_url( $photo_src ); ?>" 
													alt="<?php echo esc_attr( $name_display ); ?>"
													class="staff-card__image"
													loading="lazy"
												>
											<?php else : ?>
												<lottie-player src="<?php echo esc_url( get_template_directory_uri() . '/assets/animations/wired-outline-61-camera-hover-flash.json' ); ?>" background="transparent" style="width: 100%; height: 100%; min-height: 300px;"></lottie-player>
											<?php endif; ?>
										</div>

										<!-- Правая колонка 50%: Информация -->
										<div class="staff-card__info">
											<h3 class="staff-card__name"><?php echo esc_html( $name_display ); ?></h3>
											<p class="staff-card__position"><?php echo esc_html( $position ); ?></p>
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
