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

// Получить данные руководства из БД
global $wpdb;
$management_team = $wpdb->get_results( 
	"SELECT * FROM {$wpdb->prefix}arsenal_management ORDER BY position, name ASC" 
);

?>
<div class="page-management-wrapper">
	<div class="page-management-container">
		<h2 class="management-title">Руководство клуба</h2>
		
		<div class="management-grid">
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
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php
get_footer();

