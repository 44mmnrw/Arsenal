/**
 * Arsenal Theme JavaScript
 *
 * @package Arsenal
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		
		// Мобильное меню
		$('.menu-toggle').on('click', function() {
			$(this).toggleClass('active');
			$('.nav-menu').toggleClass('toggled');
			$(this).attr('aria-expanded', $(this).attr('aria-expanded') === 'false' ? 'true' : 'false');
		});

		// Плавная прокрутка для якорных ссылок
		$('a[href^="#"]').on('click', function(event) {
			var target = $(this.getAttribute('href'));
			if (target.length) {
				event.preventDefault();
				$('html, body').stop().animate({
					scrollTop: target.offset().top - 100
				}, 800);
			}
		});

		// Sticky header при прокрутке
		var header = $('.site-header');
		var headerOffset = header.offset().top;
		
		$(window).scroll(function() {
			if ($(window).scrollTop() > headerOffset) {
				header.addClass('sticky');
			} else {
				header.removeClass('sticky');
			}
		});

	});

})(jQuery);
