/**
 * Lottie Player Interval Control
 * Проигрывает анимации lottie-player с интервалом каждые 7 секунд
 * Оптимизировано: один setInterval для всех плееров, отключается когда вкладка неактивна
 *
 * @package Arsenal
 * @since 1.0.0
 */

(function() {
	let isPageVisible = true;
	let intervalId = null;
	const ANIMATION_SPEED = 1;     // Скорость воспроизведения
	const ANIMATION_INTERVAL = 7000; // Интервал повторения в мс

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

		// Применяем скорость ко всем плеерам
		players.forEach(player => {
			player.speed = ANIMATION_SPEED;
			player.play();
		});

		// Один setInterval для всех плееров
		function startInterval() {
			intervalId = setInterval(() => {
				players.forEach(player => {
					player.seek('0%'); // вернуть в начало
					player.play();     // запустить
				});
			}, ANIMATION_INTERVAL);
		}

		if (isPageVisible) {
			startInterval();
		}
	});
})();
