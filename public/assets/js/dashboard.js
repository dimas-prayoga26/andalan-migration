jQuery(function ($) {
            var $dashboardMenuCarousel = $('.dashboard-menu-carousel');
            var isMobileLikeDevice = window.matchMedia('(max-width: 1024px)').matches;

            if (!$dashboardMenuCarousel.length) {
                return;
            }

            if (isMobileLikeDevice) {
                if ($dashboardMenuCarousel.hasClass('owl-loaded')) {
                    $dashboardMenuCarousel.trigger('destroy.owl.carousel');
                }

                $dashboardMenuCarousel.addClass('mobile-snap');
                return;
            }

            $dashboardMenuCarousel.removeClass('mobile-snap');

            if ($dashboardMenuCarousel.hasClass('owl-loaded')) {
                $dashboardMenuCarousel.trigger('destroy.owl.carousel');
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

            $dashboardMenuCarousel.owlCarousel(carouselOptions);
        });