/**
 * Класс для управления социальным шарингом
 * Использует простые шаринг-ссылки Facebook и VK
 * Работает везде: веб, мобильный браузер, приложения
 * 
 * @class SocialSharing
 */
class SocialSharing {
	/**
	 * Конструктор класса
	 */
	constructor() {
		this.init();
	}

	/**
	 * Инициализация слушателей событий
	 */
	init() {
		document.addEventListener('DOMContentLoaded', () => {
			this.bindShareButtons();
		});
	}

	/**
	 * Привязать обработчики к кнопкам шаринга
	 */
	bindShareButtons() {
		const facebookBtn = document.querySelector('[data-social="facebook"]');
		const vkBtn = document.querySelector('[data-social="vk"]');

		if (facebookBtn) {
			facebookBtn.addEventListener('click', (e) => this.handleFacebookShare(e));
		}

		if (vkBtn) {
			vkBtn.addEventListener('click', (e) => this.handleVKShare(e));
		}
	}

	/**
	 * Обработка Facebook шаринга
	 * Открывает веб-форму Facebook Sharer с ссылкой на страницу
	 * 
	 * @param {Event} e - Событие клика
	 */
	handleFacebookShare(e) {
		e.preventDefault();
		const url = e.currentTarget.dataset.url;
		const quote = document.querySelector('.single-news-title')?.textContent || 'Arsenal Derzhinsk';
		
		// Facebook Sharer - работает везде (веб, мобиль, приложение)
		const shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '&quote=' + encodeURIComponent(quote);
		
		window.open(shareUrl, 'facebook-share', 'width=600,height=400');
	}

	/**
	 * Обработка VK шаринга
	 * Открывает веб-форму VK Share с ссылкой на страницу
	 * 
	 * @param {Event} e - Событие клика
	 */
	handleVKShare(e) {
		e.preventDefault();
		const url = e.currentTarget.dataset.url;
		const title = e.currentTarget.dataset.title || document.querySelector('.single-news-title')?.textContent || 'Arsenal Derzhinsk';
		
		// VK Share - работает везде (веб, мобиль, приложение)
		const shareUrl = 'https://vk.com/share.php?url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title);
		
		window.open(shareUrl, 'vk-share', 'width=600,height=400');
	}
}

// Инициализировать при загрузке скрипта
new SocialSharing();
