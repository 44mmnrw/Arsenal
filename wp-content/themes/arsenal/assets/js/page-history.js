/**
 * Scripts для страницы "История клуба"
 * 
 * @package Arsenal
 */

(function() {
    'use strict';

    // Инициализация при загрузке DOM
    document.addEventListener('DOMContentLoaded', function() {
        initTimeline();
    });

    /**
     * Инициализация временной шкалы
     */
    function initTimeline() {
        const progressLine = document.querySelector('.timeline__progress-line');
        const items = document.querySelectorAll('.timeline__item');
        
        if (!progressLine || items.length === 0) {
            return;
        }

        // Функция обновления прогресса
        function updateProgress() {
            const container = document.querySelector('.timeline__items');
            if (!container) return;

            const containerWidth = container.offsetWidth;
            const containerLeft = container.getBoundingClientRect().left;
            
            // Найти последнюю видимую точку (2025)
            const lastItem = items[items.length - 1];
            if (!lastItem) return;

            const itemRect = lastItem.getBoundingClientRect();
            const itemPosition = lastItem.offsetLeft + (lastItem.offsetWidth / 2);
            
            // Вычислить процент прогресса
            const progressPercent = (itemPosition / containerWidth) * 100;
            progressLine.style.width = Math.min(progressPercent, 100) + '%';
        }

        // Обновить прогресс при загрузке
        updateProgress();

        // Обновить при изменении размера окна
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateProgress, 250);
        });

        // Добавить интерактивность на годы
        items.forEach((item, index) => {
            item.addEventListener('click', function() {
                const year = 2018 + index;
                onYearClick(year);
            });

            // Добавить стиль при наведении
            item.addEventListener('mouseenter', function() {
                item.style.cursor = 'pointer';
                const dot = item.querySelector('.timeline__dot');
                if (dot) {
                    dot.style.transform = 'scale(1.2)';
                    dot.style.transition = 'transform 0.2s ease';
                }
            });

            item.addEventListener('mouseleave', function() {
                const dot = item.querySelector('.timeline__dot');
                if (dot) {
                    dot.style.transform = 'scale(1)';
                }
            });
        });
    }

    /**
     * Обработчик клика на год (опционально)
     * @param {number} year - Год
     */
    function onYearClick(year) {
        console.log('Year selected:', year);
        
        // Здесь можно добавить дополнительную функциональность:
        // - Загрузить информацию о событиях за год
        // - Показать модальное окно
        // - Прокрутить к нужной секции
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

    // Инициализация Intersection Observer при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        setupIntersectionObserver();
        setupSmoothScroll();
    });

    /**
     * Функция для получения информации о рекордах из API (если нужна динамическая загрузка)
     */
    window.arsenal = window.arsenal || {};
    
    window.arsenal.loadHistoryData = function(callback) {
        // Здесь можно добавить AJAX запрос к WordPress REST API
        // для загрузки динамических данных о рекордах и статистике
        
        fetch('/wp-json/arsenal/v1/history-data')
            .then(response => response.json())
            .then(data => {
                if (callback && typeof callback === 'function') {
                    callback(data);
                }
            })
            .catch(error => {
                console.error('Error loading history data:', error);
            });
    };

})();
