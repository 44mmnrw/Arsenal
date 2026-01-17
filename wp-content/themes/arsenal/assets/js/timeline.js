/**
 * Timeline Interactive Handler
 * 
 * Показывает tooltip при наведении на точку временной шкалы
 * Заполняет линию цветом при наведении
 */

(function() {
	'use strict';

	function initTimeline() {
		const dots = document.querySelectorAll('.timeline-dot');
		const events = document.querySelectorAll('.timeline-event');
		const fillLine = document.querySelector('.timeline-fill-line');
		const timelineContainer = document.querySelector('.timeline-container');

		if (!dots.length || !events.length || !fillLine || !timelineContainer) {
			return;
		}

		const totalPoints = dots.length;

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

			// Функция для расчета позиции (используем ту же формулу, что и для точек)
			function calculateFillPercent(pointIndex) {
				return ((pointIndex + 1) / (totalPoints + 1)) * 100;
			}

			// Hover на точку
			dot.addEventListener('mouseover', function() {
				// Показываем подсказку
				if (tooltip) {
					tooltip.style.display = 'block';
					tooltip.style.opacity = '1';
				}

				// Заполняем линию до этой точки по правильной формуле
				const fillPercent = calculateFillPercent(index);
				fillLine.style.width = fillPercent + '%';
			});

			// Уход мыши с точки
			dot.addEventListener('mouseout', function() {
				if (tooltip) {
					tooltip.style.display = 'none';
					tooltip.style.opacity = '0';
				}
			});

			// Также оставляем hover на саму подсказку (если наводить прямо на неё)
			event.addEventListener('mouseover', function() {
				if (tooltip) {
					tooltip.style.display = 'block';
					tooltip.style.opacity = '1';
				}

				// Заполняем линию до этой точки по правильной формуле
				const fillPercent = calculateFillPercent(index);
				fillLine.style.width = fillPercent + '%';
			});

			event.addEventListener('mouseout', function() {
				if (tooltip) {
					tooltip.style.display = 'none';
					tooltip.style.opacity = '0';
				}
			});
		});

		// При уходе мыши со всей шкалы - убираем заполнение
		timelineContainer.addEventListener('mouseleave', function() {
			fillLine.style.width = '0%';
		});
	}

	// Инициализация при загрузке DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initTimeline);
	} else {
		initTimeline();
	}
})();
