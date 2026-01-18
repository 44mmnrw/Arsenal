/**
 * Динамический расчет расстояния между точками timeline
 * Автоматически пересчитывает расстояния при добавлении новых точек
 *
 * @package Arsenal
 * @since 1.0.0
 */

(function () {
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

    // Инициализировать при загрузке документа
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.timelineManager = new TimelineManager();
        });
    } else {
        window.timelineManager = new TimelineManager();
    }

    // Экспортировать для использования в других скриптах
    window.TimelineManager = TimelineManager;
})();
