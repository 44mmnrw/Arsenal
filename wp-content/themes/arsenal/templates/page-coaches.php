<?php
/**
 * Template Name: Тренеры
 * Description: Displays list of coaches and staff members grouped by position
 * 
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wpdb;

// Запрос: все тренеры и штаб с их должностями
$sql = "SELECT s.*, jt.job_title_name as job_title, CONCAT(s.first_name, ' ', s.second_name) as full_name
	FROM {$wpdb->prefix}arsenal_staff s
	LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
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
				<h1 class="teams-title">Тренерский штаб</h1>
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
									$name_display = ! empty( $person->full_name ) ? trim( $person->full_name ) : 'Неизвестно';
									$position = ! empty( $person->job_title ) ? $person->job_title : 'Специалист';
								?>
									<a href="#" class="coach-card" title="<?php echo esc_attr( $name_display ); ?>">
										<!-- Левая колонка 50%: Фото -->
										<div class="coach-card__photo">
											<img 
												src="<?php echo esc_url( $photo_url ); ?>" 
												alt="<?php echo esc_attr( $name_display ); ?>"
												class="coach-card__image"
												loading="lazy"
											>
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
