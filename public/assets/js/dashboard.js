jQuery(function ($) {
    var $dashboardMenuCarousel = $('.dashboard-menu-carousel');
    var resizeTimeoutId = null;

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
            margin: 12,
            stagePadding: 8,
            nav: true
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

    function initOrRefreshCarousel() {
        if (!$dashboardMenuCarousel.hasClass('owl-loaded')) {
            $dashboardMenuCarousel.owlCarousel(carouselOptions);
        }

        $dashboardMenuCarousel.trigger('refresh.owl.carousel');
        $dashboardMenuCarousel.trigger('play.owl.autoplay', [5000]);
    }

    function handleViewportChange() {
        clearTimeout(resizeTimeoutId);
        resizeTimeoutId = window.setTimeout(initOrRefreshCarousel, 120);
    }

    $(window).on('resize orientationchange', handleViewportChange);

    initOrRefreshCarousel();
});
