/**
 * Arsenal Theme - Page History Full Handler
 * 
 * Консолидированный скрипт для страницы "История клуба"
 * Включает:
 * - TimelineManager - динамическое управление точками timeline
 * - Timeline Interactive - hover эффекты и анимация
 * - Smooth Scroll и Intersection Observer
 * - REST API для загрузки данных
 *
 * @package Arsenal
 * @since 1.0.0
 */

(function() {
	'use strict';

	/**
	 * Timeline Manager - управление динамическим расчетом расстояний
	 */
	class TimelineManager {
		constructor() {
			this.timelineContainer = null;
			this.timelineDots = null;
			this.timelineEvents = null;
			this.observer = null;
			this.init();
		}

		/**
		 * Инициализация
		 */
		init() {
			this.timelineContainer = document.querySelector('.timeline-container');
			if (!this.timelineContainer) {
				return;
			}

			this.timelineDots = this.timelineContainer.querySelector('.timeline-dots');
			this.timelineEvents = this.timelineContainer.querySelector('.timeline-events');

			// Первоначальный расчет
			this.recalculateDistances();

			// Наблюдение за изменениями DOM (для динамического добавления точек)
			this.observeChanges();

			// Инициализация hover эффектов
			this.initHoverEffects();
		}

		/**
		 * Пересчитать расстояния между точками
		 */
		recalculateDistances() {
			if (!this.timelineDots) {
				return;
			}

			// Получить все точки
			const dots = this.timelineDots.querySelectorAll('.timeline-dot');
			const totalItems = dots.length;

			// Обновить переменную количества элементов
			this.timelineDots.style.setProperty('--timeline-items', totalItems);

			// Пересчитать позицию каждой точки
			dots.forEach((dot, index) => {
				// Первая и последняя в 5% от края, остальные равномерно между ними
				const percentage = 5 + (index / Math.max(1, totalItems - 1)) * 90;
				dot.style.left = percentage + '%';
				dot.style.setProperty('--dot-position', index + 1);
			});

			// Пересчитать позиции event'ов
			if (this.timelineEvents) {
				this.timelineEvents.style.setProperty('--timeline-items', totalItems);
				const events = this.timelineEvents.querySelectorAll('.timeline-event');
				events.forEach((event, index) => {
					// Первая и последняя в 5% от края, остальные равномерно между ними
					const percentage = 5 + (index / Math.max(1, totalItems - 1)) * 90;
					event.style.left = percentage + '%';
					event.style.setProperty('--dot-position', index + 1);
				});
			}

			// Пересчитать позиции бейджей с годами
			const yearBadges = this.timelineContainer.querySelector('.timeline-years-badges');
			if (yearBadges) {
				const badges = yearBadges.querySelectorAll('.timeline-year-badge');
				badges.forEach((badge, index) => {
					// Первая и последняя в 5% от края, остальные равномерно между ними
					const percentage = 5 + (index / Math.max(1, totalItems - 1)) * 90;
					badge.style.left = percentage + '%';
				});
			}
		}

		/**
		 * Инициализация hover эффектов
		 */
		initHoverEffects() {
			if (!this.timelineDots || !this.timelineEvents) {
				return;
			}

			const dots = this.timelineDots.querySelectorAll('.timeline-dot');
			const events = this.timelineEvents.querySelectorAll('.timeline-event');
			const fillLine = this.timelineContainer.querySelector('.timeline-fill-line');

			if (!fillLine) {
				return;
			}

			const totalPoints = dots.length;

			// Функция для расчета позиции
			const calculateFillPercent = (pointIndex) => {
				return 5 + (pointIndex / Math.max(1, totalPoints - 1)) * 90;
			};

			// Синхронизируем точки с событиями по индексу
			dots.forEach((dot, index) => {
				if (!events[index]) {
					return;
				}

				const event = events[index];
				const tooltip = event.querySelector('.timeline-event__tooltip');

				// Убедимся, что элементы имеют правильные свойства
				dot.style.pointerEvents = 'auto';
				dot.style.cursor = 'pointer';
				dot.style.zIndex = '1000';

				// Hover на точку
				dot.addEventListener('mouseover', () => {
					// Показываем подсказку
					if (tooltip) {
						tooltip.classList.add('visible');
					}

					// Заполняем линию до этой точки
					const fillPercent = calculateFillPercent(index);
					fillLine.style.width = fillPercent + '%';
				});

				// Уход мыши с точки
				dot.addEventListener('mouseout', () => {
					if (tooltip) {
						tooltip.classList.remove('visible');
					}
				});

				// Также оставляем hover на саму подсказку
				event.addEventListener('mouseover', () => {
					if (tooltip) {
						tooltip.classList.add('visible');
					}

					const fillPercent = calculateFillPercent(index);
					fillLine.style.width = fillPercent + '%';
				});

				event.addEventListener('mouseout', () => {
					if (tooltip) {
						tooltip.classList.remove('visible');
					}
				});
			});

			// При уходе мыши со всей шкалы - убираем заполнение
			this.timelineContainer.addEventListener('mouseleave', () => {
				fillLine.style.width = '0%';
			});
		}

		/**
		 * Наблюдать за изменениями DOM
		 */
		observeChanges() {
			if (!this.timelineContainer || !window.MutationObserver) {
				return;
			}

			this.observer = new MutationObserver((mutations) => {
				// Проверить, были ли добавлены новые точки
				const hasDotsChanged = mutations.some(
					(mutation) =>
						mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0
				);

				if (hasDotsChanged) {
					// Пересчитать расстояния с небольшой задержкой
					setTimeout(() => {
						this.recalculateDistances();
						this.initHoverEffects();
					}, 100);
				}
			});

			// Наблюдать за добавлением/удалением элементов в .timeline-dots
			if (this.timelineDots) {
				this.observer.observe(this.timelineDots, {
					childList: true,
					subtree: false,
				});
			}

			// Наблюдать за добавлением/удалением элементов в .timeline-events
			if (this.timelineEvents) {
				this.observer.observe(this.timelineEvents, {
					childList: true,
					subtree: false,
				});
			}
		}

		/**
		 * Добавить новую точку на timeline
		 * @param {string} year - Год
		 * @param {string} event - Описание события
		 * @param {string} color - Цвет точки
		 */
		addTimelinePoint(year, event, color = '#900') {
			if (!this.timelineDots) {
				return;
			}

			// Создать новую точку
			const newDot = document.createElement('div');
			newDot.className = 'timeline-dot';
			newDot.style.backgroundColor = color;
			newDot.dataset.year = year;
			this.timelineDots.appendChild(newDot);

			// Создать новое событие если есть timeline-events
			if (this.timelineEvents && event) {
				const newEvent = document.createElement('div');
				const totalItems = this.timelineDots.querySelectorAll('.timeline-dot').length;
				newEvent.className =
					totalItems % 2 === 0 ? 'timeline-event--top' : 'timeline-event--bottom';
				newEvent.className += ' timeline-event';

				const tooltip = document.createElement('div');
				tooltip.className = 'timeline-event__tooltip';
				tooltip.style.borderTopColor = color;
				tooltip.textContent = event;
				newEvent.appendChild(tooltip);

				this.timelineEvents.appendChild(newEvent);
			}

			// Пересчитать расстояния
			this.recalculateDistances();
			this.initHoverEffects();
		}

		/**
		 * Удалить точку по индексу
		 * @param {number} index - Индекс точки
		 */
		removeTimelinePoint(index) {
			if (!this.timelineDots) {
				return;
			}

			const dots = this.timelineDots.querySelectorAll('.timeline-dot');
			if (dots[index]) {
				dots[index].remove();
			}

			// Удалить соответствующее событие
			if (this.timelineEvents) {
				const events = this.timelineEvents.querySelectorAll('.timeline-event');
				if (events[index]) {
					events[index].remove();
				}
			}

			// Пересчитать расстояния
			this.recalculateDistances();
			this.initHoverEffects();
		}

		/**
		 * Очистить все точки
		 */
		clearTimeline() {
			if (this.timelineDots) {
				this.timelineDots.innerHTML = '';
			}
			if (this.timelineEvents) {
				this.timelineEvents.innerHTML = '';
			}
		}

		/**
		 * Получить количество точек
		 * @returns {number}
		 */
		getPointCount() {
			if (!this.timelineDots) {
				return 0;
			}
			return this.timelineDots.querySelectorAll('.timeline-dot').length;
		}
	}

	/**
	 * Функция для smooth scroll к элементам
	 */
	function setupSmoothScroll() {
		const links = document.querySelectorAll('a[href^="#"]');
		links.forEach(link => {
			link.addEventListener('click', function(e) {
				const href = this.getAttribute('href');
				if (href === '#') return;

				const target = document.querySelector(href);
				if (target) {
					e.preventDefault();
					target.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});
				}
			});
		});
	}

	/**
	 * Функция для инициализации видимости элементов при скролле
	 */
	function setupIntersectionObserver() {
		if (!('IntersectionObserver' in window)) {
			return;
		}

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					entry.target.style.opacity = '1';
					entry.target.style.transform = 'translateY(0)';
				}
			});
		}, {
			threshold: 0.1,
			rootMargin: '0px 0px -100px 0px'
		});

		// Наблюдать за секциями
		const sections = document.querySelectorAll('.history-intro, .timeline-section, .achievements-section, .stadiums-section');
		sections.forEach(section => {
			section.style.opacity = '0';
			section.style.transform = 'translateY(20px)';
			section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
			observer.observe(section);
		});
	}

	/**
	 * Загрузить данные из REST API
	 */
	window.arsenal = window.arsenal || {};

	window.arsenal.loadHistoryData = function(callback) {
		var url = (typeof arsenalHistoryData !== 'undefined') ? arsenalHistoryData.restUrl : '/wp-json/arsenal/v1/history-data';
		fetch(url)
			.then(response => {
				if (!response.ok) {
					throw new Error('HTTP error ' + response.status);
				}
				return response.json();
			})
			.then(data => {
				if (callback && typeof callback === 'function') {
					callback(data);
				}
			})
			.catch(error => {
				console.error('Error loading history data:', error);
			});
	};

	/**
	 * Инициализация при загрузке документа
	 */
	function initPage() {
		// Инициализировать Timeline Manager
		if (document.readyState === 'loading') {
			window.addEventListener('DOMContentLoaded', () => {
				window.timelineManager = new TimelineManager();
			});
		} else {
			window.timelineManager = new TimelineManager();
		}

		// Smooth scroll
		setupSmoothScroll();

		// Intersection Observer для анимации секций
		setupIntersectionObserver();
	}

	// Запустить инициализацию
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initPage);
	} else {
		initPage();
	}

	// Экспортировать класс для использования
	window.TimelineManager = TimelineManager;

})();
