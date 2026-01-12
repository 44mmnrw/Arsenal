/**
 * Lottie Player Interval Control
 * Проигрывает анимации lottie-player с интервалом каждые 3 секунды
 * Оптимизировано: один setInterval для всех плееров, отключается когда вкладка неактивна
 *
 * @package Arsenal
 * @since 1.0.0
 */

(function() {
	let isPageVisible = true;
	let intervalId = null;

	// Слушаем видимость вкладки
	document.addEventListener('visibilitychange', () => {
		isPageVisible = !document.hidden;

		if (isPageVisible && !intervalId) {
			startInterval(); // запустить если вернулись на вкладку
		} else if (!isPageVisible && intervalId) {
			clearInterval(intervalId); // остановить если ушли со вкладки
			intervalId = null;
		}
	});

	// Загрузка страницы
	document.addEventListener('DOMContentLoaded', () => {
		const players = document.querySelectorAll('lottie-player');

		if (players.length === 0) return;

		// Первый запуск для всех
		players.forEach(player => player.play());

		// Один setInterval для всех плееров
		function startInterval() {
			intervalId = setInterval(() => {
				players.forEach(player => {
					player.seek('0%'); // вернуть в начало
					player.play();     // запустить
				});
			}, 7000); // каждые 7 секунд
		}

		if (isPageVisible) {
			startInterval();
		}
	});
})();
