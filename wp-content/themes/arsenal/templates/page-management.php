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
// Данные руководства клуба
$management_team = array(
	array(
		'name'        => 'Александр Петрович Лукашевич',
		'position'    => 'Президент клуба',
		'description' => 'Руководит клубом с 2018 года. Предприниматель и меценат, инвестирующий в развитие спорта в Дзержинске.',
		'image'       => 'https://via.placeholder.com/706x384?text=President', // Замени на реальное изображение
		'image_alt'   => 'Президент клуба',
	),
	array(
		'name'        => 'Виктор Николаевич Ковалев',
		'position'    => 'Генеральный директор',
		'description' => 'Отвечает за операционную деятельность клуба и стратегическое планирование.',
		'image'       => 'https://via.placeholder.com/706x384?text=Director', // Замени на реальное изображение
		'image_alt'   => 'Генеральный директор',
	),
	array(
		'name'        => 'Ирина Владимировна Сидорова',
		'position'    => 'Финансовый директор',
		'description' => 'Управляет финансами клуба, бюджетированием и инвестиционными проектами.',
		'image'       => 'https://via.placeholder.com/706x384?text=Finance', // Замени на реальное изображение
		'image_alt'   => 'Финансовый директор',
	),
	array(
		'name'        => 'Дмитрий Сергеевич Павленко',
		'position'    => 'Спортивный директор',
		'description' => 'Отвечает за трансферную политику, работу с тренерским штабом и развитие академии.',
		'image'       => 'https://via.placeholder.com/706x384?text=Sports', // Замени на реальное изображение
		'image_alt'   => 'Спортивный директор',
	),
);

?>
<div class="page-management-wrapper">
	<div class="page-management-container">
		<h2 class="management-title">Руководство клуба</h2>
		
		<div class="management-grid">
			<?php foreach ( $management_team as $person ) : ?>
				<div class="management-card">
					<div class="management-card-image-wrapper">
						<img 
							src="<?php echo esc_url( $person['image'] ); ?>" 
							alt="<?php echo esc_attr( $person['image_alt'] ); ?>"
							class="management-card-image"
						/>
						<div class="management-card-image-gradient"></div>
						<div class="management-card-position-badge">
							<?php echo esc_html( $person['position'] ); ?>
						</div>
					</div>
					
					<div class="management-card-content">
						<h3 class="management-card-name"><?php echo esc_html( $person['name'] ); ?></h3>
						<p class="management-card-description"><?php echo esc_html( $person['description'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php
get_footer();

