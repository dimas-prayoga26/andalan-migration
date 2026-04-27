jQuery(function ($) {
    var $dashboardMenuCarousel = $('.dashboard-menu-carousel');
    var $mainWrapper = $('#main-wrapper');
    var resizeTimeoutId = null;
    var bootstrapTimeoutId = null;
    var bootstrapRetryCount = 0;
    var maxBootstrapRetry = 40;
    var lastViewportBucket = '';
    var lastCarouselWidth = 0;

    if (!$dashboardMenuCarousel.length || typeof $.fn.owlCarousel !== 'function') {
        return;
    }

    function getViewportWidth() {
        if (window.visualViewport && window.visualViewport.width) {
            return Math.round(window.visualViewport.width);
        }

        return Math.round(window.innerWidth || document.documentElement.clientWidth || 0);
    }

    function getCarouselWidth() {
        return Math.round($dashboardMenuCarousel.outerWidth() || $dashboardMenuCarousel.parent().width() || 0);
    }

    function getViewportBucket() {
        var viewportWidth = getViewportWidth();

        if (viewportWidth < 576) {
            return 'xs';
        }

        if (viewportWidth < 1200) {
            return 'mobile';
        }

        return 'desktop';
    }

    function destroyCarousel() {
        if (!$dashboardMenuCarousel.hasClass('owl-loaded')) {
            return;
        }

        $dashboardMenuCarousel.trigger('stop.owl.autoplay');
        $dashboardMenuCarousel.trigger('destroy.owl.carousel');
        $dashboardMenuCarousel.removeClass('owl-loaded owl-hidden');
        $dashboardMenuCarousel.find('.owl-stage-outer').children().unwrap();
        $dashboardMenuCarousel.find('.owl-nav, .owl-dots').remove();
    }

    function initCarousel() {
        destroyCarousel();

        $dashboardMenuCarousel.owlCarousel({
            loop: true,
            items: 1,
            slideBy: 1,
            autoWidth: false,
            margin: 10,
            nav: true,
            dots: false,
            smartSpeed: 450,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            touchDrag: true,
            mouseDrag: true,
            checkVisible: false,
            responsiveRefreshRate: 100,
            navText: [
                '<i class="bx bx-chevron-left"></i>',
                '<i class="bx bx-chevron-right"></i>'
            ],
            responsive: {
                0: {
                    margin: 8,
                    nav: true
                },
                1200: {
                    margin: 12,
                    nav: true
                }
            }
        });
    }

    function refreshCarousel(forceRebuild) {
        var currentCarouselWidth = getCarouselWidth();
        var currentViewportBucket = getViewportBucket();
        var viewportChanged = currentViewportBucket !== lastViewportBucket;
        var carouselWidthChanged = Math.abs(currentCarouselWidth - lastCarouselWidth) > 2;

        if (currentCarouselWidth <= 0) {
            return false;
        }

        if (forceRebuild || !$dashboardMenuCarousel.hasClass('owl-loaded') || viewportChanged) {
            initCarousel();
        } else if (carouselWidthChanged) {
            $dashboardMenuCarousel.trigger('refresh.owl.carousel');
        }

        lastViewportBucket = currentViewportBucket;
        lastCarouselWidth = currentCarouselWidth;
        $dashboardMenuCarousel.trigger('play.owl.autoplay', [5000]);

        return true;
    }

    function refreshWithDelay(forceRebuild, delay) {
        window.setTimeout(function () {
            refreshCarousel(!!forceRebuild);
        }, delay);
    }

    function isLayoutReady() {
        if (!$mainWrapper.length) {
            return true;
        }

        return $mainWrapper.hasClass('show');
    }

    function bootstrapCarousel() {
        if (refreshCarousel(true)) {
            refreshWithDelay(false, 140);
            refreshWithDelay(false, 360);
            refreshWithDelay(false, 900);
            return;
        }

        if (bootstrapRetryCount >= maxBootstrapRetry) {
            return;
        }

        bootstrapRetryCount += 1;
        clearTimeout(bootstrapTimeoutId);
        bootstrapTimeoutId = window.setTimeout(bootstrapCarousel, 120);
    }

    function startWhenReady() {
        if (!isLayoutReady()) {
            clearTimeout(bootstrapTimeoutId);
            bootstrapTimeoutId = window.setTimeout(startWhenReady, 120);
            return;
        }

        bootstrapRetryCount = 0;
        bootstrapCarousel();
    }

    function scheduleViewportRefresh(forceRebuild) {
        clearTimeout(resizeTimeoutId);
        resizeTimeoutId = window.setTimeout(function () {
            if (!isLayoutReady()) {
                startWhenReady();
                return;
            }

            refreshCarousel(!!forceRebuild);
        }, 180);
    }

    $(window).on('load pageshow', function () {
        startWhenReady();
    });

    $(window).on('resize orientationchange', function () {
        scheduleViewportRefresh(false);
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () {
            scheduleViewportRefresh(false);
        });
    }

    $(document).on('click', '.nav-control, .hamburger', function () {
        scheduleViewportRefresh(true);
    });

    $mainWrapper.on('transitionend', function () {
        scheduleViewportRefresh(false);
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            scheduleViewportRefresh(false);
        }
    });

    startWhenReady();
});
