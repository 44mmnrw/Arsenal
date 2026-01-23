<?php
/**
 * Блок социального шаринга
 * 
 * Выводит кнопки для шаринга в социальные сети (Facebook, VK)
 * Использует Web Share API на мобильных, веб-диалоги на десктопе
 * 
 * @package Arsenal
 */
?>

<div class="single-news-share">
	<div class="single-news-share__buttons">
		<span class="single-news-share__icon">
			<?php arsenal_icon( 'icon-share', 20, 20 ); ?>
		</span>
		<a href="#" 
		   class="single-news-share__btn single-news-share__btn--facebook" 
		   data-social="facebook"
		   data-url="<?php echo esc_attr( get_permalink() ); ?>"
		   title="<?php esc_attr_e( 'Поделиться в Facebook', 'arsenal' ); ?>">
			<?php arsenal_icon( 'icon-facebook', 18, 18 ); ?>
		</a>
		<a href="#" 
		   class="single-news-share__btn single-news-share__btn--vk" 
		   data-social="vk"
		   data-url="<?php echo esc_attr( get_permalink() ); ?>"
		   data-title="<?php echo esc_attr( get_the_title() ); ?>"
		   title="<?php esc_attr_e( 'Поделиться в ВКонтакте', 'arsenal' ); ?>">
			<?php arsenal_icon( 'icon-vk', 18, 18 ); ?>
		</a>
	</div>
</div>
