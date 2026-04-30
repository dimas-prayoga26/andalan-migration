@include('layouts.mainhead')

<body>

    {{-- @include('layouts.loader') --}}

    @php
        $isSuperuserLogoShift = auth()->user()?->hasRole('superuser') ?? false;
    @endphp

    <!-- Start - Main Wrapper -->
    <div id="main-wrapper" class="{{ $isSuperuserLogoShift ? 'superuser-logo-shift' : '' }}">

        <!-- Start - Nav header -->
         @php
            $companyLogoFilename = trim((string) (auth()->user()?->company?->images ?? ''));
            $companyDisplayName = trim((string) (auth()->user()?->company?->name ?? ''));
            $companyLogoPublicPath = $companyLogoFilename !== ''
                ? public_path('assets/logo_perusahaan/'.$companyLogoFilename)
                : null;
            $companyLogoUrl = $companyLogoPublicPath !== null && file_exists($companyLogoPublicPath)
                ? asset('assets/logo_perusahaan/'.rawurlencode($companyLogoFilename))
                : asset('images/logo.png');
            $companyMobileLogoUrl = $companyLogoPublicPath !== null && file_exists($companyLogoPublicPath)
                ? $companyLogoUrl
                : asset('images/favicon.png');
        @endphp
         <div class="nav-header">
            <a href="{{ route('home') }}" class="brand-logo" aria-label="Andalan Bersama Group">
                <img class="logo-mobile" src="{{ $companyMobileLogoUrl }}" alt="Andalan Bersama Group Icon">
                <img class="logo-desktop" src="{{ $companyLogoUrl }}" alt="Andalan Bersama Group Logo">
                <span class="logo-company-name">
                    {{ $companyDisplayName !== '' ? $companyDisplayName : 'Andalan Bersama Group' }}
                </span>
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
