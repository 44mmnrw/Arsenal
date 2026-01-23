/**
 * Customizer Controls JavaScript
 * АКТИВНО держит Arsenal панель открытой
 */

console.log('=== CUSTOMIZER-CONTROLS.JS ЗАГРУЖЕН ===');

(function($, api) {
	'use strict';

	api.bind('ready', function() {
		console.log('=== Customizer ready ===');
		
		// ПОКАЗЫВАЕМ ВСЕ ПАНЕЛИ
		console.log('=== СПИСОК ВСЕХ ПАНЕЛЕЙ ===');
		api.panel.each(function(panel) {
			console.log('Панель:', panel.id, '- Заголовок:', panel.params.title);
		});
		console.log('=== КОНЕЦ СПИСКА ПАНЕЛЕЙ ===');
		
		// Получаем Arsenal панель
		var bannerPanel = api.panel('arsenal_banner_panel');
		console.log('Arsenal панель найдена?', bannerPanel ? 'ДА' : 'НЕТ');
		
		if (bannerPanel) {
			console.log('Arsenal панель params:', bannerPanel.params);
			
			// Функция принудительного открытия через DOM
			function forceExpandDOM() {
				var panelElement = $('#accordion-panel-arsenal_banner_panel');
				if (panelElement.length > 0) {
					if (!panelElement.hasClass('open')) {
						console.log('DOM: Принудительно добавляю класс open');
						// Напрямую добавляем классы вместо клика
						panelElement.addClass('open').addClass('expanded');
						panelElement.find('.accordion-section-content').first().show();
					}
				}
			}
			
			// Блокируем клики по заголовку панели (чтобы не закрывалась)
			$(document).on('click', '#accordion-panel-arsenal_banner_panel > .accordion-section-title', function(e) {
				console.log('БЛОКИРОВАН клик по заголовку Arsenal панели');
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				return false;
			});
			
			// АВТОМАТИЧЕСКИ ОТКРЫВАЕМ панель через API
			setTimeout(function() {
				bannerPanel.expand({ duration: 0 });
				console.log('Arsenal панель ОТКРЫТА автоматически');
				
				// Также открываем через DOM после задержки
				setTimeout(forceExpandDOM, 200);
			}, 100);
			
			// Следим за previewer ready (когда iframe загружается)
			api.previewer.bind('ready', function() {
				console.log('Preview загружен, переоткрываю панель');
				setTimeout(function() {
					bannerPanel.expand({ duration: 0 });
					forceExpandDOM();
				}, 100);
			});
			
			// ПОСТОЯННО проверяем DOM каждые 100ms
			setInterval(function() {
				var panelElement = $('#accordion-panel-arsenal_banner_panel');
				if (panelElement.length > 0 && !panelElement.hasClass('open')) {
					console.log('ПАНЕЛЬ ЗАКРЫЛАСЬ (DOM)! Открываю снова!');
					bannerPanel.expand({ duration: 0 });
					forceExpandDOM();
				}
			}, 100);
			
			// Блокируем collapse
			bannerPanel.collapse = function(params) {
				console.log('БЛОКИРОВАНО вызов collapse()');
				return;
			};
			
			// Следим за изменением expanded и НЕМЕДЛЕННО восстанавливаем
			bannerPanel.expanded.bind(function(isExpanded) {
				console.log('Arsenal панель expanded изменён на:', isExpanded);
				if (!isExpanded) {
					console.log('ПРИНУДИТЕЛЬНО ОТКРЫВАЮ панель обратно');
					setTimeout(function() {
						bannerPanel.expand({ duration: 0 });
					}, 1);
				}
			});
			
			console.log('Arsenal панель защищена');
		} else {
			console.error('!!! Arsenal панель НЕ НАЙДЕНА В JAVASCRIPT !!!');
		}
	});

})(jQuery, wp.customize);
