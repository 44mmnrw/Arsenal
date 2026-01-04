/**
 * Banner Carousel Script
 *
 * Управление каруселью баннера
 *
 * @package Arsenal
 * @since 1.0.0
 */

(function() {
	'use strict';

	/**
	 * Инициализация карусели баннера
	 */
	function initBannerCarousel() {
		const banner = document.querySelector('.site-banner.has-carousel');
		
		if (!banner) {
			return;
		}

		const slides = banner.querySelectorAll('.banner-slide');
		const dots = banner.querySelectorAll('.banner-dot');
		const prevBtn = banner.querySelector('.banner-nav-prev');
		const nextBtn = banner.querySelector('.banner-nav-next');
		
		if (slides.length <= 1) {
			return;
		}

		let currentSlide = 0;
		let autoplayInterval = null;
		const autoplay = banner.dataset.autoplay === 'true';
		const interval = parseInt(banner.dataset.interval, 10) || 5000;

		// Устанавливаем CSS переменную для прогресс-бара
		banner.style.setProperty('--banner-interval', interval + 'ms');

		/**
		 * Переключение на определённый слайд
		 */
		function goToSlide(index) {
			// Убираем активный класс со всех слайдов и точек
			slides.forEach(function(slide) {
				slide.classList.remove('active');
			});
			dots.forEach(function(dot) {
				dot.classList.remove('active');
			});

			// Нормализуем индекс
			if (index >= slides.length) {
				index = 0;
			} else if (index < 0) {
				index = slides.length - 1;
			}

			// Активируем нужный слайд и точку
			slides[index].classList.add('active');
			if (dots[index]) {
				dots[index].classList.add('active');
			}

			currentSlide = index;

			// Перезапускаем анимацию прогресс-бара
			if (autoplay) {
				banner.classList.remove('autoplay-active');
				// Принудительный reflow для перезапуска анимации
				void banner.offsetWidth;
				banner.classList.add('autoplay-active');
			}
		}

		/**
		 * Следующий слайд
		 */
		function nextSlide() {
			goToSlide(currentSlide + 1);
		}

		/**
		 * Предыдущий слайд
		 */
		function prevSlide() {
			goToSlide(currentSlide - 1);
		}

		/**
		 * Запуск автопроигрывания
		 */
		function startAutoplay() {
			if (!autoplay) {
				return;
			}
			
			stopAutoplay();
			banner.classList.add('autoplay-active');
			autoplayInterval = setInterval(nextSlide, interval);
		}

		/**
		 * Остановка автопроигрывания
		 */
		function stopAutoplay() {
			if (autoplayInterval) {
				clearInterval(autoplayInterval);
				autoplayInterval = null;
			}
			banner.classList.remove('autoplay-active');
		}

		// Обработчики событий
		if (prevBtn) {
			prevBtn.addEventListener('click', function() {
				prevSlide();
				startAutoplay(); // Перезапускаем автопроигрывание
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function() {
				nextSlide();
				startAutoplay(); // Перезапускаем автопроигрывание
			});
		}

		// Клик по точкам
		dots.forEach(function(dot, index) {
			dot.addEventListener('click', function() {
				goToSlide(index);
				startAutoplay(); // Перезапускаем автопроигрывание
			});
		});

		// Пауза при наведении
		banner.addEventListener('mouseenter', stopAutoplay);
		banner.addEventListener('mouseleave', startAutoplay);

		// Поддержка клавиатуры
		banner.setAttribute('tabindex', '0');
		banner.addEventListener('keydown', function(e) {
			if (e.key === 'ArrowLeft') {
				prevSlide();
				startAutoplay();
			} else if (e.key === 'ArrowRight') {
				nextSlide();
				startAutoplay();
			}
		});

		// Поддержка свайпов на мобильных
		let touchStartX = 0;
		let touchEndX = 0;

		banner.addEventListener('touchstart', function(e) {
			touchStartX = e.changedTouches[0].screenX;
		}, { passive: true });

		banner.addEventListener('touchend', function(e) {
			touchEndX = e.changedTouches[0].screenX;
			handleSwipe();
		}, { passive: true });

		function handleSwipe() {
			const swipeThreshold = 50;
			const diff = touchStartX - touchEndX;

			if (Math.abs(diff) > swipeThreshold) {
				if (diff > 0) {
					// Свайп влево - следующий слайд
					nextSlide();
				} else {
					// Свайп вправо - предыдущий слайд
					prevSlide();
				}
				startAutoplay();
			}
		}

		// Запускаем автопроигрывание
		startAutoplay();

		// Останавливаем когда вкладка не активна
		document.addEventListener('visibilitychange', function() {
			if (document.hidden) {
				stopAutoplay();
			} else {
				startAutoplay();
			}
		});
	}

	// Инициализация при загрузке DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBannerCarousel);
	} else {
		initBannerCarousel();
	}

})();

/**
 * Parallax Effect for Banner
 *
 * Создаёт эффект параллакса для фонового изображения баннера
 */
(function() {
	'use strict';

	function initParallax() {
		const banner = document.querySelector('.site-banner');
		if (!banner) return;

		const backgrounds = banner.querySelectorAll('.banner-background');
		if (!backgrounds.length) return;

		// Коэффициент скорости параллакса (0.3 = фон движется на 30% медленнее)
		const parallaxSpeed = 0.4;

		// Проверяем поддержку prefers-reduced-motion
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (prefersReducedMotion) return;

		let ticking = false;

		function updateParallax() {
			const scrolled = window.pageYOffset;
			const bannerRect = banner.getBoundingClientRect();

			// Применяем параллакс только когда баннер видим
			if (bannerRect.bottom > 0) {
				const yPos = scrolled * parallaxSpeed;
				backgrounds.forEach(function(bg) {
					bg.style.transform = 'translateY(' + yPos + 'px)';
				});
			}

			ticking = false;
		}

		function onScroll() {
			if (!ticking) {
				requestAnimationFrame(updateParallax);
				ticking = true;
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });

		// Инициализация
		updateParallax();
	}

	// Инициализация при загрузке DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initParallax);
	} else {
		initParallax();
	}

})();
