/**
 * Sponsors Carousel Auto-Scroll
 * @package Arsenal
 */

document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('sponsors-carousel');

    if (!carousel) {
        return;
    }

    // Auto-scroll settings
    const scrollDistance = 300; // pixels to scroll
    const scrollInterval = 4000; // milliseconds between scrolls
    let scrollTimer;

    /**
     * Auto-scroll carousel to the right
     */
    function autoScroll() {
        carousel.scrollBy({
            left: scrollDistance,
            behavior: 'smooth'
        });

        // Reset to beginning when reaching the end
        setTimeout(function() {
            if (carousel.scrollLeft >= carousel.scrollWidth - carousel.clientWidth - 10) {
                carousel.scrollLeft = 0;
            }
        }, 500); // Wait for smooth scroll to finish
    }

    /**
     * Start auto-scroll
     */
    function startAutoScroll() {
        scrollTimer = setInterval(autoScroll, scrollInterval);
    }

    /**
     * Pause auto-scroll on hover
     */
    carousel.addEventListener('mouseenter', function() {
        clearInterval(scrollTimer);
    });

    /**
     * Resume auto-scroll on mouse leave
     */
    carousel.addEventListener('mouseleave', function() {
        startAutoScroll();
    });

    /**
     * Pause on manual scroll
     */
    let scrollTimeout;
    carousel.addEventListener('scroll', function() {
        clearInterval(scrollTimer);
        clearTimeout(scrollTimeout);

        scrollTimeout = setTimeout(function() {
            startAutoScroll();
        }, 3000); // Resume after 3 seconds of no scrolling
    });

    // Start initial auto-scroll
    startAutoScroll();
});

