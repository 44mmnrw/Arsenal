<?php
/**
 * Template Name: Руководство
 *
 * Шаблон страницы "Руководство клуба"
 * Сетка карточек управления с фото и описанием
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Получить фильтры из post_meta
$post_id = get_the_ID();
$squad_id_filter = get_post_meta( $post_id, '_arsenal_management_squad_id_filter', true );
$department_id_filter = get_post_meta( $post_id, '_arsenal_management_department_id_filter', true );

// Построить SQL запрос с фильтрами
global $wpdb;
$where_conditions = array();

if ( ! empty( $squad_id_filter ) ) {
	$where_conditions[] = $wpdb->prepare( "s.squad_id = %d", intval( $squad_id_filter ) );
}

if ( ! empty( $department_id_filter ) ) {
	$where_conditions[] = $wpdb->prepare( "s.department_id = %d", intval( $department_id_filter ) );
}

$where_clause = '';
if ( ! empty( $where_conditions ) ) {
	$where_clause = ' WHERE ' . implode( ' AND ', $where_conditions );
}

// Запрос: сотрудники с JOIN к должностям и отделам
// CONCAT_WS обрабатывает NULL значения корректно, COALESCE дает fallback значения
// ORDER BY оптимизирован для LEFT JOIN (без зависимости от NULL полей в join таблицах)
$management_team = $wpdb->get_results( 
	"SELECT s.id, s.photo_url, 
			CONCAT_WS( ' ', COALESCE( s.first_name, '' ), COALESCE( s.second_name, '' ) ) as name,
			COALESCE( jt.job_title_name, 'Должность не указана' ) as position,
			s.bio as description
	 FROM {$wpdb->prefix}arsenal_staff s
	 LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles jt ON s.job_title_id = jt.id
	 LEFT JOIN {$wpdb->prefix}arsenal_staff_department sd ON s.department_id = sd.id
	 {$where_clause}
	 ORDER BY COALESCE( s.sort_order, 999 ) ASC, 
	 		 COALESCE( s.second_name, '' ) ASC, 
	 		 COALESCE( s.first_name, '' ) ASC" 
);

?>

<main id="primary" class="site-main">
	<div class="page-management-wrapper">
		<div class="page-management-container">
			<div class="entry-header">
				<h1 class="entry-title">Руководство</h1>
			</div>
			
			<div class="management-grid">
				<?php if ( ! empty( $management_team ) ) : ?>
					<?php foreach ( $management_team as $person ) : ?>
						<div class="management-card">
						<div class="management-card-image-wrapper">
							<?php if ( ! empty( $person->photo_url ) ) : ?>
								<img 
									src="<?php echo esc_url( $person->photo_url ); ?>" 
									alt="<?php echo esc_attr( $person->name ); ?>"
									class="management-card-image"
								/>
							<?php else : ?>
								<div class="management-card-image management-card-image-empty">
									<span class="dashicons dashicons-admin-users"></span>
								</div>
							<?php endif; ?>
							<div class="management-card-image-gradient"></div>
							<div class="management-card-position-badge">
								<?php echo esc_html( $person->position ); ?>
							</div>
						</div>
						
						<div class="management-card-content">
							<h3 class="management-card-name"><?php echo esc_html( $person->name ); ?></h3>
							<p class="management-card-description"><?php echo esc_html( $person->description ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>				<?php else : ?>
					<p style="text-align: center; padding: 40px 20px; color: #666; font-size: 16px;">
						Руководящий состав не указан
					</p>
				<?php endif; ?>			</div>
		</div>
	</div>
</main>

<?php
get_footer();

