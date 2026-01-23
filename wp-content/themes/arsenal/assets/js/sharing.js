/**
 * Класс для управления социальным шарингом
 * Использует Web Share API на мобильных, веб-ссылки на десктопе
 * 
 * @class SocialSharing
 */
class SocialSharing {
	/**
	 * Конструктор класса
	 */
	constructor() {
		this.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
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
	 * @param {Event} e - Событие клика
	 */
	handleFacebookShare(e) {
		e.preventDefault();
		const url = e.currentTarget.dataset.url;
		const title = document.querySelector('.single-news-title')?.textContent || 'Arsenal';

		if (this.isMobile && navigator.share) {
			// Мобильные: Web Share API
			navigator.share({
				title: 'Arsenal Derzhinsk',
				text: title,
				url: url
			}).catch(err => {
				// Если пользователь отменил, просто выходим
				if (err.name !== 'AbortError') {
					console.log('Share failed:', err);
				}
			});
		} else {
			// Десктоп: открыть веб-диалог Facebook
			const shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
			window.open(shareUrl, 'facebook-share', 'width=600,height=400');
		}
	}

	/**
	 * Обработка VK шаринга
	 * @param {Event} e - Событие клика
	 */
	handleVKShare(e) {
		e.preventDefault();
		const url = e.currentTarget.dataset.url;
		const title = e.currentTarget.dataset.title || document.querySelector('.single-news-title')?.textContent || 'Arsenal';

		if (this.isMobile && navigator.share) {
			// Мобильные: Web Share API
			navigator.share({
				title: 'Arsenal Derzhinsk',
				text: title,
				url: url
			}).catch(err => {
				if (err.name !== 'AbortError') {
					console.log('Share failed:', err);
				}
			});
		} else {
			// Десктоп: открыть веб-диалог VK
			const shareUrl = 'https://vk.com/share.php?url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title);
			window.open(shareUrl, 'vk-share', 'width=600,height=400');
		}
	}
}

// Инициализировать при загрузке скрипта
new SocialSharing();
