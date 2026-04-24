jQuery(function ($) {
    var $dashboardMenuCarousel = $('.dashboard-menu-carousel');
    var mobileMediaQuery = window.matchMedia('(max-width: 1024px)');
    var currentMode = null;
    var resizeTimeoutId = null;

    if (!$dashboardMenuCarousel.length) {
        return;
    }

    var carouselOptions = {
        loop: false,
        autoWidth: false,
        margin: 12,
        nav: true,
        dots: false,
        smartSpeed: 450,
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
            slideBy: 3,
            margin: 10,
            stagePadding: 0,
            nav: true
        },
        1200: {
            items: 4,
            slideBy: 4,
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

    function applyMobileMode() {
        destroyCarouselIfNeeded();
        $dashboardMenuCarousel.addClass('mobile-snap');
    }

    function applyDesktopMode() {
        $dashboardMenuCarousel.removeClass('mobile-snap');

        if (!$dashboardMenuCarousel.hasClass('owl-loaded')) {
            $dashboardMenuCarousel.owlCarousel(carouselOptions);
        }

        $dashboardMenuCarousel.trigger('refresh.owl.carousel');
    }

    function syncCarouselMode() {
        var nextMode = mobileMediaQuery.matches ? 'mobile' : 'desktop';

        if (nextMode === currentMode) {
            if (nextMode === 'desktop' && $dashboardMenuCarousel.hasClass('owl-loaded')) {
                $dashboardMenuCarousel.trigger('refresh.owl.carousel');
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
