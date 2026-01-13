<?php
/**
 * Шаблон главной страницы
 *
 * Отображает главную страницу сайта с баннером и секциями.
 * Записи блога здесь не выводятся.
 *
 * @package Arsenal
 * @since 1.0.0
 */

get_header();
?>

<main id="main" class="site-main front-page">
	
	<?php
	// Баннер-карусель
	get_template_part( 'template-parts/widgets/banner' );
	
	// Статистика клуба (красная полоса) - ДИНАМИЧЕСКАЯ ИЗ БД
	get_template_part( 'template-parts/widgets/stats-bar' );
	
	// Ближайший матч - ДИНАМИЧЕСКИЙ ИЗ БД
	get_template_part( 'template-parts/widgets/upcoming-match' );
	
	// Новости
	get_template_part( 'template-parts/widgets/news' );
	
	// Результаты прошедших игр - ДИНАМИЧЕСКИЕ ИЗ БД
	get_template_part( 'template-parts/widgets/last-games' );
	
	// Турнирная таблица (уже использует JSON)
	get_template_part( 'template-parts/widgets/tournament-table' );
	
	// Партнёры и спонсоры
	get_template_part( 'template-parts/widgets/sponsors' );
	
	// Здесь будут добавлены другие секции главной страницы
	?>
	
</main>

<?php
get_footer();
