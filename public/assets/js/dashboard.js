jQuery(function ($) {
    var $dashboardMenuCarousel = $('.dashboard-menu-carousel');
    var mobileMediaQuery = window.matchMedia('(max-width: 1024px)');
    var currentMode = null;
    var resizeTimeoutId = null;
    var mobileAutoplayIntervalId = null;

    if (!$dashboardMenuCarousel.length) {
        return;
    }

    var carouselOptions = {
        loop: true,
        autoWidth: false,
        margin: 12,
        nav: true,
        dots: false,
        smartSpeed: 450,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        touchDrag: true,
        mouseDrag: true,
        navText: [
            '<i class="bx bx-chevron-left"></i>',
            '<i class="bx bx-chevron-right"></i>'
        ]
    };

    carouselOptions.responsive = {
        0: {
            items: 1,
            slideBy: 1,
            margin: 0,
            stagePadding: 0,
            nav: false
        },
        768: {
            items: 3,
            slideBy: 1,
            margin: 10,
            stagePadding: 0,
            nav: true
        },
        1200: {
            items: 4,
            slideBy: 1,
            margin: 12,
            stagePadding: 0,
            nav: true
        }
    };

    function destroyCarouselIfNeeded() {
        if ($dashboardMenuCarousel.hasClass('owl-loaded') || $dashboardMenuCarousel.data('owl.carousel')) {
            $dashboardMenuCarousel.trigger('destroy.owl.carousel');
        }
    }

    function stopMobileAutoplay() {
        if (mobileAutoplayIntervalId) {
            window.clearInterval(mobileAutoplayIntervalId);
            mobileAutoplayIntervalId = null;
        }
    }

    function startMobileAutoplay() {
        var carouselElement = $dashboardMenuCarousel.get(0);
        var totalSlides = $dashboardMenuCarousel.find('.dashboard-activity-item').length;

        stopMobileAutoplay();

        if (!carouselElement || totalSlides < 2) {
            return;
        }

        mobileAutoplayIntervalId = window.setInterval(function () {
            if (!mobileMediaQuery.matches || !$dashboardMenuCarousel.hasClass('mobile-snap')) {
                return;
            }

            var viewportWidth = carouselElement.clientWidth;
            var currentIndex = Math.round(carouselElement.scrollLeft / Math.max(viewportWidth, 1));
            var nextIndex = (currentIndex + 1) % totalSlides;

            carouselElement.scrollTo({
                left: nextIndex * viewportWidth,
                behavior: 'smooth'
            });
        }, 5000);
    }

    function applyMobileMode() {
        destroyCarouselIfNeeded();
        $dashboardMenuCarousel.addClass('mobile-snap');
        startMobileAutoplay();
    }

    function applyDesktopMode() {
        stopMobileAutoplay();
        $dashboardMenuCarousel.removeClass('mobile-snap');

        if (!$dashboardMenuCarousel.hasClass('owl-loaded')) {
            $dashboardMenuCarousel.owlCarousel(carouselOptions);
        }

        $dashboardMenuCarousel.trigger('refresh.owl.carousel');
        $dashboardMenuCarousel.trigger('play.owl.autoplay', [5000]);
    }

    function syncCarouselMode() {
        var nextMode = mobileMediaQuery.matches ? 'mobile' : 'desktop';

        if (nextMode === currentMode) {
            if (nextMode === 'mobile') {
                startMobileAutoplay();
            } else if ($dashboardMenuCarousel.hasClass('owl-loaded')) {
                $dashboardMenuCarousel.trigger('refresh.owl.carousel');
                $dashboardMenuCarousel.trigger('play.owl.autoplay', [5000]);
            }

            return;
        }

        currentMode = nextMode;

        if (nextMode === 'mobile') {
            applyMobileMode();
        } else {
            applyDesktopMode();
        }
    }

    function handleViewportChange() {
        clearTimeout(resizeTimeoutId);
        resizeTimeoutId = window.setTimeout(syncCarouselMode, 120);
    }

    if (typeof mobileMediaQuery.addEventListener === 'function') {
        mobileMediaQuery.addEventListener('change', syncCarouselMode);
    } else if (typeof mobileMediaQuery.addListener === 'function') {
        mobileMediaQuery.addListener(syncCarouselMode);
    }

    $(window).on('resize orientationchange', handleViewportChange);

    syncCarouselMode();
});
