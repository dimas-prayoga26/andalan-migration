@include('layouts.mainhead')

<body>

    @include('layouts.loader')

    <!-- Start - Main Wrapper -->
    <div id="main-wrapper">

        <!-- Start - Nav header -->
         <div class="nav-header">
            <a href="{{ url('/') }}" class="brand-logo" aria-label="Andalan Bersama Group">
                <img class="logo-mobile" src="{{ asset('images/favicon.png') }}" alt="Andalan Bersama Group Icon" width="48">
                <img class="logo-desktop" src="{{ asset('images/logo.png') }}" alt="Andalan Bersama Group Logo">
            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!-- End - Nav header -->
        @include('layouts.header')

        @include('layouts.sidebar')

        <main class="content-body">
			<div class="container-fluid">

                @yield('content')

            </div>
       	</main>

		@include('layouts.footer')

   </div>
    <!-- End - Main Wrapper -->
	@include('layouts.commonjs')

	<script>
		function carouselReview(){
			if (typeof jQuery.fn.owlCarousel === 'undefined') {
				return;
			}

			if (jQuery('.testimonial-one').length === 0) {
				return;
			}

			/*  testimonial one function by = owl.carousel.js */
			jQuery('.testimonial-one').owlCarousel({
				nav:true,
				loop:true,
				autoplay:true,
				margin:30,
				dots: false,
				rtl: true,
				navText: ['<i class="fa fa-chevron-left" aria-hidden="true"></i>', '<i class="fa fa-chevron-right" aria-hidden="true"></i>'],
				responsive:{
					0:{
						items:1
					},
					484:{
						items:2
					},
					882:{
						items:3
					},	
					1200:{
						items:2
					},			
					
					1540:{
						items:3
					},
					1740:{
						items:4
					}
				}
			})			
		}
		jQuery(window).on('load',function(){
			setTimeout(function(){
				carouselReview();
			}, 1000); 
		});
	</script>

	@yield('script')
</body>

</html>
